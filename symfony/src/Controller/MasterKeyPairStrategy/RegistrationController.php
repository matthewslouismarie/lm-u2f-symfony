<?php

namespace App\Controller\MasterKeyPairStrategy;

use App\Factory\MemberFactory;
use App\Form\U2FTokenRegistrationType;
use App\Form\RegistrationType;
use App\FormModel\RegistrationSubmission;
use App\FormModel\U2FTokenRegistration;
use App\Service\U2FTokenRegistrationService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @todo Add route prefix here.
 */
class RegistrationController extends AbstractController
{
    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
        $this->session->start();
    }

    /**
     * @Route("/mkps/registration", name="mkps_registration", methods={"GET"})
     */
    public function fetchLandingPage()
    {
        return $this->render('mkps/registration.html.twig');
    }

    /**
     * @Route(
     *  "/tks/username-and-password",
     *  name="tks_username_and_password",
     *  methods={"GET", "POST"})
     */
    public function usernameAndPassword(
        MemberFactory $mf,
        Request $request)
    {
        $this->session->start();
        ob_start();
        var_dump($this->session->get('tks_member'));
        $m = ob_get_clean();
        $submission = new RegistrationSubmission();
        $form = $this->createForm(RegistrationType::class, $submission);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $member = $mf->create(
                $submission->getUsername(),
                $submission->getPassword()
            );
            $this->session->set('tks_member', $member);
            return $this->render('tks/tmp.html.twig');
        }

        return $this->render('tks/username_and_password.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route(
     *  "/tks/first-key",
     *  name="mkps_first_key",
     *  methods={"GET", "POST"})
     */
    public function firstKey(
        Request $request,
        U2FTokenRegistrationService $service)
    {
        if (null === $this->session->get('tks_member')) {
            return new RedirectResponse(
                $this->generateUrl('tks_username_and_password')
            );
        }

        $rp_request = $service->generate();

        $submission = new U2FTokenRegistration();

        if ('GET' === $request->getMethod()) {
            $submission->setRequestId($rp_request['request_id']);
        }

        $form = $this->createForm(U2FTokenRegistrationType::class, $submission);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $u2fToken = $service->getU2fTokenFromResponse(
                    $submission->getU2fTokenResponse(),
                    $this->session->get('tks_member'),
                    new \DateTimeImmutable(),
                    $submission->getRequestId()
                );
                $this->session->set('tks_first_u2f_token', $u2fToken);
                return new Response('Ça marche.');
            }
            catch (\TypeError $e) {
                $form->addError(new FormError('An error occured. Please try again.'));
            }
        }

        return $this->render('tks/first_key.html.twig', array(
            'request_json' => $rp_request['request_json'],
            'sign_requests' => $rp_request['sign_requests'],
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route(
     *  "/mkps/master-pair-second-key",
     *  name="mkps_master_pair_second_key",
     *  methods={"GET"})
     */
    public function fetchMkpsSecondKey()
    {
        $rp_request = $service->generate();

        $submission = new U2FTokenRegistration();
        $submission->setRequestId($rp_request['request_id']);

        $form = $this->createForm(U2FTokenRegistrationType::class, $submission);

        return $this->render('mkps/master_pair_first_key.html.twig', array(
            'request_json' => $rp_request['request_json'],
            'sign_requests' => $rp_request['sign_requests'],
            'form' => $form->createView(),
        ));
    }
}
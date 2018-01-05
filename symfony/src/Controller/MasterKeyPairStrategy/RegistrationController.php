<?php

namespace App\Controller\MasterKeyPairStrategy;

use App\Factory\MemberFactory;
use App\Form\U2FTokenRegistrationType;
use App\Form\RegistrationType;
use App\FormModel\RegistrationSubmission;
use App\FormModel\U2FTokenRegistration;
use App\Service\U2FService;
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
        return $this->render('tks/registration.html.twig');
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
        $submission = new RegistrationSubmission();
        $form = $this->createForm(RegistrationType::class, $submission);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $member = $mf->create(
                $submission->getUsername(),
                $submission->getPassword()
            );
            $this->session->set('tks_member', $member);
            $url = $this->generateUrl('tks_key', array('id' => 1));
            return new RedirectResponse($url);
        }

        return $this->render('tks/username_and_password.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route(
     *  "/tks/key-{id}",
     *  name="tks_key",
     *  requirements={"id"="\d+"},
     *  methods={"GET", "POST"})
     */
    public function key(
        Request $request,
        U2FTokenRegistrationService $service,
        int $id)
    {
        if (null === $this->session->get('tks_member') ||
        (1 !== $id && null === $this->session->get('tks_u2f_token_'.($id - 1)))) {
            return new RedirectResponse(
                $this->generateUrl('tks_username_and_password')
            );
        }
        
        if ('POST' === $request->getMethod()) {
            $submission = new U2FTokenRegistration();
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
                    $this->session->set('tks_u2f_token_'.$id, $u2fToken);
                    if ($id != U2FService::N_U2F_TOKENS_PER_MEMBER) {
                        $url = $this->generateUrl('tks_key', array(
                            'id' => $id + 1,
                        ));
                        return new RedirectResponse($url);
                    }
                }
                catch (\TypeError $e) {
                    $form->addError(new FormError('An error occured. Please try again.'));
                }
            }
        }

        $rp_request = $service->generate();
        $submission = new U2FTokenRegistration();
        $submission->setRequestId($rp_request['request_id']);
        $form = $this->createForm(U2FTokenRegistrationType::class, $submission);

        return $this->render('tks/key.html.twig', array(
            'request_json' => $rp_request['request_json'],
            'sign_requests' => $rp_request['sign_requests'],
            'form' => $form->createView(),
        ));
    }
}
<?php

namespace App\Controller;

use App\Factory\MemberFactory;
use App\Form\U2FTokenRegistrationType;
use App\Form\RegistrationType;
use App\Form\UserConfirmationType;
use App\FormModel\RegistrationSubmission;
use App\FormModel\U2FTokenRegistration;
use App\Service\U2FService;
use App\Service\U2FTokenRegistrationService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Persistence\ObjectManager;
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
        Request $request,
        SessionInterface $session)
    {
        $session->start();
        $submission = new RegistrationSubmission();
        $form = $this->createForm(RegistrationType::class, $submission);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $member = $mf->create(
                null,
                $submission->getUsername(),
                $submission->getPassword()
            );
            $session->set('tks_member', $member);
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
        SessionInterface $session,
        U2FTokenRegistrationService $service,
        ObjectManager $om,
        int $id)
    {
        if (null === $session->get('tks_member') ||
        (1 !== $id && null === $session->get('tks_u2f_token_'.($id - 1)))) {
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
                        $session->get('tks_member'),
                        new \DateTimeImmutable(),
                        $submission->getRequestId()
                    );
                    $session->set('tks_u2f_token_'.$id, $u2fToken);
                    if ($id != U2FService::N_U2F_TOKENS_PER_MEMBER) {
                        $url = $this->generateUrl('tks_key', array(
                            'id' => $id + 1,
                        ));
                        return new RedirectResponse($url);
                    } else {
                        $endUrl = $this->generateUrl('tks_finish_registration');
                        return new RedirectResponse($endUrl);
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

    /**
     * @Route(
     *  "/tks/finish-registration",
     *  name="tks_finish_registration",
     *  methods={"GET", "POST"})
     */
    public function finishRegistration(
        EntityManagerInterface $om,
        Request $request,
        SessionInterface $session)
    {
        if (null === $session
            ->get('tks_u2f_token_3')) {
            return new RedirectResponse(
                $this->generateUrl('tks_username_and_password')
            );
        }

        $form = $this->createForm(UserConfirmationType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $member = $session->get('tks_member');
            $u2fTokens = array();
            for ($i = 1; $i <= U2FService::N_U2F_TOKENS_PER_MEMBER; $i++) {
                $u2fTokens[] = $session->get('tks_u2f_token_'.$i);
            }

            for ($i = 1; $i <= U2FService::N_U2F_TOKENS_PER_MEMBER; $i++) {
                $session->remove('tks_u2f_token_'.$i);
            }
            $session->remove('tks_member');
            $session->save();

            $om->persist($member);
            for ($i = 0; $i < U2FService::N_U2F_TOKENS_PER_MEMBER; $i++) {
                $om->persist($u2fTokens[$i]);
            }
            $om->flush();
            
        }

        return $this->render('tks/finish_registration.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route(
     *  "/tks/reset-registration",
     *  name="tks_reset_registration",
     *  methods={"GET", "POST"})
     */
    public function reset(Request $request, SessionInterface $session)
    {
        if (null === $session->get('tks_member')) {
            $url = $this->generateUrl('mkps_registration');
            return new RedirectResponse($url);
        }

        $form = $this->createForm(UserConfirmationType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            for ($i = 1; $i <= U2FService::N_U2F_TOKENS_PER_MEMBER; $i++) {
                $session->remove('tks_u2f_token_'.$i);
            }
            $session->remove('tks_member');
            $session->save();

            $url = $this->generateUrl('mkps_registration');
            return new RedirectResponse($url);
        }

        return $this->render('tks/reset.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}
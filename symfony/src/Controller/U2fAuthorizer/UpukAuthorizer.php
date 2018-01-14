<?php

namespace App\Controller\U2fAuthorizer;

use App\Entity\Member;
use App\Exception\NonexistentMemberException;
use App\Form\U2fAuthenticationType;
use App\Form\UsernameAndPasswordType;
use App\FormModel\U2fAuthenticationSubmission;
use App\FormModel\UsernameAndPasswordSubmission;
use App\Model\IAuthorizationRequest;
use App\Model\AuthorizationRequest;
use App\Service\U2fAuthenticationManager;
use App\Service\SecureSession;
use Doctrine\Common\Persistence\ObjectManager;
use Firehed\U2F\SecurityException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * This class handles the authorisation of IAuthorizationRequest objects. UPUK
 * stands for Username, Password and U2F Key.
 */
class UpukAuthorizer extends AbstractController
{
    public function __construct(
        ObjectManager $om,
        UserPasswordEncoderInterface $encoder)
    {
        $this->om = $om;
        $this->encoder = $encoder;
    }

    /**
     * @todo Is all the good prefix for the route?
     * @todo Move username / password check in form.
     *
     * @Route(
     *  "/all/u2f-authorization/upuk/up/{sessionId}",
     *  name="u2f_authorization_upuk_up",
     *  methods={"GET", "POST"},
     *  requirements={"sessionId"=".+"})
     */
    public function upukUp(
        Request $request,
        SecureSession $sSession,
        string $sessionId)
    {
        $upSubmission = new UsernameAndPasswordSubmission();
        $form = $this->createForm(UsernameAndPasswordType::class, $upSubmission);
        $form->handleRequest($request);

        try {
            if ($form->isSubmitted() && $form->isValid()) {
                $this->checkAuthentication(
                    $upSubmission->getUsername(),
                    $upSubmission->getPassword());
                $upSubmissionId = $sSession->storeObject($upSubmission, UsernameAndPasswordSubmission::class);
                $url = $this->generateUrl('u2f_authorization_upuk_uk', array(
                    'sessionId' => $sessionId,
                    'upSubmissionId' => $upSubmissionId,
                ));

                return new RedirectResponse($url);
            }
        } catch (AuthenticationException $e) {
            $form->addError(new FormError('Invalid username or password.'));
        }

        return $this->render('registration/username_and_password.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @todo What if the username doesn't exist?
     * @todo What if the member doesn't have U2F tokens?
     *
     * @Route(
     *  "/all/u2f-authorization/upuk/uk/{sessionId}/{upSubmissionId}",
     *  name="u2f_authorization_upuk_uk",
     *  methods={"GET", "POST"},
     *  requirements={"sessionId"=".+", "upSubmissionId"=".+"})
     */
    public function upukUk(
        U2fAuthenticationManager $auth,
        Request $request,
        SecureSession $sSession,
        string $sessionId,
        string $upSubmissionId)
    {
        $upSubmission = $sSession
            ->getObject($upSubmissionId, UsernameAndPasswordSubmission::class);

        try {
            $u2fData = $auth->generate($upSubmission->getUsername());
        } catch (NonexistentMemberException $e) {
            return new Response('error');
        }
        $u2fSubmission = new U2fAuthenticationSubmission(
            $upSubmission->getUsername(),
            null,
            $u2fData['auth_id']);
        $form = $this->createForm(U2fAuthenticationType::class, $u2fSubmission);
        $form->handleRequest($request);

        try {
            if ($form->isSubmitted() && $form->isValid()) {
                $action = $sSession
                    ->getAndRemoveObject($sessionId, IAuthorizationRequest::class);
                if (!$action instanceof IAuthorizationRequest) {
                    return new Response('Sorry, an error happened');
                }

                $auth->processResponse(
                    $u2fSubmission->getU2fAuthenticationRequestId(),
                    $u2fSubmission->getUsername(),
                    $u2fSubmission->getU2fTokenResponse()
                );

                $validatedAction = new AuthorizationRequest(
                    true,
                    $action->getSuccessRoute(),
                    $u2fSubmission->getUsername());
                $authorizationRequestSid = $sSession->storeObject($validatedAction, IAuthorizationRequest::class);
                $url = $this->generateUrl($action->getSuccessRoute(), array(
                    'authorizationRequestSid' => $authorizationRequestSid,
                ));

                return new RedirectResponse($url);
            }
        } catch (SecurityException $e) {
            $form->addError(new FormError('Invalid U2F token response.'));
        } catch (AuthenticationException $e) {
            $form->addError(new FormError('Invalid U2F token response.'));
        }

        return $this->render('u2f_authorization/upuk/uk_authentication.html.twig', array(
            'form' => $form->createView(),
            'sign_requests_json' => $u2fData['sign_requests_json'],
        ));
    }

    private function checkAuthentication(string $username, string $password)
    {
        $member = $this
            ->om
            ->getRepository(Member::class)->findOneBy(array(
                'username' => $username,
        ));
        $isPasswordValid = $this
            ->encoder
            ->isPasswordValid($member, $password)
        ;
        if (null === $member || !$isPasswordValid) {
            throw new AuthenticationException();
        }
    }
}

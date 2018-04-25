<?php

declare(strict_types=1);

namespace App\Controller;

use App\Callback\Authentifier\MemberAuthenticationCallback;
use App\Entity\Member;
use App\Enum\Setting;
use App\Service\Authentifier\MiddlewareDecorator;
use App\Callback\Authentifier\FailureClosure;
use App\Service\AppConfigManager;
use App\Service\LoginForcer;
use LM\Common\Enum\Scalar;
use LM\Common\Model\ArrayObject;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use LM\AuthAbstractor\Challenge\CredentialChallenge;
use LM\AuthAbstractor\Challenge\ExistingUsernameChallenge;
use LM\AuthAbstractor\Challenge\U2fChallenge;
use LM\AuthAbstractor\Implementation\Callback;
use LM\AuthAbstractor\Model\AuthenticationProcess ;
use LM\AuthAbstractor\Model\AuthentifierResponse;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;
use Twig_Environment;

class LoginController extends AbstractController
{
    /**
     * @Route(
     *  "/not-authenticated/choose-authenticate",
     *  name="choose_authenticate")
     */
    public function chooseAuthentication(AppConfigManager $config)
    {
        if ($config->getBoolSetting(Setting::ALLOW_PWD_LOGIN)
        && $config->getBoolSetting(Setting::ALLOW_U2F_LOGIN)) {
            return $this->render('choose_authentication_method.html.twig');
        } elseif ($config->getBoolSetting(Setting::ALLOW_PWD_LOGIN)) {
            return new RedirectResponse($this->generateUrl('login_pwd'));
        } elseif ($config->getBoolSetting(Setting::ALLOW_U2F_LOGIN)) {
            return new RedirectResponse($this->generateUrl('login_u2f'));
        } else {
            return $this->render("messages/unspecified_error.html.twig");
        }
    }

    /**
     * @Route(
     *  "/not-authenticated/login/u2f/{sid}",
     *  name="login_u2f")
     */
    public function login(
        string $sid = null,
        DiactorosFactory $psr7Factory,
        LoginForcer $loginForcer,
        MiddlewareDecorator $decorator,
        MemberAuthenticationCallback $callback,
        FailureClosure $failure,
        Request $httpRequest,
        Twig_Environment $twig
    ) {
        if (null === $sid) {
            return $decorator->createProcess(
                $httpRequest->get('_route'),
                new ArrayObject([
                    CredentialChallenge::class,
                    U2fChallenge::class,
                ], Scalar::_STR)
            )
            ;
        } else {
            $manager = $this
                ->getDoctrine()
                ->getManager()
            ;

            return $decorator->updateProcess(
                $httpRequest,
                $sid,
                new Callback(
                    $failure->getClosure(),
                    function (AuthenticationProcess $authProcess) use ($loginForcer, $manager, $psr7Factory, $twig): ResponseInterface {
                        $loginForcer->logUserIn(new Request(), $manager
                            ->getRepository(Member::class)
                            ->findOneBy([
                                'username' => $authProcess->getUsername(),
                            ]))
                        ;

                        $httpResponse = $twig
                            ->render('messages/success.html.twig', [
                                'pageTitle' => 'Successful login',
                                'message' => 'You logged in successfully.',
                            ])
                        ;

                        return $psr7Factory
                            ->createResponse(new Response($httpResponse))
                        ;
                    }
                )
            );
        }
    }

    /**
     * @Route(
     *  "/not-authenticated/tmp-login/{sid}",
     *  name="login_u2f_u2f")
     */
    public function tmpLoginTwo(
        string $sid = null,
        MemberAuthenticationCallback $callback,
        MiddlewareDecorator $decorator,
        Request $httpRequest
    ) {
        if (null === $sid) {
            return $decorator->createProcess(
                $httpRequest->get('_route'),
                new ArrayObject([
                    ExistingUsernameChallenge::class,
                    U2fChallenge::class,
                    U2fChallenge::class,
                ], Scalar::_STR)
            )
            ;
        } else {
            return $decorator->updateProcess($httpRequest, $sid, $callback);
        }
    }

    /**
     * @Route(
     *  "/not-authenticated/login/pwd/{sid}",
     *  name="login_pwd")
     */
    public function pwdLogin(
        string $sid = null,
        MemberAuthenticationCallback $callback,
        MiddlewareDecorator $decorator,
        Request $httpRequest
    ) {
        if (null === $sid) {
            return $decorator->createProcess(
                $httpRequest->get('_route'),
                new ArrayObject([
                    CredentialChallenge::class,
                ], Scalar::_STR)
            )
            ;
        } else {
            return $decorator->updateProcess($httpRequest, $sid, $callback);
        }
    }

    /**
     * @Route(
     *  "/authenticated/logout",
     *  name="unauthenticate",
     *  methods={"GET", "POST"})
     */
    public function unauthenticate(Request $request)
    {
    }

    /**
     * @Route(
     *  "/authenticated/not-logged-out",
     *  name="not_logged_out",
     *  methods={"GET"})
     */
    public function notLoggedOut()
    {
        return $this->render('not_logged_out_error.html.twig');
    }
}

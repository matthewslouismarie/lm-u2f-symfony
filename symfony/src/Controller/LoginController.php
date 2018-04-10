<?php

namespace App\Controller;

use App\Callback\Authentifier\MemberAuthenticationCallback;
use App\Enum\Setting;
use App\Service\Authentifier\MiddlewareDecorator;
use App\Service\AppConfigManager;
use LM\Common\Enum\Scalar;
use LM\Common\Model\ArrayObject;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use LM\Authentifier\Challenge\CredentialChallenge;
use LM\Authentifier\Challenge\ExistingUsernameChallenge;
use LM\Authentifier\Challenge\U2fChallenge;

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
        MemberAuthenticationCallback $callback,
        MiddlewareDecorator $decorator,
        Request $httpRequest)
    {
        if (null === $sid) {
            return $decorator->createProcess(
                $callback,
                $httpRequest->get('_route'),
                new ArrayObject([
                    ExistingUsernameChallenge::class,
                    U2fChallenge::class,
                ], Scalar::_STR))
            ;
        } else {
            return $decorator->updateProcess($httpRequest, $sid);
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
        Request $httpRequest)
    {
        if (null === $sid) {
            return $decorator->createProcess(
                $callback,
                $httpRequest->get('_route'),
                new ArrayObject([
                    ExistingUsernameChallenge::class,
                    U2fChallenge::class,
                    U2fChallenge::class,
                ], Scalar::_STR))
            ;
        } else {
            return $decorator->updateProcess($httpRequest, $sid);
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
        Request $httpRequest)
    {
        if (null === $sid) {
            return $decorator->createProcess(
                $callback,
                $httpRequest->get('_route'),
                new ArrayObject([
                    CredentialChallenge::class,
                ], Scalar::_STR))
            ;
        } else {
            return $decorator->updateProcess($httpRequest, $sid);
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

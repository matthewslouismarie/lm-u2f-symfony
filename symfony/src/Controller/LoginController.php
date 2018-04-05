<?php

namespace App\Controller;

use App\Callback\Authentifier\MemberAuthenticationCallback;
use App\Enum\Setting;
use App\Exception\AccessDeniedException;
use App\Form\LoginRequestType;
use App\FormModel\CredentialAuthenticationSubmission;
use App\FormModel\LoginRequest;
use App\Form\UserConfirmationType;
use App\Model\AuthorizationRequest;
use App\Model\BooleanObject;
use App\Model\GrantedAuthorization;
use App\Repository\U2fTokenRepository;
use App\Repository\MemberRepository;
use App\Security\MemberAuthenticator;
use App\Service\Authentifier\Configuration;
use App\Service\Authentifier\MiddlewareDecorator;
use App\Service\AuthenticationManager;
use App\Service\AppConfigManager;
use App\Service\SecureSession;
use Firehed\U2F\Registration;
use LM\Authentifier\Controller\AuthenticationKernel;
use LM\Authentifier\Enum\AuthenticationProcess\Status;
use LM\Authentifier\Factory\AuthenticationProcessFactory;
use LM\Authentifier\Model\AuthenticationProcess;
use LM\Authentifier\Model\DataManager;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use UnexpectedValueException;

class LoginController extends AbstractController
{
    /**
     * @Route(
     *  "/not-authenticated/login/{sid}",
     *  name="login")
     */
    public function login(
        string $sid = null,
        MemberAuthenticationCallback $callback,
        MiddlewareDecorator $decorator,
        Request $httpRequest)
    {
        return $decorator->processRequest(
            $callback,
            $httpRequest,
            "login",
            $sid)
        ;
    }
}

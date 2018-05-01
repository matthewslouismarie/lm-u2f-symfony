<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\MemberRepository;
use App\Repository\U2fTokenRepository;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use LM\AuthAbstractor\Controller\AuthenticationKernel;
use LM\AuthAbstractor\Implementation\ApplicationConfiguration;
use LM\AuthAbstractor\Challenge\CredentialChallenge;
use LM\AuthAbstractor\Challenge\U2fChallenge;
use LM\AuthAbstractor\Implementation\Callback;
use LM\AuthAbstractor\Model\IMember;

/**
 * This is just a controller demonstrating the use of auth-abtsractor.
 */
class TmpController extends AbstractController
{
    /**
     * @Route(
     *  "/tmp",
     *  name="tmp")
     */
    public function tmp(
        DiactorosFactory $diactorosFactory,
        MemberRepository $repo,
        Request $httpRequest,
        U2fTokenRepository $u2fRepo
    ) {
        $kernel = new AuthenticationKernel(new ApplicationConfiguration(
            'https://localhost', // HTTPS URL of your app (for U2F)
            'https://localhost/assets', // Assets base URL
            function (string $username) use ($repo): ?IMember {
                return $repo->findOneBy([
                    'username' => $username,
                ]);
            }
        ));

        $authProcess = null;
        if (!isset($_SESSION['auth_process']) || null === $_SESSION['auth_process']) {
            $authProcess = $kernel
                ->getAuthenticationProcessFactory()
                ->createProcess(
                    [
                        CredentialChallenge::class, // class that is part of auth-abstractor
                        // U2fChallenge::class, // class that is part of auth-abstractor
                    ],
                    3,
                    null,
                    []
            );
        } else {
            $authProcess = $_SESSION['auth_process'];
        }

        $response = $kernel->processHttpRequest(
            $diactorosFactory->createRequest($httpRequest),
            $authProcess, // The $authProcess object just created or retrieved from session
            new Callback(
                function ($authProcess) use ($diactorosFactory) { // if the user fails authenticating
                    return $diactorosFactory->createResponse(
                        new Response('You tried too many login attempts!')
                    );
                },
                function ($authProcess) use ($diactorosFactory) { // if the user succeeds logging in
                    $_SESSION['logged_in'] = true;
                    return $diactorosFactory->createResponse(
                        new Response('You\'re logged in!')
                    );
                }
            )
        );

        // store new auth_process in session
        $_SESSION['auth_process'] = $response->getAuthenticationProcess();

        // display http response to user
        return $response->getHttpResponse();
    }

    /**
     * @Route(
     *  "/tmpreset",
     *  name="tmpreset")
     */
    public function tmpReset(
        DiactorosFactory $diactorosFactory,
        Request $httpRequest
    ) {
        $_SESSION['auth_process'] = null;
    }
}

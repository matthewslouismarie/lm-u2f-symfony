<?php

declare(strict_types=1);

namespace App\Controller;

use App\Enum\Setting;
use App\Service\AppConfigManager;
use App\Service\Authentifier\MiddlewareDecorator;
use LM\AuthAbstractor\Challenge\CredentialRegistrationChallenge;
use LM\AuthAbstractor\Challenge\U2fRegistrationChallenge;
use LM\Common\Enum\Scalar;
use LM\Common\Model\ArrayObject;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Callback\Authentifier\AccountCreationCallback;

class AccountCreationController extends AbstractController
{
    /**
     * @todo Service for route name?
     * @todo ArrayObject should acccept a type checker?
     * @Route(
     *  "/not-authenticated/account-creation/{sid}",
     *  name="account_creation")
     */
    public function createAccount(
        ?string $sid = null,
        AccountCreationCallback $callback,
        AppConfigManager $config,
        MiddlewareDecorator $middleware,
        Request $httpRequest
    ) {
        if (null === $sid) {
            return $middleware->createProcess(
                $httpRequest->get('_route'),
                $this->getChallenges($config)
            );
        }
        return $middleware->updateProcess($httpRequest, $sid, $callback);
    }

    /**
     * @return string[] An array of challenge class names.
     */
    private function getChallenges(AppConfigManager $config): ArrayObject
    {
        $u2fRegChallenges = [];
        $nChallenges = $config->getSetting(Setting::N_U2F_KEYS_REG, Scalar::_INT);
        for ($i = 0; $i < $nChallenges; ++$i) {
            $u2fRegChallenges[] = U2fRegistrationChallenge::class;
        }
        $challenges = array_merge(
            [
                CredentialRegistrationChallenge::class,
            ],
            $u2fRegChallenges
        );

        return new ArrayObject(
            $challenges,
            Scalar::_STR
        );
    }
}

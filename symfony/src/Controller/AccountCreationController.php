<?php

declare(strict_types=1);

namespace App\Controller;

use App\Callback\Authentifier\RegistrationCallback;
use App\DataStructure\TransitingDataManager;
use App\Enum\Setting;
use App\Factory\MemberFactory;
use App\Form\CredentialRegistrationType;
use App\Form\NewU2fRegistrationType;
use App\FormModel\CredentialRegistrationSubmission;
use App\FormModel\NewU2fRegistrationSubmission;
use App\Model\TransitingData;
use App\Service\AppConfigManager;
use App\Service\Authentifier\MiddlewareDecorator;
use App\Service\SecureSession;
use App\Service\U2fRegistrationManager;
use App\Service\U2fService;
use DateTimeImmutable;
use Doctrine\Common\Persistence\ObjectManager;
use Firehed\U2F\ClientErrorException;
use Firehed\U2F\RegisterRequest;
use Firehed\U2F\RegisterResponse;
use Firehed\U2F\Registration;
use LM\AuthAbstractor\Challenge\CredentialRegistrationChallenge;
use LM\AuthAbstractor\Challenge\U2fRegistrationChallenge;
use LM\Common\Enum\Scalar;
use LM\Common\Model\ArrayObject;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Callback\Authentifier\AccountCreationCallback;
use UnexpectedValueException;

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

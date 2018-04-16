<?php

namespace App\Service;

use App\DataStructure\TransitingDataManager;
use App\Enum\SecurityStrategy;
use App\Enum\Setting;
use App\Exception\IdentityChecker\BeingProcessedException;
use App\Exception\IdentityChecker\InvalidCheckerException;
use App\Exception\IdentityChecker\StartedIdentityCheckException;
use App\Exception\IdentityChecker\ProcessedException;
use LM\Common\Enum\Scalar;
use LM\Common\Model\ArrayObject;
use App\Model\IdentityVerificationRequest;
use LM\Common\Model\StringObject;
use App\Model\TransitingData;
use Exception;
use UnexpectedValueException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @todo Delete.
 */
class AuthenticationManager
{
    const NOT_PROCESSED = 0;

    const BEING_PROCESSED = 1;

    const PROCESSED = 2;

    private $config;

    private $router;

    private $secureSession;

    private $tokenStorage;

    public function __construct(
        AppConfigManager $config,
        RouterInterface $router,
        SecureSession $secureSession,
        TokenStorageInterface $tokenStorage)
    {
        $this->config = $config;
        $this->router = $router;
        $this->secureSession = $secureSession;
        $this->tokenStorage = $tokenStorage;
    }

    public function achieveOperation(string $sid, string $routeName): TransitingDataManager
    {
        $tdm = $this
            ->secureSession
            ->getObject($sid, TransitingDataManager::class)
        ;
        return $this->achieveOperationTdm($tdm, $routeName, $sid);
    }

    public function achieveOperationTdm(TransitingDataManager $tdm, string $routeName, string $sid): TransitingDataManager
    {
        $this->assertSuccessful($tdm);
        $this->assertNotProcessed($tdm);
        $newTdm = $tdm->replaceByKey(new TransitingData(
            'is_processed',
            $routeName,
            new Integer(self::PROCESSED)
        ));

        $this
            ->secureSession
            ->setObject(
                $sid,
                $newTdm,
                TransitingDataManager::class
            )
        ;

        return $newTdm;
    }

    public function assertNotProcessed(TransitingDataManager $tdm): void
    {
        $tdmStatus = $tdm
            ->getBy('key', 'is_processed')
            ->getOnlyValue()
            ->getValue(Integer::class)
            ->toInteger()
        ;
        if ($tdmStatus !== self::NOT_PROCESSED)  {
            if ($tdmStatus === self::BEING_PROCESSED) {
                throw new BeingProcessedException();
            } elseif ($tdmStatus === self::PROCESSED) {
                throw new ProcessedException();
            }
            throw new VerificationStatusException();
        }
    }

    /**
     * @todo Use a more specific exception.
     */
    public function assertSuccessful(TransitingDataManager $tdm): void
    {
        $identityVerified = $this->isIdentityCheckedFromObject($tdm);
        if (false === $identityVerified) {
            throw new Exception();
        }
    }

    public function assertUnitialized(TransitingDataManager $tdm): void
    {
        $currentCheckerIndexTdm = $tdm->getBy('key', 'current_checker_index');
        if (0 !== $currentCheckerIndexTdm->getSize()) {
            throw new StartedIdentityCheckException();
        }
    }

    public function assertValidRoute(string $routeName, TransitingDataManager $tdm): int
    {
        $checkerIndex = $tdm
            ->getBy('key', 'current_checker_index')
            ->getOnlyValue()
            ->getValue(Integer::class)
            ->toInteger()
        ;
        $checkers = $tdm
            ->getBy('key', 'checkers')
            ->getOnlyValue()
            ->getValue(ArrayObject::class)
            ->toArray()
        ;
        if ($routeName !== $checkers[$checkerIndex]) {
            throw new InvalidCheckerException();
        }

        return $checkerIndex;
    }

    /**
     * @todo Exception.
     */
    public function createHighSecurityAuthenticationProcess(
        string $callerRouteName,
        string $calleeRouteName,
        array $additionalData = []): IdentityVerificationRequest
    {
        switch ($this->config->getIntSetting(Setting::SECURITY_STRATEGY)) {
            case SecurityStrategy::U2F:
                return $this->create(
                    $callerRouteName,
                    [
                        'ic_u2f',
                        $calleeRouteName,
                    ],
                    $additionalData)
                ;

            case SecurityStrategy::PWD:
                return $this->create(
                    $calleeRouteName,
                    [
                        'ic_password',
                        $calleeRouteName,
                    ],
                    $additionalData)
                ;

            default:
                throw new Exception();
        }
    }

    /**
     * @todo Check that $routeName is a valid route and that $checkers is a
     * valid array of route names (string + existing route)?
     */
    public function create(
        string $routeName,
        array $checkers,
        array $additionalData = []): IdentityVerificationRequest
    {
        $tdm = $this->createTdm($additionalData, $checkers, $routeName);
        $sid = $this
            ->secureSession
            ->storeObject($tdm, TransitingDataManager::class)
        ;
        $url = $this
            ->router
            ->generate('ic_initialization', [
                'sid' => $sid,
            ])
        ;

        return new IdentityVerificationRequest($sid, $url);
    }

    private function createDefaultTdm(
        array $additionalData,
        array $checkers,
        string $routeName)
    {
        return (new TransitingDataManager())
            ->add(new TransitingData(
                'checkers',
                $routeName,
                new ArrayObject($checkers, Scalar::_STR)))
            ->add(new TransitingData(
                'additional_data',
                $routeName,
                new ArrayObject($additionalData, 'null')))
            ->add(new TransitingData(
                'is_processed',
                $routeName,
                new Integer(self::NOT_PROCESSED)
            ))
        ;
    }

    private function createTdm(
        array $additionalData,
        array $checkers,
        string $routeName)
    {
        if (false === in_array('ic_username', $checkers, true) &&
            false === in_array('ic_credential', $checkers, true)) {
            return $this
                ->createDefaultTdm($additionalData, $checkers, $routeName)
                ->add(new TransitingData(
                    'username',
                    $routeName,
                    new StringObject($this
                        ->tokenStorage
                        ->getToken()
                        ->getUser()
                        ->getUsername())))
            ;
        } else {
            return $this->createDefaultTdm($additionalData, $checkers, $routeName);
        }
    }

    public function getAdditionalData(TransitingDataManager $tdm): array
    {
        return $tdm
            ->getBy('key', 'additional_data')
            ->getOnlyValue()
            ->getValue(ArrayObject::class)
            ->toArray()
        ;
    }

    public function getUsername(TransitingDataManager $tdm): string
    {
        return $tdm
            ->getBy('key', 'username')
            ->getOnlyValue()
            ->getValue(StringObject::class)
            ->toString()
        ;
    }

    /**
     * @todo Dodgy.
     */
    public function isIdentityCheckedFromObject(TransitingDataManager $tdm): bool
    {
        try {
            $checkers = $tdm
                ->getBy('key', 'checkers')
                ->getOnlyValue()
                ->getValue(ArrayObject::class)
                ->toArray()
            ;
        }
        catch (UnexpectedValueException $e) {
            return false;
        }
        foreach ($checkers as $checker)
        {
            try {
                $valids = $tdm
                    ->getBy('route', $checker)
                    ->getBy('key', 'post_authentication')
                    ->toArray()
                ;
            } catch (UnexpectedValueException $e) {
               return false;
            }
            foreach ($valids as $valid) {
                if (true !== $valid->toBoolean()) {
                    return false;
                }
            }
        }
        return true;
    }

    public function setAsProcessed(TransitingDataManager $tdm, string $routeName): TransitingDataManager
    {
        return $tdm->replaceByKey(new TransitingData(
            'is_processed',
            $routeName,
            new Integer(self::PROCESSED)
        ));
    }
}

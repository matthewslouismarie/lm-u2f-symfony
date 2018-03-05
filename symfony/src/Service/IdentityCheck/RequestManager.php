<?php

namespace App\Service\IdentityCheck;

use App\DataStructure\TransitingDataManager;
use App\Model\ArrayObject;
use App\Model\IdentityRequest;
use App\Model\StringObject;
use App\Model\TransitingData;
use App\Service\SecureSession;
use App\Repository\U2fTokenRepository;
use Exception;
use UnexpectedValueException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @todo Rename to IdentityRequestManager?
 * @todo Magic string.
 * @todo Method for checking sid?
 */
class RequestManager
{
    private $router;

    private $secureSession;

    private $tokenStorage;

    public function __construct(
        RouterInterface $router,
        SecureSession $secureSession,
        TokenStorageInterface $tokenStorage)
    {
        $this->router = $router;
        $this->secureSession = $secureSession;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @todo Check that $routeName is a valid route and that $checkers is a
     * valid array of route names (string + existing route)?
     */
    public function create(
        string $routeName,
        array $checkers,
        array $additionalData = []): IdentityRequest
    {

        $tdm = (new TransitingDataManager())
            ->add(new TransitingData(
                'checkers',
                $routeName,
                new ArrayObject($checkers)))
            ->add(new TransitingData(
                'additional_data',
                $routeName,
                new ArrayObject($additionalData)))
        ;
        if (false === in_array('ic_username', $checkers, true) &&
            false === in_array('ic_credential', $checkers, true)) {
            $tdm = $tdm->add(new TransitingData(
                'username',
                $routeName,
                new StringObject($this
                    ->tokenStorage
                    ->getToken()
                    ->getUser()
                    ->getUsername())))
            ;
        }
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

        return new IdentityRequest($sid, $url);
    }

    public function getAdditionalData(string $sid): array
    {
        $tdm = $this
            ->secureSession
            ->getObject($sid, TransitingDataManager::class)
        ;
        return $tdm
            ->getBy('key', 'additional_data')
            ->getOnlyValue()
            ->getValue()
            ->toArray()
        ;
    }

    /**
     * @todo Use a more specific exception.
     */
    public function checkIdentityFromSid(string $sid): bool
    {
        $tdm = $this
            ->secureSession
            ->getObject($sid, TransitingDataManager::class)
        ;
        $identityVerified = $this->isIdentityCheckedFromObject($tdm);
        if (false === $identityVerified) {
            throw new Exception();
        }
    }

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
                    ->getBy('key', 'successful_authentication')
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
}

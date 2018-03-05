<?php

namespace App\Service\IdentityCheck;

use App\DataStructure\TransitingDataManager;
use App\Model\ArrayObject;
use App\Model\IdentityRequest;
use App\Model\TransitingData;
use App\Service\SecureSession;
use Symfony\Component\Routing\RouterInterface;

/**
 * @todo Rename to IdentityRequestManager?
 * @todo Magic string.
 * @todo Method for checking sid?
 */
class RequestManager
{
    private $router;

    private $secureSession;

    public function __construct(
        RouterInterface $router,
        SecureSession $secureSession)
    {
        $this->router = $router;
        $this->secureSession = $secureSession;
    }

    /**
     * @todo Check that $routeName is a valid route and that $checkers is a
     * valid array of route names (string + existing route)?
     */
    public function create(string $routeName, array $checkers): IdentityRequest
    {
        $tdm = (new TransitingDataManager())
            ->add(new TransitingData(
                'checkers',
                'initial_route',
                new ArrayObject($checkers)))
        ;
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
}

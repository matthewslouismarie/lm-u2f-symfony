<?php

namespace App\Controller\IdentityChecker;

use App\DataStructure\TransitingDataManager;
use App\Model\ArrayObject;
use App\Model\Integer;
use App\Model\TransitingData;
use App\Service\SecureSession;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;

class MasterChecker extends AbstractController
{
    /**
     * @Route(
     *  "/all/initiate-identity-check/{sid}",
     *  name="ic_initialization")
     */
    public function initiateIdentityCheck(
            string $sid,
            SecureSession $secureSession)
    {
        $tdm = $secureSession->getObject($sid, TransitingDataManager::class);
        $checkers = $tdm
            ->getBy('key', 'checkers')
            ->getOnlyValue()
            ->getValue(ArrayObject::class)
            ->toArray()
        ;
        $secureSession
            ->setObject(
                $sid,
                $tdm->add(new TransitingData(
                    'current_checker_index',
                    'ic_initialization',
                    new Integer(0)
                )),
                TransitingDataManager::class)
        ;

        return new RedirectResponse($this->generateUrl($checkers[0], [
            'sid' => $sid,
        ]));
    }
}

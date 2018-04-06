<?php

namespace App\Controller\IdentityChecker;

use App\DataStructure\TransitingDataManager;
use App\Exception\IdentityChecker\StartedIdentityCheckException;
use LM\Common\Model\ArrayObject;
use LM\Common\Model\IntegerObject;
use App\Model\TransitingData;
use App\Service\AuthenticationManager;
use App\Service\SecureSession;
use UnexpectedValueException;
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
        AuthenticationManager $idCheckManager,
        SecureSession $secureSession)
    {
        try {
            $tdm = $secureSession->getObject($sid, TransitingDataManager::class);
            $idCheckManager->assertUnitialized($tdm);
            $checkers = $tdm
                ->getBy('key', 'checkers')
                ->getOnlyValue()
                ->getValue(ArrayObject::class)
                ->toArray()
            ;

            $secureSession
                ->setObject(
                    $sid,
                    $tdm
                        ->add(new TransitingData(
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
        catch (StartedIdentityCheckException $e) {
            return $this->render('identity_checker/errors/already_started.html.twig');
        }
        catch (UnexpectedValueException $e) {
            return $this->render('identity_checker/errors/general_error.html.twig');
        }
    }
}

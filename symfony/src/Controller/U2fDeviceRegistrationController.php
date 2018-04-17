<?php

namespace App\Controller;

use App\Callback\Authentifier\U2fDeviceRegistrationCallback;
use App\DataStructure\TransitingDataManager;
use App\Entity\U2fToken;
use App\Enum\Setting;
use App\Form\NewU2fRegistrationType;
use App\FormModel\NewU2fRegistrationSubmission;
use App\Model\TransitingData;
use App\Service\AppConfigManager;
use App\Service\Authentifier\MiddlewareDecorator;
use App\Service\SecureSession;
use App\Service\U2fRegistrationManager;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Firehed\U2F\ClientErrorException;
use Firehed\U2F\RegisterRequest;
use LM\Authentifier\Challenge\U2fChallenge;
use LM\Authentifier\Challenge\U2fRegistrationChallenge;
use LM\Common\Enum\Scalar;
use LM\Common\Model\ArrayObject;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class U2fDeviceRegistrationController extends AbstractController
{
    /**
     * @Route(
     *  "/authenticated/add-u2f-device/{sid}",
     *  name="add_u2f_device")
     */
    public function addU2fDevice(
        string $sid = null,
        U2fDeviceRegistrationCallback $callback,
        MiddlewareDecorator $decorator,
        Request $httpRequest
    ) {
        if (null === $sid) {
            return $decorator->createProcess(
                $callback,
                $httpRequest->get('_route'),
                new ArrayObject([
                    U2fRegistrationChallenge::class,
                ], Scalar::_STR)
            )
            ;
        } else {
            return $decorator->updateProcess($httpRequest, $sid);
        }
    }
}

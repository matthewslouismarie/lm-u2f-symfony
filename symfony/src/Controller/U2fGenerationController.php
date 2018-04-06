<?php

namespace App\Controller;

use App\DataStructure\TransitingDataManager;
use App\Enum\Setting;
use App\Factory\MemberFactory;
use App\Form\CredentialRegistrationType;
use App\Form\NewU2fRegistrationType;
use App\Form\UserConfirmationType;
use App\FormModel\CredentialRegistrationSubmission;
use App\FormModel\NewU2fRegistrationSubmission;
use App\Model\TransitingData;
use App\Service\AppConfigManager;
use App\Service\SecureSession;
use App\Service\U2fRegistrationManager;
use App\Service\U2fService;
use DateTimeImmutable;
use Doctrine\Common\Persistence\ObjectManager;
use Firehed\U2F\ClientErrorException;
use Firehed\U2F\RegisterRequest;
use Firehed\U2F\RegisterResponse;
use Firehed\U2F\Registration;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use UnexpectedValueException;

class U2fGenerationController extends AbstractController
{
    public function __construct(AppConfigManager $appConfigManager)
    {
    }
}

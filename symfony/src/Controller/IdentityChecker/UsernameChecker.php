<?php

namespace App\Controller\IdentityChecker;

use App\DataStructure\TransitingDataManager;
use App\Form\CredentialAuthenticationType;
use App\FormModel\CredentialAuthenticationSubmission;
use App\Model\ArrayObject;
use App\Model\BooleanObject;
use App\Model\StringObject;
use App\Model\TransitingData;
use App\Service\SecureSession;
use App\Service\StatelessU2fAuthenticationManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\FormModel\ExistingUsernameSubmission;
use App\Form\ExistingUsernameType;
use App\FormModel\U2fAuthenticationRequest;

class UsernameChecker extends AbstractController
{
    /**
     * @Route(
     *  "/all/check-username/{sid}",
     *  name="ic_username")
     */
    public function checkUsername(
        string $sid,
        Request $httpRequest,
        SecureSession $secureSession,
        StatelessU2fAuthenticationManager $u2fAuthenticationManager)
    {
        $tdm = $secureSession->getObject($sid, TransitingDataManager::class);

        $submission = new ExistingUsernameSubmission();
        $form = $this->createForm(ExistingUsernameType::class, $submission);

        $form->handleRequest($httpRequest);
        if ($form->isSubmitted() && $form->isValid()) {
            $secureSession
                ->setObject(
                    $sid,
                    $tdm
                        ->add(new TransitingData(
                            'username',
                            'ic_username',
                            new StringObject($submission->getUsername())
                        )),
                    TransitingDataManager::class)
            ;

            return new RedirectResponse(
                $this->generateUrl(
                    $tdm
                        ->getBy('key', 'checkers')
                        ->getOnlyValue()
                        ->getValue(ArrayObject::class)
                        ->toArray()[1],
                    [
                        'sid' => $sid,
                    ]))
            ;
        }

        return $this->render('identity_checker/username.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}

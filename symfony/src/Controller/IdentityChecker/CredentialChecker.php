<?php

namespace App\Controller\IdentityChecker;

use App\DataStructure\TransitingDataManager;
use App\Form\CredentialAuthenticationType;
use App\FormModel\CredentialAuthenticationSubmission;
use App\Model\BooleanObject;
use App\Model\StringObject;
use App\Model\TransitingData;
use App\Service\SecureSession;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CredentialChecker extends AbstractController
{
    /**
     * @Route(
     *  "/all/check-credential/{sid}",
     *  name="ic_credential")
     */
    public function checkUsernameAndPassword(
        string $sid,
        Request $httpRequest,
        SecureSession $secureSession)
    {
        $tdm = $secureSession->getObject($sid, TransitingDataManager::class);
        $submission = new CredentialAuthenticationSubmission();
        $form = $this->createForm(
            CredentialAuthenticationType::class,
            $submission)
        ;

        $form->handleRequest($httpRequest);
        if ($form->isSubmitted() && $form->isValid()) {
            $successRoute = $tdm
                ->getBy('key', 'success_route')
                ->getOnlyValue()
                ->getValue()
                ->toString()
            ;
            $secureSession
                ->setObject(
                    $sid,
                    $tdm
                        ->add(new TransitingData(
                            'username',
                            'ic_credential',
                            new StringObject($submission->getUsername())
                        ))
                        ->add(new TransitingData(
                            'successful_authentication',
                            'ic_credential',
                            new BooleanObject(true)
                        )),
                    TransitingDataManager::class)
            ;

            return new RedirectResponse($this->generateUrl($successRoute, [
                'sid' => $sid
            ]));
        }

        return $this->render('identity_checker/credential.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}

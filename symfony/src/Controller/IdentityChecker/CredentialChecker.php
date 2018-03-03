<?php

namespace App\Controller\IdentityChecker;

use App\DataStructure\TransitingDataManager;
use App\Form\CredentialAuthenticationType;
use App\FormModel\CredentialAuthenticationSubmission;
use App\Model\ArrayObject;
use App\Model\BooleanObject;
use App\Model\Integer;
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
    public function checkCredential(
        string $sid,
        Request $httpRequest,
        SecureSession $secureSession)
    {
        $tdm = $secureSession->getObject($sid, TransitingDataManager::class);

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
        if ('ic_credential' !== $checkers[$checkerIndex]) {
            throw new Exception();
        }

        $submission = new CredentialAuthenticationSubmission();
        $form = $this->createForm(
            CredentialAuthenticationType::class,
            $submission)
        ;

        $form->handleRequest($httpRequest);
        if ($form->isSubmitted() && $form->isValid()) {
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
                        ))
                        ->filterBy('key', 'current_checker_index')
                        ->add(new TransitingData(
                            'current_checker_index',
                            'ic_credential',
                            new Integer($checkerIndex + 1))),
                    TransitingDataManager::class)
            ;

            return new RedirectResponse(
                $this->generateUrl(
                    $tdm
                        ->getBy('key', 'checkers')
                        ->getOnlyValue()
                        ->getValue(ArrayObject::class)
                        ->toArray()[$checkerIndex + 1],
                    [
                        'sid' => $sid,
                    ]))
            ;
        }

        return $this->render('identity_checker/credential.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}

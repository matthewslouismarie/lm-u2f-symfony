<?php

namespace App\Controller\IdentityChecker;

use App\DataStructure\TransitingDataManager;
use App\Exception\IdentityChecker\InvalidCheckerException;
use App\Form\CredentialAuthenticationType;
use App\Form\ExistingUsernameType;
use App\FormModel\CredentialAuthenticationSubmission;
use App\FormModel\ExistingUsernameSubmission;
use App\FormModel\U2fAuthenticationRequest;
use App\Model\ArrayObject;
use App\Model\BooleanObject;
use App\Model\Integer;
use App\Model\StringObject;
use App\Model\TransitingData;
use App\Service\IdentityVerificationRequestManager;
use App\Service\SecureSession;
use App\Service\StatelessU2fAuthenticationManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use UnexpectedValueException;

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
        IdentityVerificationRequestManager $idRequestManager,
        SecureSession $secureSession,
        StatelessU2fAuthenticationManager $u2fAuthenticationManager)
    {
        try {
            $checkerIndex = $idRequestManager->verifyRoute('ic_username', $sid);
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
                            ))
                            ->add(new TransitingData(
                                'successful_authentication',
                                'ic_u2f',
                                new BooleanObject(true)
                            ))
                            ->replaceByKey(new TransitingData(
                                'current_checker_index',
                                'ic_username',
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
    
            return $this->render('identity_checker/username.html.twig', [
                'form' => $form->createView(),
            ]);
        }
        catch (InvalidCheckerException $e) {
            /**
             * @todo Redirect to correct route instead.
             */
            return $this->render('identity_checker/errors/general_error.html.twig');
        }
        catch (UnexpectedValueException $e) {
            return $this->render('identity_checker/errors/general_error.html.twig');
        }
    }
}

<?php

namespace App\Controller\IdentityChecker;

use App\DataStructure\TransitingDataManager;
use App\Exception\IdentityChecker\InvalidCheckerException;
use App\Form\CredentialAuthenticationType;
use App\Form\NewU2fAuthenticationType;
use App\FormModel\CredentialAuthenticationSubmission;
use App\FormModel\NewU2fAuthenticationSubmission;
use App\FormModel\U2fAuthenticationRequest;
use App\Model\ArrayObject;
use App\Model\BooleanObject;
use App\Model\Integer;
use App\Model\StringObject;
use App\Model\TransitingData;
use App\Service\IdentityVerificationRequestManager;
use App\Service\SecureSession;
use App\Service\StatelessU2fAuthenticationManager;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use UnexpectedValueException;

class U2fChecker extends AbstractController
{
    /**
     * @Route(
     *  "/all/check-u2f/{sid}",
     *  name="ic_u2f")
     */
    public function checkU2f(
        string $sid,
        Request $httpRequest,
        IdentityVerificationRequestManager $idRequestManager,
        SecureSession $secureSession,
        StatelessU2fAuthenticationManager $u2fAuthenticationManager)
    {
        try {
            $tdm = $secureSession->getObject($sid, TransitingDataManager::class);
            $checkerIndex = $idRequestManager->assertValidRoute('ic_u2f', $tdm);

            $username = $tdm
                ->getBy('key', 'username')
                ->getOnlyValue()
                ->getValue(StringObject::class)
                ->toString()
            ;

            $usedU2fKeyIdsTdm = $tdm
                ->getBy('key', 'used_u2f_key_ids')
            ;
            $usedU2fKeyIds = (0 === $usedU2fKeyIdsTdm->getSize()) ? [] : $usedU2fKeyIdsTdm
                ->getOnlyValue()
                ->getValue(ArrayObject::class)
                ->toArray()
            ;
    
            $submission = new NewU2fAuthenticationSubmission();
            $form = $this->createForm(NewU2fAuthenticationType::class, $submission);

            $form->handleRequest($httpRequest);
            if ($form->isSubmitted() && $form->isValid()) {
                $u2fAuthenticationRequest = $tdm
                    ->getBy('key', 'u2f_authentication_request')
                    ->getOnlyValue()
                    ->getValue(U2fAuthenticationRequest::class)
                ;
                $usedU2fKeyIds[] = $u2fAuthenticationManager->processResponse(
                    $u2fAuthenticationRequest,
                    $username,
                    $submission->getU2fTokenResponse()
                );
    
                $secureSession
                    ->setObject(
                        $sid,
                        $tdm
                            ->add(new TransitingData(
                                'successful_authentication',
                                'ic_u2f',
                                new BooleanObject(true)
                            ))
                            ->filterBy('key', 'u2f_authentication_request')
                            ->replaceByKey(new TransitingData(
                                'current_checker_index',
                                'ic_u2f',
                                new Integer($checkerIndex + 1)))
                            ->replaceByKey(new TransitingData(
                                'used_u2f_key_ids',
                                'ic_u2f',
                                new ArrayObject($usedU2fKeyIds))),
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
            $u2fAuthenticationRequest = $u2fAuthenticationManager->generate($username, $usedU2fKeyIds);
            $secureSession->setObject(
                $sid,
                $tdm->add(new TransitingData(
                    'u2f_authentication_request',
                    'ic_u2f',
                    $u2fAuthenticationRequest)),
                TransitingDataManager::class)
            ;

            return $this->render('identity_checker/u2f.html.twig', [
                'form' => $form->createView(),
                'sign_requests_json' => $u2fAuthenticationRequest->getJsonSignRequests(),
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

<?php

namespace App\Controller\IdentityChecker;

use App\DataStructure\TransitingDataManager;
use App\Exception\IdentityChecker\InvalidCheckerException;
use App\Form\ValidPasswordType;
use App\FormModel\ValidPasswordSubmission;
use App\Model\ArrayObject;
use App\Model\BooleanObject;
use App\Model\Integer;
use App\Model\StringObject;
use App\Model\TransitingData;
use App\Service\AuthenticationManager;
use App\Service\SecureSession;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use UnexpectedValueException;

class PasswordChecker extends AbstractController
{
    /**
     * @Route(
     *  "/all/check-password/{sid}",
     *  name="ic_password")
     */
    public function checkCredential(
        string $sid,
        Request $httpRequest,
        AuthenticationManager $idRequestManager,
        SecureSession $secureSession)
    {
        try {
            $tdm = $secureSession->getObject($sid, TransitingDataManager::class);
            $checkerIndex = $idRequestManager->assertValidRoute('ic_password', $tdm);

            $submission = new ValidPasswordSubmission();
            $form = $this->createForm(
                ValidPasswordType::class,
                $submission)
            ;

            $form->handleRequest($httpRequest);
            if ($form->isSubmitted() && $form->isValid()) {
                $secureSession
                    ->setObject(
                        $sid,
                        $tdm
                            ->add(new TransitingData(
                                'successful_authentication',
                                'ic_password',
                                new BooleanObject(true)
                            ))
                            ->replaceByKey(new TransitingData(
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
    
            return $this->render('identity_checker/password.html.twig', [
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


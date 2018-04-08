<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Callback\Authentifier\MoneyTransferCallback;
use App\Exception\IdentityChecker\ProcessedException;
use App\Form\UserConfirmationType;
use App\Service\AuthenticationManager;
use App\Service\Authentifier\MiddlewareDecorator;
use App\Service\SecureSession;
use LM\Authentifier\Challenge\CredentialChallenge;
use LM\Authentifier\Challenge\PasswordChallenge;
use LM\Authentifier\Challenge\U2fChallenge;
use LM\Common\Model\ArrayObject;

class MoneyTransferController extends AbstractController
{
    private $requestManager;

    public function __construct(
        AuthenticationManager $requestManager)
    {
        $this->requestManager = $requestManager;
    }

    /**
     * @Route(
     *  "/authenticated/transfer-money/{sid}",
     *  name="transfer_money")
     */
    public function transferMoney(
        string $sid = null,
        MiddlewareDecorator $decorator,
        MoneyTransferCallback $callback,
        Request $httpRequest)
    {
        if (null === $sid) {
            $form = $this->createForm(UserConfirmationType::class);

            $form->handleRequest($httpRequest);
            if ($form->isSubmitted() && $form->isValid()) {
                return $decorator->createProcess(
                    $callback,
                    $httpRequest->get('_route'),
                    new ArrayObject([
                        PasswordChallenge::class,
                    ], 'string'),
                    $this->getUser()->getUsername());
            }

            return $this->render('transfer_money.html.twig', [
                'form' => $form->createView(),
            ]);
        } else {
            return $decorator->updateProcess($httpRequest, $sid);
        }
    }
}
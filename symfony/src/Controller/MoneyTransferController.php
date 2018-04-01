<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Exception\IdentityChecker\ProcessedException;
use App\Form\UserConfirmationType;
use App\Service\AuthenticationManager;
use App\Service\SecureSession;

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
     *  "/authenticated/transfer-money",
     *  name="transfer_money")
     */
    public function transferMoney(Request $httpRequest)
    {
        $form = $this->createForm(UserConfirmationType::class);

        $form->handleRequest($httpRequest);
        if ($form->isSubmitted() && $form->isValid()) {
            $req = $this
                ->requestManager
                ->createHighSecurityAuthenticationRequest(
                    'transfer_money',
                    'complete_money_transfer')
            ;

            return new RedirectResponse($req->getUrl());
        }

        return $this->render('transfer_money.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route(
     *  "/authenticated/complete-money-transfer/{sid}",
     *  name="complete_money_transfer")
     */
    public function completeMoneyTransfer(string $sid)
    {
        try {
            $tdm = $this
                ->requestManager
                ->achieveOperation($sid, 'complete_money_transfer')
            ;

            return $this->render('messages/success.html.twig', [
                "pageTitle" => "Successful money transfer",
                "message" => "The transfer was made successfully."
            ]);
        } catch (ProcessedException $e) {
            return $this->render("messages/unspecified_error.html.twig");
        }
    }
}
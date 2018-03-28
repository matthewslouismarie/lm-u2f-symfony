<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\UserConfirmationType;
use App\Service\IdentityVerificationRequestManager;
use App\Service\SecureSession;

class MoneyTransferController extends AbstractController
{
    private $requestManager;

    public function __construct(
        IdentityVerificationRequestManager $requestManager)
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
        $tdm = $this
            ->requestManager
            ->achieveOperation($sid, 'complete_money_transfer')
        ;

        return $this->render('successful_money_transfer.html.twig');
    }
}
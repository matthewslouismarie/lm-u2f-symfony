<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Callback\Authentifier\MoneyTransferCallback;
use App\Form\UserConfirmationType;
use App\Service\Authentifier\MiddlewareDecorator;
use App\Service\ChallengeSpecification;

class MoneyTransferController extends AbstractController
{
    /**
     * @Route(
     *  "/authenticated/transfer-money/{sid}",
     *  name="transfer_money")
     */
    public function transferMoney(
        string $sid = null,
        ChallengeSpecification $cs,
        MiddlewareDecorator $decorator,
        MoneyTransferCallback $callback,
        Request $httpRequest
    ) {
        if (null === $sid) {
            $form = $this->createForm(UserConfirmationType::class);

            $form->handleRequest($httpRequest);
            if ($form->isSubmitted() && $form->isValid()) {
                return $decorator->createProcess(
                    $httpRequest->get('_route'),
                    $cs->getChallenges($this->getUser()->getUsername()),
                    $this->getUser()->getUsername()
                );
            }

            return $this->render('transfer_money.html.twig', [
                'form' => $form->createView(),
            ]);
        } else {
            return $decorator->updateProcess($httpRequest, $sid, $callback);
        }
    }
}

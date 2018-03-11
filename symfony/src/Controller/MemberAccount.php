<?php

namespace App\Controller;

use App\Entity\U2fToken;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class MemberAccount extends AbstractController
{
    /**
     * @Route(
     *  "/my-account",
     *  name="member_account"
     * )
     */
    public function memberAccount()
    {
        return $this->render('member_account.html.twig');
    }
}

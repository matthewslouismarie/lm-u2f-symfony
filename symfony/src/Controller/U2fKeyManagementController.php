<?php

namespace App\Controller;

use App\Repository\U2fTokenRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class U2fKeyManagementController extends AbstractController
{
    /**
     * @Route(
     *  "/authenticated/manage-u2f-keys",
     *  name="manage_u2f_keys")
     */
    public function manageU2fKeys(U2fTokenRepository $u2fTokenRepo)
    {
        $u2fKeys = $u2fTokenRepo->getU2fTokens($this->getUser()->getId());
        return $this->render('manage_u2f_keys.html.twig', [
            'u2f_keys' => $u2fKeys,
        ]);
    }
}

<?php

namespace App\Controller;

use App\Form\UsernameAndPasswordType;
use App\FormModel\UsernameAndPasswordSubmission;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TksUpSecurityController extends AbstractController
{
    /**
     * @Route(
     *  "/tks-0/not-authenticated/authenticate",
     *  name="tks_0_authenticate",
     *  methods={"GET", "POST"})
     */
    public function authenticate()
    {
        $submission = new UsernameAndPasswordSubmission();
        $form = $this
            ->createForm(UsernameAndPasswordType::class, $submission)
        ;
        return $this->render('tks/up/up_login.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}
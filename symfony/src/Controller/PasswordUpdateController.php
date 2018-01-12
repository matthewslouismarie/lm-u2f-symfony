<?php

namespace App\Controller;

use App\Entity\Member;
use App\Factory\MemberFactory;
use App\Form\PasswordUpdateType;
use App\FormModel\PasswordUpdateSubmission;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class PasswordUpdateController extends AbstractController
{
    /**
     * @Route(
     *  "/authenticated/change-password",
     *  name="tks_change_password",
     *  methods={"GET", "POST"})
     */
    public function changePassword(
        MemberFactory $mf,
        ObjectManager $om,
        Request $request)
    {
        $submission = new PasswordUpdateSubmission();
        $form = $this->createForm(PasswordUpdateType::class, $submission);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $member = $this->getUser();
            $mf->setPassword($member, $submission->getPassword());
            $om->persist($member);
            $om->flush();
        }

        return $this->render('change_password.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}
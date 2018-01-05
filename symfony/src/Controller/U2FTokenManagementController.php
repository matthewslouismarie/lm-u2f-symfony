<?php

namespace App\Controller;

use App\Entity\U2FToken;
use App\Service\U2FTokenRegistrationService;
use App\Form\U2fTokenUpdateType;
use App\Form\UserConfirmationType;
use App\FormModel\U2FTokenRegistration;
use App\FormModel\U2fTokenUpdate;
use App\Form\U2FTokenRegistrationType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;  
class U2FTokenManagementController extends AbstractController
{
    /**
     * @Route("/view-my-u2f-tokens", name="view-my-u2f-tokens", methods={"GET"})
     */
    public function viewU2FTokens()
    {
        $repo = $this->getDoctrine()->getRepository(U2FToken::class);
        $tokens = $repo->findBy(array('member' => $this->getUser()));
        return $this->render('u2f_token_list.html.twig', array(
            'tokens' => $tokens
        ));
    }

    /**
     * @todo CSRF
     * @Route(
     *  "/delete-u2f-token/{u2fTokenName}",
     *  name="delete-u2f-token",
     *  methods={"GET", "POST"})
     */
    public function deleteU2FToken(Request $request, string $u2fTokenName)
    {
        $repo = $this->getDoctrine()->getRepository(U2FToken::class);

        $token = $repo->findMemberU2fToken($u2fTokenName, $this->getUser());

        $form = $this->createForm(UserConfirmationType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $em->remove($token);
            $em->flush();
            return $this->render('post_u2f_token_deletion.html.twig');
        } else {
            return $this->render('delete_u2f_token.html.twig', array(
                'form' => $form->createView(),
                'token' => $token,
            ));
        }
    }
}
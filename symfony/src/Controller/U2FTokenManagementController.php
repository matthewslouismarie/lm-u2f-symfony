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
     * @todo refactor and finish $token check
     * @Route(
     *  "/delete-u2f-token/{id}",
     *  name="delete-u2f-token",
     *  methods={"GET", "POST"},
     *  requirements={"id"="\d+"})
     */
    public function deleteU2FToken(Request $request, int $id)
    {
        $repo = $this->getDoctrine()->getRepository(U2FToken::class);

        $token = $repo->find($id);
        if (null === $token || $this->getUser() !== $token->getMember()) {
            echo 'outch';
        }

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
                'id' => $id,
                'token' => $token,
            ));
        }
    }

    /**
     * @todo Use a custom exception.
     * 
     * @Route(
     *  "/edit-u2f-token/{u2fTokenId}",
     *  name="edit_u2f_token",
     *  methods={"GET", "POST"},
     *  requirements={"u2fTokenId"="\d+"})
     */
    public function editU2fToken(Request $request, int $u2fTokenId)
    {
        $repo = $this->getDoctrine()->getRepository(U2FToken::class);
        $token = $repo->find($u2fTokenId);
        if (null === $token || $this->getUser() !== $token->getMember()) {
            throw new \Exception();
        }
        $u2fTokenUpdate = new U2fTokenUpdate();
        $u2fTokenUpdate->setName($token->getName());

        $form = $this->createForm(U2fTokenUpdateType::class, $u2fTokenUpdate);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newToken = $repo->setName($token, $u2fTokenUpdate->getName());
            return $this->render('u2f_token.html.twig', array(
                'form' => $form->createView(),
            ));
        } else {
            return $this->render('u2f_token.html.twig', array(
                'form' => $form->createView(),
            )); 
        }
    }
}
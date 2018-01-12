<?php

namespace App\Controller;

use App\Entity\U2fToken;
use App\Form\UserConfirmationType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

/**
 * @todo Delete this class.
 */
class U2fTokenManagementController extends AbstractController
{
    /**
     * @Route("/view-my-u2f-tokens", name="view-my-u2f-tokens", methods={"GET"})
     */
    public function viewU2fTokens()
    {
        $repo = $this->getDoctrine()->getRepository(U2fToken::class);
        $tokens = $repo->findBy(array('member' => $this->getUser()));

        return $this->render('u2f_token_list.html.twig', array(
            'tokens' => $tokens,
        ));
    }

    /**
     * @todo CSRF
     * @Route(
     *  "/delete-u2f-token/{u2fTokenName}",
     *  name="delete-u2f-token",
     *  methods={"GET", "POST"})
     */
    public function deleteU2fToken(Request $request, string $u2fTokenName)
    {
        $repo = $this->getDoctrine()->getRepository(U2fToken::class);

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

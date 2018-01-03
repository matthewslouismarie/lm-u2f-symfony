<?php

namespace App\Controller;

use App\Entity\U2FToken;
use App\Service\AddU2FTokenService;
use App\FormModel\U2FTokenRegistration;
use App\Form\U2FTokenRegistrationType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ManageU2FTokensController extends AbstractController
{
    /**
     * @Route("/view-my-u2f-tokens", name="view-my-u2f-tokens", methods={"GET"})
     */
    public function viewU2FTokens()
    {
        $repo = $this->getDoctrine()->getRepository(U2FToken::class);
        $tokens = $repo->findBy(array('member' => $this->getUser()));
        return $this->render('view-my-u2f-tokens.html.twig', array(
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
    public function deleteU2FToken(int $id)
    {
        $request = Request::createFromGlobals();
        $repo = $this->getDoctrine()->getRepository(U2FToken::class);
        $token = $repo->find($id);

        if (null === $token || $this->getUser() !== $token->getMember()) {
            echo 'outch';
        }
        if ('GET' === $request->getMethod()) {
            return $this->render('delete-u2f-token.html.twig', array(
                'token' => $token,
                'id' => $id
            ));
        } elseif ('POST' === $request->getMethod()) {
            $em = $this->getDoctrine()->getEntityManager();
            $em->remove($token);
            $em->flush();
            return $this->render('post_u2f_token_deletion.html.twig');
        }
    }

    /**
     * @Route(
     *  "/edit-u2f-token/{id}",
     *  name="edit_u2f_token",
     *  methods={"GET", "POST"},
     *  requirements={"id"="\d+"})
     */
    public function editU2FToken(int $id)
    {
        $request = Request::createFromGlobals();
        $repo = $this->getDoctrine()->getRepository(U2FToken::class);
        $token = $repo->find($id);
        if (null === $token || $this->getUser() !== $token->getMember()) {
            echo 'outch';
        }
        if ('GET' === $request->getMethod()) {
            return $this->render('u2f_token.html.twig', array(
                'id' => $id,
                'token' => $token,
            ));
        } elseif ('POST' === $request->getMethod()) {
            $newToken = $repo->setName($token, $request->request->get('name'));
            return $this->render('u2f_token.html.twig', array(
                'id' => $id,
                'token' => $newToken,
            ));
        }
    }
}
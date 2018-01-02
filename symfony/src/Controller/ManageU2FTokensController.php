<?php

namespace App\Controller;

use App\Entity\U2FToken;
use App\Service\AddU2FTokenService;
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
     * @todo set timezone
     * @Route("/add-u2f-token", name="add-u2f-token", methods={"GET", "POST"})
     */
    public function addU2FToken(AddU2FTokenService $service): Response
    {
        $request = Request::createFromGlobals();
        if ('GET' === $request->getMethod()) {
            $challenge_data = $service->generate();
            return $this->render('add-u2f-token.html.twig', $challenge_data);
        } elseif ('POST' === $request->getMethod()) {
            $post = $request->request;
            $service->processResponse(
                $post->get('challenge'),
                $post->get('name'),
                $this->getUser(),
                new \DateTimeImmutable(),
                $post->get('reg-id'));
            return new Response('went okay');
        }
    }

    /**
     * @todo CSRF
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
}
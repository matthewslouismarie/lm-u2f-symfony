<?php

namespace App\Controller;

use App\Service\PDOService;
use App\Service\RegisterRequestService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Firehed\U2F\RegisterResponse;

class RegisterController extends AbstractController
{
    private $register;

    public function __construct(RegisterRequestService $reg_req_service)
    {
        $this->register = $reg_req_service;
    }

    /**
     * @Route("register", name="get_register", methods={"GET"})
     * @todo why do we need sign_requests for registration?
     */
    public function doGet(): Response
    {
        $challenge_data = $this->register->generate();
        return $this->render('register.html.twig', $challenge_data);
    }

    /**
     * @Route("register", name="post_register", methods={"POST"})
     */
    public function doPost()
    {
        $post = Request::createFromGlobals()->request;
        $this->register->processResponse(
            $post->get('reg-id'),
            $this->getUser()->getUsername(),
            $post->get('challenge')
        );
        return new Response('went okay');
    }
}
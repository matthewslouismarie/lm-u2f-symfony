<?php

namespace App\Controller\U2fAuthorizer;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * This class handles the authorisation of IUserRequestedAction objects. UPUK
 * stands for Username, Password and U2F Key.
 */
class UpukAuthorizer extends AbstractController
{
    /**
     * @todo Is all the good prefix for the route?
     * 
     * @Route(
     *  "/all/u2f-authorization/upuk",
     *  name="u2f_authorization_upuk",
     *  methods={"GET", "POST"})
     */
    public function upuk()
    {
        return new Response('Hello');
    }
}
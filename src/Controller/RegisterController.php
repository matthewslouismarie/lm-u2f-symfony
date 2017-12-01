<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;

class RegisterController
{
    public function doGet(): Response
    {
        return new Response("Hello");
    }
}
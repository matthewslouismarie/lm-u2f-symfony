<?php

namespace App\Controller\ApplicationExample;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class ApplicationExampleController extends AbstractController
{
    /**
     * @Route(
     *  "/",
     *  name="ae_homepage"
     * )
     */
    public function homepage()
    {
        return $this->render('application_example/homepage.html.twig');
    }
}

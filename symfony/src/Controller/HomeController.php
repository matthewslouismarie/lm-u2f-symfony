<?php

namespace App\Controller;

use App\Entity\Member;
use App\Entity\U2FToken;
use App\Form\LoginForm;
use App\Form\RegistrationType;
use App\Form\UserConfirmationType;
use App\FormModel\LoginSubmission;
use App\FormModel\RegistrationSubmission;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class HomeController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function home(Request $request)
    {
        ob_start();
        $content = ob_get_clean();
        return $this->render('home.html.twig', array('c' => $content));
    }
}
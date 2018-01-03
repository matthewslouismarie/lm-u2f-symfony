<?php

namespace App\Controller;

use App\Entity\Member;
use App\Form\LoginForm;
use App\Form\RegistrationForm;
use App\Form\UserConfirmationType;
use App\FormModel\LoginSubmission;
use App\FormModel\RegistrationSubmission;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends Controller
{
    /**
     * @Route("/login", name="security_login")
     */
    public function login(Request $request, AuthenticationUtils $authUtils)
    {
        $error = $authUtils->getLastAuthenticationError();
        $lastUsername = $authUtils->getLastUsername();
        $form = $this->createForm(
            LoginForm::class,
            new LoginSubmission($lastUsername, '')
        );
        return $this->render(
            'security/login.html.twig',
            array(
                'form' => $form->createView(),
                'error' => $error,
            )
        );
    }

    /**
     * @Route("/logout", name="logout", methods={"GET", "POST"})
     */
    public function logout(Request $request)
    {
        $form = $this->createForm(UserConfirmationType::class);
        return $this->render('logout.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/not-logged-out", name="not_logged_out", methods={"GET"})
     */
    public function notLoggedOut()
    {
        return $this->render('not_logged_out_error.html.twig');
    }

    /**
     * @Route("/register", name="register", methods={"GET", "POST"})
     */
    public function register(
        ObjectManager $manager,
        Request $request,
        UserPasswordEncoderInterface $encoder)
    {
        $submission = new RegistrationSubmission();
        $form = $this->createForm(
            RegistrationForm::class,
            $submission
        );
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $member = new Member($submission->getUsername());
            $encoded = $encoder->encodePassword($member, $submission->getPassword());
            $member->setPassword($encoded);
            $manager->persist($member);
            $manager->flush();
            return $this->render('successful_registration.html.twig');
        }
        return $this->render('registration.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/", name="homepage")
     */
    public function home()
    {
        ob_start();
        var_dump($this->getUser());
        $content = ob_get_clean();
        return $this->render('home.html.twig', array('c' => $content));
    }
}
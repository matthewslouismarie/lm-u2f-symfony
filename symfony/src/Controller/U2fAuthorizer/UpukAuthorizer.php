<?php

namespace App\Controller\U2fAuthorizer;

use App\Form\UsernameAndPasswordType;
use App\FormModel\UsernameAndPasswordSubmission;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
     *  "/all/u2f-authorization/upuk/up/{sessionId}",
     *  name="u2f_authorization_upuk_up",
     *  methods={"GET", "POST"},
     *  requirements={"sessionId"=".+"})
     */
    public function upukUp(Request $request, string $sessionId)
    {
        $submission = new UsernameAndPasswordSubmission();
        $form = $this->createForm(UsernameAndPasswordType::class, $submission);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $url = $this->generateUrl('u2f_authorization_upuk_uk', array(
                'sessionId' => $sessionId,
            ));
            return new RedirectResponse($url);
        }
        return $this->render('tks/username_and_password.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route(
     *  "/all/u2f-authorization/upuk/uk/{sessionId}",
     *  name="u2f_authorization_upuk_uk",
     *  methods={"GET", "POST"},
     *  requirements={"sessionId"=".+"})
     */
    public function upukUk()
    {
        return $this->render('u2f_authorization/upuk/uk_login.html.twig');
    }
}
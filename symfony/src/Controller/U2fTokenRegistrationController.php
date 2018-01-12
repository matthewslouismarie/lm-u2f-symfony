<?php

namespace App\Controller;

use App\Form\U2FTokenRegistrationType;
use App\FormModel\U2FTokenRegistration;
use App\Service\U2FTokenRegistrationService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @todo Delete this class.
 */
class U2fTokenRegistrationController extends AbstractController
{
    /**
     * @Route(
     *  "/authenticated/add-u2f-token",
     *  name="get_add_u2f_token",
     *  methods={"GET"})
     */
    public function doGet(Request $request, U2FTokenRegistrationService $service)
    {
        $rp_request = $service->generate();

        $submission = new U2FTokenRegistration();
        $submission->setRequestId($rp_request['request_id']);

        $form = $this->createForm(U2FTokenRegistrationType::class, $submission);

        return $this->render('add_u2f_token.html.twig', array(
            'request_json' => $rp_request['request_json'],
            'sign_requests' => $rp_request['sign_requests'],
            'form' => $form->createView(),
        ));
    }

    /**
     * @todo set timezone
     * @Route(
     *  "/authenticated/add-u2f-token",
     *  name="post_add_u2f_token",
     *  methods={"POST"})
     */
    public function doPost(Request $request, U2FTokenRegistrationService $service)
    {
        $submission = new U2FTokenRegistration();
        $form = $this->createForm(U2FTokenRegistrationType::class, $submission);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $u2fToken = $service->processResponse(
                $submission->getU2fTokenResponse(),
                $this->getUser(),
                new \DateTimeImmutable(),
                $submission->getRequestId()
            );
            ob_start();
            echo '<pre>';
            var_dump($u2fToken);
            echo '</pre>';

            return new Response(ob_get_clean());
        } else {
            return new RedirectResponse($this->generateUrl('get_add_u2f_token'));
        }
    }
}

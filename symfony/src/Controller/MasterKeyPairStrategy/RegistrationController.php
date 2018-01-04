<?php

namespace App\Controller\MasterKeyPairStrategy;

use App\Form\U2FTokenRegistrationType;
use App\FormModel\U2FTokenRegistration;
use App\Service\U2FTokenRegistrationService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RegistrationController extends AbstractController
{
    /**
     * @Route("/mkps/registration", name="mkps_registration", methods={"GET"})
     */
    public function fetchLandingPage()
    {
        return $this->render('mkps/registration.html.twig');
    }

    /**
     * @Route(
     *  "/mkps/master-pair-first-key",
     *  name="mkps_master_pair_first_key",
     *  methods={"GET", "POST"})
     */
    public function fetchPairFirstKey(
        Request $request,
        U2FTokenRegistrationService $service)
    {

        $rp_request = $service->generate();

        $submission = new U2FTokenRegistration();
        $submission->setRequestId($rp_request['request_id']);

        $form = $this->createForm(U2FTokenRegistrationType::class, $submission);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $service->processResponse(
                    $submission->getU2fTokenResponse(),
                    $submission->getName(),
                    $this->getUser(),
                    new \DateTimeImmutable(),
                    $submission->getRequestId()
                );
                return new Response('Ça marche.');
            }
            catch (\TypeError $e) {
                $form->addError(new FormError('An error occured. Please try again.'));
            }
        }

        return $this->render('mkps/master_pair_first_key.html.twig', array(
            'request_json' => $rp_request['request_json'],
            'sign_requests' => $rp_request['sign_requests'],
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route(
     *  "/mkps/master-pair-second-key",
     *  name="mkps_master_pair_second_key",
     *  methods={"GET"})
     */
    public function fetchMkpsSecondKey()
    {
        $rp_request = $service->generate();

        $submission = new U2FTokenRegistration();
        $submission->setRequestId($rp_request['request_id']);

        $form = $this->createForm(U2FTokenRegistrationType::class, $submission);

        return $this->render('mkps/master_pair_first_key.html.twig', array(
            'request_json' => $rp_request['request_json'],
            'sign_requests' => $rp_request['sign_requests'],
            'form' => $form->createView(),
        ));
    }
}
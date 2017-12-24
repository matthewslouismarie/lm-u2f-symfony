<?php

namespace App\Controller;

use App\Service\PDOService;
use App\Service\SessionService;
use Firehed\U2F\Server;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Firehed\U2F\RegisterResponse;

class RegisterController extends AbstractController
{
    private $pdo;
    private $server;

    public function __construct(PDOService $pdo)
    {
        $this->pdo = $pdo;
        $this->server = new Server();
        $this->server->disableCAVerification()
       ->setAppId('https://'.$_SERVER['SERVER_NAME']);
    }

    /**
     * @Route("register", name="get_register", methods={"GET"})
     */
    public function doGet(SessionService $session): Response
    {
        file_get_contents(__DIR__.'/../db.json');
        $request = $this->server->generateRegisterRequest();
        $reg_id = \Firehed\U2F\generate_reg_id();
        $session->set('register-request', serialize($request));
        $request_json = json_encode($request); // ->jsonSerialize()?

        $registrations = \firehed\u2f\get_registrations_for_user(0, $this->pdo->getPdo()); // @todo why do we need sign_requests for registration?
        $sign_requests = json_encode($this->server->generateSignRequests($registrations, $reg_id));
        return $this->render('register.html.twig', array(
            'request_json' => $request_json,
            'sign_requests' => $sign_requests,
            'reg_id' => $reg_id,
        ));
    }

    /**
     * @Route("register", name="post_register", methods={"POST"})
     */
    public function doPost(SessionService $session, PDOService $pdo_service)
    {
        $post = Request::createFromGlobals()->request;
        $request = unserialize($session->get('register-request'));
        $session->remove('register-request');
        $username = $post->get('username');
        $this->server->setRegisterRequest($request);
        $response = RegisterResponse::fromJson($post->get('challenge'));
        $registration = $this->server->register($response);
        $db_credentials = json_decode(file_get_contents(__DIR__.'/../db.json'), true);
        $pdo = $pdo_service->getPdo();
        $pdo->beginTransaction();
        $members_insert = $pdo->prepare('INSERT INTO members VALUES (NULL, :username)');
        $members_insert->bindParam(':username', $username);
        $success = $members_insert->execute();
        $u2f_authenticators_insert = $pdo->prepare('INSERT INTO u2f_authenticators VALUES (NULL, :member_id, :counter, :attestation, :public_key, :key_handle)');
        $member_id = $pdo->lastInsertId();
        $u2f_authenticators_insert->bindParam(':member_id', $member_id);
        $attestation = base64_encode($registration->getAttestationCertificateBinary());
        $u2f_authenticators_insert->bindParam(':attestation', $attestation);
        $counter = $registration->getCounter();
        $u2f_authenticators_insert->bindParam(':counter', $counter);
        $public_key = base64_encode($registration->getPublicKey());
        $u2f_authenticators_insert->bindParam(':public_key', $public_key);
        $key_handle = base64_encode($registration->getKeyHandleBinary());
        $u2f_authenticators_insert->bindParam(':key_handle', $key_handle);
        $u2f_authenticators_insert->execute();
        $pdo->commit();
        ?>
        Last insert id: <?= $pdo->lastInsertId() ?><br>
        Attestation: <?= base64_encode($registration->getAttestationCertificateBinary()) ?><br>
        Counter:  <?= $registration->getCounter() ?><br>
        Public key: <?= base64_encode($registration->getPublicKey()) ?><br>
        Key handle: <?= base64_encode($registration->getKeyHandleBinary()) ?><br>
        <?php
    }
}
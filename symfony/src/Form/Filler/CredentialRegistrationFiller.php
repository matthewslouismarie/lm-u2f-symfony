<?php

namespace App\Form\Filler;

use App\Exception\NonexistentNodeException;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Form;

class CredentialRegistrationFiller
{
    private $crawler;

    private $password;

    private $passwordConfirmation;

    private $username;

    public function __construct(
        Crawler $crawler,
        string $password,
        string $passwordConfirmation,
        string $username)
    {
        $this->crawler = $crawler;
        $this->password = $password;
        $this->passwordConfirmation = $passwordConfirmation;
        $this->username = $username;
    }

    public function fillForm(): Form
    {
        $button = $this
            ->crawler
            ->selectButton('credential_registration[submit]')
        ;
        if (0 === $button->count()) {
            throw new NonexistentNodeException();
        }
        $form = $button->form([
            'credential_registration[username]' => $this->username,
            'credential_registration[password]' => $this->password,
            'credential_registration[passwordConfirmation]' => $this->passwordConfirmation,
        ]);

        return $form;
    }
}
<?php

namespace App\Service\Form\Filler;

use App\Exception\NonexistentNodeException;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Form;

class CredentialRegistrationFiller
{
    public function fillForm(
        Crawler $crawler,
        string $password,
        string $passwordConfirmation,
        string $username
        ): Form
    {
        $button = $crawler->selectButton('credential_registration[submit]');
        if (0 === $button->count()) {
            throw new NonexistentNodeException();
        }
        $form = $button->form([
            'credential_registration[username]' => $username,
            'credential_registration[password]' => $password,
            'credential_registration[passwordConfirmation]' => $passwordConfirmation,
        ]);

        return $form;
    }
}

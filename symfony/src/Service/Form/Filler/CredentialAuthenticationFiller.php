<?php

namespace App\Service\Form\Filler;

use App\Exception\NonexistentNodeException;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Form;

class CredentialAuthenticationFiller
{
    public function fillForm(
        Crawler $crawler,
        string $password,
        string $username): Form
    {
        $button = $crawler->selectButton('credential_authentication[submit]');
        if (0 === $button->count()) {
            throw new NonexistentNodeException();
        }
        $form = $button->form([
            'credential_authentication[username]' => $username,
            'credential_authentication[password]' => $password,
        ]);

        return $form;
    }
}

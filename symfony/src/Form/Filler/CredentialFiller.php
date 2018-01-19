<?php

namespace App\Form\Filler;

use App\Exception\NonexistentNodeException;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Form;

class CredentialFiller
{
    private $crawler;

    private $password;

    private $username;

    public function __construct(
        Crawler $crawler,
        string $username,
        string $password)
    {
        $this->crawler = $crawler;
        $this->password = $password;
        $this->username = $username;
    }

    public function getFilledForm(): Form
    {
        $button = $this
            ->crawler
            ->selectButton('credential_authentication[submit]')
        ;
        if (0 === $button->count()) {
            throw new NonexistentNodeException();
        }
        $form = $button->form([
            'credential_authentication[username]' => $this->username,
            'credential_authentication[password]' => $this->password,
        ]);

        return $form;
    }
}

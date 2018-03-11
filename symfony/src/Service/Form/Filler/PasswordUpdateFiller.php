<?php

namespace App\Service\Form\Filler;

use App\Exception\NonexistentNodeException;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Form;

class PasswordUpdateFiller
{
    public function fillForm(Crawler $crawler, string $password): Form
    {
        $button = $crawler->selectButton('password_update[submit]');
        if (0 === $button->count()) {
            throw new NonexistentNodeException();
        }

        return $button->form([
            'password_update[password]' => $password,
            'password_update[passwordConfirmation]' => $password,
        ]);
    }
}

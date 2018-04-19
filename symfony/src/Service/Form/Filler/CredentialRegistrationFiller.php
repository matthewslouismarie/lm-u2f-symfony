<?php

declare(strict_types=1);

namespace App\Service\Form\Filler;

use App\Exception\NonexistentNodeException;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Form;

class CredentialRegistrationFiller
{
    const FORM_NAME = 'form';

    public function fillForm(
        Crawler $crawler,
        string $password,
        string $passwordConfirmation,
        string $username
        ): Form {
        $button = $crawler->selectButton(self::FORM_NAME.'[submit]');
        if (0 === $button->count()) {
            throw new NonexistentNodeException();
        }
        $form = $button->form([
            self::FORM_NAME.'[username]' => $username,
            self::FORM_NAME.'[password][first]' => $password,
            self::FORM_NAME.'[password][second]' => $passwordConfirmation,
        ]);

        return $form;
    }
}

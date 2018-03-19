<?php

namespace App\Service\Form\Filler;

use App\Exception\NonexistentNodeException;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Form;

class PasswordConfigFiller
{
    public function fillForm(
        Crawler $crawler,
        int $minimumLength,
        bool $enforceMinimumLength,
        bool $requireNumbers,
        bool $requireSpecialCharacters,
        bool $requireUppercaseLetters): Form
    {
        $button = $crawler->selectButton('pwd_config[submit]');
        if (0 === $button->count()) {
            throw new NonexistentNodeException();
        }
        $form = $button->form([
            'pwd_config[minimumLength]' => $minimumLength,
            'pwd_config[enforceMinimumLength]' => $enforceMinimumLength,
            'pwd_config[requireNumbers]' => $requireNumbers,
            'pwd_config[requireSpecialCharacters]' => $requireSpecialCharacters,
            'pwd_config[requireUppercaseLetters]' => $requireUppercaseLetters,
        ]);

        return $form;
    }
}

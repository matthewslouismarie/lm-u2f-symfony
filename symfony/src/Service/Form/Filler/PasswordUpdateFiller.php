<?php

declare(strict_types=1);

namespace App\Service\Form\Filler;

use App\Exception\NonexistentNodeException;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Form;

class PasswordUpdateFiller
{
    const FORM_NAME = 'form';

    public function fillForm(Crawler $crawler, string $password): Form
    {
        $button = $crawler->selectButton(self::FORM_NAME.'[submit]');
        if (0 === $button->count()) {
            throw new NonexistentNodeException();
        }

        return $button->form([
            self::FORM_NAME.'[password][first]' => $password,
            self::FORM_NAME.'[password][second]' => $password,
        ]);
    }
}

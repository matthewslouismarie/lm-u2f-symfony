<?php

declare(strict_types=1);

namespace App\Service\Form\Filler;

use App\Exception\NonexistentNodeException;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Form;

class ExistingUsernameFiller
{
    public function fillForm(Crawler $crawler, string $username): Form
    {
        $button = $crawler->selectButton('form[submit]');
        if (0 === $button->count()) {
            throw new NonexistentNodeException();
        }
        $form = $button->form([
            'form[username]' => $username,
        ]);

        return $form;
    }
}

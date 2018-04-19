<?php

declare(strict_types=1);

namespace App\Service\Form\Filler;

use App\Exception\NonexistentNodeException;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Form;

class LoginRequestFiller
{
    public function fillForm(Crawler $crawler): Form
    {
        $button = $crawler->selectButton('login_request[submit]');
        if (0 === $button->count()) {
            throw new NonexistentNodeException();
        }

        return $button->form();
    }
}

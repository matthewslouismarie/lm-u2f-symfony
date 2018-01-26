<?php

namespace App\Service\Form\Filler;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Form;

class UserConfirmationFiller
{
    public function fillForm(Crawler $crawler): Form
    {
        $button = $crawler->selectButton('user_confirmation[submit]');
        return $button->form();
    }
}

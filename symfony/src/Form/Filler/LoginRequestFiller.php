<?php

namespace App\Form\Filler;

use App\Exception\NonexistentNodeException;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Form;

class LoginRequestFiller
{
    private $crawler;

    public function __construct(Crawler $crawler)
    {
        $this->crawler = $crawler;
    }

    public function getFilledForm(): Form
    {
        $button = $this->crawler->selectButton('login_request[submit]');
        if (0 === $button->count()) {
            throw new NonexistentNodeException();
        }

        return $button->form();
    }
}

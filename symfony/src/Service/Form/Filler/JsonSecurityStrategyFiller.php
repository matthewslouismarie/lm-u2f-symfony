<?php

declare(strict_types=1);

namespace App\Service\Form\Filler;

use App\Exception\NonexistentNodeException;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Form;

class JsonSecurityStrategyFiller
{
    const FORM_NAME = 'json_security_strategy';

    public function fillForm(
        Crawler $crawler,
        string $json
    ): Form {
        $button = $crawler->selectButton(self::FORM_NAME.'[submit]');
        if (0 === $button->count()) {
            throw new NonexistentNodeException();
        }

        return $button->form([
            self::FORM_NAME.'[json]' => $json,
        ]);
    }
}

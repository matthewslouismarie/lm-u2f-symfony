<?php

namespace App\Service\Form\Filler;

use App\Exception\NonexistentNodeException;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Form;

class UserStudyConfigFiller
{
    const FORM_ID = "user_study_config";

    public function fillForm(
        Crawler $crawler,
        bool $isUserStudyModeActive,
        ?string $participantId
    ): Form {
        $button = $crawler->selectButton(self::FORM_ID."[submit]");
        if (0 === $button->count()) {
            throw new NonexistentNodeException();
        }
        $form = $button->form([
            self::FORM_ID."[isUserStudyModeActive]" => $isUserStudyModeActive,
            self::FORM_ID."[participantId]" => $participantId,
        ]);

        return $form;
    }
}

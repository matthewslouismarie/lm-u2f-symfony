<?php

namespace App\Service\Form\Filler;

use App\Exception\NonexistentNodeException;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Form;

class U2fConfigFiller
{
    public function fillForm(
        Crawler $crawler,
        bool $allowU2fLogin,
        int $nU2fKeysPostAuth,
        int $nU2fKeysReg,
        bool $allowMemberToManageU2fKeys): Form
    {
        $button = $crawler->selectButton('u2f_config[submit]');
        if (0 === $button->count()) {
            throw new NonexistentNodeException();
        }

        return $button->form([
            'u2f_config[allowU2fLogin]' => $allowU2fLogin,
            'u2f_config[nU2fKeysPostAuth]' => $nU2fKeysPostAuth,
            'u2f_config[nU2fKeysReg]' => $nU2fKeysReg,
            'u2f_config[allowMemberToManageU2fKeys]' => $allowMemberToManageU2fKeys,
        ]);
    }
}

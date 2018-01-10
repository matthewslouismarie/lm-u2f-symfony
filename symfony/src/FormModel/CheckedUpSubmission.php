<?php

namespace App\FormModel;

class CheckedUpSubmission
{
    private $upSubmission;

    public function __construct(UsernameAndPasswordSubmission $upSubmission)
    {
        $this->upSubmission = $upSubmission;
    }

    public function getUpSubmission(): ?UsernameAndPasswordSubmission
    {
        return $this->upSubmission;
    }

    public function setUpSubmission(
        UsernameAndPasswordSubmission $upSubmission): void
    {
        $this->upSubmission = $upSubmission;
    }
}
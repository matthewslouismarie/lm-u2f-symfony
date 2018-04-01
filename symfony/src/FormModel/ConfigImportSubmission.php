<?php

namespace App\FormModel;

class ConfigImportSubmission
{
    public $jsonConfig;

    public function __construct(?string $jsonConfig = null)
    {
        $this->jsonConfig = $jsonConfig;
    }
}

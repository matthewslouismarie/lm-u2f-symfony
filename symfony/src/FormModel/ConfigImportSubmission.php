<?php

declare(strict_types=1);

namespace App\FormModel;

class ConfigImportSubmission
{
    public $jsonConfig;

    public function __construct(?string $jsonConfig = null)
    {
        $this->jsonConfig = $jsonConfig;
    }
}

<?php

namespace App\Controller;

use App\Service\AppConfigManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class U2fGenerationController extends AbstractController
{
    public function __construct(AppConfigManager $appConfigManager)
    {
    }
}

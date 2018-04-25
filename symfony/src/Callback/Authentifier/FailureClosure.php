<?php

declare(strict_types=1);

namespace App\Callback\Authentifier;

use Closure;
use LM\Authentifier\Model\AuthenticationProcess;
use LM\Authentifier\Model\AuthentifierResponse;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Component\HttpFoundation\Response;
use Twig_Environment;

/**
 * @todo Should probably be moved in Service.
 */
class FailureClosure
{
    private $twig;

    public function __construct(Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    public function getClosure(): Closure
    {
        $twig = $this->twig;

        return function (AuthenticationProcess $authProcess) use ($twig) : AuthentifierResponse {
            $html = $twig
                ->render('messages/error.html.twig', [
                    'pageTitle' => 'Unsuccessful identity verification',
                    'message' => 'Sorry, you tried too many wrong attempts',
                ])
            ;

            return new AuthentifierResponse(
                $authProcess,
                (new DiactorosFactory())->createResponse(new Response($html))
            );
        }
        ;
    }
}

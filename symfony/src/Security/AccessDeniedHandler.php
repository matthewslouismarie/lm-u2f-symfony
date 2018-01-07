<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;
use Symfony\Component\Routing\RouterInterface;

class AccessDeniedHandler implements AccessDeniedHandlerInterface
{
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * @todo Redirect to page the user tried to visit?
     */
    public function handle(Request $request, AccessDeniedException $exception)
    {
        $response = new RedirectResponse(
            $this->router->generate('not_logged_out')
        );
        $response->setStatusCode(403);
        return $response;
    }
}
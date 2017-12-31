<?php

namespace App\Security\Firewall;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;

class WsseListener implements ListenerInterface
{
    protected $tokenStorage;
    protected $authenticationManager;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthenticationManagerInterface $authenticationManager)
    {
        $this->tokenStorage = $tokenStorage;
        $this->authenticationManager = $authenticationManager;
    }

    /**
     * Sets the $tokenStorage variable, on which authentication and
     * authorization decisions are based.
     * @todo Remove hard-coded references: _username, _password,
     * our_db_provider, and roles.
     * @todo Find an elegant and robust way to detect if the request comes from
     * the login page.
     */
    public function handle(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        // ob_start();
        // var_dump($request);
        // $content = ob_get_clean();
        // echo '<pre>'.$content.'</pre>';

        $post_username = $request->request->get('_username');
        $post_password = $request->request->get('_password');
        if (null !==  $post_username && null !== $post_password) {
            $token = new UsernamePasswordToken(
                $post_username,
                $post_password,
                'our_db_provider',
                array('ROLE_USER'));
            try {
                $authToken = $this->authenticationManager->authenticate($token);
                $this->tokenStorage->setToken($authToken);
    
                return;
            } catch (AuthenticationException $failed) {
                // ... you might log something here
    
                // To deny the authentication clear the token. This will redirect to the login page.
                // Make sure to only clear your token, not those of other authentication listeners.
                // $token = $this->tokenStorage->getToken();
                // if ($token instanceof WsseUserToken && $this->providerKey === $token->getProviderKey()) {
                //     $this->tokenStorage->setToken(null);
                // }
                // return;
            }
        }

        // // By default deny authorization
        // $response = new Response();
        // $response->setStatusCode(Response::HTTP_FORBIDDEN);
        // $event->setResponse($response);
    }
}

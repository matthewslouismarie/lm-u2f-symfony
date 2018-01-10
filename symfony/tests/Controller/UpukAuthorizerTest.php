<?php

namespace App\Tests\Controller;

use App\Model\AuthorizationRequest;
use Firehed\U2F\SignRequest;

class UpukAuthorizerTest extends AbstractAccessManagementTestCase
{
    private const UP_ROUTE_REGEX =
    '/^http:\/\/localhost\/all\/u2f-authorization\/upuk\/up\/[a-z0-9]+$/';
    private const UK_ROUTE_REGEX =
    '/^http:\/\/localhost\/all\/u2f-authorization\/upuk\/uk\/[a-z0-9]+\/[a-z0-9]+$/';
    private $session;

    public function setUp()
    {
        parent::setUp();
        $this->session = $this
            ->getContainer()
            ->get('App\Service\SecureSessionService')
        ;
    }

    public function testCorrectLogin()
    {
        $authorizationRequest = new AuthorizationRequest(false, 'login_success_route', null);
        $sessionId = $this
            ->session
            ->store($authorizationRequest)
        ;

        $this
            ->getClient()
            ->request('GET', '/all/u2f-authorization/upuk/up/'.$sessionId);

        $this->upLogInFromUpPage('louis', 'hello', $sessionId);
        
        $requestId = $this->storeInSessionU2fToken(true);

        $this->getClient()->followRedirect();
        
        $this->ukLogInFromUkPage($requestId);
        
        $this->getClient()->followRedirect();
        $this->assertRegExp(
            '/^http:\/\/localhost\/not-authenticated\/login\/[a-z0-9]+$/',
            $this->getClient()->getRequest()->getUri());
    }

    public function testIncorrectupLogIn()
    {
        $authorizationRequest = new AuthorizationRequest(false, 'login_success_route', null);
        $sessionId = $this
            ->session
            ->store($authorizationRequest)
        ;

        $this
            ->getClient()
            ->request('GET', '/all/u2f-authorization/upuk/up/'.$sessionId);

        $this->upLogInFromUpPage('loui', 'hello', $sessionId);

        $this->assertRegExp(
            self::UP_ROUTE_REGEX,
            $this->getClient()->getRequest()->getUri());
    }

    public function testIncorrectUkLogin()
    {
        $authorizationRequest = new AuthorizationRequest(false, 'login_success_route', null);
        $sessionId = $this
            ->session
            ->store($authorizationRequest)
        ;

        $this
            ->getClient()
            ->request('GET', '/all/u2f-authorization/upuk/up/'.$sessionId);

        $this->upLogInFromUpPage('louis', 'hello', $sessionId);
        
        $requestId = $this->storeInSessionU2fToken(false);

        $this->getClient()->followRedirect();
        
        $this->ukLogInFromUkPage($requestId);
        
        $this->assertRegExp(
            self::UK_ROUTE_REGEX,
            $this->getClient()->getRequest()->getUri());
    }
}
<?php

namespace App\Tests\Controller;

use App\Model\UserRequestedAction;

class UpukAuthorizerTest extends DbWebTestCase
{
    private $session;

    public function setUp()
    {
        parent::setUp();
        $this->session = $this
            ->getContainer()
            ->get('App\Service\SecureSessionService')
        ;
    }

    public function testUpukAuthorizer()
    {
        $userRequestedAction = new UserRequestedAction(false, '/');
        $sessionId = $this
            ->session
            ->store($userRequestedAction)
        ;
        $crawler = $this
            ->getClient()
            ->request('GET', '/all/u2f-authorization/upuk/'.$sessionId)
        ;
        $statusCode = $this
            ->getClient()
            ->getResponse()
            ->getStatusCode()
        ;
        $this->assertEquals(200, $statusCode);
    }
}
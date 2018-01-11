<?php

namespace App\Tests;

use App\Entity\Member;
use App\Tests\Controller\AbstractAccessManagementTestCase;

class SecureSessionServiceTest extends AbstractAccessManagementTestCase
{
    public function testSecureSessionService()
    {
        $sSession = self::$kernel
            ->getContainer()
            ->get('App\Service\SecureSessionService');
        $member = new Member(null, 'louis');
        $sid = $sSession->storeObject($member);
        $sessionMember = $sSession->getObject($sid, Member::class);
        $this->assertEquals($member, $sessionMember);
    }
}
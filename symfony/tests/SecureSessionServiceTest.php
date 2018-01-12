<?php

namespace App\Tests;

use App\Entity\Member;
use App\Tests\Controller\AbstractAccessManagementTestCase;

class SecureSessionTest extends AbstractAccessManagementTestCase
{
    public function testSecureSession()
    {
        $sSession = self::$kernel
            ->getContainer()
            ->get('App\Service\SecureSession');
        $member = new Member(null, 'louis');
        $sid = $sSession->storeObject($member, Member::class);
        $sessionMember = $sSession->getObject($sid, Member::class);
        $this->assertEquals($member, $sessionMember);
    }
}

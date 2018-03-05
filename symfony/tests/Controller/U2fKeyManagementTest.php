<?php

namespace App\Tests\Controller;

use App\Entity\U2fToken;
use App\Tests\TestCaseTemplate;

class U2fKeyManagementTest extends TestCaseTemplate
{
    use AuthenticationTrait;

    public function testManagementPage()
    {
        $this->authenticateAsLouis();
        $this->doGet('/authenticated/manage-u2f-keys');

        $member = $this->getLoggedInMember();
        $u2fTokens = $this
            ->get('doctrine')
            ->getManager()
            ->getRepository(U2fToken::class)
            ->getU2fTokens($member->getId())
        ;
        $this->assertEquals(
            count($u2fTokens),
            $this->getCrawler()->filter('.item-list > .item')->count()
        );
    }

    public function testKeyReset()
    {
        $this->authenticateAsLouis();
        $member = $this->getLoggedInMember();
        $u2fTokens = $this
            ->get('doctrine')
            ->getManager()
            ->getRepository(U2fToken::class)
            ->getU2fTokens($member->getId())
        ;
        $originalNOfU2fKeys = count($u2fTokens);
        $this->doGet('/authenticated/manage-u2f-keys');
        $firstLink = $this
            ->getCrawler()
            ->filter('.item-list > .item > .link')
            ->first()
            ->link()
        ;
        $this->doGet($firstLink->getUri());
        $this->submit($this
            ->getUserConfirmationFiller()
            ->fillForm($this->getCrawler()))
        ;
        $this->followRedirect();
        $this->performHighSecurityIdCheck();
        $this->assertEquals(
            $originalNOfU2fKeys - 1,
            count($this
                ->get('doctrine')
                ->getManager()
                ->getRepository(U2fToken::class)
                ->getU2fTokens($member->getId())))
        ;
        $this->assertNull($this->getLoggedInMember());
    }
}

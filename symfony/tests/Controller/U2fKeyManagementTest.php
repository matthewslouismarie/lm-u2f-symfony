<?php

namespace App\Tests\Controller;

use App\Entity\U2fToken;
use App\Service\AppConfigManager;
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
            ->getU2fTokens($member)
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
            ->getU2fTokens($member)
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
        $newNOfU2fKeys = count($this
            ->get('doctrine')
            ->getManager()
            ->getRepository(U2fToken::class)
            ->getU2fTokens($member))
        ;
        $this->assertEquals(
            $originalNOfU2fKeys - 1,
            $newNOfU2fKeys)
        ;
        $requiredNKeys = $this
            ->get('App\Service\AppConfigManager')
            ->getIntSetting(AppConfigManager::POST_AUTH_N_U2F_KEYS)
        ;
        if ($newNOfU2fKeys < $requiredNKeys) {
            $this->doGet('/');
            $this->assertContains(
                'you need to register',
                $this->getCrawler()->text()
            );  
        }
    }
}

<?php

namespace App\Tests;

use App\Entity\Member;
use App\DataFixtures\AppFixture;

class AccountDeletionTest extends TestCaseTemplate
{
    use LoginTrait;

    public function testAccountDeletion()
    {
        $this->assertNotNull($this
            ->getObjectManager()
            ->getRepository(Member::class)
            ->findOneBy([
                'username' => AppFixture::ADMIN_USERNAME,
            ])
        );
        $this->assertTrue(true);
        $this->login();
        $this->doGet('/authenticated/my-account/delete-account');
        $this->assertContains(
            'Do you really want to delete your account?',
            $this->getClient()->getResponse()->getContent())
        ;
        $this->submit($this
            ->get('App\Service\Form\Filler\UserConfirmationFiller')
            ->fillForm($this->getCrawler()))
        ;
        $this->followRedirect();
        $this->authenticateAsAdmin();
        $this->assertNull($this
            ->getObjectManager()
            ->getRepository(Member::class)
            ->findOneBy([
                'username' => AppFixture::ADMIN_USERNAME,
            ])
        );
        $this->assertNull($this->getLoggedInMember());
    }
}

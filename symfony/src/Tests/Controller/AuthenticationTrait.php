<?php

namespace App\Tests\Controller;

use App\Enum\SecurityStrategy;
use App\Enum\Setting;
use Exception;

trait AuthenticationTrait
{
    public function u2fAuthenticate()
    {
        $this->doGet('/not-authenticated/authenticate');
        $this->followRedirect();
        $this->followRedirect();
        $this->submit(
            $this
            ->get('App\Service\Form\Filler\ExistingUsernameFiller')
            ->fillForm($this->getCrawler(), 'louis'))
        ;
        $this->followRedirect();

        $this->submit(
            $this
            ->get('App\Service\Form\Filler\U2fAuthenticationFiller1')
            ->fillForm($this->getCrawler(), $this->getUriLastPart()))
        ;

        $this->followRedirect();
    }

    public function pwdAuthenticate()
    {
        $this->doGet('/not-authenticated/pwd-authenticate');
        $this->followRedirect();
        $this->followRedirect();
        $this->submit(
            $this
            ->get('App\Service\Form\Filler\CredentialAuthenticationFiller')
            ->fillForm($this->getCrawler(), 'hello', 'louis'))
        ;
        $this->followRedirect();
        $this->followRedirect();
    }

    /**
     * @todo Delete.
     */
    private function performHighSecurityIdCheck()
    {
        $this->followRedirect();
        $this->submit($this
            ->getU2fAuthenticationFiller()
            ->fillForm($this->getCrawler(), $this->getUriLastPart()))
        ;
        $this->followRedirect();
    }

    private function performHighSecurityAuthenticationAsLouis()
    {
        $this->followRedirect();
        $this->followRedirect();
        switch ($this->getAppConfigManager()->getIntSetting(Setting::SECURITY_STRATEGY)) {
            case SecurityStrategy::U2F:
                $this->submit($this
                    ->get('App\Service\Form\Filler\U2fAuthenticationFiller1')
                    ->fillForm($this->getCrawler(), $this->getUriLastPart()))
                ;
                $this->followRedirect();
                break;

            case SecurityStrategy::PWD:
                $this->submit($this
                    ->get('App\Service\Form\Filler\CredentialAuthenticationFiller')
                    ->fillForm($this->getCrawler(), 'louis', 'hello'))
                ;
                $this->followRedirect();
                break;

            default:
                throw new Exception();
        }
    }
}

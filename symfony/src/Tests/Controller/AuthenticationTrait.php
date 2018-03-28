<?php

namespace App\Tests\Controller;

use App\Enum\SecurityStrategy;
use App\Enum\Setting;
use Exception;

trait AuthenticationTrait
{
    /**
     * @todo Make public.
     * @todo Rename to u2fAuthenticate().
     */
    private function authenticateAsLouis()
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

    /**
     * @todo Make public.
     * @todo Rename to pwdAuthenticate().
     */
    private function pwdAuthenticateAsLouis()
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

<?php

namespace App\Tests;

use LM\Authentifier\Model\AuthenticationProcess;
use LM\Authentifier\Model\DataManager;
use LM\Authentifier\Model\RequestDatum;
use LM\Common\Enum\Scalar;
use LM\Common\Model\ArrayObject;
use LM\Common\Model\StringObject;

class MiddlewareSessionTest extends TestCaseTemplate
{
    public function testSerialization()
    {
        $session = $this->getSecureSession();

        $authentifiers = [
            ExistingUsernameChallenge::class,
            U2fChallenge::class,
        ];
        $dataManager = new DataManager([
            new RequestDatum("used_u2f_key_public_keys", new ArrayObject([], StringObject::class)),
            new RequestDatum("challenges", new ArrayObject($authentifiers, Scalar::_STR)),
        ]);
        $authenticationProcess = new AuthenticationProcess($dataManager);
        $challenges = $authenticationProcess->getChallenges();
        $challenges->setToNextItem();
        $sid = $session->storeObject(
            new AuthenticationProcess($authenticationProcess
                ->getDataManager()
                ->replace(
                    new RequestDatum("challenges", $challenges),
                    RequestDatum::KEY_PROPERTY)),
            AuthenticationProcess::class)
        ;
        $unserializedProcess = $session->getObject($sid, AuthenticationProcess::class);
        $this->assertSame(
            U2fChallenge::class,
            $unserializedProcess->getCurrentChallenge());
    }
}

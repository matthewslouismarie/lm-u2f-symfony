<?php

namespace App\Tests;

use LM\Authentifier\Model\AuthenticationProcess;
use LM\Common\DataStructure\TypedMap;
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
        $dataManager = new TypedMap([
            'used_u2f_key_public_keys' => new ArrayObject([], StringObject::class),
            'challenges' => new ArrayObject($authentifiers, Scalar::_STR),
        ]);
        $authenticationProcess = new AuthenticationProcess($dataManager);
        $challenges = $authenticationProcess->getChallenges();
        $challenges->setToNextItem();
        $sid = $session->storeObject(
            new AuthenticationProcess($authenticationProcess
                ->getDataManager()
                ->set('challenges', $challenges, ArrayObject::class)),
            AuthenticationProcess::class)
        ;
        $unserializedProcess = $session->getObject($sid, AuthenticationProcess::class);
        $this->assertSame(
            U2fChallenge::class,
            $unserializedProcess->getCurrentChallenge());
    }
}

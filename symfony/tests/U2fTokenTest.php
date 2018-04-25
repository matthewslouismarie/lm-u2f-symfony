<?php

declare(strict_types=1);

namespace App\Tests;

use App\Factory\U2fRegistrationFactory;
use LM\AuthAbstractor\Implementation\U2fRegistration;

class U2fTokenTest extends TestCaseTemplate
{
    use LoginTrait;

    public function testU2fToken()
    {
        $this->login();

        $u2fReg = new U2fRegistration(
            'MIICSjCCATKgAwIBAgIEEkpy/jANBgkqhkiG9w0BAQsFADAuMSwwKgYDVQQDEyNZdWJpY28gVTJGIFJvb3QgQ0EgU2VyaWFsIDQ1NzIwMDYzMTAgFw0xNDA4MDEwMDAwMDBaGA8yMDUwMDkwNDAwMDAwMFowLDEqMCgGA1UEAwwhWXViaWNvIFUyRiBFRSBTZXJpYWwgMjQ5NDE0OTcyMTU4MFkwEwYHKoZIzj0CAQYIKoZIzj0DAQcDQgAEPYsbvS/L9ghuEHRxYBRoSEFTwcbTtLaKXoVebkB1fuIrzYmIvzvv183yHLC/XXoVDYRK/pgQPGxmB9n6rih8AqM7MDkwIgYJKwYBBAGCxAoCBBUxLjMuNi4xLjQuMS40MTQ4Mi4xLjEwEwYLKwYBBAGC5RwCAQEEBAMCBSAwDQYJKoZIhvcNAQELBQADggEBAKFPHuoAdva4R2oQor5y5g0CcbtGWy37/Hwb0S01GYmRcDJjHXldCX+jCiajJWNOhXIbwtAahjA/a8B15ZlzGeEiFIsElu7I0fT5TPQRDeYmwolEPR8PW7sjnKE+gdHVqp31r442EmR1v8I68GKDFXJSdi/2iHm88O9XjVXWf5UbTzK2PIrqWw+Zxn19gUp/9ab1Lfg+iUo6XZyLguf4vI2vTIAXX/iXL9p5Mz7EZdgG6syUjxurIgRalVWKSMICJtrAA9QfvJ4F6iimu14QpJ3gYKCk9qJnajTWjEq+jGGHQ1W5An6CjKngZLAC1i6NjPB0SSF1PTXjyHxdV3lFPnc=',
            0,
            'PeTDOgdeJiftM3YOMVzr4lBEdMoR+wRdYARe8eWnuSB9V8VeD1wjcRkhbOadiZBSh/J/7XrQN4h31PjOaK+JwA==',
            'BAcdB+X8+hq8MulBDtyfknw+bJsjyrK74dGuVg2hx6gSjFg3rHrhcH6J92r6qCBRYogNo04eeSV5XwwquVGFpFI='
        );
        $u2fToken = $this
            ->get(U2fRegistrationFactory::class)
            ->toEntity($u2fReg, $this->getLoggedInMember())
        ;
        $this->assertSame(
            $u2fReg->getAttestationCertificate(),
            $u2fToken->getAttestationCertificate()
        );
        $this->assertSame(
            $u2fReg->getCounter(),
            $u2fToken->getCounter()
        );
        $this->assertSame(
            $u2fReg->getKeyHandle(),
            $u2fToken->getKeyHandle()
        );
        $this->assertSame(
            $u2fReg->getPublicKey(),
            $u2fToken->getPublicKey()
        );
    }
}

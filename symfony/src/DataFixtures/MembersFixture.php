<?php

namespace App\DataFixtures;

use App\Entity\Member;
use App\Entity\U2FToken;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class MembersFixture extends Fixture
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        $member = new Member(null, 'louis');
        $encoded = $this->encoder->encodePassword($member, 'hello');
        $member->setPassword($encoded);
        $manager->persist($member);

        $u2fToken = new U2FToken(
            null,
            'MIICSjCCATKgAwIBAgIEEkpy/jANBgkqhkiG9w0BAQsFADAuMSwwKgYDVQQDEyNZdWJpY28gVTJGIFJvb3QgQ0EgU2VyaWFsIDQ1NzIwMDYzMTAgFw0xNDA4MDEwMDAwMDBaGA8yMDUwMDkwNDAwMDAwMFowLDEqMCgGA1UEAwwhWXViaWNvIFUyRiBFRSBTZXJpYWwgMjQ5NDE0OTcyMTU4MFkwEwYHKoZIzj0CAQYIKoZIzj0DAQcDQgAEPYsbvS/L9ghuEHRxYBRoSEFTwcbTtLaKXoVebkB1fuIrzYmIvzvv183yHLC/XXoVDYRK/pgQPGxmB9n6rih8AqM7MDkwIgYJKwYBBAGCxAoCBBUxLjMuNi4xLjQuMS40MTQ4Mi4xLjEwEwYLKwYBBAGC5RwCAQEEBAMCBSAwDQYJKoZIhvcNAQELBQADggEBAKFPHuoAdva4R2oQor5y5g0CcbtGWy37/Hwb0S01GYmRcDJjHXldCX+jCiajJWNOhXIbwtAahjA/a8B15ZlzGeEiFIsElu7I0fT5TPQRDeYmwolEPR8PW7sjnKE+gdHVqp31r442EmR1v8I68GKDFXJSdi/2iHm88O9XjVXWf5UbTzK2PIrqWw+Zxn19gUp/9ab1Lfg+iUo6XZyLguf4vI2vTIAXX/iXL9p5Mz7EZdgG6syUjxurIgRalVWKSMICJtrAA9QfvJ4F6iimu14QpJ3gYKCk9qJnajTWjEq+jGGHQ1W5An6CjKngZLAC1i6NjPB0SSF1PTXjyHxdV3lFPnc=',
            0,
            'v8IplXz0zSQUXVYjvSWNcP/70AamVDoaROr1UcREnWaARrRABftdhhaKTFsYTgOj5CH6BUYxztAN9qrU3WcBZg==',
            $member,
            new \DateTimeImmutable('2018-01-05 21:26:10.282143'),
            'BPXPn5wJaS5cnRfe45NYPv/1foHyRIPMFn4ABhzu8jXbnuGbZXHrDS3gmwP1OywFqADOYsQMg14GbQk1+RDBhHQ=');
        $manager->persist($u2fToken);
        $manager->flush();
    }
}
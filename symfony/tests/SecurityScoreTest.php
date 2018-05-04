<?php

declare(strict_types=1);

namespace App\Tests;

use App\Model\U2fChallengeDefinition;
use App\Model\PwdChallengeDefinition;
use App\Service\SecurityScoreCalculator;
use InvalidArgumentException;

class SecurityScoreTest extends TestCaseTemplate
{
    public function testCpwdVsSpwdU2f()
    {
        $securityScoreCalculator = $this->get(SecurityScoreCalculator::class);
        $cpwdScore = $securityScoreCalculator->calculate([
            [
                new PwdChallengeDefinition(8, true, true, true),
            ],
        ]);
        $spwdU2fScore = $securityScoreCalculator->calculate([
            [
                new U2fChallengeDefinition(),
                new PwdChallengeDefinition(0, false, false, false),
            ],
        ]);
        $this->assertTrue($spwdU2fScore > $cpwdScore);
    }

    public function testUnexistingChallenge()
    {
        $securityScoreCalculator = $this->get(SecurityScoreCalculator::class);
        $this->expectException(InvalidArgumentException::class);
        $cpwdScore = $securityScoreCalculator->calculate([
            [
                [
                    'id' => 'unexistingchallenge',
                ],
            ],
        ]);
    }

    public function testPasswordComplexity()
    {
        $securityScoreCalculator = $this->get(SecurityScoreCalculator::class);
        $spwdScore = $securityScoreCalculator->calculate([
            [
                new PwdChallengeDefinition(0, false, false, false),
            ],
        ]);
        $cpwdScore = $securityScoreCalculator->calculate([
            [
                new PwdChallengeDefinition(8, true, true, true),
            ],
        ]);
        $this->assertTrue($cpwdScore > $spwdScore);
    }

    public function testWeakestLink()
    {
        $securityScoreCalculator = $this->get(SecurityScoreCalculator::class);
        $spwdScore = $securityScoreCalculator->calculate([
            [
                new PwdChallengeDefinition(0, false, false, false),
            ],
        ]);
        $cpwdScore = $securityScoreCalculator->calculate([
            [
                new PwdChallengeDefinition(8, true, true, true),
            ],
        ]);
        $cspwdScore = $securityScoreCalculator->calculate([
            [
                new PwdChallengeDefinition(0, false, false, false),
            ],
            [
                new PwdChallengeDefinition(8, true, true, true),
            ],
        ]);
        $this->assertTrue(intval($spwdScore) === intval($cspwdScore));
    }

    public function testFactorAmplifier()
    {
        $securityScoreCalculator = $this->get(SecurityScoreCalculator::class);
        $spwdU2fProcessScore = $securityScoreCalculator->calculateProcessScore([
            new PwdChallengeDefinition(0, false, false, false),
            new U2fChallengeDefinition(),
        ]);
        $spwdScore = $securityScoreCalculator->calculateChallengeScore(
            new PwdChallengeDefinition(0, false, false, false)
        );
        $u2fScore = $securityScoreCalculator->calculateChallengeScore(
            new U2fChallengeDefinition()
        );
        $this->assertTrue($spwdU2fProcessScore > $spwdScore + $u2fScore);
    }

    public function testNegativePwdMinLength()
    {
        $this->expectException(InvalidArgumentException::class);
        new PwdChallengeDefinition(-1, false, false, false);
    }

    public function testNFactors()
    {
        $securityScoreCalculator = $this->get(SecurityScoreCalculator::class);
        $this->assertSame(
            2,
            intval($securityScoreCalculator->getNFactors([
                new PwdChallengeDefinition(0, false, false, false),
                new U2fChallengeDefinition(),
            ]))
        );
        $this->assertSame(
            1,
            intval($securityScoreCalculator->getNFactors([
                new PwdChallengeDefinition(0, false, false, false),
            ]))
        );
        $this->assertTrue(
            $securityScoreCalculator->getNFactors([
                new U2fChallengeDefinition(),
                new U2fChallengeDefinition(),
            ]) >
            $securityScoreCalculator->getNFactors([
                new PwdChallengeDefinition(0, false, false, false),
            ])
        );
        $this->assertSame(
            1,
            intval($securityScoreCalculator->getNFactors([
                new PwdChallengeDefinition(0, false, false, false),
                new PwdChallengeDefinition(8, true, true, true),
            ]))
        );
        $this->assertSame(
            0,
            intval($securityScoreCalculator->getNFactors([]))
        );
    }

    public function testDuplicateChallengeFactor()
    {
        $u2fChallengeDef = new U2fChallengeDefinition();
        $pwdChallengeDef = new PwdChallengeDefinition(0, false, false, false);
        $this->assertTrue(
            $u2fChallengeDef->getDuplicationFactor() >
            $pwdChallengeDef->getDuplicationFactor()
        );
    }

    public function testSimplePassword()
    {
        $securityScoreCalculator = $this->get(SecurityScoreCalculator::class);
        
        $pwdChallengeDef = new PwdChallengeDefinition(6, false, false, false);
        $this->assertGreaterThan(
            0,
            $securityScoreCalculator->calculate([
                [
                    $pwdChallengeDef,
                ],
            ])
        );
    }
}

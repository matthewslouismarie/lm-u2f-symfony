<?php

declare(strict_types=1);

namespace App\Tests;

use App\Service\SecurityScoreCalculator;
use InvalidArgumentException;

class SecurityScoreTest extends TestCaseTemplate
{
    const CPWD_PROCESS = [
        [
            'id' => 'pwd',
            'min_length' => 8,
            'special_chars' => true,
            'numbers' => true,
            'uppercase' => true,
        ],
    ];

    const SPWD_CHALLENGE =  [
        'id' => 'pwd',
        'min_length' => 0,
        'special_chars' => false,
        'numbers' => false,
        'uppercase' => false,
    ];

    const SPWD_PROCESS = [
        self::SPWD_CHALLENGE,
    ];

    const SPWDU2F_PROCESS = [
        [
            'id' => 'pwd',
            'min_length' => 0,
            'special_chars' => false,
            'numbers' => false,
            'uppercase' => false,
        ],
        [
            'id' => 'u2f',
        ],
    ];

    const U2F_CHALLENGE =  [
        'id' => 'u2f',
    ];

    const U2F_PROCESS = [
        self::U2F_CHALLENGE
    ];

    public function testCpwdVsSpwdU2f()
    {
        $securityScoreCalculator = $this->get(SecurityScoreCalculator::class);
        $cpwdScore = $securityScoreCalculator->calculate([
            self::CPWD_PROCESS,
        ]);
        $spwdU2fScore = $securityScoreCalculator->calculate([
            self::SPWDU2F_PROCESS,
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
                ]
            ],
        ]);
    }

    public function testPasswordComplexity()
    {
        $securityScoreCalculator = $this->get(SecurityScoreCalculator::class);
        $spwdScore = $securityScoreCalculator->calculate([
            self::SPWD_PROCESS,
        ]);
        $cpwdScore = $securityScoreCalculator->calculate([
            self::CPWD_PROCESS,
        ]);
        $this->assertTrue($cpwdScore > $spwdScore);
    }

    public function testWeakestLink()
    {
        $securityScoreCalculator = $this->get(SecurityScoreCalculator::class);
        $spwdScore = $securityScoreCalculator->calculate([
            self::SPWD_PROCESS,
        ]);
        $cpwdScore = $securityScoreCalculator->calculate([
            self::CPWD_PROCESS,
        ]);
        $cspwdScore = $securityScoreCalculator->calculate([
            self::SPWD_PROCESS,
            self::CPWD_PROCESS,
        ]);
        $this->assertTrue(intval($spwdScore) === intval($cspwdScore));
    }

    public function testFactorAmplifier()
    {
        $securityScoreCalculator = $this->get(SecurityScoreCalculator::class);
        $spwdU2fProcessScore = $securityScoreCalculator->calculateProcessScore(
            self::SPWDU2F_PROCESS
        );
        $spwdScore = $securityScoreCalculator->calculateChallengeScore(
            self::SPWD_CHALLENGE
        );
        $u2fScore = $securityScoreCalculator->calculateChallengeScore(
            self::U2F_CHALLENGE
        );
        $this->assertTrue($spwdU2fProcessScore > $spwdScore + $u2fScore);
    }

    public function testDuplicateChallenges()
    {
    }

    public function testNegativePwdMinLength()
    {
        $securityScoreCalculator = $this->get(SecurityScoreCalculator::class);
        $this->expectException(InvalidArgumentException::class);
        $securityScoreCalculator->calculate([
            [
                'id' => 'pwd',
                'min_length' => -1,
                'special_chars' => true,
                'numbers' => true,
                'uppercase' => true,
            ],
        ]);
    }

    public function testMissingParameters()
    {
        $securityScoreCalculator = $this->get(SecurityScoreCalculator::class);
        $this->expectException(InvalidArgumentException::class);
        $securityScoreCalculator->calculate([
            [
                'id' => 'pwd',
                'min_length' => 0,
                'uppercase' => true,
                'numbers' => true,
            ],
        ]);
    }

    public function testNFactors()
    {
        $securityScoreCalculator = $this->get(SecurityScoreCalculator::class);
        $this->assertSame(
            2,
            intval($securityScoreCalculator->getNFactors(self::SPWDU2F_PROCESS))
        );
        $this->assertSame(
            1,
            intval($securityScoreCalculator->getNFactors(self::SPWD_PROCESS))
        );
        $this->assertTrue(
            $securityScoreCalculator->getNFactors([
                self::U2F_CHALLENGE,
                self::U2F_CHALLENGE,
            ]) >
            $securityScoreCalculator->getNFactors(self::SPWD_PROCESS)
        );
        $this->assertSame(
            1,
            intval($securityScoreCalculator->getNFactors([
                [
                    'id' => 'pwd',
                    'min_length' => 0,
                    'special_chars' => false,
                    'numbers' => false,
                    'uppercase' => false,
                ],
                [
                    'id' => 'pwd',
                    'min_length' => 8,
                    'special_chars' => true,
                    'numbers' => true,
                ],
            ]))
        );
        $this->assertSame(
            0,
            intval($securityScoreCalculator->getNFactors([]))
        );
    }

    public function testNFactorsInvalidProcess()
    {
        $securityScoreCalculator = $this->get(SecurityScoreCalculator::class);
        $this->expectException(InvalidArgumentException::class);
        $securityScoreCalculator->getNFactors([
            [
                'min_length' => 5,
            ]
        ]);
    }

    public function testDuplicateChallengeFactor()
    {
        $securityScoreCalculator = $this->get(SecurityScoreCalculator::class);
        $this->assertTrue(
            $securityScoreCalculator->getDuplicateChallengeFactor('u2f') >
            $securityScoreCalculator->getDuplicateChallengeFactor('pwd')
        );
    }
}

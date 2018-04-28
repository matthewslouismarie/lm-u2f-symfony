<?php

declare(strict_types=1);

namespace App\Service;

use App\Enum\Setting;
use LM\Common\Enum\Scalar;
use App\Enum\SecurityStrategy;

class UserErrorFinder
{
    private $rules;

    public function __construct(AppConfigManager $config)
    {
        $securityStrategy = $config->getSetting(
            Setting::SECURITY_STRATEGY,
            Scalar::_STR
        );
        $nU2fRegistrations = $config->getSetting(
            Setting::N_U2F_KEYS_LOGIN,
            Scalar::_INT
        );
        $nTransferMoney = SecurityStrategy::U2F === $securityStrategy ? $nU2fRegistrations + 2 : 2;
        $this->rules = [
            '/\/not-authenticated\/login\/u2f\/[a-z0-9]+/' => $nU2fRegistrations + 1,
            '/\/not-authenticated\/login\/pwd\/[a-z0-9]+/' => 2,
            '/\/not-authenticated\/register\/(?!u2f-key)[a-z0-9]+/' => 2,
            '/\/not-authenticated\/register\/u2f-key\/[a-z0-9]+/' => 2,
            '/\/authenticated\/transfer-money\/[a-z0-9]+/' => $nTransferMoney,
        ];
    }

    public function getNErrors(array $uris): int
    {
        if (0 === count($uris)) {
            return 0;
        }

        $nErrors = 0;
        while (null !== ($currentUri = array_pop($uris))) {
            $nErrors += $this->isError($currentUri, $uris) ? 1 : 0;
        }

        return $nErrors;
    }

    /**
     * $uris must be 0, 1, 2, etc. with the latest request higher num
     * @todo Doesn't check for preg_match returning false.
     */
    public function isError(string $currentUri, array $previousUris): bool
    {
        $latestUriIndex = count($previousUris) - 1;
        $rule = $this->getRule($currentUri);

        if (null === $rule) {
            return false;
        }

        $nPreviousUris = count($previousUris);
        if ($rule['nUris'] > $nPreviousUris) {
            return false;
        }
        $lastUriIndex = $nPreviousUris - 1;

        for ($i = $lastUriIndex; ($lastUriIndex - $i) < $rule['nUris']; $i--) {
            if (0 === preg_match($rule['regex'], $previousUris[$i])) {
                return false;
            }
        }

        return true;
    }

    /**
     * @todo Doesn't check for preg_match returning false.
     */
    private function getRule(string $uri): ?array
    {
        foreach ($this->rules as $regex => $nUris) {
            if (1 === preg_match($regex, $uri)) {
                return [
                    'nUris' => $nUris,
                    'regex' => $regex,
                ];
            }
        }

        return null;
    }
}

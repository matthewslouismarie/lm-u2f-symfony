<?php

$filePath = __DIR__.'/sql.sql';

define('REGEXES', [
    'reg_start' => '/\'\/not-authenticated\/registration\/start\'/',
    // 'reg_post_cred' => '/\'\/not-authenticated\/register\/.+\'/', // (if the one after is u2f-key)
    'reg_post_u2f' => '/\'\/not-authenticated\/register\/u2f-key\/.+\/\'/', // (if the one after is not /not-authenticated/register/u2f-key/{sid})
    'choose_login' => '/\'\/not-authenticated\/choose-authenticate\'/',
    'login_u2f' => '/\'\/not-authenticated\/login\/u2f\'/',
    'login_pwd' => '/\'\/not-authenticated\/login\/pwd\'/',
    'trsf_money' => '/\'\/authenticated\/transfer-money\'/', // if the previous one was transfer-money as well
    'logout' => '/\'\/authenticated\/logout\'/',
]);

$nRedirections = 0;
$nResponses = 0;

$lines = file($filePath);
$nLines = count($lines);
for ($i = 0; $i < $nLines; ++$i) {
    $line = $lines[$i];

    /**
     * substr is faster than a regex expression, of course it's based
     * on the assumption on the file structure.
     */
    if (false !== strpos($line, 'TYPE_RESPONSE')) {
        ++$nResponses;
        $matches = array_filter(
            REGEXES,
            function (string $regex) use ($line): bool {
                $match = preg_match($regex, $line);
                if (false === $match) {
                    throw new Exception('preg_match() encountered an error.');
                }

                return 1 === $match;
            }
        );

        $isRedirection = null;
        if (1 === count($matches)) {
            switch (current($matches)) {
                case REGEXES['reg_post_cred']:
                    if (isset($lines[$i+1]) && 1 === preg_match(REGEXES['reg_post_u2f'], $lines[$i + 1])) {
                        $isRedirection = true;
                    } else {
                        $isRedirection = false;
                    }
                    break;

                case REGEXES['reg_post_u2f']:
                    if (isset($lines[$i + 1]) && 0 === preg_match(REGEXES['reg_post_u2f'], $lines[$i + 1])) {
                        $isRedirection = true;
                    } else {
                        $isRedirection = false;
                    }
                    break;

                case REGEXES['trsf_money']:
                        if (isset($lines[$i - 2]) && 1 === preg_match(REGEXES['trsf_money'], $lines[$i - 2])) {
                        $isRedirection = true;
                    } else {
                        $isRedirection = false;
                    }
                    break;

                default:
                    $isRedirection = true;
            }
        } elseif (0 === count($matches)) {
            $isRedirection = false;
        } else {
            throw new Exception('Error in the regexes: they should be exclusive.');            
        }

        if (true === $isRedirection) {
            $line = str_replace('NULL', '1', $line);
            ++$nRedirections;
        } elseif (false === $isRedirection) {
            $line = str_replace('NULL', '0', $line);
        } else {
            throw new Exception('Error in the code: $isRedirection can\'t be null!');
        }

    }
    // Replace P (as in P0) by p
    $line = str_replace(', \'P', ', \'p', $line);

    file_put_contents('sql/sqltransformed.sql', $line, FILE_APPEND);
}

$redirectionsPercent = ($nRedirections / $nResponses) * 100;

echo "\n{$nRedirections} redirections ({$redirectionsPercent}%)\n";

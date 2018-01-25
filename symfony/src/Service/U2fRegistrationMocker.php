<?php

namespace App\Service;

use App\Model\U2fRegistrationCycle;
use Firehed\U2F\RegisterRequest;

class U2fRegistrationMocker
{
    private $cycles;

    private $currentIndex;

    public function __construct()
    {
        $this->currentIndex = 0;
        $this->cycles = [
            $this->getFirstCycle(),
        ];
    }

    public function getNewCycle()
    {
        return $this->cycles[$this->currentIndex++];
    }

    public function getFirstCycle(): U2fRegistrationCycle
    {
        $request = new RegisterRequest();
        $request->setAppId('https://172.16.238.10');
        $request->setChallenge('H270HTBdA-03iu2acPm_MA');
        $cycle = new U2fRegistrationCycle(
            $request,
            '{"registrationData":"BQRjFMi1-LYB4nWrYI4bFJLoZu4RkJ3RH-eey0ffOQf0WyPv1pGnt_r6ZCmBlEdQXRUuo3n1dMHUa1oF9OaLLv66QE2M2WIY061Fl9oWWuuAS0_1go9vxlSp5D-pti2K069qAsH7Hw_o7foTvpchxUq5IJwaVU2xjBTmnFkfJkeyWNcwggJKMIIBMqADAgECAgQSSnL-MA0GCSqGSIb3DQEBCwUAMC4xLDAqBgNVBAMTI1l1YmljbyBVMkYgUm9vdCBDQSBTZXJpYWwgNDU3MjAwNjMxMCAXDTE0MDgwMTAwMDAwMFoYDzIwNTAwOTA0MDAwMDAwWjAsMSowKAYDVQQDDCFZdWJpY28gVTJGIEVFIFNlcmlhbCAyNDk0MTQ5NzIxNTgwWTATBgcqhkjOPQIBBggqhkjOPQMBBwNCAAQ9ixu9L8v2CG4QdHFgFGhIQVPBxtO0topehV5uQHV-4ivNiYi_O-_XzfIcsL9dehUNhEr-mBA8bGYH2fquKHwCozswOTAiBgkrBgEEAYLECgIEFTEuMy42LjEuNC4xLjQxNDgyLjEuMTATBgsrBgEEAYLlHAIBAQQEAwIFIDANBgkqhkiG9w0BAQsFAAOCAQEAoU8e6gB29rhHahCivnLmDQJxu0ZbLfv8fBvRLTUZiZFwMmMdeV0Jf6MKJqMlY06FchvC0BqGMD9rwHXlmXMZ4SIUiwSW7sjR9PlM9BEN5ibCiUQ9Hw9buyOcoT6B0dWqnfWvjjYSZHW_wjrwYoMVclJ2L_aIebzw71eNVdZ_lRtPMrY8iupbD5nGfX2BSn_1pvUt-D6JSjpdnIuC5_i8ja9MgBdf-Jcv2nkzPsRl2AbqzJSPG6siBFqVVYpIwgIm2sAD1B-8ngXqKKa7XhCkneBgoKT2omdqNNaMSr6MYYdDVbkCfoKMqeBksALWLo2M8HRJIXU9NePIfF1XeUU-dzBEAiASMs6Ae2s7NZ-n0UOqmrWC1ZGBjheWDpxRBePe9PLv-QIgeZ5f77JT3dVNo9c8IzVbBkLH_WjhHnG_RTqivZPsmnc","version":"U2F_V2","challenge":"H270HTBdA-03iu2acPm_MA","appId":"https://172.16.238.10","clientData":"eyJ0eXAiOiJuYXZpZ2F0b3IuaWQuZmluaXNoRW5yb2xsbWVudCIsImNoYWxsZW5nZSI6IkgyNzBIVEJkQS0wM2l1MmFjUG1fTUEiLCJvcmlnaW4iOiJodHRwczovLzE3Mi4xNi4yMzguMTAiLCJjaWRfcHVia2V5IjoidW51c2VkIn0"}')
        ;

        return $cycle;
    }
}

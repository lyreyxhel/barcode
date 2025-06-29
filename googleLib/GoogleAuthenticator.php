<?php
class GoogleAuthenticator {
    protected $_codeLength = 6;

    public function createSecret($secretLength = 16) {
        $validChars = $this->_getBase32LookupTable();
        unset($validChars[32]);

        $secret = '';
        for ($i = 0; $i < $secretLength; $i++) {
            $secret .= $validChars[array_rand($validChars)];
        }
        return $secret;
    }

    public function getCode($secret, $timeSlice = null) {
        if ($timeSlice === null) {
            $timeSlice = floor(time() / 30);
        }

        $secretkey = $this->_base32Decode($secret);
        $time = chr(0).chr(0).chr(0).chr(0).pack('N*', $timeSlice);
        $hm = hash_hmac('sha1', $time, $secretkey, true);
        $offset = ord(substr($hm, -1)) & 0x0F;
        $hashpart = substr($hm, $offset, 4);
        $value = unpack("N", $hashpart)[1];
        $value = $value & 0x7FFFFFFF;

        return str_pad($value % pow(10, $this->_codeLength), $this->_codeLength, '0', STR_PAD_LEFT);
    }

    public function verifyCode($secret, $code, $discrepancy = 1, $currentTimeSlice = null) {
        if ($currentTimeSlice === null) {
            $currentTimeSlice = floor(time() / 30);
        }

        for ($i = -$discrepancy; $i <= $discrepancy; $i++) {
            $calculatedCode = $this->getCode($secret, $currentTimeSlice + $i);
            if ($calculatedCode === $code) {
                return true;
            }
        }

        return false;
    }

    protected function _base32Decode($secret) {
        if (empty($secret)) return '';

        $base32chars = $this->_getBase32LookupTable();
        $base32charsFlipped = array_flip($base32chars);

        $paddingCharCount = substr_count($secret, '=');
        $allowedValues = [6, 4, 3, 1, 0];
        if (!in_array($paddingCharCount, $allowedValues)) return false;

        for ($i = 0; $i < 4; $i++) {
            if ($paddingCharCount == $allowedValues[$i] &&
                substr($secret, -($allowedValues[$i])) != str_repeat('=', $allowedValues[$i])) return false;
        }

        $secret = str_replace('=', '', $secret);
        $binaryString = '';
        for ($i = 0; $i < strlen($secret); $i = $i + 8) {
            $x = '';
            if (!in_array($secret[$i], $base32chars)) return false;

            for ($j = 0; $j < 8; $j++) {
                $x .= str_pad(base_convert(@$base32charsFlipped[@$secret[$i + $j]], 10, 2), 5, '0', STR_PAD_LEFT);
            }

            $eightBits = str_split($x, 8);
            for ($z = 0; $z < count($eightBits); $z++) {
                $binaryString .= chr(base_convert($eightBits[$z], 2, 10));
            }
        }

        return $binaryString;
    }

    protected function _getBase32LookupTable() {
        return [
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', // 7
            'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', // 15
            'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', // 23
            'Y', 'Z', '2', '3', '4', '5', '6', '7', // 31
            '='  // padding char
        ];
    }
}

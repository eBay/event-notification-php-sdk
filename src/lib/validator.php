<?php

/*
 * *
 *  * Copyright 2022 eBay Inc.
 *  *
 *  * Licensed under the Apache License, Version 2.0 (the "License");
 *  * you may not use this file except in compliance with the License.
 *  * You may obtain a copy of the License at
 *  *
 *  *  http://www.apache.org/licenses/LICENSE-2.0
 *  *
 *  * Unless required by applicable law or agreed to in writing, software
 *  * distributed under the License is distributed on an "AS IS" BASIS,
 *  * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  * See the License for the specific language governing permissions and
 *  * limitations under the License.
 *  *
 */

namespace EventNotificationPHPSdk\lib;

use EventNotificationPHPSdk\lib\Client;
use EventNotificationPHPSdk\lib\Constants;

class Validator {
    private $client;
    private $constants;

    function __construct() {
        $this->client = new Client();
        $this->constants = new Constants();
    }

    /**
     * Generates challenge response.
     * 
     * @param string $challengeCode
     * @param array $config
     * @return string challengeResponse
     */
    public function generateChallengeResponse($challengeCode, $config) {
        $hash = hash_init($this->constants::SHA256);

        hash_update($hash, $challengeCode);
        hash_update($hash, $config['verificationToken']);
        hash_update($hash, $config['endpoint']);

        $responseHash = hash_final($hash);
        return $responseHash;
    }

    /**
     * Validate the signature.
     * 
     * @param array $message
     * @param string $signatureHeader
     * @param array $config
     */
    public function validateSignature($message, $signatureHeader, $config) {
        $signature = json_decode(base64_decode($signatureHeader), true) ?: [];
        if (empty($signature['kid'])) {
            throw new \Exception('Signature not decoded.');
        }
        $publicKey = $this->client->getPublicKey($signature['kid'], $config);

        if (preg_match($this->constants::KEY_PATTERN, $publicKey['key'], $matches)) {
            $key = $this->constants::KEY_START
                . implode("\n", str_split($matches[1], 64))
                . $this->constants::KEY_END;
        } else {
            throw new \Exception('Invalid key.');
        }

        return openssl_verify(
            json_encode($message),
            base64_decode($signature['signature']),
            $key,
            OPENSSL_ALGO_SHA1
        );
    }

    /**
     * Attach the Client object.
     * 
     * @param Client $client
     */
    public function attach($client) {
        $this->client = $client;
    }
}
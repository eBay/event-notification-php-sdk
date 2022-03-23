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

use GuzzleHttp\Client;
use EventNotificationPHPSdk\lib\Constants;

class Oauth {
    private $constants;
    private $credentials;
    private $grantType;
    private $scope;

    function __construct($options) {
        if($options === null){
            throw new \Exception("This method accepts an object with client id and client secret.");
        }
        $this->constants = new Constants();
        $this->credentials = $this->readOptions($options);
        $this->grantType = '';
    }

    /**
     * Get app token.
     * 
     * @param string $environment
     * @return application token
     */
    public function getApplicationToken($environment) {
        $this->validateParams($environment, $this->constants::CLIENT_CERT_SCOPE, $this->credentials);
        $this->grantType = $this->constants::CLIENT_CREDENTIALS;
        $this->scope = $this->constants::CLIENT_CERT_SCOPE;
        $body = http_build_query(array(
            'grant_type' => $this->grantType,
            'scope' => $this->scope
        ));
        $clientId = $this->credentials[$environment]['clientId'];
        $clientSecret = $this->credentials[$environment]['clientSecret'];
        $headers = array(
            'Authorization' => $this->constants::BASIC . base64_encode($clientId.':'.$clientSecret),
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Content-Length' => strlen($body),
        );
        $client = new Client();
        $response = $client->post($this->credentials[$environment]['baseUrl'].'/identity/v1/oauth2/token', [
            'body' => $body,
            'headers' => $headers
        ]);
        $token = json_decode($response->getBody()->getContents(), true)['access_token'];
        return $token;
    }

    /**
     * Get the credentials from the input.
     * 
     * @param array $options
     * @return array credentials
     */
    private function readOptions($options) {
        $credentials = [];
        if($options['env'] === null){
            $options['env'] = 'PRODUCTION';
        }
        $options['baseUrl'] = $options['env'] === 'PRODUCTION'? $this->constants::PROD_BASE_URL : $this->constants::SANDBOX_BASE_URL;
        $credentials[$options['env']] = $options;
        return $credentials;
    }

    /**
     * Validate the parameters.
     * 
     * @param string $environment
     * @param string $scopes
     * @param array $credentials
     */
    private function validateParams($environment, $scopes, $credentials) {
        if($environment === null){
            throw new \Exception("Kindly provide the environment - PRODUCTION/SANDBOX.");
        }
        if($scopes === null){
            throw new \Exception("scopes is required.");
        }
        if($credentials === null){
            throw new \Exception("credentials configured incorrectly.");
        }
    }
}

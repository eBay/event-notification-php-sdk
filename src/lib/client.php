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

use EventNotificationPHPSdk\lib\Constants;
use EventNotificationPHPSdk\lib\Oauth;
use GuzzleHttp\Client as Httpclient;

class Client {
    private $constants;
    private $cache;
    private $oauth;

    function __construct() {
        $this->cache = new \LRUCache\LRUCache(1000);
        $this->constants = new Constants();
    }

    /**
     * Look for the Public key in cache, if not found call eBay Notification API.
     * 
     * @param string $keyId
     * @param array $config
     * @return string public key
     */
    public function getPublicKey($keyId, $config) {
        $publicKey = $this->cache->get($keyId);
        if($publicKey !== null) {
            return $publicKey;
        }
        try {
            $notificationApiEndpoint = $config['environment'] === $this->constants::ENVIRONMENT['SANDBOX'] ?
                $this->constants::NOTIFICATION_API_ENDPOINT_SANDBOX :
                $this->constants::NOTIFICATION_API_ENDPOINT_PRODUCTION;
            $accessToken = $this->getAppToken($config);
            $uri = $notificationApiEndpoint.$keyId;
            $headers = array(
                'Authorization' => $this->constants::BEARER.$accessToken,
                'Content-Type' => $this->constants::HEADERS['APPLICATION_JSON']
            );
            $httpclient = new Httpclient();
            $notificationApiResponse = $httpclient->get($uri, [
                'headers' => $headers
            ]);
            if ($notificationApiResponse === null || $notificationApiResponse->getStatusCode() !== $this->constants::HTTP_STATUS_CODE['OK']) {
                throw new \Exception("Public key retrieval failed with " . $notificationApiResponse->getStatusCode() . " for " . $uri);
            }
            $responseBody = json_decode($notificationApiResponse->getBody(), true);
            $this->cache->put($keyId, $notificationApiResponse->getBody()->getContents());
            return $responseBody;
        } catch(\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get app token.
     * 
     * @param array $config
     * @return application token
     */
    private function getAppToken($config) {
        $this->oauth = new Oauth(array(
            'clientId' => $config['clientId'],
            'clientSecret' => $config['clientSecret'],
            'env' => $config['environment'],
            'redirectUri' => ''
        ));
        $token = $this->oauth->getApplicationToken($config['environment']);
        return $token;
    }
}

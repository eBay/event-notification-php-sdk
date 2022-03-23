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

use EventNotificationPHPSdk\lib\Logger;
use EventNotificationPHPSdk\lib\Validator;
use EventNotificationPHPSdk\lib\Constants;
use EventNotificationPHPSdk\lib\processor\Processor;

class Index {
    private $validator;
    private $constants;
    private $processor;
    private $logger;

    function __construct() {
        $this->validator = new Validator();
        $this->constants = new Constants();
        $this->processor = new Processor();
        $this->logger = new Logger();
    }

    /**
     * Generates challenge response.
     * 
     * @param string $challengeCode
     * @param array $config
     * @return string challengeResponse
     */
    public function validateEndpoint($challengeCode, $config) {
        if ($challengeCode === null) {
            throw new \Exception("The challengeCode is required.");
        }
        if ($config === null) {
            throw new \Exception("Please provide the config.");
        }
        if ($config['endpoint'] === null) {
            throw new \Exception("The endpoint is required.");
        }
        if ($config['verificationToken'] === null) {
            throw new \Exception("The verificationToken is required.");
        }

        try {
            return $this->validator->generateChallengeResponse($challengeCode, $config);
        } catch (\Exception $e) {
            $this->logger->log($e);
        }
    }

    /**
     * Validate the signature and process the message.
     * 
     * @param array $message
     * @param string $signature
     * @param array $config
     * @param string $environment
     */
    public function process($message, $signature, $config, $environment) {
        if ($message === null || $message['metadata'] === null || $message['notification'] === null) {
            throw new \Exception("Please provide the message.");
        }
        if ($signature === null) {
            throw new \Exception("Please provide the signature.");
        }
        if ($config === null) {
            throw new \Exception("Please provide the config.");
        }
        if ($config['PRODUCTION']['clientId'] === null && $config['SANDBOX']['clientId'] === null) {
            throw new \Exception("Please provide the client ID.");
        }
        if ($config['PRODUCTION']['clientSecret'] === null && $config['SANDBOX']['clientSecret'] === null) {
            throw new \Exception("Please provide the client secret.");
        }
        if (
            $environment === null ||
            ($environment !== $this->constants::ENVIRONMENT['SANDBOX'] &&
            $environment !== $this->constants::ENVIRONMENT['PRODUCTION'])
        ) {
            throw new \Exception("Please provide the Environment.");
        }
        try {
            $envConfig = [];
            if ($environment === $this->constants::ENVIRONMENT['SANDBOX']) {
                $envConfig = $config['SANDBOX'];
                $envConfig['environment'] = $this->constants::ENVIRONMENT['SANDBOX'];
            } else {
                $envConfig = $config['PRODUCTION'];
                $envConfig['environment'] = $this->constants::ENVIRONMENT['PRODUCTION'];
            }

            $response = $this->validator->validateSignature($message, $signature, $envConfig);
            if ($response === 1) {
                $this->processor->getProcessor($message['metadata']['topic'])->processInternal($message);
                return $this->constants::HTTP_STATUS_CODE['NO_CONTENT'];
            }
            return $this->constants::HTTP_STATUS_CODE['PRECONDITION_FAILED'];
        } catch (\Exception $e) {
            $this->logger->log($e);
            return $this->constants::HTTP_STATUS_CODE['INTERNAL_SERVER_ERROR'];
        }
    }

    /**
     * Attach the Validator object.
     * 
     * @param Validator $validator
     */
    public function attach($validator) {
        $this->validator = $validator;
    }
}

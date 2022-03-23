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

namespace EventNotificationPHPSdk\lib\processor;

use EventNotificationPHPSdk\lib\Logger;

class AccountDeletionMessageProcessor {
    private $logger;

    function __construct() {
        $this->logger = new Logger();
	}

    /**
     * Process the message.
     * 
     * @param array $message
     */
    public function processInternal($message) {
        $data = $message['notification']['data'];
        $this->logger->log("\n==========================\nUser ID: " . $data['userId'] .
        "\nUsername: " . $data['username'] . "\n==========================\n");
    }
}

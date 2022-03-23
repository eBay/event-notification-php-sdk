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

class Constants {
    const BASIC = 'Basic ';
    const BEARER = 'bearer ';
    const CLIENT_CERT_SCOPE = 'https://api.ebay.com/oauth/api_scope';
    const CLIENT_CREDENTIALS = 'client_credentials';
    const ENVIRONMENT = [
        'SANDBOX' => 'SANDBOX',
        'PRODUCTION' => 'PRODUCTION'
    ];
    const HTTP_STATUS_CODE = [
        'NO_CONTENT' => 204,
        'OK' => 200,
        'PRECONDITION_FAILED' => 412,
        'INTERNAL_SERVER_ERROR' => 500
    ];
    const HEADERS = [
        'APPLICATION_JSON' => 'application/json'
    ];
    const KEY_END = "\n-----END PUBLIC KEY-----";
    const KEY_PATTERN = "/^-----BEGIN PUBLIC KEY-----(.+)-----END PUBLIC KEY-----$/";
    const KEY_START = "-----BEGIN PUBLIC KEY-----\n";
    const NOTIFICATION_API_ENDPOINT_PRODUCTION = 'https://api.ebay.com/commerce/notification/v1/public_key/';
    const NOTIFICATION_API_ENDPOINT_SANDBOX = 'https://api.sandbox.ebay.com/commerce/notification/v1/public_key/';
    const PROD_BASE_URL = 'https://api.ebay.com';
    const SANDBOX_BASE_URL = 'https://api.sandbox.ebay.com';
    const SHA256 = 'sha256';
    const TOPICS = [
        'MARKETPLACE_ACCOUNT_DELETION' => 'MARKETPLACE_ACCOUNT_DELETION'
    ];
    const X_EBAY_SIGNATURE = 'x-ebay-signature';
}

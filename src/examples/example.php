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

use Slim\Factory\AppFactory;
use EventNotificationPHPSdk\lib\Index;
use EventNotificationPHPSdk\lib\Logger;
use EventNotificationPHPSdk\lib\Constants;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require __DIR__ . '/../../vendor/autoload.php';

$app = AppFactory::create();

$app->get('/webhook', function (Request $request, Response $response, $args) {
    $eventNotificationPHPSdk = new Index();
    $logger = new Logger();
    $constant = new Constants();

    if ($request->getQueryParams()["challenge_code"] !== null) {
        try {
            $challengeResponse = $eventNotificationPHPSdk->validateEndpoint(
                $request->getQueryParams()["challenge_code"],
                json_decode(file_get_contents(__DIR__ . "/config.json"), true)
            );
            $response->getBody()->write(json_encode(['challengeResponse' => $challengeResponse]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus($constant::HTTP_STATUS_CODE['OK']);
        } catch (\Exception $e) {
            $logger->log("Endpoint validation failure: " . $e);
            return $response->withStatus($constant::HTTP_STATUS_CODE['INTERNAL_SERVER_ERROR']);
        }
    } else {
        $logger->log("challenge_code is null");
        return $response->withStatus($constant::HTTP_STATUS_CODE['INTERNAL_SERVER_ERROR']);
    }
});

$app->post('/webhook', function (Request $request, Response $response, $args) {
    $eventNotificationPHPSdk = new Index();
    $logger = new Logger();
    $constant = new Constants();
    $environment = 'PRODUCTION';

    try {
        $responseCode = $eventNotificationPHPSdk->process(
            json_decode($request->getBody(), true),
            $request->getHeader($constant::X_EBAY_SIGNATURE)[0],
            json_decode(file_get_contents(__DIR__ . "/config.json"), true),
            $environment
        );
        if ($responseCode === constants::HTTP_STATUS_CODE['NO_CONTENT']) {
            $logger->log("Message processed successfully for: \n- Topic: " . json_decode($request->getBody(), true)['metadata']['topic']
                . "\n- NotificationId: " . json_decode($request->getBody(), true)['notification']['notificationId']);
        } else if ($responseCode === constants::HTTP_STATUS_CODE['PRECONDITION_FAILED']) {
            $logger->log("Signature mismatch");
        }
        return $response->withStatus($responseCode);
    } catch (\Exception $e) {
        $logger->log("Signature validation processing failure: " . $e);
        return $response->withStatus($constant::HTTP_STATUS_CODE['INTERNAL_SERVER_ERROR']);
    }
});

$app->run();

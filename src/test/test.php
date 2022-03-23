<?php declare(strict_types=1);

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

use PHPUnit\Framework\TestCase;
use EventNotificationPHPSdk\lib\Index;
use EventNotificationPHPSdk\lib\Client;
use EventNotificationPHPSdk\lib\Validator;

final class Test extends TestCase
{
    private $env;
    private $client;
    private $testData;
    private $validator;
    private $sampleConfig;
    private $eventNotificationPHPSdk;

    protected function setUp(): void
    {
        $this->eventNotificationPHPSdk = new Index();
        $this->validator = new Validator();
        $this->client = $this->createMock(Client::class);
        $this->validator->attach($this->client);
        $this->eventNotificationPHPSdk->attach($this->validator);
        $this->testData = json_decode(file_get_contents(__DIR__ . '/test.json'), true);
        $this->sampleConfig = $this->testData['CONFIG'];
        $this->env = [
            'SANDBOX' => 'SANDBOX',
            'PRODUCTION' => 'PRODUCTION'
        ];
    }

    public function testWithoutMessage(): void
    {
        try{
            $this->eventNotificationPHPSdk->process(null, 'signature', null, null);
        } catch (\Exception $error) {
            $this->assertEquals("Please provide the message.", $error->getMessage());
        }
    }

    public function testPayloadProcessingSuccess(): void
    {
        $this->client->expects($this->once())
            ->method('getPublicKey')
            ->with(
                $this->anything(),
                $this->anything()
            )
            ->willReturn($this->returnValue($this->testData['VALID']['response']));

        $res = $this->eventNotificationPHPSdk->process(
            $this->testData['VALID']['message'],
            $this->testData['VALID']['signature'],
            $this->sampleConfig,
            $this->env['PRODUCTION']
        );
        $this->assertEquals(204, $res);
    }

    public function testPayLoadVerificationFailure(): void
    {
        $this->client->expects($this->once())
            ->method('getPublicKey')
            ->with(
                $this->anything(),
                $this->anything()
            )
            ->willReturn($this->returnValue($this->testData['INVALID']['response']));

        $res = $this->eventNotificationPHPSdk->process(
            $this->testData['INVALID']['message'],
            $this->testData['INVALID']['signature'],
            $this->sampleConfig,
            $this->env['PRODUCTION']
        );

        $this->assertEquals(412, $res);
    }

    public function testSignatureMismatch(): void
    {
        $this->client->expects($this->once())
            ->method('getPublicKey')
            ->with(
                $this->anything(),
                $this->anything()
            )
            ->willReturn($this->returnValue($this->testData['SIGNATURE_MISMATCH']['response']));

        $res = $this->eventNotificationPHPSdk->process(
            $this->testData['SIGNATURE_MISMATCH']['message'],
            $this->testData['SIGNATURE_MISMATCH']['signature'],
            $this->sampleConfig,
            $this->env['PRODUCTION']
        );

        $this->assertEquals(412, $res);
    }

    public function testInternalServerError(): void
    {
        $this->client->expects($this->once())
            ->method('getPublicKey')
            ->with(
                $this->anything(),
                $this->anything()
            )
            ->willReturn($this->returnValue($this->testData['ERROR']['response']));

        $res = $this->eventNotificationPHPSdk->process(
            $this->testData['ERROR']['message'],
            $this->testData['ERROR']['signature'],
            $this->sampleConfig,
            $this->env['PRODUCTION']
        );

        $this->assertEquals(500, $res);
    }

    public function testValidateEndpoint(): void
    {
        $challengeCode = "71745723-d031-455c-bfa5-f90d11b4f20a";
        $config = [
            'endpoint' => 'http://www.testendpoint.com/webhook',
            'verificationToken' => '71745723-d031-455c-bfa5-f90d11b4f20a'
        ];

        $hash = hash_init('sha256');
        hash_update($hash, $challengeCode);
        hash_update($hash, $config['verificationToken']);
        hash_update($hash, $config['endpoint']);
        $responseHash = hash_final($hash);

        $challengeResponse = $this->eventNotificationPHPSdk->validateEndpoint($challengeCode, $config);
        $this->assertEquals($responseHash, $challengeResponse);
    }
}
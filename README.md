# Event Notification SDK

With notifications, business moments are communicated to all interested listeners a.k.a. subscribers of those event streams. eBay's most recent notification payloads are also secured using ECC signature headers.

This PHP SDK is designed to simplify processing eBay notifications. The application receives subscribed messages, validates the integrity of the message using the X-EBAY-SIGNATURE header and delegates to a custom configurable MessageProcessor for plugging in usecase specific processing logic.

# Table of contents
  * [What Notifications are covered?](#notifications)
  * [Features](#features)
  * [Usage](#usage)
  * [Logging](#logging)
  * [License](#license)

# Notifications

This SDK is intended for the latest eBay notifications that use ECC signatures and JSON payloads.
While this SDK is generic for any topic, it currently includes the schema definition for MARKETPLACE_ACCOUNT_DELETION notifications.

# Features

This SDK is intended to bootstrap subscriptions to eBay Notifications and provides a ready PHP example.

This SDK now also incorporates support for endpoint validation.

This SDK incorporates

- A deployable example PHP application that is generic across topics and can process incoming https notifications
- Allows registration of custom Message Processors.
- [Verify the integrity](https://github.corp.ebay.com/shuoyao/notification-sdk-php/blob/92d7cc9a6dae51576b2fba92bfa82885ae383a3c/src/lib/validator.php#L39) of the incoming messages
  - Use key id from the decoded signature header to fetch public key required by the verification algorithm. An LRU cache is used to prevent refetches for same 'key'.
  - On verification success, delegate processing to the registered custom message processor and respond with a 204 HTTP status code.
  - On verification failure, respond back with a 412 HTTP status code
  - Generate the challenge response required for validating this endpoint.
For more details on endpoint validation please refer to the [documentation](https://developer.ebay.com/marketplace-account-deletion).

# Usage
**Prerequisite**

```
PHP: v7.3 or higher
```

**Configure**

* Update config.json with the client credentials (required to fetch Public Key from /commerce/notification/v1/public_key/{public_key_id}).
* Specify environment (PRODUCTION or SANDBOX) in [example.php](./src/examples/example.php). Default: PRODUCTION

* For Endpoint Validation
  * **verificationToken** associated with your endpoint. A random sample is included for your endpoint, this needs to be the same as that provided to eBay.
  * **Endpoint** specific to this deployment. A random url is included as an example.

**Note**: it is recommended that the _verificationToken_ be stored in a secure location.

```json
{
   "SANDBOX": {
       "clientId": "<appid-from-developer-portal>",
       "clientSecret": "<certid-from-developer-portal>",
       "devid": "<devid-from-developer-portal>",
       "redirectUri": "<redirect_uri-from-developer-portal>",
       "baseUrl": "api.sandbox.ebay.com"
   },
   "PRODUCTION": {
       "clientId": "<appid-from-developer-portal>",
       "clientSecret": "<certid-from-developer-portal>",
       "devid": "<devid-from-developer-portal>",
       "redirectUri": "<redirect_uri-from-developer-portal>",
       "baseUrl": "api.ebay.com"
   },
   "endpoint": "<endpoint_url>",
   "verificationToken": "<verification_token>"
}
```

For MARKETPLACE_ACCOUNT_DELETION use case simply implement custom logic in [accountDeletionMessageProcessor.processInternal()](./src/lib/processor/accountDeletionMessageProcessor.php)

**Onboard any new topic in 3 simple steps! :**

- Add the new topic constant to [constants.php](src/lib/constants.php)
- Add a custom message processor for the new topic in `src/lib/processor/`
- Update the [processor.php](src/lib/processor/processor.php) to return the new message processor for the topic

Note: You can refer to [example.php](src/examples/example.php) for an example of how to setup an express server and use the SDK.

**Install and Run**

```
composer install
composer run-script start
```

**Note for Production deployment**

```
For production, please host with HTTPS enabled.
```

# Logging

Uses standard error logging.

# License

Copyright 2022 eBay Inc.<br />
Developer: Shuoyang Yao

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    https://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.

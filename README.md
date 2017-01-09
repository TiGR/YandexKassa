# YandexKassa

[![Build Status](https://travis-ci.org/TiGR/YandexKassa.svg?branch=master)](https://travis-ci.org/TiGR/YandexKassa)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%205.3-8892BF.svg)](https://secure.php.net/)
[![Coverage Status](https://coveralls.io/repos/TiGR/YandexKassa/badge.svg?branch=master&service=github)](https://coveralls.io/github/TiGR/YandexKassa?branch=master)
[![Total Downloads](https://poser.pugx.org/tigr/yandex-kassa/downloads)](https://packagist.org/packages/tigr/yandex-kassa)
[![Latest Stable Version](https://poser.pugx.org/tigr/yandex-kassa/v/stable)](https://packagist.org/packages/tigr/yandex-kassa)
[![License](https://poser.pugx.org/tigr/yandex-kassa/license)](https://packagist.org/packages/tigr/yandex-kassa)

Helper for yandex kassa, helps handling callbacks and requests.

## Installation

    composer require tigr/yandex-kassa

## Usage

1. Create controllers that would handle yandex kassa requests and do something like this in it:

 ```php
<?php

    use TiGR\YandexKassa\YandexKassaHelper;
    use TiGR\YandexKassa\Exception\AuthorizationErrorException;
    use TiGR\YandexKassa\Exception\BadRequestException;

    $helper = new YandexKassaHelper(KASSA_SHOP_ID, KASSA_SHOP_PASSWORD);

    $errorStatus = YandexKassaHelper::STATUS_BAD_REQUEST;

    if ($helper->getAction() == YandexKassaHelper::ACTION_CHECK) {
        $errorStatus = YandexKassaHelper::STATUS_PAYMENT_REJECTED;
    }

    try {
        $helper->parseRequest($_POST);
    } catch (AuthorizationErrorException $e) {
        // ... handle this exception ...

        return $this->xmlResponse($helper->buildResponse(null, $e->getMessage()));
    } catch (BadRequestException $e) {
        // ... handle this exception ...

        return $this->xmlResponse($helper->buildResponse(null, $e->getMessage()));
    } catch (\Exception $e) {
        // ... handle this exception ...

        return $this->xmlResponse($helper->buildResponse($errorStatus));
    }

    try {
        $payment = $helper->getPayment();

        // ... do some validation using $payment data ...

        if ($helper->getAction() == YandexKassaHelper::ACTION_AVISO) {
            // ... Mark this payment as settled in your system ...
            // ... log successful transaction, if needed ...
            // ... notify user of successful transaction. if needed ...
        }

        return $this->xmlResponse($helper->buildResponse(/* successful by default */));
    } catch (\Exception $e) {
        // ... handle this exception ...

        return $this->xmlResponse(
            $helper->buildResponse(YandexKassaHelper::STATUS_PAYMENT_REJECTED, $e->getMessage())
        );
    }
 ```
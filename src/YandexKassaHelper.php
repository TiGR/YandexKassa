<?php

namespace polosatus\YandexKassa;

use polosatus\YandexKassa\Exception\AuthorizationErrorException;
use polosatus\YandexKassa\Exception\BadRequestException;

class YandexKassaHelper
{
    const SECURITY_MD5 = 1;
    const SECURITY_PKCS7 = 2;

    const ACTION_AVISO = 'paymentAviso';
    const ACTION_CHECK = 'checkOrder';

    const STATUS_SUCCESS = 0;
    const STATUS_AUTHORIZATION_ERROR = 1;
    const STATUS_PAYMENT_REJECTED = 100;
    const STATUS_BAD_REQUEST = 200;

    const DATETIME_FORMAT = "Y-m-d\\TH:i:s.000P";

    private $shopId;
    private $shopPassword;
    private $securityMode;

    /** @var array $postData */
    private $postData;

    /** @var Payment $payment */
    private $payment;

    private $status = 0;
    private $action;

    /**
     * @param $shopId       int     ID магазина в яндекс кассе.
     * @param $shopPassword string  пароль магазина в Яндекс Кассе
     * @param $securityMode int     Тип подписи сообщений - SECURITY_MD5 или SECURITY_PKCS7
     */
    public function __construct($shopId, $shopPassword, $securityMode = self::SECURITY_MD5)
    {
        $this->shopId = $shopId;
        $this->shopPassword = $shopPassword;

        $securityMode = (int)$securityMode;

        $this->securityMode = $securityMode;
    }

    /**
     * @param $postData array POST данные полученного запроса
     */
    public function parseRequest(array $postData)
    {
        $this->postData = $postData;

        $this->validateRequest();

        $this->action = $this->postData['action'];
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @return Payment
     */
    public function getPayment()
    {
        if (!isset($this->payment)) {
            $this->payment = new Payment($this->postData);
        }

        return $this->payment;
    }

    /**
     * Собирает xml ответ. В большинстве случаев собирает автоматически, и никаких параметров передавать не нужно.
     * В целом, передавать что-то нужно только в случае отказа от проведения платежа или какой-то другой ошибки вне
     * кода данного класса. Все внутренние ошибки будут автоматически корректно оформлены.
     *
     * @param null $status
     * @param null $message
     * @param null $techMessage
     * @return string
     */
    public function buildResponse($status = null, $message = null, $techMessage = null)
    {
        if (isset($status)) {
            $status = (int)$status;
            $availableStatuses = array(
                self::STATUS_SUCCESS,
                self::STATUS_AUTHORIZATION_ERROR,
                self::STATUS_BAD_REQUEST,
                self::STATUS_PAYMENT_REJECTED,
            );
            if (!in_array($status, $availableStatuses)) {
                throw new \InvalidArgumentException('Bad status code provided: '.$status);
            }
        }

        $xml = new \SimpleXMLElement("<{$this->action}Response />");
        $date = new \DateTime();
        $xml->addAttribute('performedDatetime', $date->format(self::DATETIME_FORMAT));
        $xml->addAttribute('code', isset($status) ? $status : $this->status);
        $xml->addAttribute('invoiceId', $this->getPayment()->getInvoiceId());
        $xml->addAttribute('shopId', $this->shopId);

        if (isset($message)) {
            $xml->addAttribute('message', $message);
        }

        if (isset($techMessage)) {
            $xml->addAttribute('techMessage', $techMessage);
        }

        return $xml->asXML();
    }

    /**
     *
     */
    private function validateRequest()
    {
        if ($this->securityMode == self::SECURITY_MD5) {
            $this->validateRequiredFields();
            $this->checkMD5Signature();
        } elseif ($this->securityMode == self::SECURITY_PKCS7) {
            $this->status = self::STATUS_BAD_REQUEST;
            throw new \RuntimeException('Yandex Kassa certificate security mode not implemented');
        } else {
            $this->status = self::STATUS_BAD_REQUEST;
            throw new \InvalidArgumentException('Bad Yandex Kassa security mode: '.$this->securityMode);
        }
    }

    /**
     * Checking the MD5 sign.
     * @return bool true if MD5 hash is correct
     */
    private function checkMD5Signature()
    {
        $str = $this->postData['action'].";"
            .$this->postData['orderSumAmount'].";".$this->postData['orderSumCurrencyPaycash'].";"
            .$this->postData['orderSumBankPaycash'].";".$this->postData['shopId'].";"
            .$this->postData['invoiceId'].";".$this->postData['customerNumber'].";".$this->shopPassword;
        $md5 = strtoupper(md5($str));

        if ($md5 != strtoupper($this->postData['md5'])) {
            $this->status = self::STATUS_AUTHORIZATION_ERROR;
            throw new AuthorizationErrorException(
                sprintf('Hash mismatch. Expected: %s, got: %s.', $md5, $this->postData['md5'])
            );
        }

        return true;
    }

    private function validateRequiredFields()
    {
        $requiredFields = array(
            'requestDatetime',
            'action',
            'md5',
            'shopId',
            'shopArticleId',
            'invoiceId',
            'customerNumber',
            'orderCreatedDatetime',
            'orderSumAmount',
            'orderSumCurrencyPaycash',
            'orderSumBankPaycash',
            'shopSumAmount',
            'shopSumCurrencyPaycash',
            'shopSumBankPaycash',
            'paymentPayerCode',
            'paymentType',
        );

        $action = (isset($this->postData['action']) ? $this->postData['action'] : null);

        switch ($action) {
            case self::ACTION_AVISO:
                $requiredFields[] = 'paymentDatetime';
                break;

            case self::ACTION_CHECK:
                break;

            default:
                $this->status = self::STATUS_BAD_REQUEST;
                throw new BadRequestException('Unexpected action value: '.$this->postData['action']);
                break;
        }

        $missingFields = array_diff($requiredFields, array_keys($this->postData));
        if (!empty($missingFields)) {
            $this->status = self::STATUS_BAD_REQUEST;
            throw new BadRequestException('Missing required fields: '.implode(', ', $missingFields));
        }

        if ($this->postData['shopId'] != $this->shopId) {
            $this->status = self::STATUS_BAD_REQUEST;
            throw new BadRequestException(
                sprintf('Got wrong shopId. Expected: %s, got: %s', $this->shopId, $this->postData['shopId'])
            );
        }
    }
}

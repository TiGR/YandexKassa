<?php

namespace polosatus\YandexKassa;

class Payment
{
    private $shopArticleId;
    private $invoiceId;
    private $orderNumber;
    private $customerNumber;
    private $paymentPayerCode;
    private $paymentType;

    /** @var Sum $orderSum */
    private $orderSum;

    /** @var Sum $shopSum */
    private $shopSum;

    /** @var array $data */
    private $data;

    /** @var \DateTime $requestDatetime */
    private $requestDatetime;

    /** @var \DateTime $datetime */
    private $datetime;

    /** @var \DateTime $orderCreatedDate */
    private $orderCreatedDate;

    public function __construct(array $data = null)
    {
        if (!empty($data)) {
            $this->loadData($data);
        }
    }

    public function loadData(array $data)
    {
        $this->setRequestDatetime($data['requestDatetime']);
        $this->setShopArticleId($data['shopArticleId']);
        $this->setInvoiceId($data['invoiceId']);
        $this->setCustomerNumber($data['customerNumber']);
        $this->setPayerCode($data['paymentPayerCode']);
        $this->setType($data['paymentType']);
        $this->setOrderCreatedDate($data['orderCreatedDatetime']);

        if (isset($data['paymentDatetime'])) {
            $this->setDatetime($data['paymentDatetime']);
        }

        if (isset($data['orderNumber'])) {
            $this->setOrderNumber($data['orderNumber']);
        }

        $this->data = $data;
    }

    /**
     * @return Sum
     */
    public function getOrderSum()
    {
        if (!isset($this->orderSum)) {
            $this->orderSum = new Sum(
                $this->data['orderSumAmount'],
                $this->data['orderSumCurrencyPaycash'],
                $this->data['orderSumBankPaycash']
            );
        }

        return $this->orderSum;
    }

    /**
     * @return Sum
     */
    public function getShopSum()
    {
        if (!isset($this->shopSum)) {
            $this->shopSum = new Sum(
                $this->data['shopSumAmount'],
                $this->data['shopSumCurrencyPaycash'],
                $this->data['shopSumBankPaycash']
            );
        }

        return $this->shopSum;
    }

    /**
     * @return mixed
     */
    public function getOrderNumber()
    {
        return $this->orderNumber;
    }

    /**
     * @param mixed $orderNumber
     */
    public function setOrderNumber($orderNumber)
    {
        $this->orderNumber = $orderNumber;
    }

    /**
     * @return \DateTime
     */
    public function getOrderCreatedDate()
    {
        return $this->orderCreatedDate;
    }

    /**
     * @param $createdDate
     */
    public function setOrderCreatedDate($createdDate)
    {
        $this->orderCreatedDate = $this->parseDatestamp($createdDate);
    }

    /**
     * @return mixed
     */
    public function getInvoiceId()
    {
        return $this->invoiceId;
    }

    /**
     * @param mixed $invoiceId
     */
    public function setInvoiceId($invoiceId)
    {
        $this->invoiceId = $invoiceId;
    }

    /**
     * @return mixed
     */
    public function getShopArticleId()
    {
        return $this->shopArticleId;
    }

    /**
     * @param mixed $shopArticleId
     */
    public function setShopArticleId($shopArticleId)
    {
        $this->shopArticleId = $shopArticleId;
    }

    /**
     * @return mixed
     */
    public function getCustomerNumber()
    {
        return $this->customerNumber;
    }

    /**
     * @param mixed $customerNumber
     */
    public function setCustomerNumber($customerNumber)
    {
        $this->customerNumber = $customerNumber;
    }

    /**
     * @return mixed
     */
    public function getPayerCode()
    {
        return $this->paymentPayerCode;
    }

    /**
     * @param mixed $paymentPayerCode
     */
    public function setPayerCode($paymentPayerCode)
    {
        $this->paymentPayerCode = $paymentPayerCode;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->paymentType;
    }

    /**
     * @param mixed $paymentType
     */
    public function setType($paymentType)
    {
        $this->paymentType = $paymentType;
    }

    /**
     * @return \DateTime|null
     */
    public function getDatetime()
    {
        return $this->datetime;
    }

    /**
     * @param mixed $datetime
     */
    public function setDatetime($datetime)
    {
        $this->datetime = $this->parseDatestamp($datetime);
    }

    /**
     * @return \DateTime
     */
    public function getRequestDatetime()
    {
        return $this->requestDatetime;
    }

    /**
     * @param mixed $requestDatetime
     */
    public function setRequestDatetime($requestDatetime)
    {
        $this->requestDatetime = $this->parseDatestamp($requestDatetime);
    }

    /**
     * @param $datetime
     * @return \DateTime
     */
    private function parseDatestamp($datetime)
    {
        if (!$datetime instanceof \DateTime) {
            $datetime = new \DateTime($datetime);
        }

        return $datetime;
    }
}

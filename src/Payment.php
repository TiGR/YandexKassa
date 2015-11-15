<?php

namespace polosatus\YandexKassa;

class Payment
{
    private $shopArticleId;
    private $invoiceId;
    private $customerNumber;
    private $paymentPayerCode;
    private $paymentType;

    /** @var Sum $orderSum */
    private $orderSum;

    /** @var Sum $shopSum */
    private $shopSum;

    /** @var array $data */
    private $data;

    /** @var \DateTime $createdDate */
    private $createdDate;

    public function __construct(array $data)
    {
        $this->setShopArticleId($data['shopArticleId']);
        $this->setInvoiceId($data['invoiceId']);
        $this->setCustomerNumber($data['customerNumber']);
        $this->setPaymentPayerCode($data['paymentPayerCode']);
        $this->setPaymentType($data['paymentType']);
        $this->setCreatedDate($data['orderCreatedDatetime']);

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
     * @return \DateTime
     */
    public function getCreatedDate()
    {
        return $this->createdDate;
    }

    /**
     * @param $createdDate
     */
    public function setCreatedDate($createdDate)
    {
        if (!$createdDate instanceof \DateTime) {
            $createdDate = new \DateTime($createdDate);
        }

        $this->createdDate = $createdDate;
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
    public function getPaymentPayerCode()
    {
        return $this->paymentPayerCode;
    }

    /**
     * @param mixed $paymentPayerCode
     */
    public function setPaymentPayerCode($paymentPayerCode)
    {
        $this->paymentPayerCode = $paymentPayerCode;
    }

    /**
     * @return mixed
     */
    public function getPaymentType()
    {
        return $this->paymentType;
    }

    /**
     * @param mixed $paymentType
     */
    public function setPaymentType($paymentType)
    {
        $this->paymentType = $paymentType;
    }

}

<?php

namespace polosatus\YandexKassa;

class Sum
{
    const CURRENCY_RUB = 643;
    const CURRENCY_TEST = 10643;

    private $amount;
    private $currency;
    private $bank;

    public function __construct($amount, $currency, $bank)
    {
        $this->setAmount($amount);
        $this->setCurrency($currency);
        $this->setBank($bank);
    }

    /**
     * @return mixed
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param mixed $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * @return mixed
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param mixed $currency
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
    }

    /**
     * @return mixed
     */
    public function getBank()
    {
        return $this->bank;
    }

    /**
     * @param mixed $bank
     */
    public function setBank($bank)
    {
        $this->bank = $bank;
    }
}

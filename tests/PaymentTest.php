<?php

namespace polosatus\YandexKassa;

class PaymentTest extends \PHPUnit_Framework_TestCase
{
    public function testGetters()
    {
        $data = $this->getData();

        $payment = new Payment($data);

        $this->assertInstanceOf(__NAMESPACE__.'\\Sum', $payment->getOrderSum());
        $this->assertInstanceOf(__NAMESPACE__.'\\Sum', $payment->getShopSum());
        $this->assertInstanceOf('\\DateTime', $payment->getCreatedDate());

        $this->assertEquals('2015-11-13T12:34:56-0700', $payment->getCreatedDate()->format(\DateTime::ISO8601));
        $this->assertEquals($data['shopArticleId'], $payment->getShopArticleId());
        $this->assertEquals($data['invoiceId'], $payment->getInvoiceId());
        $this->assertEquals($data['customerNumber'], $payment->getCustomerNumber());
        $this->assertEquals($data['paymentPayerCode'], $payment->getPaymentPayerCode());
        $this->assertEquals($data['paymentType'], $payment->getPaymentType());
        $this->assertNull($payment->getOrderNumber());

        $payment = new Payment($data + array('orderNumber' => 100500));
        $this->assertEquals(100500, $payment->getOrderNumber());
    }


    private function getData()
    {
        return array(
            'shopArticleId' => '9876543210',
            'invoiceId' => '234567890',
            'customerNumber' => 'user123',
            'paymentPayerCode' => '321',
            'paymentType' => '123',
            'orderCreatedDatetime' => '2015-11-13T12:34:56.000-07:00',
            'orderSumAmount' => 100.00,
            'orderSumCurrencyPaycash' => Sum::CURRENCY_RUB,
            'orderSumBankPaycash' => 1234,
            'shopSumAmount' => 100.00,
            'shopSumCurrencyPaycash' => Sum::CURRENCY_TEST,
            'shopSumBankPaycash' => 1000,
        );
    }

}

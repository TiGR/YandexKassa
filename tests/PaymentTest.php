<?php

namespace TiGR\YandexKassa;

class PaymentTest extends \PHPUnit_Framework_TestCase
{
    public function testGetters()
    {
        $data = $this->getData();

        $payment = new Payment($data);

        $this->assertInstanceOf(__NAMESPACE__.'\\Sum', $payment->getOrderSum());
        $this->assertInstanceOf(__NAMESPACE__.'\\Sum', $payment->getShopSum());
        $this->assertInstanceOf('\\DateTime', $payment->getOrderCreatedDate());

        $this->assertEquals('2015-11-13T12:34:56-0700', $payment->getOrderCreatedDate()->format(\DateTime::ISO8601));
        $this->assertEquals(null, $payment->getShopArticleId());
        $this->assertEquals($data['invoiceId'], $payment->getInvoiceId());
        $this->assertEquals($data['customerNumber'], $payment->getCustomerNumber());
        $this->assertEquals($data['paymentPayerCode'], $payment->getPayerCode());
        $this->assertEquals($data['paymentType'], $payment->getType());
        $this->assertNull($payment->getOrderNumber());
        $this->assertNull($payment->getDatetime());

        $payment = new Payment(
            $data + array(
                'orderNumber' => 100500,
                'paymentDatetime' => '2015-11-13T12:34:56.000+03:00',
                'shopArticleId' => '9876543210',
            )
        );
        $this->assertEquals(100500, $payment->getOrderNumber());
        $this->assertEquals('2015-11-13T12:34:56+0300', $payment->getDatetime()->format(\DateTime::ISO8601));
        $this->assertEquals('2015-11-13T12:34:56+0100', $payment->getRequestDatetime()->format(\DateTime::ISO8601));
        $this->assertEquals('9876543210', $payment->getShopArticleId());
    }


    private function getData()
    {
        return array(
            'requestDatetime' => '2015-11-13T12:34:56.000+01:00',
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

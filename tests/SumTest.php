<?php

namespace TiGR\YandexKassa;

class SumTest extends \PHPUnit_Framework_TestCase
{
    public function testSum()
    {
        $sum = new Sum(123.00, Sum::CURRENCY_RUB, 1000);

        $this->assertEquals(123.00, $sum->getAmount());
        $this->assertEquals(Sum::CURRENCY_RUB, $sum->getCurrency());
        $this->assertEquals(1000, $sum->getBank());
    }
}

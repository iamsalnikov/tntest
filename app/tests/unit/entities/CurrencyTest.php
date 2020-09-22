<?php

namespace tests\unit\entities;

use entities\Currency;
use Exception;
use exceptions\InvalidCurrencyNominalException;
use PHPUnit\Framework\TestCase;

class CurrencyTest extends TestCase
{
    /**
     * @dataProvider getTestNominalValueTable
     *
     * @param int $nominal
     * @param Exception|null $expException
     *
     * @throws InvalidCurrencyNominalException
     */
    public function testNominalValue(int $nominal, ?Exception $expException)
    {
        if ($expException !== null) {
            $this->expectExceptionObject($expException);
        }

        $currency = new Currency("", "", $nominal);
        $this->assertEquals($nominal, $currency->getNominal());
    }

    public function getTestNominalValueTable(): array
    {
        return [
            [
                "nominal" => 1,
                "expException" => null,
            ],
            [
                "nominal" => 0,
                "expException" => new InvalidCurrencyNominalException("Номинал валюты должен быть больше 0"),
            ],
            [
                "nominal" => -1,
                "expException" => new InvalidCurrencyNominalException("Номинал валюты должен быть больше 0"),
            ],
        ];
    }
}

<?php

namespace tests\unit\web\controllers;

use entities\Currency;
use exceptions\CurrencyRepositoryException;
use PHPUnit\Framework\TestCase;
use repositories\CurrencyRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use web\controllers\CurrencyController;

class CurrencyControllerTest extends TestCase
{
    public function testActionCurrenciesListOnCurrenciesRepositoryProblem()
    {
        $repo = $this->createMock(CurrencyRepositoryInterface::class);
        $repo->method("getCurrencies")->willThrowException(
            new CurrencyRepositoryException("Не удалось получить список валют")
        );

        $this->expectExceptionObject(new HttpException(500, "Не удалось получить список валют"));

        $controller = new CurrencyController($repo);
        $controller->actionCurrenciesList();
    }

    /**
     * @dataProvider getActionCurrenciesListTestTable
     *
     * @param $repoCurrencies
     * @param $expResponse
     */
    public function testActionCurrenciesList($repoCurrencies, $expResponse)
    {
        $repo = $this->createMock(CurrencyRepositoryInterface::class);
        $repo->method("getCurrencies")->willReturn($repoCurrencies);

        $controller = new CurrencyController($repo);
        $response = $controller->actionCurrenciesList();

        $this->assertEquals($expResponse, $response);
    }

    public function getActionCurrenciesListTestTable()
    {
        return [
            [
                "repoCurrencies" => [],
                "expResponse" => new JsonResponse([])
            ],
            [
                "repoCurrencies" => [
                    new Currency("hello", "world", 1),
                    new Currency("twitter","facebook", 10),
                ],
                "expResponse" => new JsonResponse([
                    [
                        "id" => "hello",
                        "name" => "world",
                        "nominal" => 1,
                    ],
                    [
                        "id" => "twitter",
                        "name" => "facebook",
                        "nominal" => 10,
                    ],
                ])
            ]
        ];
    }
}

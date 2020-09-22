<?php

namespace tests\unit\infrastructure\cbr;

use entities\Currency;
use exceptions\CurrencyNotFoundException;
use exceptions\CurrencyRepositoryException;
use infrastructure\cbr\CurrencyRepository;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class CurrencyRepositoryTest extends TestCase
{
    /**
     * @throws CurrencyRepositoryException
     */
    public function testCurrenciesListOnNetworkProblem()
    {
        $client = $this->createMock(ClientInterface::class);
        $exception = $this->createMock(ClientExceptionInterface::class);
        $client->method("sendRequest")->willThrowException($exception);

        $repo = new CurrencyRepository($client, "https://hello.ru");
        $expException = new CurrencyRepositoryException("Не удалось извлеч данные из cbr.ru", 0, $exception);
        $this->expectExceptionObject($expException);
        $currencies = $repo->getCurrencies();
        $this->assertEmpty($currencies);
    }

    /**
     * @throws CurrencyRepositoryException
     */
    public function testCurrencyByIDOnNetworkProblem()
    {
        $client = $this->createMock(ClientInterface::class);
        $exception = $this->createMock(ClientExceptionInterface::class);
        $client->method("sendRequest")->willThrowException($exception);

        $repo = new CurrencyRepository($client, "https://hello.ru");
        $expException = new CurrencyRepositoryException("Не удалось извлеч данные из cbr.ru", 0, $exception);
        $this->expectExceptionObject($expException);
        $repo->getCurrencyByID("hello");
    }

    /**
     * @throws CurrencyRepositoryException
     */
    public function testCurrenciesListOnBadResponseStatus()
    {
        $client = $this->createMock(ClientInterface::class);
        $response = $this->createConfiguredMock(ResponseInterface::class, [
            "getStatusCode" => 500,
        ]);
        $client->method("sendRequest")->willReturn($response);

        $repo = new CurrencyRepository($client, "https://hello.ru");
        $expException = new CurrencyRepositoryException("Не удалось извлеч данные из cbr.ru", 0);
        $this->expectExceptionObject($expException);
        $currencies = $repo->getCurrencies();
        $this->assertEmpty($currencies);
    }

    /**
     * @throws CurrencyRepositoryException
     */
    public function testCurrenciesByIDOnBadResponseStatus()
    {
        $client = $this->createMock(ClientInterface::class);
        $response = $this->createConfiguredMock(ResponseInterface::class, [
            "getStatusCode" => 500,
        ]);
        $client->method("sendRequest")->willReturn($response);

        $repo = new CurrencyRepository($client, "https://hello.ru");
        $expException = new CurrencyRepositoryException("Не удалось извлеч данные из cbr.ru", 0);
        $this->expectExceptionObject($expException);
        $repo->getCurrencyByID("hello");
    }

    /**
     * @throws CurrencyRepositoryException
     */
    public function testCurrenciesListOnBadResponseContent()
    {
        $client = $this->createMock(ClientInterface::class);
        $response = $this->createConfiguredMock(ResponseInterface::class, [
            "getStatusCode" => 200,
            "getBody" => $this->createConfiguredMock(StreamInterface::class, [
                "getContents" => "hello world",
            ])
        ]);
        $client->method("sendRequest")->willReturn($response);

        $repo = new CurrencyRepository($client, "https://hello.ru");
        $expException = new CurrencyRepositoryException("Не удалось прочесть ответ из cbr.ru", 0);
        $this->expectExceptionObject($expException);
        $currencies = $repo->getCurrencies();
        $this->assertEmpty($currencies);
    }

    /**
     * @throws CurrencyRepositoryException
     */
    public function testCurrencyByIDOnBadResponseContent()
    {
        $client = $this->createMock(ClientInterface::class);
        $response = $this->createConfiguredMock(ResponseInterface::class, [
            "getStatusCode" => 200,
            "getBody" => $this->createConfiguredMock(StreamInterface::class, [
                "getContents" => "hello world",
            ])
        ]);
        $client->method("sendRequest")->willReturn($response);

        $repo = new CurrencyRepository($client, "https://hello.ru");
        $expException = new CurrencyRepositoryException("Не удалось прочесть ответ из cbr.ru", 0);
        $this->expectExceptionObject($expException);
        $repo->getCurrencyByID("hello");
    }

    /**
     * @dataProvider getCurrenciesListGoodResponseTestTable
     *
     * @param $responseContent
     * @param $expCurrencies
     *
     * @throws CurrencyRepositoryException
     */
    public function testCurrenciesListGoodResponse(array $responseContent, array $expCurrencies)
    {
        $client = $this->createMock(ClientInterface::class);
        $response = $this->createConfiguredMock(ResponseInterface::class, [
            "getStatusCode" => 200,
        ]);

        $rc = array_map(function($content) {
            return $this->createConfiguredMock(StreamInterface::class, [
                "getContents" => $content,
            ]);
        }, $responseContent);

        $response->method("getBody")->willReturnOnConsecutiveCalls(...$rc);
        $client->method("sendRequest")->willReturn($response);

        $repo = new CurrencyRepository($client, "https://hello.ru");
        $currencies = $repo->getCurrencies();
        $this->assertEquals($expCurrencies, $currencies);
    }

    public function getCurrenciesListGoodResponseTestTable(): array
    {
        return [
            "empty response" => [
                "responseContent" => [
                    file_get_contents(__DIR__ . "/../../../data/empty_currencies.xml"),
                    file_get_contents(__DIR__ . "/../../../data/empty_currencies.xml"),
                ],
                "expCurrencies" => [],
            ],
            "two currencies" => [
                "responseContent" => [
                    file_get_contents(__DIR__ . "/../../../data/currencies.xml"),
                    file_get_contents(__DIR__ . "/../../../data/empty_currencies.xml"),
                ],
                "expCurrencies" => [
                    new Currency("R01010", "Австралийский доллар", 1),
                    new Currency("R01015", "Австрийский шиллинг", 1000),
                ],
            ],
        ];
    }

    /**
     * @throws CurrencyRepositoryException
     */
    public function testCurrencyByIDThatDoesNotExists()
    {
        $client = $this->createMock(ClientInterface::class);
        $response = $this->createConfiguredMock(ResponseInterface::class, [
            "getStatusCode" => 200,
            "getBody" => $this->createConfiguredMock(StreamInterface::class, [
                "getContents" => file_get_contents(__DIR__ . "/../../../data/currencies.xml"),
            ])
        ]);
        $client->method("sendRequest")->willReturn($response);

        $repo = new CurrencyRepository($client, "https://hello.ru");
        $expException = new CurrencyNotFoundException("Валюта с идентификатором hello не найдена");
        $this->expectExceptionObject($expException);
        $repo->getCurrencyByID("hello");
    }

    /**
     * @throws CurrencyRepositoryException
     */
    public function testCurrencyByIDThatExists()
    {
        $client = $this->createMock(ClientInterface::class);
        $response = $this->createConfiguredMock(ResponseInterface::class, [
            "getStatusCode" => 200,
            "getBody" => $this->createConfiguredMock(StreamInterface::class, [
                "getContents" => file_get_contents(__DIR__ . "/../../../data/currencies.xml"),
            ])
        ]);
        $client->method("sendRequest")->willReturn($response);

        $repo = new CurrencyRepository($client, "https://hello.ru");
        $currency = $repo->getCurrencyByID("R01010");
        $expCurrency = new Currency("R01010", "Австралийский доллар", 1);
        $this->assertEquals($expCurrency, $currency);
    }

    public function testCBRRequestBaseURL()
    {
        $client = $this->createMock(ClientInterface::class);
        $response = $this->createConfiguredMock(ResponseInterface::class, [
            "getStatusCode" => 200,
            "getBody" => $this->createConfiguredMock(StreamInterface::class, [
                "getContents" => file_get_contents(__DIR__ . "/../../../data/currencies.xml"),
            ])
        ]);
        $client->expects($this->exactly(2))->method("sendRequest")->with(
            $this->callback(function (RequestInterface $request) {
                return $request->getUri()->getHost() === "test.ru" && $request->getUri()->getScheme() === "http";
            })
        )->willReturn($response);

        $repo = new CurrencyRepository($client, "http://test.ru");
        $repo->getCurrencies();
    }
}

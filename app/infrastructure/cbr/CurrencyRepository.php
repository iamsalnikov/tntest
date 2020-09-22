<?php

namespace infrastructure\cbr;

use DOMDocument;
use entities\Currency;
use exceptions\CurrencyNotFoundException;
use exceptions\CurrencyRepositoryException;
use exceptions\InvalidCurrencyNominalException;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use repositories\CurrencyRepositoryInterface;
use Throwable;

/**
 * Репозиторий валют CBR
 *
 * @package infrastructure\cbr
 */
class CurrencyRepository implements CurrencyRepositoryInterface
{
    private const MODE_DAILY = 0;
    private const MODE_MONTHLY = 1;

    /**
     * @var ClientInterface
     */
    private ClientInterface $httpClient;

    /**
     * @var string
     */
    private string $baseURL;

    /**
     * CurrencyRepository constructor.
     *
     * @param ClientInterface $httpClient
     * @param string $baseURL
     */
    public function __construct(ClientInterface $httpClient, string $baseURL)
    {
        $this->httpClient = $httpClient;
        $this->baseURL = rtrim($baseURL, "/");
    }

    /**
     * @inheritDoc
     */
    public function getCurrencyByID(string $id): Currency
    {
        $currencies = $this->getCurrencies();
        foreach ($currencies as $currency) {
            if ($currency->getId() === $id) {
                return $currency;
            }
        }

        throw new CurrencyNotFoundException(sprintf("Валюта с идентификатором %s не найдена", $id));
    }

    /**
     * @inheritDoc
     */
    public function getCurrencies(): array
    {
        return array_merge(
            $this->getCurrenciesList(static::MODE_DAILY),
            $this->getCurrenciesList(static::MODE_MONTHLY),
        );
    }

    /**
     * @param int $mode
     *
     * @return Currency[]
     * @throws CurrencyRepositoryException
     * @throws InvalidCurrencyNominalException
     */
    private function getCurrenciesList(int $mode): array
    {
        $url = sprintf("%s/scripts/XML_val.asp?d=%d", $this->baseURL, $mode);
        $request = new Request("GET", $url);

        try {
            $response = $this->httpClient->sendRequest($request);
        } catch (ClientExceptionInterface $e) {
            throw new CurrencyRepositoryException("Не удалось извлеч данные из cbr.ru", 0, $e);
        }

        if ($response->getStatusCode() !== 200) {
            throw new CurrencyRepositoryException("Не удалось извлеч данные из cbr.ru", 0);
        }

        $content = $response->getBody()->getContents();
        return $this->parseCurrenciesResponse($content);
    }

    /**
     * @param string $responseContent
     *
     * @return Currency[]
     * @throws CurrencyRepositoryException
     * @throws InvalidCurrencyNominalException
     */
    private function parseCurrenciesResponse(string $responseContent): array
    {
        $dom = new DOMDocument();
        try {
            $successInit = $dom->loadXML($responseContent);
        } catch (Throwable $e) {
            throw new CurrencyRepositoryException("Не удалось прочесть ответ из cbr.ru", 0, $e);
        }

        if (!$successInit) {
            throw new CurrencyRepositoryException("Не удалось прочесть ответ из cbr.ru", 0);
        }

        $result = [];
        $currencyItems = $dom->getElementsByTagName("Item");
        for ($i = 0; $i < $currencyItems->length; $i++) {
            $currencyItem = $currencyItems->item($i);
            if ($currencyItem === null) {
                continue;
            }

            if (!$currencyItem->hasAttributes()) {
                continue;
            }

            $currencyID = $currencyItem->attributes->getNamedItem("ID");
            if ($currencyID === null) {
                continue;
            }
            $currencyID = $currencyID->textContent;

            $currencyName = $currencyItem->getElementsByTagName("Name")->item(0)->textContent;

            $currencyNominal = (int) $currencyItem->getElementsByTagName("Nominal")->item(0)->textContent;

            $result[] = new Currency($currencyID, $currencyName, $currencyNominal);
        }

        return $result;
    }
}

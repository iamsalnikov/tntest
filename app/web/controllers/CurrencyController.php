<?php

namespace web\controllers;

use entities\Currency;
use exceptions\CurrencyRepositoryException;
use repositories\CurrencyRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Контроллер для работы с валютами
 *
 * @package web\controllers
 */
class CurrencyController
{
    private CurrencyRepositoryInterface $currencies;

    /**
     * CurrencyController constructor.
     *
     * @param CurrencyRepositoryInterface $currencies
     */
    public function __construct(CurrencyRepositoryInterface $currencies)
    {
        $this->currencies = $currencies;
    }

    /**
     * Получение списка валют
     *
     * @return JsonResponse
     */
    public function actionCurrenciesList(): JsonResponse
    {
        try {
            $currencies = $this->currencies->getCurrencies();
        } catch (CurrencyRepositoryException $e) {
            throw new HttpException(500, $e->getMessage(), $e);
        }

        $data = array_map(function(Currency $currency) {
            return [
                "id" => $currency->getId(),
                "name" => $currency->getName(),
                "nominal" => $currency->getNominal(),
            ];
        }, $currencies);

        return new JsonResponse($data);
    }
}

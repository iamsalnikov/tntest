<?php

namespace repositories;

use entities\Currency;
use exceptions\CurrencyNotFoundException;
use exceptions\CurrencyRepositoryException;

/**
 * Интерфейс репозитория валют
 *
 * @package repositories
 */
interface CurrencyRepositoryInterface
{
    /**
     * Получение списка доступных валют
     *
     * @return Currency[]
     * @throws CurrencyRepositoryException
     */
    public function getCurrencies(): array;

    /**
     * Получение информации о валюте по идентификатору
     *
     * @param string $id
     *
     * @return Currency
     * @throws CurrencyRepositoryException
     * @throws CurrencyNotFoundException
     */
    public function getCurrencyByID(string $id): Currency;
}

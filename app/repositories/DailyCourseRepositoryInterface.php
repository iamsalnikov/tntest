<?php

namespace repositories;

use DateTimeInterface;
use entities\DailyCourseResponse;
use exceptions\DailyCourseRepositoryException;
use exceptions\InvalidDailyCourseRequestException;

/**
 * Репозиторий дневного курса
 *
 * @package repositories
 */
interface DailyCourseRepositoryInterface
{
    /**
     * Получение дневной информации по курсу
     *
     * @param DateTimeInterface $date дата, за которую нам нужна информация
     * @param string $currencyID идентификатор валюты, по которому нам нужна информация
     * @param string|null $baseCurrencyID идентификатор базовой валюты
     *
     * @return DailyCourseResponse
     * @throws DailyCourseRepositoryException
     * @throws InvalidDailyCourseRequestException
     */
    public function getDailyCourse(DateTimeInterface $date, string $currencyID, ?string $baseCurrencyID): DailyCourseResponse;
}

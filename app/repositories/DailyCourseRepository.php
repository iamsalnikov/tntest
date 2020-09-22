<?php

namespace repositories;

use DateTimeInterface;
use entities\CourseRange;
use entities\Currency;
use entities\DailyCourseResponse;
use exceptions\CourseRangeException;
use exceptions\CurrencyNotFoundException;
use exceptions\CurrencyRepositoryException;
use exceptions\InvalidDailyCourseRequestException;

/**
 * Class DailyCourseRepository
 *
 * @package repositories
 */
class DailyCourseRepository implements DailyCourseRepositoryInterface
{
    private CurrencyRepositoryInterface $currencies;
    private CourseRangeRepositoryInterface $courseRanges;
    private CrossCourseRangeRepositoryInterface $crossCourseRanges;

    /**
     * DailyCourseRepository constructor.
     *
     * @param CurrencyRepositoryInterface $currencies
     * @param CourseRangeRepositoryInterface $courseRanges
     * @param CrossCourseRangeRepositoryInterface $crossCourseRanges
     */
    public function __construct(
        CurrencyRepositoryInterface $currencies,
        CourseRangeRepositoryInterface $courseRanges,
        CrossCourseRangeRepositoryInterface $crossCourseRanges
    ) {
        $this->currencies = $currencies;
        $this->courseRanges = $courseRanges;
        $this->crossCourseRanges = $crossCourseRanges;
    }

    /**
     * @inheritDoc
     */
    public function getDailyCourse(DateTimeInterface $date, string $currencyID, ?string $baseCurrencyID): DailyCourseResponse
    {
        $currency = $this->getCurrencyByID($currencyID);

        $baseCurrency = null;
        if ($baseCurrencyID !== null) {
            $baseCurrency = $this->getCurrencyByID($baseCurrencyID);
        }

        $courseRange = $this->getCourseRange($date, $currency, $baseCurrency);

        $value = $courseRange->getCourseByDate($date)->getValue();
        $difference = $courseRange->differenceWithPreviousDay($date);

        return new DailyCourseResponse($value, $difference);
    }

    /**
     * @param string $currencyID
     *
     * @return Currency
     * @throws CurrencyRepositoryException
     * @throws InvalidDailyCourseRequestException
     */
    private function getCurrencyByID(string $currencyID): Currency
    {
        try {
            return $this->currencies->getCurrencyByID($currencyID);
        } catch (CurrencyNotFoundException $e) {
            throw new InvalidDailyCourseRequestException(sprintf("Валюта с кодом %s не найдена", $currencyID), 0, $e);
        }
    }

    /**
     * @param DateTimeInterface $date
     * @param Currency $currency
     * @param Currency|null $baseCurrency
     *
     * @return CourseRange
     * @throws CourseRangeException
     */
    private function getCourseRange(DateTimeInterface $date, Currency $currency, ?Currency $baseCurrency): CourseRange
    {
        $to = clone $date;
        $from = \DateTime::createFromFormat("Y-m-d", $date->format("Y-m-d"));
        // Отступим 30 дней, т.к. в торговых днях есть перерывы
        // Это костыль, лучше найти календарь торговых дней
        $from->modify("-30 days");

        if ($baseCurrency === null) {
            return $this->courseRanges->getCourseRange($currency, $from, $to);
        }

        return $this->crossCourseRanges->getCrossCourseRange($baseCurrency, $currency, $from, $to);
    }

}

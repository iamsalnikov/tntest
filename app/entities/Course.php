<?php

namespace entities;

use DateTimeInterface;
use exceptions\InvalidCourseValueException;
use utils\CurrencyOperation;

/**
 * Курс валюты
 *
 * @package entities
 */
class Course
{
    /**
     * @var DateTimeInterface дата курса
     */
    private DateTimeInterface $date;

    /**
     * @var Currency данные по валюте
     */
    private Currency $currency;

    /**
     * @var float стоимость валюты
     */
    private float $value;

    /**
     * Course constructor.
     *
     * @param DateTimeInterface $date дата курса
     * @param Currency $currency данные по валюте в указанную дату
     * @param float $value цена валюты
     *
     * @throws InvalidCourseValueException
     */
    public function __construct(DateTimeInterface $date, Currency $currency, float $value)
    {
        if (CurrencyOperation::lt($value, 0)) {
            throw new InvalidCourseValueException("Стоимость валюты не может быть меньше 0");
        }

        $this->date = $date;
        $this->currency = $currency;
        $this->value = $value;
    }

    /**
     * Получение даты курса
     *
     * @return DateTimeInterface
     */
    public function getDate(): DateTimeInterface
    {
        return $this->date;
    }

    /**
     * Получение данных по валюте в дату курса
     *
     * @return Currency
     */
    public function getCurrency(): Currency
    {
        return $this->currency;
    }

    /**
     * Получение стоимости валюты
     *
     * @return float
     */
    public function getValue(): float
    {
        return $this->value;
    }
}

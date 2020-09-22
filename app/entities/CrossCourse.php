<?php

namespace entities;

use DateTimeInterface;
use utils\CurrencyOperation;

/**
 * Кросскурс
 *
 * Класс расширяет обычный курс, добавляя возможность рассчета по базовому курсу
 *
 * @package entities
 */
class CrossCourse extends Course
{
    /**
     * CrossCourse constructor.
     *
     * @param DateTimeInterface $date дата курса валют
     * @param Currency $currency валюта
     * @param float $value значение курса валюты за указанную дату
     * @param Course $baseCourse базовый курс, по которому происходит рассчет кросскурса
     *
     * @throws \exceptions\InvalidCourseValueException
     */
    public function __construct(DateTimeInterface $date, Currency $currency, float $value, Course $baseCourse)
    {
        // за сколько исходной валюты можно купить один рубль
        $srcPrice = CurrencyOperation::div($currency->getNominal(), $value);
        // за сколько базовой валюты можно купить один рубль
        $basePrice = CurrencyOperation::div($baseCourse->getCurrency()->getNominal(), $baseCourse->getValue());

        $crossValue = CurrencyOperation::div($basePrice, $srcPrice);
        parent::__construct($date, $currency, $crossValue);
    }
}

<?php

namespace repositories;

use DateTimeInterface;
use entities\CourseRange;
use entities\Currency;
use exceptions\CourseRangeException;

/**
 * Репозиторий кросскурсов валют
 *
 * @package repositories
 */
interface CrossCourseRangeRepositoryInterface
{
    /**
     * Получение кросскурса валют
     *
     * @param Currency $baseCurrency базовая валюта курса
     * @param Currency $currency валюта, по которой получаем курс
     * @param DateTimeInterface $from с какой даты нужен курс
     * @param DateTimeInterface $to по какую дату нужен курс
     *
     * @return CourseRange
     * @throws CourseRangeException
     */
    public function getCrossCourseRange(
        Currency $baseCurrency,
        Currency $currency,
        DateTimeInterface $from,
        DateTimeInterface $to
    ): CourseRange;
}

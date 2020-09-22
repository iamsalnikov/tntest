<?php

namespace repositories;

use DateTimeInterface;
use entities\CourseRange;
use entities\Currency;
use exceptions\CourseRangeException;

/**
 * Репозиторий курсов валют
 *
 * @package repositories
 */
interface CourseRangeRepositoryInterface
{
    /**
     * Получение курса валют
     *
     * @param Currency $currency валюта, по которой получаем курс
     * @param DateTimeInterface $from с какой даты нужен курс
     * @param DateTimeInterface $to по какую дату нужен курс
     *
     * @return CourseRange
     * @throws CourseRangeException
     */
    public function getCourseRange(Currency $currency, DateTimeInterface $from, DateTimeInterface $to): CourseRange;
}

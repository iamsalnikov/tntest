<?php

namespace entities;

use DateTimeInterface;

/**
 * Class DailyCourseResponse
 *
 * Содержит информацию о значении курса и разнице с предыдущим торговым днем
 *
 * @package entities
 */
class DailyCourseResponse
{
    /**
     * @var float текущее значение курса
     */
    private float $value;

    /**
     * @var float разница с предыдущим торговым днем
     */
    private float $previousDayDifference;

    /**
     * DailyCourseResponse constructor.
     *
     * @param float $value значение курса
     * @param float $previousDayDifference разница с предыдущим торговым днем
     */
    public function __construct(float $value, float $previousDayDifference)
    {
        $this->value = $value;
        $this->previousDayDifference = $previousDayDifference;
    }

    /**
     * Получение значения курса
     *
     * @return float
     */
    public function getValue(): float
    {
        return $this->value;
    }

    /**
     * Получение разницы с предыдущим торговым днем
     *
     * @return float
     */
    public function getPreviousDayDifference(): float
    {
        return $this->previousDayDifference;
    }

}

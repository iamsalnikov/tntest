<?php

namespace utils;

abstract class CurrencyOperation
{
    /**
     * @param $a
     * @param $b
     * @return float
     */
    public static function sub($a, $b): float
    {
        return (float) bcsub($a, $b, CurrencyScale::TEN_THOUSANDTH);
    }

    /**
     * @param $a
     * @param $b
     * @return float
     */
    public static function mul($a, $b): float
    {
        return (float) bcmul($a, $b, CurrencyScale::TEN_THOUSANDTH);
    }

    /**
     * @param $a
     * @param $b
     * @return float
     */
    public static function add($a, $b): float
    {
        return (float) bcadd($a, $b, CurrencyScale::TEN_THOUSANDTH);
    }

    /**
     * @param $a
     * @param $b
     * @return int
     */
    public static function comp($a, $b): int
    {
        return bccomp($a, $b, CurrencyScale::TEN_THOUSANDTH);
    }

    /**
     * @param $a
     * @param $b
     * @return bool
     */
    public static function eq($a, $b): bool
    {
        return static::comp($a, $b) === 0;
    }

    /**
     * @param $a
     * @param $b
     * @return bool
     */
    public static function gt($a, $b): bool
    {
        return static::comp($a, $b) === 1;
    }

    /**
     * @param $a
     * @param $b
     * @return bool
     */
    public static function gte($a, $b): bool
    {
        return static::comp($a, $b) !== -1;
    }

    /**
     * @param $a
     * @param $b
     * @return bool
     */
    public static function lt($a, $b): bool
    {
        return static::comp($a, $b) === -1;
    }

    /**
     * @param $a
     * @param $b
     * @return bool
     */
    public static function lte($a, $b): bool
    {
        return static::comp($a, $b) !== 1;
    }

    /**
     * @param $a
     * @param $b
     * @return float
     */
    public static function div($a, $b): float
    {
        return (float) bcdiv($a, $b, CurrencyScale::TEN_THOUSANDTH);
    }
}

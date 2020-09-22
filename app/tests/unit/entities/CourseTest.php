<?php

namespace tests\unit\entities;

use entities\Course;
use entities\Currency;
use Exception;
use exceptions\InvalidCourseValueException;
use PHPUnit\Framework\TestCase;

class CourseTest extends TestCase
{
    /**
     * @dataProvider getCourseValueTestTable
     *
     * @param float $value
     * @param Exception|null $expException
     *
     * @throws InvalidCourseValueException
     */
    public function testCourseValue(float $value, ?Exception $expException)
    {
        if ($expException !== null) {
            $this->expectExceptionObject($expException);
        }

        $date = $this->createMock(\DateTime::class);
        $currency = $this->createMock(Currency::class);

        $course = new Course($date, $currency, $value);
        $this->assertEquals($value, $course->getValue());
    }

    public function getCourseValueTestTable(): array
    {
        return [
            [
                "value" => 1,
                "expException" => null,
            ],
            [
                "value" => 0,
                "expException" => null,
            ],
            [
                "value" => -1,
                "expException" => new InvalidCourseValueException("Стоимость валюты не может быть меньше 0"),
            ]
        ];
    }
}

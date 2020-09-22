<?php

namespace tests\unit\entities;

use entities\Course;
use entities\CrossCourse;
use entities\Currency;
use PHPUnit\Framework\TestCase;

class CrossCourseTest extends TestCase
{
    /**
     * @dataProvider getCrossCourseValueTestTable
     *
     * @param $baseNominal
     * @param $baseValue
     * @param $srcNominal
     * @param $srcValue
     * @param $expValue
     *
     * @throws \exceptions\InvalidCourseValueException
     */
    public function testCrossCourseValue($baseNominal, $baseValue, $srcNominal, $srcValue, $expValue)
    {
        $baseCourse = new Course(
            $this->createMock(\DateTime::class),
            new Currency("", "", $baseNominal),
            $baseValue,
        );

        $course = new CrossCourse(
            $this->createMock(\DateTime::class),
            new Currency("", "", $srcNominal),
            $srcValue,
            $baseCourse
        );

        $this->assertEquals($expValue, $course->getValue());
    }

    public function getCrossCourseValueTestTable(): array
    {
        return [
            [
                "baseNominal" => 1,
                "baseValue" => 75.50,
                "srcNominal" => 1,
                "srcValue" => 89.56,
                "expValue" => 1.1891
            ],
            [
                "baseNominal" => 1,
                "baseValue" => 75.50,
                "srcNominal" => 100,
                "srcValue" => 15.99,
                "expValue" => 0.0021
            ],
        ];
    }
}

<?php

namespace tests\unit\entities;

use DateTimeInterface;
use entities\Course;
use entities\CourseRange;
use entities\Currency;
use Exception;
use exceptions\EmptyCourseRangeDateException;
use exceptions\EmptyCourseRangeException;
use exceptions\InvalidCourseRangeItemException;
use exceptions\InvalidCourseValueException;
use exceptions\InvalidCurrencyNominalException;
use PHPUnit\Framework\TestCase;

class CourseRangeTest extends TestCase
{
    /**
     * @dataProvider getConstructorExceptionsTestTable
     *
     * @param Course[] $courses
     * @param Exception|null $expException
     *
     * @throws EmptyCourseRangeException
     * @throws InvalidCourseRangeItemException
     */
    public function testConstructorExceptions(array $courses, ?Exception $expException)
    {
        if ($expException !== null) {
            $this->expectExceptionObject($expException);
        }

        $range = new CourseRange($courses);
        $this->assertEquals($courses, $range->getCourses());
    }

    /**
     * @return array[]
     * @throws InvalidCourseValueException
     * @throws InvalidCurrencyNominalException
     */
    public function getConstructorExceptionsTestTable(): array
    {
        return [
            [
                "courses" => [],
                "expException" => new EmptyCourseRangeException("Диапазон данных по курсу должен содержать данные хотя бы за одну дату")
            ],
            [
                "courses" => [null],
                "expExceptions" => new InvalidCourseRangeItemException("Массив курсов должен состоять из курсов"),
            ],
            [
                "courses" => [1],
                "expExceptions" => new InvalidCourseRangeItemException("Массив курсов должен состоять из курсов"),
            ],
            [
                "courses" => ["hello"],
                "expExceptions" => new InvalidCourseRangeItemException("Массив курсов должен состоять из курсов"),
            ],

            [
                "courses" => [new Course(new \DateTime(), new Currency("", "", 10), 10)],
                "expExceptions" => null
            ],
        ];
    }

    /**
     * @dataProvider getChronologicalCoursesTestTable
     *
     * @param Course[] $srcCourses
     * @param Course[] $expCourses
     *
     * @throws EmptyCourseRangeException
     * @throws InvalidCourseRangeItemException
     */
    public function testChronologicalCourses(array $srcCourses, array $expCourses)
    {
        $range = new CourseRange($srcCourses);
        $this->assertEquals($expCourses, $range->getCourses());
    }

    /**
     * @return array[]
     * @throws InvalidCourseValueException
     * @throws InvalidCurrencyNominalException
     */
    public function getChronologicalCoursesTestTable(): array
    {
        return [
            [
                "srcCourses" => [
                    new Course(
                        (new \DateTime())
                            ->setDate(2000, 12, 11)
                            ->setTime(0, 0, 0, 0),
                        new Currency("", "", 1),
                        10
                    ),
                ],
                "expCourses" => [
                    new Course(
                        (new \DateTime())
                            ->setDate(2000, 12, 11)
                            ->setTime(0, 0, 0, 0),
                        new Currency("", "", 1),
                        10
                    ),
                ],
            ],

            [
                "srcCourses" => [
                    new Course(
                        (new \DateTime())
                            ->setDate(2000, 12, 11)
                            ->setTime(0, 0, 0, 0),
                        new Currency("", "", 1),
                        10
                    ),
                    new Course(
                        (new \DateTime())
                            ->setDate(2000, 12, 10)
                            ->setTime(0, 0, 0, 0),
                        new Currency("", "", 1),
                        10
                    ),
                ],
                "expCourses" => [
                    new Course(
                        (new \DateTime())
                            ->setDate(2000, 12, 10)
                            ->setTime(0, 0, 0, 0),
                        new Currency("", "", 1),
                        10
                    ),
                    new Course(
                        (new \DateTime())
                            ->setDate(2000, 12, 11)
                            ->setTime(0, 0, 0, 0),
                        new Currency("", "", 1),
                        10
                    ),
                ],
            ],

            [
                "srcCourses" => [
                    new Course(
                        (new \DateTime())
                            ->setDate(2000, 12, 11)
                            ->setTime(0, 0, 0, 0),
                        new Currency("", "", 1),
                        10
                    ),
                    new Course(
                        (new \DateTime())
                            ->setDate(2000, 12, 10)
                            ->setTime(0, 0, 0, 0),
                        new Currency("", "", 1),
                        10
                    ),
                    new Course(
                        (new \DateTime())
                            ->setDate(2001, 12, 11)
                            ->setTime(0, 0, 0, 0),
                        new Currency("", "", 1),
                        10
                    ),
                ],
                "expCourses" => [
                    new Course(
                        (new \DateTime())
                            ->setDate(2000, 12, 10)
                            ->setTime(0, 0, 0, 0),
                        new Currency("", "", 1),
                        10
                    ),
                    new Course(
                        (new \DateTime())
                            ->setDate(2000, 12, 11)
                            ->setTime(0, 0, 0, 0),
                        new Currency("", "", 1),
                        10
                    ),
                    new Course(
                        (new \DateTime())
                            ->setDate(2001, 12, 11)
                            ->setTime(0, 0, 0, 0),
                        new Currency("", "", 1),
                        10
                    ),
                ],
            ]
        ];
    }

    /**
     * @dataProvider getDatesBordersTestTable
     *
     * @param Course[] $srcCourses
     * @param DateTimeInterface $expFirstDate
     * @param DateTimeInterface $expLastDate
     *
     * @throws EmptyCourseRangeException
     * @throws InvalidCourseRangeItemException
     */
    public function testDatesBorders(array $srcCourses, DateTimeInterface $expFirstDate, DateTimeInterface $expLastDate)
    {
        $range = new CourseRange($srcCourses);
        $this->assertEquals($expFirstDate, $range->getFirstDate());
        $this->assertEquals($expLastDate, $range->getLastDate());
    }

    /**
     * @return array[]
     * @throws InvalidCourseValueException
     * @throws InvalidCurrencyNominalException
     */
    public function getDatesBordersTestTable(): array
    {
        return [
            [
                "srcCourses" => [
                    new Course(
                        (new \DateTime())
                            ->setDate(2000, 12, 11)
                            ->setTime(0, 0, 0, 0),
                        new Currency("", "", 1),
                        10
                    ),
                ],
                "expFirstDate" => (new \DateTime())
                    ->setDate(2000, 12, 11)
                    ->setTime(0, 0, 0, 0),
                "expLastDate" => (new \DateTime())
                    ->setDate(2000, 12, 11)
                    ->setTime(0, 0, 0, 0),
            ],

            [
                "srcCourses" => [
                    new Course(
                        (new \DateTime())
                            ->setDate(2000, 12, 11)
                            ->setTime(0, 0, 0, 0),
                        new Currency("", "", 1),
                        10
                    ),
                    new Course(
                        (new \DateTime())
                            ->setDate(2000, 12, 10)
                            ->setTime(0, 0, 0, 0),
                        new Currency("", "", 1),
                        10
                    ),
                ],
                "expFirstDate" => (new \DateTime())
                    ->setDate(2000, 12, 10)
                    ->setTime(0, 0, 0, 0),
                "expLastDate" => (new \DateTime())
                    ->setDate(2000, 12, 11)
                    ->setTime(0, 0, 0, 0),
            ],

            [
                "srcCourses" => [
                    new Course(
                        (new \DateTime())
                            ->setDate(2000, 12, 11)
                            ->setTime(0, 0, 0, 0),
                        new Currency("", "", 1),
                        10
                    ),
                    new Course(
                        (new \DateTime())
                            ->setDate(2000, 12, 10)
                            ->setTime(0, 0, 0, 0),
                        new Currency("", "", 1),
                        10
                    ),
                    new Course(
                        (new \DateTime())
                            ->setDate(2001, 12, 11)
                            ->setTime(0, 0, 0, 0),
                        new Currency("", "", 1),
                        10
                    ),
                ],
                "expFirstDate" => (new \DateTime())
                    ->setDate(2000, 12, 10)
                    ->setTime(0, 0, 0, 0),
                "expLastDate" => (new \DateTime())
                    ->setDate(2001, 12, 11)
                    ->setTime(0, 0, 0, 0),
            ]
        ];
    }

    /**
     * @dataProvider getCourseRangeByDateTestTable
     *
     * @param $courses
     * @param $dateToFind
     * @param $expCourse
     * @param $expException
     *
     * @throws EmptyCourseRangeException
     * @throws InvalidCourseRangeItemException
     * @throws EmptyCourseRangeDateException
     */
    public function testGetCourseRangeByDate($courses, $dateToFind, $expCourse, $expException)
    {
        if ($expException !== null) {
            $this->expectExceptionObject($expException);
        }

        $courseRange = new CourseRange($courses);
        $course = $courseRange->getCourseByDate($dateToFind);
        $this->assertEquals($expCourse, $course);
    }

    public function getCourseRangeByDateTestTable(): array
    {
        return [
            [
                "courses" => [
                    new Course(
                        (new \DateTime())->setDate(1, 1, 2)->setTime(0, 0, 0),
                        new Currency("", "", 1),
                        10
                    ),
                ],
                "dateToFind" => (new \DateTime())->setDate(1, 1, 1)->setTime(0, 0, 0),
                "expCourse" => null,
                "expException" => new EmptyCourseRangeDateException("За указаную дату нет данных по курсу"),
            ],
            [
                "courses" => [
                    new Course(
                        (new \DateTime())->setDate(1, 1, 2)->setTime(0, 0, 0, 0),
                        new Currency("", "", 1),
                        10
                    ),
                ],
                "dateToFind" => (new \DateTime())->setDate(1, 1, 2),
                "expCourse" => new Course(
                    (new \DateTime())->setDate(1, 1, 2)->setTime(0, 0, 0, 0),
                    new Currency("", "", 1),
                    10
                ),
                "expException" => null,
            ],
            [
                "courses" => [
                    new Course(
                        (new \DateTime())->setDate(1, 1, 2)->setTime(0, 0, 0, 0),
                        new Currency("", "", 1),
                        10
                    ),
                    new Course(
                        (new \DateTime())->setDate(1, 1, 3)->setTime(0, 0, 0, 0),
                        new Currency("", "", 1),
                        10
                    ),
                ],
                "dateToFind" => (new \DateTime())->setDate(1, 1, 2),
                "expCourse" => new Course(
                    (new \DateTime())->setDate(1, 1, 2)->setTime(0, 0, 0, 0),
                    new Currency("", "", 1),
                    10
                ),
                "expException" => null,
            ],
        ];
    }

    /**
     * @dataProvider getDifferenceWithPreviousDayTestTable
     *
     * @param $courses
     * @param $date
     * @param $expException
     * @param $expDifference
     *
     * @throws EmptyCourseRangeException
     * @throws InvalidCourseRangeItemException
     */
    public function testDifferenceWithPreviousDay($courses, $date, $expException, $expDifference)
    {
        if ($expException !== null) {
            $this->expectExceptionObject($expException);
        }

        $courseRange = new CourseRange($courses);
        $difference = $courseRange->differenceWithPreviousDay($date);
        $this->assertEquals($expDifference, $difference);
    }

    public function getDifferenceWithPreviousDayTestTable(): array
    {
        return [
            [
                "courses" => [
                    new Course(
                        (new \DateTime())->setDate(2000, 1, 1),
                        new Currency("", "", 1),
                        100
                    )
                ],
                "date" => (new \DateTime())->setDate(2000, 1, 1),
                "expException" => new EmptyCourseRangeDateException("Нет данных предыдущему торговому дню для 01.01.2000"),
                "expDifference" => null,
            ],
            [
                "courses" => [
                    new Course(
                        (new \DateTime())->setDate(2000, 1, 1),
                        new Currency("", "", 1),
                        100
                    ),
                    new Course(
                        (new \DateTime())->setDate(2000, 1, 2),
                        new Currency("", "", 1),
                        50
                    )
                ],
                "date" => (new \DateTime())->setDate(2000, 1, 1),
                "expException" => new EmptyCourseRangeDateException("Нет данных предыдущему торговому дню для 01.01.2000"),
                "expDifference" => null,
            ],
            [
                "courses" => [
                    new Course(
                        (new \DateTime())->setDate(2000, 1, 2),
                        new Currency("", "", 1),
                        100
                    ),
                    new Course(
                        (new \DateTime())->setDate(2000, 1, 1),
                        new Currency("", "", 1),
                        50
                    )
                ],
                "date" => (new \DateTime())->setDate(2000, 1, 2),
                "expException" => null,
                "expDifference" => 50,
            ],
            [
                "courses" => [
                    new Course(
                        (new \DateTime())->setDate(2000, 1, 2),
                        new Currency("", "", 1),
                        100
                    ),
                    new Course(
                        (new \DateTime())->setDate(2000, 1, 1),
                        new Currency("", "", 1),
                        150
                    )
                ],
                "date" => (new \DateTime())->setDate(2000, 1, 2),
                "expException" => null,
                "expDifference" => -50,
            ],
        ];
    }
}

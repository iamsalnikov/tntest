<?php

namespace tests\unit\repositories;

use DateTime;
use entities\Course;
use entities\CourseRange;
use entities\Currency;
use entities\DailyCourseResponse;
use exceptions\CurrencyNotFoundException;
use exceptions\InvalidDailyCourseRequestException;
use PHPUnit\Framework\TestCase;
use repositories\CourseRangeRepositoryInterface;
use repositories\CrossCourseRangeRepositoryInterface;
use repositories\CurrencyRepositoryInterface;
use repositories\DailyCourseRepository;

class DailyCourseRepositoryTest extends TestCase
{
    public function testGetDailyCourseOnBadCurrency()
    {
        $currencyRepository = $this->createMock(CurrencyRepositoryInterface::class);
        $cr = $this->createMock(CourseRangeRepositoryInterface::class);
        $ccr = $this->createMock(CrossCourseRangeRepositoryInterface::class);

        $currencyRepository->method("getCurrencyByID")->willThrowException(
            new CurrencyNotFoundException()
        );

        $this->expectExceptionObject(
            new InvalidDailyCourseRequestException("Валюта с кодом hello не найдена")
        );

        $repo = new DailyCourseRepository($currencyRepository, $cr, $ccr);
        $repo->getDailyCourse(
            $this->createMock(\DateTime::class),
            "hello",
            null
        );
    }

    public function testGetDailyCourseOnBadBaseCurrency()
    {
        $currencyRepository = $this->createMock(CurrencyRepositoryInterface::class);
        $cr = $this->createMock(CourseRangeRepositoryInterface::class);
        $ccr = $this->createMock(CrossCourseRangeRepositoryInterface::class);

        $currencyRepository->method("getCurrencyByID")->willReturnCallback(function ($id) {
            if ($id === "hello") {
                return $this->createMock(Currency::class);
            }

            throw new CurrencyNotFoundException();
        });

        $this->expectExceptionObject(
            new InvalidDailyCourseRequestException("Валюта с кодом world не найдена")
        );

        $repo = new DailyCourseRepository($currencyRepository, $cr, $ccr);
        $repo->getDailyCourse(
            $this->createMock(\DateTime::class),
            "hello",
            "world"
        );
    }

    public function testCourseRangeCallLogic()
    {
        $currencyRepository = $this->createMock(CurrencyRepositoryInterface::class);
        $cr = $this->createMock(CourseRangeRepositoryInterface::class);
        $ccr = $this->createMock(CrossCourseRangeRepositoryInterface::class);

        $cr->expects($this->once())->method("getCourseRange");
        $ccr->expects($this->never())->method("getCrossCourseRange");

        $repo = new DailyCourseRepository($currencyRepository, $cr, $ccr);
        $repo->getDailyCourse(
            new DateTime(),
            "hello",
            null
        );
    }

    public function testCrossCourseRangeCallLogic()
    {
        $currencyRepository = $this->createMock(CurrencyRepositoryInterface::class);
        $cr = $this->createMock(CourseRangeRepositoryInterface::class);
        $ccr = $this->createMock(CrossCourseRangeRepositoryInterface::class);

        $cr->expects($this->never())->method("getCourseRange");
        $ccr->expects($this->once())->method("getCrossCourseRange");

        $repo = new DailyCourseRepository($currencyRepository, $cr, $ccr);
        $repo->getDailyCourse(
            new DateTime(),
            "hello",
            "world"
        );
    }

    public function testCourseRangeResponse()
    {
        $currencyRepository = $this->createMock(CurrencyRepositoryInterface::class);
        $cr = $this->createMock(CourseRangeRepositoryInterface::class);
        $ccr = $this->createMock(CrossCourseRangeRepositoryInterface::class);

        $cr->method("getCourseRange")->willReturn(
            $this->createConfiguredMock(CourseRange::class, [
                "getCourseByDate" => $this->createConfiguredMock(Course::class, [
                    "getValue" => 11.0
                ]),
                "differenceWithPreviousDay" => 10.0
            ])
        );

        $repo = new DailyCourseRepository($currencyRepository, $cr, $ccr);
        $dc = $repo->getDailyCourse(
            new DateTime(),
            "hello",
            null
        );

        $expDc = new DailyCourseResponse(11, 10);
        $this->assertEquals($expDc, $dc);
    }

    public function testCrossCourseRangeResponse()
    {
        $currencyRepository = $this->createMock(CurrencyRepositoryInterface::class);
        $cr = $this->createMock(CourseRangeRepositoryInterface::class);
        $ccr = $this->createMock(CrossCourseRangeRepositoryInterface::class);

        $ccr->method("getCrossCourseRange")->willReturn(
            $this->createConfiguredMock(CourseRange::class, [
                "getCourseByDate" => $this->createConfiguredMock(Course::class, [
                    "getValue" => 11.0
                ]),
                "differenceWithPreviousDay" => 10.0
            ])
        );

        $repo = new DailyCourseRepository($currencyRepository, $cr, $ccr);
        $dc = $repo->getDailyCourse(
            new DateTime(),
            "hello",
            "world"
        );

        $expDc = new DailyCourseResponse(11, 10);
        $this->assertEquals($expDc, $dc);
    }
}

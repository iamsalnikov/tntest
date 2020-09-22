<?php

namespace tests\unit\infrastructure\cbr;

use DateTime;
use entities\Course;
use entities\CourseRange;
use entities\CrossCourse;
use entities\Currency;
use exceptions\CourseRangeException;
use infrastructure\cbr\CourseRangeRepository;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class CourseRangeRepositoryTest extends TestCase
{
    public function testGetCourseRangeOnNetworkProblem()
    {
        $currency = $this->createMock(Currency::class);
        $from = $this->createMock(DateTime::class);
        $to = $this->createMock(DateTime::class);

        $client = $this->createMock(ClientInterface::class);
        $client->method("sendRequest")->willThrowException(
            $this->createMock(ClientExceptionInterface::class)
        );

        $expException = new CourseRangeException("Не удалось получить данные из cbr.ru", 0);
        $this->expectExceptionObject($expException);

        $repo = new CourseRangeRepository($client, "https://hello.ru");
        $repo->getCourseRange($currency, $from, $to);
    }

    public function testGetCourseRangeOnBadResponseStatusCode()
    {
        $currency = $this->createMock(Currency::class);
        $from = $this->createMock(DateTime::class);
        $to = $this->createMock(DateTime::class);

        $client = $this->createConfiguredMock(ClientInterface::class, [
            "sendRequest" => $this->createConfiguredMock(ResponseInterface::class, [
                "getStatusCode" => 500,
            ])
        ]);

        $expException = new CourseRangeException("Не удалось получить данные из cbr.ru", 0);
        $this->expectExceptionObject($expException);

        $repo = new CourseRangeRepository($client, "https://hello.ru");
        $repo->getCourseRange($currency, $from, $to);
    }

    public function testGetCourseRangeOnBadBody()
    {
        $currency = $this->createMock(Currency::class);
        $from = $this->createMock(DateTime::class);
        $to = $this->createMock(DateTime::class);

        $client = $this->createConfiguredMock(ClientInterface::class, [
            "sendRequest" => $this->createConfiguredMock(ResponseInterface::class, [
                "getStatusCode" => 200,
                "getBody" => $this->createConfiguredMock(StreamInterface::class, [
                    "getContents" => "hello world"
                ]),
            ])
        ]);

        $expException = new CourseRangeException("Не удалось получить данные из cbr.ru", 0);
        $this->expectExceptionObject($expException);

        $repo = new CourseRangeRepository($client, "https://hello.ru");
        $repo->getCourseRange($currency, $from, $to);
    }

    /**
     * @dataProvider getCourseRangeOnGoodResponseTestTable
     *
     * @param $currency
     * @param $response
     * @param $expRange
     *
     * @throws CourseRangeException
     */
    public function testGetCourseRangeOnGoodResponse($currency, $response, $expRange)
    {
        $from = $this->createMock(DateTime::class);
        $to = $this->createMock(DateTime::class);

        $client = $this->createConfiguredMock(ClientInterface::class, [
            "sendRequest" => $this->createConfiguredMock(ResponseInterface::class, [
                "getStatusCode" => 200,
                "getBody" => $this->createConfiguredMock(StreamInterface::class, [
                    "getContents" => $response
                ]),
            ])
        ]);

        $repo = new CourseRangeRepository($client, "https://hello.ru");
        $courseRange = $repo->getCourseRange($currency, $from, $to);
        $this->assertEquals($expRange, $courseRange);
    }

    public function getCourseRangeOnGoodResponseTestTable(): array
    {
        return [
            [
                "currency" => new Currency("R01235", "", 10),
                "response" => file_get_contents(__DIR__ . "/../../../data/course.xml"),
                "expRange" => new CourseRange([
                    new Course(
                        (new DateTime())->setDate(2001, 3, 2)->setTime(0, 0, 0, 0),
                        new Currency("R01235", "", 1),
                        28.6200
                    ),
                    new Course(
                        (new DateTime())->setDate(2001, 3, 3)->setTime(0, 0, 0, 0),
                        new Currency("R01235", "", 1),
                        28.6500
                    ),
                    new Course(
                        (new DateTime())->setDate(2001, 3, 6)->setTime(0, 0, 0, 0),
                        new Currency("R01235", "", 1),
                        28.6600
                    ),
                ])
            ],
        ];
    }

    public function testGetCourseRangeOnGoodResponseAndWrongCurrencyID()
    {
        $from = $this->createMock(DateTime::class);
        $to = $this->createMock(DateTime::class);
        $currency = $this->createConfiguredMock(Currency::class, [
            "getId" => "hello world",
        ]);

        $client = $this->createConfiguredMock(ClientInterface::class, [
            "sendRequest" => $this->createConfiguredMock(ResponseInterface::class, [
                "getStatusCode" => 200,
                "getBody" => $this->createConfiguredMock(StreamInterface::class, [
                    "getContents" => file_get_contents(__DIR__ . "/../../../data/course.xml"),
                ]),
            ])
        ]);

        $this->expectExceptionObject(new CourseRangeException("cbr.ru отдал данные не по запрашиваемой валюте"));

        $repo = new CourseRangeRepository($client, "https://hello.ru");
        $courseRange = $repo->getCourseRange($currency, $from, $to);
    }

    public function testGetCrossCourseRangeMissingDate()
    {
        $from = $this->createMock(DateTime::class);
        $to = $this->createMock(DateTime::class);
        $srcCurrency = $this->createConfiguredMock(Currency::class, [
            "getId" => "hello",
        ]);
        $baseCurrency = $this->createConfiguredMock(Currency::class, [
            "getId" => "world",
        ]);

        $responses = [
            $this->createConfiguredMock(ResponseInterface::class, [
                "getStatusCode" => 200,
                "getBody" => $this->createConfiguredMock(StreamInterface::class, [
                    "getContents" => file_get_contents(__DIR__ . "/../../../data/cross_course_missing_date_1.xml"),
            ])]),
            $this->createConfiguredMock(ResponseInterface::class, [
                "getStatusCode" => 200,
                "getBody" => $this->createConfiguredMock(StreamInterface::class, [
                    "getContents" => file_get_contents(__DIR__ . "/../../../data/cross_course_missing_date_2.xml"),
            ])]),
        ];

        $client = $this->createMock(ClientInterface::class);
        $client->method("sendRequest")->willReturnOnConsecutiveCalls(...$responses);

        $this->expectExceptionObject(new CourseRangeException("За указаную дату нет данных по курсу"));

        $repo = new CourseRangeRepository($client, "https://hello.ru");
        $repo->getCrossCourseRange($baseCurrency, $srcCurrency, $from, $to);
    }

    public function testGetCrossCourseRange()
    {
        $from = $this->createMock(DateTime::class);
        $to = $this->createMock(DateTime::class);
        $srcCurrency = $this->createConfiguredMock(Currency::class, [
            "getId" => "hello",
        ]);
        $baseCurrency = $this->createConfiguredMock(Currency::class, [
            "getId" => "world",
        ]);

        $responses = [
            $this->createConfiguredMock(ResponseInterface::class, [
                "getStatusCode" => 200,
                "getBody" => $this->createConfiguredMock(StreamInterface::class, [
                    "getContents" => file_get_contents(__DIR__ . "/../../../data/cross_course_date_1.xml"),
                ])]),
            $this->createConfiguredMock(ResponseInterface::class, [
                "getStatusCode" => 200,
                "getBody" => $this->createConfiguredMock(StreamInterface::class, [
                    "getContents" => file_get_contents(__DIR__ . "/../../../data/cross_course_date_2.xml"),
                ])]),
        ];

        $client = $this->createMock(ClientInterface::class);
        $client->method("sendRequest")->willReturnOnConsecutiveCalls(...$responses);

        $baseCourse = new Course(
            (new DateTime())->setDate(2001, 3, 2)->setTime(0, 0, 0, 0),
            new Currency("world", "", 1),
            28.6200
        );

        $expCourse = new CrossCourse(
            (new DateTime())->setDate(2001, 3, 2)->setTime(0, 0, 0, 0),
            new Currency("hello", "", 100),
            10,
            $baseCourse
        );

        $expRange = new CourseRange([$expCourse]);

        $repo = new CourseRangeRepository($client, "https://hello.ru");
        $courseRange = $repo->getCrossCourseRange($baseCurrency, $srcCurrency, $from, $to);

        $this->assertEquals($expRange, $courseRange);
    }

    public function testCBRRequestBaseURL()
    {
        $client = $this->createMock(ClientInterface::class);
        $response = $this->createConfiguredMock(ResponseInterface::class, [
            "getStatusCode" => 200,
            "getBody" => $this->createConfiguredMock(StreamInterface::class, [
                "getContents" => file_get_contents(__DIR__ . "/../../../data/cross_course_date_1.xml"),
            ])
        ]);
        $client->expects($this->exactly(1))->method("sendRequest")->with(
            $this->callback(function (RequestInterface $request) {
                return $request->getUri()->getHost() === "test.ru" && $request->getUri()->getScheme() === "http";
            })
        )->willReturn($response);

        $repo = new CourseRangeRepository($client, "http://test.ru");
        $repo->getCourseRange(
            new Currency("world", "", 100),
            $this->createMock(DateTime::class),
            $this->createMock(DateTime::class)
        );
    }
}

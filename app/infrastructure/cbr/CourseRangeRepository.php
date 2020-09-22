<?php

namespace infrastructure\cbr;

use DateTimeInterface;
use DOMDocument;
use entities\Course;
use entities\CourseRange;
use entities\CrossCourse;
use entities\Currency;
use exceptions\CourseRangeException;
use exceptions\EmptyCourseRangeException;
use exceptions\InvalidCourseRangeItemException;
use exceptions\InvalidCourseValueException;
use exceptions\InvalidCurrencyNominalException;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use repositories\CourseRangeRepositoryInterface;
use repositories\CrossCourseRangeRepositoryInterface;
use Throwable;

/**
 * Репозиторий для получения данных по диапазону курсов
 *
 * @package infrastructure\cbr
 */
class CourseRangeRepository implements CourseRangeRepositoryInterface, CrossCourseRangeRepositoryInterface
{
    /**
     * @var ClientInterface
     */
    private ClientInterface $httpClient;

    /**
     * @var string
     */
    private string $baseURL;

    /**
     * CourseRangeRepository constructor.
     *
     * @param ClientInterface $httpClient
     * @param string $baseURL
     */
    public function __construct(ClientInterface $httpClient, string $baseURL)
    {
        $this->httpClient = $httpClient;
        $this->baseURL = rtrim($baseURL, "/");
    }

    /**
     * @inheritDoc
     */
    public function getCourseRange(Currency $currency, DateTimeInterface $from, DateTimeInterface $to): CourseRange
    {
        $url = sprintf(
            "%s/scripts/XML_dynamic.asp?date_req1=%s&date_req2=%s&VAL_NM_RQ=%s",
            $this->baseURL,
            $from->format("d/m/Y"),
            $to->format("d/m/Y"),
            $currency->getId(),
        );

        $request = new Request("GET", $url);

        try {
            $response = $this->httpClient->sendRequest($request);
        } catch (ClientExceptionInterface $e) {
            throw new CourseRangeException("Не удалось получить данные из cbr.ru", 0, $e);
        }

        if ($response->getStatusCode() !== 200) {
            throw new CourseRangeException("Не удалось получить данные из cbr.ru", 0);
        }

        return $this->parseCourseRange($response->getBody()->getContents(), $currency);
    }

    /**
     * @inheritDoc
     */
    public function getCrossCourseRange(
        Currency $baseCurrency,
        Currency $currency,
        DateTimeInterface $from,
        DateTimeInterface $to
    ): CourseRange
    {
        $baseRange = $this->getCourseRange($baseCurrency, $from, $to);
        $srcRange = $this->getCourseRange($currency, $from, $to);

        $courses = [];
        foreach ($srcRange->getCourses() as $srcCourse) {
            $baseCourse = $baseRange->getCourseByDate($srcCourse->getDate());
            $courses[] = new CrossCourse(
                $srcCourse->getDate(),
                $srcCourse->getCurrency(),
                $srcCourse->getValue(),
                $baseCourse
            );
        }

        return new CourseRange($courses);
    }

    /**
     * @param string $responseBody
     *
     * @param Currency $currency
     *
     * @return CourseRange
     * @throws CourseRangeException
     * @throws EmptyCourseRangeException
     * @throws InvalidCourseRangeItemException
     * @throws InvalidCourseValueException
     * @throws InvalidCurrencyNominalException
     */
    private function parseCourseRange(string $responseBody, Currency $currency): CourseRange
    {
        $dom = new DOMDocument();
        try {
            $successInit = $dom->loadXML($responseBody);
        } catch (Throwable $e) {
            throw new CourseRangeException("Не удалось получить данные из cbr.ru", 0);
        }

        if (!$successInit) {
            throw new CourseRangeException("Не удалось получить данные из cbr.ru", 0);
        }

        $courses = [];

        $courseRecords = $dom->getElementsByTagName("Record");
        for ($i = 0; $i < $courseRecords->length; $i++) {
            $courseRecord = $courseRecords->item($i);
            if ($courseRecord === null) {
                continue;
            }

            if (!$courseRecord->hasAttributes()) {
                continue;
            }

            $currencyID = $courseRecord->attributes->getNamedItem("Id");
            if ($currencyID === null) {
                continue;
            }

            if ($currencyID->textContent !== $currency->getId()) {
                throw new CourseRangeException("cbr.ru отдал данные не по запрашиваемой валюте");
            }

            $dateAttr = $courseRecord->attributes->getNamedItem("Date");
            if ($dateAttr === null) {
                continue;
            }

            $nominal = (int) $courseRecord->getElementsByTagName("Nominal")->item(0)->textContent;
            $valueStr = $courseRecord->getElementsByTagName("Value")->item(0)->textContent;
            $valueStr = str_replace(",", ".", $valueStr);
            $value = (float) $valueStr;

            // Поправим валюту в соответствии с тем, что пришло в ответе
            // Заметил, что иногда в библиотеке валют и ответе по курсу номинал расходится
            $courseCurrency = new Currency($currency->getId(), $currency->getName(), $nominal);

            $date = \DateTime::createFromFormat("d.m.Y", $dateAttr->textContent)->setTime(0, 0, 0, 0);

            $courses[] = new Course($date, $courseCurrency, $value);
        }

        return new CourseRange($courses);
    }
}

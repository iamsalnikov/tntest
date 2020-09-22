<?php

namespace entities;

use DateTimeInterface;
use exceptions\EmptyCourseRangeDateException;
use exceptions\EmptyCourseRangeException;
use exceptions\InvalidCourseRangeItemException;
use utils\CurrencyOperation;

/**
 * Данные по диапазону курса
 *
 * @package entities
 */
class CourseRange
{
    /**
     * @var Course[]
     */
    private array $courses;

    /**
     * @var Course[]
     *
     * ключ массива - дата в формате Y-m-d
     */
    private array $indexedByDateCourses = [];

    /**
     * @var DateTimeInterface первая дата диапазона
     */
    private DateTimeInterface $firstDate;

    /**
     * @var DateTimeInterface последняя дата диапазона
     */
    private DateTimeInterface $lastDate;

    /**
     * @var float[] разницы с предыдущими торговыми днями
     *
     * ключ - дата. Значение - разница с предыдущим торговым днем
     */
    private array $daysValueDifferences = [];

    /**
     * CourseRange constructor.
     *
     * @param Course[] $courses
     *
     * @throws EmptyCourseRangeException
     * @throws InvalidCourseRangeItemException
     */
    public function __construct(array $courses)
    {
        if (empty($courses)) {
            throw new EmptyCourseRangeException("Диапазон данных по курсу должен содержать данные хотя бы за одну дату");
        }

        foreach ($courses as $course) {
            if (!$course instanceof Course) {
                throw new InvalidCourseRangeItemException("Массив курсов должен состоять из курсов");
            }
        }

        $this->setCoursesInChronologicalOrder($courses);
        $this->indexCourses();
        $this->setupDatesBorders();
        $this->setupDaysValueDifferences();
    }

    /**
     * Получение списка курсов валют
     *
     * @return Course[] отсортированные в хронологическом порядке курсы
     */
    public function getCourses(): array
    {
        return $this->courses;
    }

    /**
     * Получение первой даты диапазона данных по курсам
     *
     * @return DateTimeInterface
     */
    public function getFirstDate(): DateTimeInterface
    {
        return $this->firstDate;
    }

    /**
     * Получение последней даты диапазона данных по курсам
     *
     * @return DateTimeInterface
     */
    public function getLastDate(): DateTimeInterface
    {
        return $this->lastDate;
    }

    /**
     * Получение курса за определенную дату
     *
     * @param DateTimeInterface $date
     *
     * @return Course
     * @throws EmptyCourseRangeDateException
     */
    public function getCourseByDate(DateTimeInterface $date): Course
    {
        $key = $date->format("Y-m-d");
        if (!array_key_exists($key, $this->indexedByDateCourses)) {
            throw new EmptyCourseRangeDateException("За указаную дату нет данных по курсу");
        }

        return $this->indexedByDateCourses[$key];
    }

    /**
     * @param Course[] $courses
     */
    private function setCoursesInChronologicalOrder(array $courses): void
    {
        $this->courses = $courses;
        usort($this->courses, function(Course $a, Course $b) {
            $aTime = $a->getDate()->getTimestamp();
            $bTime = $b->getDate()->getTimestamp();

            if ($aTime < $bTime) {
                return -1;
            }

            if ($aTime > $bTime) {
                return 1;
            }

            return 0;
        });
    }

    /**
     * Установка границ дат диапазона
     */
    private function setupDatesBorders(): void
    {
        if (empty($this->courses)) {
            return;
        }

        $this->firstDate = clone $this->courses[0]->getDate();
        $this->lastDate = clone $this->courses[count($this->courses) - 1]->getDate();
    }

    /**
     * Индексация курсов по дате
     */
    private function indexCourses(): void
    {
        foreach ($this->courses as $course) {
            $this->indexedByDateCourses[$course->getDate()->format("Y-m-d")] = $course;
        }
    }

    /**
     * Установка данных по разнице с предыдущим торговым днем
     */
    private function setupDaysValueDifferences(): void
    {
        for ($i = 1; $i < count($this->courses); $i++) {
            $currentCourse = $this->courses[$i];
            $prevCourse = $this->courses[$i - 1];

            $key = $currentCourse->getDate()->format("Y-m-d");
            $difference = CurrencyOperation::sub($currentCourse->getValue(), $prevCourse->getValue());
            $this->daysValueDifferences[$key] = $difference;
        }
    }

    /**
     * Получение разницы с предыдущим торговым днем
     *
     * @param DateTimeInterface $date дата, относительно которой ищем разницу с предыдущим торговым днем
     *
     * @return float разница с предыдущим торговым днем
     * @throws EmptyCourseRangeDateException
     */
    public function differenceWithPreviousDay(DateTimeInterface $date): float
    {
        $key = $date->format("Y-m-d");
        if (array_key_exists($key, $this->daysValueDifferences)) {
            return $this->daysValueDifferences[$key];
        }

        throw new EmptyCourseRangeDateException(sprintf(
            "Нет данных предыдущему торговому дню для %s",
            $date->format("d.m.Y")
        ));
    }
}

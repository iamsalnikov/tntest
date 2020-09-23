<?php

namespace infrastructure\cache;

use DateTimeInterface;
use entities\CourseRange;
use entities\Currency;
use exceptions\CurrencyRepositoryException;
use Psr\SimpleCache\CacheInterface;
use repositories\CourseRangeRepositoryInterface;
use repositories\CrossCourseRangeRepositoryInterface;

class CourseRangeRepository implements CourseRangeRepositoryInterface, CrossCourseRangeRepositoryInterface
{
    private CourseRangeRepositoryInterface $courseRangeRepo;
    private CrossCourseRangeRepositoryInterface $crossCourseRepo;
    private CacheInterface $cache;
    private int $cacheTTL;

    /**
     * CourseRangeRepository constructor.
     *
     * @param CourseRangeRepositoryInterface $courseRangeRepo репозиторий с данными по курсу
     * @param CrossCourseRangeRepositoryInterface $crossCourseRepo репозиторий с данными по кросскурсу
     * @param CacheInterface $cache компонент для работы с кешем
     * @param int $cacheTTL время жизни кеша в секундах
     *
     * @throws CurrencyRepositoryException
     */
    public function __construct(
        CourseRangeRepositoryInterface $courseRangeRepo,
        CrossCourseRangeRepositoryInterface $crossCourseRepo,
        CacheInterface $cache,
        int $cacheTTL
    ) {
        if ($cacheTTL < 0) {
            throw new CurrencyRepositoryException("TTL кеша должен быть чисом >= 0");
        }

        $this->courseRangeRepo = $courseRangeRepo;
        $this->crossCourseRepo = $crossCourseRepo;
        $this->cache = $cache;
        $this->cacheTTL = $cacheTTL;
    }

    /**
     * @inheritDoc
     */
    public function getCourseRange(Currency $currency, DateTimeInterface $from, DateTimeInterface $to): CourseRange
    {
        $key = sprintf(
            "course-%s-%s-%s",
            $currency->getId(),
            $from->format("Y.m.d"),
            $to->format("Y.m.d")
        );

        if ($this->cache->has($key)) {
            return $this->cache->get($key);
        }

        $courseRange = $this->courseRangeRepo->getCourseRange($currency, $from, $to);
        $this->cache->set($key, $courseRange, $this->cacheTTL);
        return $courseRange;
    }

    /**
     * @inheritDoc
     */
    public function getCrossCourseRange(
        Currency $baseCurrency,
        Currency $currency,
        DateTimeInterface $from,
        DateTimeInterface $to
    ): CourseRange {
        $key = sprintf(
            "cross-course-%s-%s-%s-%s",
            $baseCurrency->getId(),
            $currency->getId(),
            $from->format("Y.m.d"),
            $to->format("Y.m.d")
        );

        if ($this->cache->has($key)) {
            return $this->cache->get($key);
        }

        $courseRange = $this->crossCourseRepo->getCrossCourseRange($baseCurrency, $currency, $from, $to);
        $this->cache->set($key, $courseRange, $this->cacheTTL);
        return $courseRange;
    }

}

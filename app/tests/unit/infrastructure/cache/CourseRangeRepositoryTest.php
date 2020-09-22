<?php

namespace tests\unit\infrastructure\cache;

use DateTime;
use entities\CourseRange;
use entities\Currency;
use Exception;
use exceptions\CurrencyRepositoryException;
use infrastructure\cache\CourseRangeRepository;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;
use repositories\CourseRangeRepositoryInterface;
use repositories\CrossCourseRangeRepositoryInterface;

class CourseRangeRepositoryTest extends TestCase
{
    public function testGetCourseRangeCacheHasProblem()
    {
        $cache = $this->createMock(CacheInterface::class);
        $cache->method("has")->willThrowException(new Exception("hello"));

        $courseRangeRepo = $this->createMock(CourseRangeRepositoryInterface::class);
        $courseRangeRepo->expects($this->never())->method("getCourseRange");

        $crossCourseRangeRepo = $this->createMock(CrossCourseRangeRepositoryInterface::class);
        $crossCourseRangeRepo->expects($this->never())->method("getCrossCourseRange");

        $this->expectExceptionObject(new Exception("hello"));

        $repo = new CourseRangeRepository($courseRangeRepo, $crossCourseRangeRepo, $cache, 0);
        $repo->getCourseRange(
            new Currency("", "", 10),
            $this->createMock(DateTime::class),
            $this->createMock(DateTime::class),
        );
    }

    public function testGetCourseRangeCacheGetProblem()
    {
        $cache = $this->createMock(CacheInterface::class);
        $cache->method("has")->willReturn(true);
        $cache->method("get")->willThrowException(new Exception("hello"));

        $courseRangeRepo = $this->createMock(CourseRangeRepositoryInterface::class);
        $courseRangeRepo->expects($this->never())->method("getCourseRange");

        $crossCourseRangeRepo = $this->createMock(CrossCourseRangeRepositoryInterface::class);
        $crossCourseRangeRepo->expects($this->never())->method("getCrossCourseRange");

        $this->expectExceptionObject(new Exception("hello"));

        $repo = new CourseRangeRepository($courseRangeRepo, $crossCourseRangeRepo, $cache, 0);
        $repo->getCourseRange(
            new Currency("", "", 10),
            $this->createMock(DateTime::class),
            $this->createMock(DateTime::class),
        );
    }

    public function testGetCourseRangeCacheResult()
    {
        $range = $this->createMock(CourseRange::class);

        $cache = $this->createMock(CacheInterface::class);
        $cache->method("has")->willReturn(true);
        $cache->method("get")->willReturn($range);

        $courseRangeRepo = $this->createMock(CourseRangeRepositoryInterface::class);
        $courseRangeRepo->expects($this->never())->method("getCourseRange");

        $crossCourseRangeRepo = $this->createMock(CrossCourseRangeRepositoryInterface::class);
        $crossCourseRangeRepo->expects($this->never())->method("getCrossCourseRange");

        $repo = new CourseRangeRepository($courseRangeRepo, $crossCourseRangeRepo, $cache, 0);
        $result = $repo->getCourseRange(
            new Currency("", "", 10),
            $this->createMock(DateTime::class),
            $this->createMock(DateTime::class),
        );

        $this->assertEquals($range, $result);
    }

    public function testGetCourseRangeCacheSetProblems()
    {
        $cache = $this->createMock(CacheInterface::class);
        $cache->method("has")->willReturn(false);
        $cache->method("set")->willThrowException(new Exception("hello"));

        $courseRangeRepo = $this->createMock(CourseRangeRepositoryInterface::class);
        $courseRangeRepo->expects($this->once())->method("getCourseRange");

        $crossCourseRangeRepo = $this->createMock(CrossCourseRangeRepositoryInterface::class);
        $crossCourseRangeRepo->expects($this->never())->method("getCrossCourseRange");

        $this->expectExceptionObject(new Exception("hello"));

        $repo = new CourseRangeRepository($courseRangeRepo, $crossCourseRangeRepo, $cache, 0);
        $repo->getCourseRange(
            new Currency("", "", 10),
            $this->createMock(DateTime::class),
            $this->createMock(DateTime::class),
        );
    }

    public function testGetCourseRangeGood()
    {
        $range = $this->createMock(CourseRange::class);

        $cache = $this->createMock(CacheInterface::class);
        $cache->method("has")->willReturn(false);

        $courseRangeRepo = $this->createMock(CourseRangeRepositoryInterface::class);
        $courseRangeRepo->expects($this->once())->method("getCourseRange")->willReturn($range);

        $crossCourseRangeRepo = $this->createMock(CrossCourseRangeRepositoryInterface::class);
        $crossCourseRangeRepo->expects($this->never())->method("getCrossCourseRange");

        $repo = new CourseRangeRepository($courseRangeRepo, $crossCourseRangeRepo, $cache, 0);
        $result = $repo->getCourseRange(
            new Currency("", "", 10),
            $this->createMock(DateTime::class),
            $this->createMock(DateTime::class),
        );

        $this->assertEquals($range, $result);
    }

    public function testGetCrossCourseRangeCacheHasProblem()
    {
        $cache = $this->createMock(CacheInterface::class);
        $cache->method("has")->willThrowException(new Exception("hello"));

        $courseRangeRepo = $this->createMock(CourseRangeRepositoryInterface::class);
        $courseRangeRepo->expects($this->never())->method("getCourseRange");

        $crossCourseRangeRepo = $this->createMock(CrossCourseRangeRepositoryInterface::class);
        $crossCourseRangeRepo->expects($this->never())->method("getCrossCourseRange");

        $this->expectExceptionObject(new Exception("hello"));

        $repo = new CourseRangeRepository($courseRangeRepo, $crossCourseRangeRepo, $cache, 0);
        $repo->getCrossCourseRange(
            new Currency("", "", 1),
            new Currency("", "", 10),
            $this->createMock(DateTime::class),
            $this->createMock(DateTime::class),
        );
    }

    public function testGetCrossCourseRangeCacheGetProblem()
    {
        $cache = $this->createMock(CacheInterface::class);
        $cache->method("has")->willReturn(true);
        $cache->method("get")->willThrowException(new Exception("hello"));

        $courseRangeRepo = $this->createMock(CourseRangeRepositoryInterface::class);
        $courseRangeRepo->expects($this->never())->method("getCourseRange");

        $crossCourseRangeRepo = $this->createMock(CrossCourseRangeRepositoryInterface::class);
        $crossCourseRangeRepo->expects($this->never())->method("getCrossCourseRange");

        $this->expectExceptionObject(new Exception("hello"));

        $repo = new CourseRangeRepository($courseRangeRepo, $crossCourseRangeRepo, $cache, 0);
        $repo->getCrossCourseRange(
            new Currency("", "", 1),
            new Currency("", "", 10),
            $this->createMock(DateTime::class),
            $this->createMock(DateTime::class),
        );
    }

    public function testGetCrossCourseRangeCacheResult()
    {
        $range = $this->createMock(CourseRange::class);

        $cache = $this->createMock(CacheInterface::class);
        $cache->method("has")->willReturn(true);
        $cache->method("get")->willReturn($range);

        $courseRangeRepo = $this->createMock(CourseRangeRepositoryInterface::class);
        $courseRangeRepo->expects($this->never())->method("getCourseRange");

        $crossCourseRangeRepo = $this->createMock(CrossCourseRangeRepositoryInterface::class);
        $crossCourseRangeRepo->expects($this->never())->method("getCrossCourseRange");

        $repo = new CourseRangeRepository($courseRangeRepo, $crossCourseRangeRepo, $cache, 0);
        $result = $repo->getCrossCourseRange(
            new Currency("", "", 1),
            new Currency("", "", 10),
            $this->createMock(DateTime::class),
            $this->createMock(DateTime::class),
        );

        $this->assertEquals($range, $result);
    }

    public function testGetCrossCourseRangeCacheSetProblems()
    {
        $cache = $this->createMock(CacheInterface::class);
        $cache->method("has")->willReturn(false);
        $cache->method("set")->willThrowException(new Exception("hello"));

        $courseRangeRepo = $this->createMock(CourseRangeRepositoryInterface::class);
        $courseRangeRepo->expects($this->never())->method("getCourseRange");

        $crossCourseRangeRepo = $this->createMock(CrossCourseRangeRepositoryInterface::class);
        $crossCourseRangeRepo->expects($this->once())->method("getCrossCourseRange");

        $this->expectExceptionObject(new Exception("hello"));

        $repo = new CourseRangeRepository($courseRangeRepo, $crossCourseRangeRepo, $cache, 0);
        $repo->getCrossCourseRange(
            new Currency("", "", 1),
            new Currency("", "", 10),
            $this->createMock(DateTime::class),
            $this->createMock(DateTime::class),
        );
    }

    public function testGetCrossCourseRangeGood()
    {
        $range = $this->createMock(CourseRange::class);

        $cache = $this->createMock(CacheInterface::class);
        $cache->method("has")->willReturn(false);

        $courseRangeRepo = $this->createMock(CourseRangeRepositoryInterface::class);
        $courseRangeRepo->expects($this->never())->method("getCourseRange");

        $crossCourseRangeRepo = $this->createMock(CrossCourseRangeRepositoryInterface::class);
        $crossCourseRangeRepo->expects($this->once())->method("getCrossCourseRange")->willReturn($range);

        $repo = new CourseRangeRepository($courseRangeRepo, $crossCourseRangeRepo, $cache, 0);
        $result = $repo->getCrossCourseRange(
            new Currency("", "", 1),
            new Currency("", "", 10),
            $this->createMock(DateTime::class),
            $this->createMock(DateTime::class),
        );

        $this->assertEquals($range, $result);
    }

    public function testCourseRepositoryBadTTL()
    {
        $cache = $this->createMock(CacheInterface::class);

        $courseRangeRepo = $this->createMock(CourseRangeRepositoryInterface::class);
        $crossCourseRangeRepo = $this->createMock(CrossCourseRangeRepositoryInterface::class);

        $this->expectExceptionObject(new CurrencyRepositoryException("TTL кеша должен быть чисом >= 0"));

        new CourseRangeRepository($courseRangeRepo, $crossCourseRangeRepo, $cache, -1);
    }

    public function testGetCourseRangeTTLCache()
    {
        $ttl = 10;

        $cache = $this->createMock(CacheInterface::class);
        $cache->method("has")->willReturn(false);
        $cache->expects($this->once())->method("set")->with(
            $this->anything(),
            $this->anything(),
            $ttl
        );

        $courseRangeRepo = $this->createMock(CourseRangeRepositoryInterface::class);
        $crossCourseRangeRepo = $this->createMock(CrossCourseRangeRepositoryInterface::class);

        $repo = new CourseRangeRepository($courseRangeRepo, $crossCourseRangeRepo, $cache, $ttl);
        $repo->getCourseRange(
            $this->createMock(Currency::class),
            $this->createMock(DateTime::class),
            $this->createMock(DateTime::class)
        );
    }

    public function testGetCrossCourseRangeTTLCache()
    {
        $ttl = 10;

        $cache = $this->createMock(CacheInterface::class);
        $cache->method("has")->willReturn(false);
        $cache->expects($this->once())->method("set")->with(
            $this->anything(),
            $this->anything(),
            $ttl
        );

        $courseRangeRepo = $this->createMock(CourseRangeRepositoryInterface::class);
        $crossCourseRangeRepo = $this->createMock(CrossCourseRangeRepositoryInterface::class);

        $repo = new CourseRangeRepository($courseRangeRepo, $crossCourseRangeRepo, $cache, $ttl);
        $repo->getCrossCourseRange(
            $this->createMock(Currency::class),
            $this->createMock(Currency::class),
            $this->createMock(DateTime::class),
            $this->createMock(DateTime::class)
        );
    }
}

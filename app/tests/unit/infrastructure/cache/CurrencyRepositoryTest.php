<?php

namespace tests\unit\infrastructure\cache;

use entities\Currency;
use Exception;
use exceptions\CurrencyRepositoryException;
use infrastructure\cache\CurrencyRepository;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;
use repositories\CurrencyRepositoryInterface;

class CurrencyRepositoryTest extends TestCase
{
    public function testGetCurrenciesCacheHasProblem()
    {
        $cache = $this->createMock(CacheInterface::class);
        $cache->method("has")->willThrowException(new Exception("hello"));

        $wrappedRepo = $this->createMock(CurrencyRepositoryInterface::class);
        $wrappedRepo->expects($this->never())->method("getCurrencies");

        $this->expectExceptionObject(new Exception("hello"));

        $repo = new CurrencyRepository($wrappedRepo, $cache, 0);
        $repo->getCurrencies();
    }

    public function testGetCurrenciesCacheGetProblem()
    {
        $cache = $this->createMock(CacheInterface::class);
        $cache->method("has")->willReturn(true);
        $cache->method("get")->willThrowException(new Exception("hello"));

        $wrappedRepo = $this->createMock(CurrencyRepositoryInterface::class);
        $wrappedRepo->expects($this->never())->method("getCurrencies");

        $this->expectExceptionObject(new Exception("hello"));

        $repo = new CurrencyRepository($wrappedRepo, $cache, 0);
        $repo->getCurrencies();
    }

    public function testGetCurrenciesCacheResult()
    {
        $cache = $this->createMock(CacheInterface::class);
        $cache->method("has")->willReturn(true);
        $cache->method("get")->willReturn([
            new Currency("wat", "swat", 1)
        ]);

        $wrappedRepo = $this->createMock(CurrencyRepositoryInterface::class);
        $wrappedRepo->expects($this->never())->method("getCurrencies");


        $repo = new CurrencyRepository($wrappedRepo, $cache, 0);
        $currencies = $repo->getCurrencies();
        $this->assertEquals([
            new Currency("wat", "swat", 1)
        ], $currencies);
    }

    public function testGetCurrenciesCacheSetProblems()
    {
        $cache = $this->createMock(CacheInterface::class);
        $cache->method("has")->willReturn(false);
        $cache->expects($this->never())->method("get");
        $cache->method("set")->willThrowException(new Exception("hello"));

        $wrappedRepo = $this->createMock(CurrencyRepositoryInterface::class);
        $wrappedRepo->expects($this->once())->method("getCurrencies");

        $this->expectExceptionObject(new Exception("hello"));
        $repo = new CurrencyRepository($wrappedRepo, $cache, 0);
        $repo->getCurrencies();
    }

    public function testGetCurrenciesGood()
    {
        $currencies = [new Currency("hello", "world", 10)];

        $cache = $this->createMock(CacheInterface::class);
        $cache->method("has")->willReturn(false);
        $cache->expects($this->never())->method("get");
        $cache->expects($this->once())->method("set")->with(
            $this->anything(),
            $currencies,
            $this->anything()
        );

        $wrappedRepo = $this->createMock(CurrencyRepositoryInterface::class);
        $wrappedRepo->expects($this->once())->method("getCurrencies")->willReturn($currencies);

        $repo = new CurrencyRepository($wrappedRepo, $cache, 0);
        $cur = $repo->getCurrencies();

        $this->assertEquals($currencies, $cur);
    }

    public function testGetCurrencyByIDCacheHasProblem()
    {
        $cache = $this->createMock(CacheInterface::class);
        $cache->method("has")->willThrowException(new Exception("hello"));

        $wrappedRepo = $this->createMock(CurrencyRepositoryInterface::class);
        $wrappedRepo->expects($this->never())->method("getCurrencyByID");

        $this->expectExceptionObject(new Exception("hello"));

        $repo = new CurrencyRepository($wrappedRepo, $cache, 0);
        $repo->getCurrencyByID("hello");
    }

    public function testGetCurrencyByIDCacheGetProblem()
    {
        $cache = $this->createMock(CacheInterface::class);
        $cache->method("has")->willReturn(true);
        $cache->method("get")->willThrowException(new Exception("hello"));

        $wrappedRepo = $this->createMock(CurrencyRepositoryInterface::class);
        $wrappedRepo->expects($this->never())->method("getCurrencyByID");

        $this->expectExceptionObject(new Exception("hello"));

        $repo = new CurrencyRepository($wrappedRepo, $cache, 0);
        $repo->getCurrencyByID("hello");
    }

    public function testGetCurrencyByIDCacheResult()
    {
        $cache = $this->createMock(CacheInterface::class);
        $cache->method("has")->willReturn(true);
        $cache->method("get")->willReturn(new Currency("wat", "swat", 1));

        $wrappedRepo = $this->createMock(CurrencyRepositoryInterface::class);
        $wrappedRepo->expects($this->never())->method("getCurrencyByID");


        $repo = new CurrencyRepository($wrappedRepo, $cache, 0);
        $currencies = $repo->getCurrencyByID("hello");
        $this->assertEquals(new Currency("wat", "swat", 1), $currencies);
    }

    public function testGetCurrencyByIDCacheSetProblems()
    {
        $cache = $this->createMock(CacheInterface::class);
        $cache->method("has")->willReturn(false);
        $cache->expects($this->never())->method("get");
        $cache->method("set")->willThrowException(new Exception("hello"));

        $wrappedRepo = $this->createMock(CurrencyRepositoryInterface::class);
        $wrappedRepo->expects($this->once())->method("getCurrencyByID");

        $this->expectExceptionObject(new Exception("hello"));
        $repo = new CurrencyRepository($wrappedRepo, $cache, 0);
        $repo->getCurrencyByID("hello");
    }

    public function testGetCurrencyByIDGood()
    {
        $currency = new Currency("hello", "world", 10);

        $cache = $this->createMock(CacheInterface::class);
        $cache->method("has")->willReturn(false);
        $cache->expects($this->never())->method("get");
        $cache->expects($this->once())->method("set")->with(
            $this->anything(),
            $currency,
            $this->anything()
        );

        $wrappedRepo = $this->createMock(CurrencyRepositoryInterface::class);
        $wrappedRepo->expects($this->once())->method("getCurrencyByID")->willReturn($currency);

        $repo = new CurrencyRepository($wrappedRepo, $cache, 0);
        $cur = $repo->getCurrencyByID("hello");

        $this->assertEquals($currency, $cur);
    }

    public function testBadCacheRepositoryTTL()
    {
        $cache = $this->createMock(CacheInterface::class);
        $wrappedRepo = $this->createMock(CurrencyRepositoryInterface::class);

        $this->expectExceptionObject(new CurrencyRepositoryException("TTL кеша должен быть чисом >= 0"));

        new CurrencyRepository($wrappedRepo, $cache, -1);
    }

    public function testGetCurrencyByIDGoodTTL()
    {
        $ttl = 10;

        $cache = $this->createMock(CacheInterface::class);
        $cache->method("has")->willReturn(false);
        $cache->expects($this->never())->method("get");
        $cache->expects($this->once())->method("set")->with(
            $this->anything(),
            $this->anything(),
            $ttl
        );

        $wrappedRepo = $this->createMock(CurrencyRepositoryInterface::class);

        $repo = new CurrencyRepository($wrappedRepo, $cache, $ttl);
        $repo->getCurrencyByID("hello");
    }

    public function testGetCurrenciesByIDGoodTTL()
    {
        $ttl = 10;

        $cache = $this->createMock(CacheInterface::class);
        $cache->method("has")->willReturn(false);
        $cache->expects($this->never())->method("get");
        $cache->expects($this->once())->method("set")->with(
            $this->anything(),
            $this->anything(),
            $ttl
        );

        $wrappedRepo = $this->createMock(CurrencyRepositoryInterface::class);

        $repo = new CurrencyRepository($wrappedRepo, $cache, $ttl);
        $repo->getCurrencies();
    }
}

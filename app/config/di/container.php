<?php

use chillerlan\SimpleCache\MemcachedCache;
use DI\ContainerBuilder;
use infrastructure\cache\CourseRangeRepository as CachedCourseRangeRepository;
use infrastructure\cache\CurrencyRepository as CachedCurrencyRepository;
use infrastructure\cbr\CourseRangeRepository as CBRCourseRangeRepo;
use infrastructure\cbr\CurrencyRepository as CBRCurrencyRepository;
use Psr\Container\ContainerInterface;
use Psr\Http\Client\ClientInterface;
use Psr\SimpleCache\CacheInterface;
use repositories\CourseRangeRepositoryInterface;
use repositories\CrossCourseRangeRepositoryInterface;
use repositories\CurrencyRepositoryInterface;
use repositories\DailyCourseRepository;
use repositories\DailyCourseRepositoryInterface;
use Zelenin\HttpClient\ClientFactory;
use function DI\get;
use function DI\object;

$cb = new ContainerBuilder();

$cb->addDefinitions([
    ClientInterface::class => function (ContainerInterface $container) {
        return (new ClientFactory())->create();
    },

    CacheInterface::class => function (ContainerInterface $container) {
        $memcached = new Memcached('memcached');
        $memcached->addServer(getenv("MEMCACHE_HOST"), getenv("MEMCACHE_PORT"));

        return new MemcachedCache($memcached);
    },

    CBRCourseRangeRepo::class => object(CBRCourseRangeRepo::class)
        ->constructorParameter("baseURL", getenv("CBR_BASE_URL")),

    CBRCurrencyRepository::class => object(CBRCurrencyRepository::class)
        ->constructorParameter("baseURL", getenv("CBR_BASE_URL")),

    CachedCourseRangeRepository::class => object(CachedCourseRangeRepository::class)
        ->constructorParameter("courseRangeRepo", get(CBRCourseRangeRepo::class))
        ->constructorParameter("crossCourseRepo", get(CBRCourseRangeRepo::class))
        ->constructorParameter("cacheTTL", (int) getenv("CBR_CACHE_TTL")),

    CurrencyRepositoryInterface::class => object(CachedCurrencyRepository::class)
        ->constructorParameter("wrappedRepo", get(CBRCurrencyRepository::class))
        ->constructorParameter("cacheTTL", (int) getenv("CBR_CACHE_TTL")),

    CourseRangeRepositoryInterface::class => object(CachedCourseRangeRepository::class),
    CrossCourseRangeRepositoryInterface::class => object(CachedCourseRangeRepository::class),
    DailyCourseRepositoryInterface::class => object(DailyCourseRepository::class),
]);

return $cb;

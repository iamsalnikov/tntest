<?php

namespace infrastructure\cache;

use entities\Currency;
use exceptions\CurrencyRepositoryException;
use Psr\SimpleCache\CacheInterface;
use repositories\CurrencyRepositoryInterface;

/**
 * Class CurrencyRepository
 *
 * @package infrastructure\cache
 */
class CurrencyRepository implements CurrencyRepositoryInterface
{
    private const CURRENCY_COLLECTION_KEY = "currency-collection";
    private const CURRENCY_CONCRETE_KEY = "currency-concrete";

    private CurrencyRepositoryInterface $wrappedRepo;
    private CacheInterface $cache;
    private int $cacheTTL;

    /**
     * CurrencyRepository constructor.
     *
     * @param CurrencyRepositoryInterface $wrappedRepo репозиторий валют
     * @param CacheInterface $cache компонент для работы с кешем
     * @param int $cacheTTL время жизни кеша в секундах
     *
     * @throws CurrencyRepositoryException
     */
    public function __construct(CurrencyRepositoryInterface $wrappedRepo, CacheInterface $cache, int $cacheTTL)
    {
        if ($cacheTTL < 0) {
            throw new CurrencyRepositoryException("TTL кеша должен быть чисом >= 0");
        }

        $this->wrappedRepo = $wrappedRepo;
        $this->cache = $cache;
        $this->cacheTTL = $cacheTTL;
    }

    /**
     * @inheritDoc
     */
    public function getCurrencies(): array
    {
        if ($this->cache->has(static::CURRENCY_COLLECTION_KEY)) {
            return $this->cache->get(static::CURRENCY_COLLECTION_KEY);
        }

        $collection = $this->wrappedRepo->getCurrencies();
        $this->cache->set(static::CURRENCY_COLLECTION_KEY, $collection, $this->cacheTTL);
        return $collection;
    }

    /**
     * @inheritDoc
     */
    public function getCurrencyByID(string $id): Currency
    {
        $key = sprintf("%s-%s", static::CURRENCY_CONCRETE_KEY, $id);
        if ($this->cache->has($key)) {
            return $this->cache->get($key);
        }

        $currency = $this->wrappedRepo->getCurrencyByID($id);
        $this->cache->set($key, $currency, $this->cacheTTL);
        return $currency;
    }
}

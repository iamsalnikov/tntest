<?php

namespace entities;

use exceptions\InvalidCurrencyNominalException;

/**
 * Class Currency
 *
 * Содержит описание валюты
 *
 * @package entities
 */
class Currency
{
    /**
     * @var string идентификатор валюты
     */
    private string $id;

    /**
     * @var string название валюты
     */
    private string $name;

    /**
     * @var int номинал валюты
     */
    private int $nominal;

    /**
     * Currency constructor.
     *
     * @param string $id идентификатор валюты
     * @param string $name название валюты
     * @param int $nominal номинал валюты
     *
     * @throws InvalidCurrencyNominalException
     */
    public function __construct(string $id, string $name, int $nominal)
    {
        if ($nominal <= 0) {
            throw new InvalidCurrencyNominalException("Номинал валюты должен быть больше 0");
        }

        $this->id = $id;
        $this->name = $name;
        $this->nominal = $nominal;
    }

    /**
     * Получение идентификатора валюты
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Получение названия валюты
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Получение номинала валюты
     *
     * @return int
     */
    public function getNominal(): int
    {
        return $this->nominal;
    }
}

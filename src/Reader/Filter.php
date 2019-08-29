<?php

namespace Yiisoft\Data\Reader;

final class Filter
{
    public const EQUALS = '=';
    public const NOT_EQUALS = '!=';
    public const LESS_THAN = '<';
    public const LESS_THAN_OR_EQUAL = '<=';
    public const GREATER_THAN = '>';
    public const GREATER_THAN_OR_EQUAL = '>=';
    public const IN = 'in';
    public const NOT_IN = 'not in';
    public const LIKE = 'like';

    public const TYPE_INTEGER = 'integer';
    public const TYPE_FLOAT = 'float';
    public const TYPE_BOOLEAN = 'boolean';
    public const TYPE_STRING = 'string';
    public const TYPE_DATETIME = 'datetime';
    public const TYPE_DATE = 'date';
    public const TYPE_TIME = 'time';

    private const ALLOWED_OPERATORS = [
        self::TYPE_INTEGER => [
            self::EQUALS,
            self::NOT_EQUALS,
            self::LESS_THAN,
            self::LESS_THAN_OR_EQUAL,
            self::GREATER_THAN,
            self::GREATER_THAN_OR_EQUAL,
            self::IN,
            self::NOT_IN,
        ],
        self::TYPE_FLOAT => [
            self::EQUALS,
            self::NOT_EQUALS,
            self::LESS_THAN,
            self::LESS_THAN_OR_EQUAL,
            self::GREATER_THAN,
            self::GREATER_THAN_OR_EQUAL,
            self::IN,
            self::NOT_IN,
        ],
        self::TYPE_BOOLEAN => [
            self::EQUALS,
            self::NOT_EQUALS,
        ],
        self::TYPE_STRING => [
            self::EQUALS,
            self::NOT_EQUALS,
            self::IN,
            self::NOT_IN,
            self::LIKE
        ],
        self::TYPE_DATETIME => [
            self::EQUALS,
            self::NOT_EQUALS,
            self::LESS_THAN,
            self::LESS_THAN_OR_EQUAL,
            self::GREATER_THAN,
            self::GREATER_THAN_OR_EQUAL,
            self::IN,
            self::NOT_IN,
        ],
        self::TYPE_DATE => [
            self::EQUALS,
            self::NOT_EQUALS,
            self::LESS_THAN,
            self::LESS_THAN_OR_EQUAL,
            self::GREATER_THAN,
            self::GREATER_THAN_OR_EQUAL,
            self::IN,
            self::NOT_IN,
        ],
        self::TYPE_TIME => [
            self::EQUALS,
            self::NOT_EQUALS,
            self::LESS_THAN,
            self::LESS_THAN_OR_EQUAL,
            self::GREATER_THAN,
            self::GREATER_THAN_OR_EQUAL,
            self::IN,
            self::NOT_IN,
        ],
    ];

    private $currentFilter = [];

    private $config;

    public function __construct(array $config)
    {
        $this->validateConfig($config);
        $this->config = $config;
    }

    private function validateConfig(array $config): void
    {
        foreach ($config as $field => $type) {
            if (is_string($field)) {
                throw new \InvalidArgumentException('Filter config keys should be field names');
            }

            //if (!in_array($type, ))
        }
    }

    public function getCriteria(): array
    {

    }

    public function getCurrentFilter(): array
    {
        return $this->currentFilter;
    }

    public function withCurrentFilter(array $filter): self
    {

    }
}

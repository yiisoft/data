<?php
declare(strict_types=1);

namespace Yiisoft\Data\Reader\Criterion;


final class In implements CriteronInterface
{
    private $field;
    private $value;

    public function __construct(string $field, array $value)
    {
        $this->validateValue($value);

        $this->field = $field;
        $this->value = $value;
    }

    private function validateValue($value): void
    {
        if (is_array($value)) {
            foreach ($value as $arrayValue) {
                if (!is_scalar($arrayValue)) {
                    throw new \InvalidArgumentException('All array values should be scalar');
                }
            }
        }
    }

    public function toArray(): array
    {
        return [self::getOperator(), $this->field, $this->value];
    }

    public static function getOperator(): string
    {
        return 'in';
    }
}

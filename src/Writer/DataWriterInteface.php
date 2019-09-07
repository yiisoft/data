<?php
declare(strict_types=1);

namespace Yiisoft\Data\Writer;

interface DataWriterInteface
{
    public function write(array $items): void;
}

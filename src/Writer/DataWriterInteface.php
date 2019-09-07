<?php
namespace Yiisoft\Data\Writer;

interface DataWriterInteface
{
    public function write(array $items): void;
}

<?php

declare(strict_types=1);

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests');

return (new PhpCsFixer\Config())
    ->setFinder($finder)
    ->setParallelConfig(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PER-CS3.0' => true,
        'no_unused_imports' => true,
        'ordered_class_elements' => true,
        'class_attributes_separation' => ['elements' => ['method' => 'one']],
    ]);

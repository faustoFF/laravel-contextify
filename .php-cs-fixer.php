<?php

return (new PhpCsFixer\Config())
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in(__DIR__ . '/src')
    )
    ->setRules([
        '@PhpCsFixer' => true,
        'phpdoc_align' => ['align' => 'left'],
        'phpdoc_types_order' => ['null_adjustment' => 'always_last'],
        'phpdoc_to_comment' => false,
        'phpdoc_var_without_name' => false,
        'concat_space' => ['spacing' => 'one'],
    ])
;

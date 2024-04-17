<?php

$header = <<<'HEADER'
This file is part of the Purus project.

(c) Anthonius Munthi <me@itstoni.com>

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
HEADER;

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('var')
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        'header_comment' => [
            'header' => $header,
            'location' => 'after_open',
        ],
    ])
    ->setFinder($finder)
;

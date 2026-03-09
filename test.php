<?php

require_once __DIR__.'/vendor/autoload.php';
require_once __DIR__.'/vendor/symfony/var-dumper/Resources/functions/dump.php';
$categoryMap = [
    'Coffee' => 'black',
    'Tea' => 'green',
    'Juice' => 'fruit',
    'Sodas' => 'carbonated',
];

foreach ($categoryMap as $categoryName => $products) {
    dump($products);
}

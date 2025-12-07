<?php

function handleRequest() {
    $filters = [
        'MODE' => $_GET['mode'] ?? 'view',
        'TYPE' => $_GET['type'] ?? null,
        'COUNTRY' => $_GET['country'] ?? null,
        'PRICELOW' => $_GET['priceLow'] ?? null,
        'PRICEHIGH' => $_GET['priceHigh'] ?? null,
        'SIZE' => $_GET['size'] ?? null,
        'SIZE_GROUP' => $_GET['size_group'] ?? null,
        'ENERGYLOW' => $_GET['energylow'] ?? null,
        'ENERGYHIGH' => $_GET['energyhigh'] ?? null,
        'LIMIT' => 25,
        'PAGE' => max(0, (int)($_GET['page'] ?? 0))
    ];

    return $filters;
}
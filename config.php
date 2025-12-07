<?php
// Tiedostopolut
$filename = __DIR__ . "/data/alkon-hinnasto-combined.csv";
$filename_xlxs = __DIR__ . "/data/alkon-hinnasto.xlsx";

$priceListDate = "01.12.2025"; // Fallback

// Näytettävät sarakkeet
$columns2Include = [
    "Numero",
    "Nimi",
    "Valmistaja",
    "Pullokoko",
    "Hinta",
    "Litrahinta",
    "Tyyppi",
    "Valmistusmaa",
    "Vuosikerta",
    "Alkoholi-%",
    "Energia kcal/100 ml"
];

// Sivutusasetukset
$itemsPerPage = 25;

// Suodatettavat kentät
$filterFields = [
    'Tyyppi',
    'Valmistusmaa',
    'Pullokoko',
    'Hinta',
    'Energia kcal/100 ml'
];
?>


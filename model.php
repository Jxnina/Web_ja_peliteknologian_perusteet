<?php
require_once("config.php");
require_once("view.php");
require_once("controller.php");

$columnNames = [];
$columnNamesMap = [];
$alkoData = [];

function readPriceList($filename) {
    global $priceListDate, $columnNames;
    $row = 0;
    $alkoDataIndex = 0;
    $alkoData = [];

    if (($handle = fopen($filename, "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ";", '"', "\\")) !== FALSE) {
            if (empty($data) || count($data) == 0) {
                $row++;
                continue;
            }

            if ($row === 0) {
                // Hinnasto päivämäärä
                $key = "Alkon hinnasto ";
                if ($key == substr($data[0], 0, strlen($key))) {
                    $priceListDate = trim(substr($data[0], strlen($key)));
                }
            } elseif ($row === 1 || $row === 2) {
                // Tyhjät rivit
            } elseif ($row === 3) {
                // Sarakkeiden nimet
                $columnNames = array_map('trim', $data);
                $columnNames = array_filter($columnNames, function($v) { return $v !== ''; });
                $columnNames = array_values($columnNames);
            } else {
                // Datarivit
                if (!empty($data[0])) {
                    $alkoData[$alkoDataIndex] = $data;
                    $alkoDataIndex++;
                }
            }
            $row++;
        }
        fclose($handle);
    }

    return $alkoData;
}

function combineHeadersAndData($data, $headers) {
    $result = [];
    foreach ($data as $row) {
        $combined = [];
        foreach ($headers as $index => $header) {
            $combined[$header] = $row[$index] ?? '';
        }
        $result[] = $combined;
    }
    return $result;
}

function initModel() {
    global $filename, $columnNames, $columnNamesMap, $alkoData;
    
    $rawData = readPriceList($filename);
    $alkoData = combineHeadersAndData($rawData, $columnNames);
    
    // Luo sarakkeiden kartta
    $columnNamesMap = array_flip($columnNames);
    
    return $alkoData;
}

function getUniqueValues($alkoData, $column) {
    $values = array_column($alkoData, $column);
    $values = array_unique($values);
    $values = array_filter($values, function($v) { return !empty(trim($v)); });
    sort($values);
    return array_values($values);
}

// Pullokokoryhmät
function getSizeCategories() {
    return [
        'Pienet (alle 0.5l)' => ['0.33 l', '0.35 l', '0.275 l', '0.44 l', '0.38 l', '0.25 l'],
        'Normaalit (0.5-0.75l)' => ['0.5 l', '0.7 l', '0.75 l'],
        'Suuret (1-1.5l)' => ['1 l', '1.5 l'],
        'Erittäin suuret (yli 3l)' => ['3 l', '5 l', '10 l', '20 l'],
        'Muut' => []
    ];
}

function getSizeCategory($size) {
    $categories = getSizeCategories();
    
    foreach ($categories as $categoryName => $sizes) {
        if (in_array($size, $sizes)) {
            return $categoryName;
        }
    }
    return 'Muut';
}

function getFilteredDataWithSizeGroups($alkoData, $filters, $columnNamesMap) {
    $result = [];
    
    foreach ($alkoData as $row) {
        $product = $row;

        // Tyyppisuodatin
        if (!empty($filters['TYPE']) && $product['Tyyppi'] !== $filters['TYPE']) continue;

        // Maasuodatin  
        if (!empty($filters['COUNTRY']) && $product['Valmistusmaa'] !== $filters['COUNTRY']) continue;

        // Hintasuodatin
        if (!empty($filters['PRICELOW']) && (float)$product['Hinta'] < (float)$filters['PRICELOW']) continue;
        if (!empty($filters['PRICEHIGH']) && (float)$product['Hinta'] > (float)$filters['PRICEHIGH']) continue;

        // Energiasuodatin
        if (!empty($filters['ENERGYLOW']) && (float)$product['Energia kcal/100 ml'] < (float)$filters['ENERGYLOW']) continue;
        if (!empty($filters['ENERGYHIGH']) && (float)$product['Energia kcal/100 ml'] > (float)$filters['ENERGYHIGH']) continue;

        // Pullokokoryhmän suodatin
        if (!empty($filters['SIZE_GROUP'])) {
            $productSizeCategory = getSizeCategory($product['Pullokoko']);
            if ($productSizeCategory !== $filters['SIZE_GROUP']) continue;
        }
        
        // Vanhan pullokokosuodatin
        if (!empty($filters['SIZE']) && $product['Pullokoko'] !== $filters['SIZE']) continue;

        $result[] = $row;
    }
    
    return $result;
}
?>

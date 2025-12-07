<?php

function createColumnHeaders($columns2Include) {
    $html = "<thead class='table-primary'><tr>";
    foreach ($columns2Include as $colName) {
        $html .= "<th scope='col'>" . htmlspecialchars($colName) . "</th>";
    }
    $html .= "</tr></thead>";
    return $html;
}

function createTableRow($product, $columns2Include, $columnNamesMap) {
    $html = "<tr>";
    foreach ($columns2Include as $i => $colName) {
        $value = $product[$colName] ?? '';
        
        // Jos arvo on array, muunna merkkijonoksi
        if (is_array($value)) {
            $value = implode(', ', $value);
        }
        
        $value = htmlspecialchars($value);

        if ($i === 0) {
            $html .= "<th scope='row'>$value</th>";
        } else {
            $html .= "<td>$value</td>";
        }
    }
    $html .= "</tr>";
    return $html;
}

function generateView($alkoData, $filters, $tblId = null) {
    global $columns2Include, $columnNamesMap, $itemsPerPage;

    if (empty($columns2Include)) {
        $columns2Include = ["Numero", "Nimi", "Valmistaja", "Pullokoko", "Hinta", "Litrahinta"];
    }

    $filteredProducts = getFilteredDataWithSizeGroups($alkoData, $filters, $columnNamesMap);
    
    $start = ($filters['PAGE'] ?? 0) * $itemsPerPage;
    $pagedProducts = array_slice($filteredProducts, $start, $itemsPerPage);

    $tableId = $tblId ? "id='$tblId'" : "";

    $html = "<table $tableId class='table table-hover table-striped table-bordered'>";
    $html .= createColumnHeaders($columns2Include);
    $html .= "<tbody>";

    if (empty($pagedProducts)) {
        $html .= "<tr><td colspan='" . count($columns2Include) . "' class='text-center py-4'>
                    <em>Ei tuotteita, jotka vastaavat hakuehtoja.</em>
                  </td></tr>";
    } else {
        foreach ($pagedProducts as $product) {
            $html .= createTableRow($product, $columns2Include, $columnNamesMap);
        }
    }

    $html .= "</tbody></table>";
    return $html;
}

function createSelectOptions($fieldName, $options, $currentValue = '') {
    $selectId = strtolower(str_replace(' ', '', $fieldName));
    $html = "<select class='form-select' name='{$fieldName}' id='{$selectId}'>";
    $html .= "<option value=''>Kaikki</option>";
    
    foreach ($options as $option) {
        $selected = ($currentValue === $option) ? 'selected' : '';
        $escaped = htmlspecialchars($option);
        $html .= "<option value='{$escaped}' {$selected}>{$escaped}</option>";
    }
    
    $html .= "</select>";
    return $html;
}

function createSizeGroupSelect($fieldName, $currentValue = '') {
    $sizeCategories = getSizeCategories();
    $selectId = strtolower(str_replace(' ', '', $fieldName));
    $html = "<select class='form-select' name='{$fieldName}' id='{$selectId}'>";
    $html .= "<option value=''>Kaikki koot</option>";
    
    foreach ($sizeCategories as $categoryName => $sizes) {
        $selected = ($currentValue === $categoryName) ? 'selected' : '';
        $escapedCategory = htmlspecialchars($categoryName);
        $html .= "<option value='{$escapedCategory}' {$selected}>{$escapedCategory}</option>";
    }
    
    $html .= "</select>";
    return $html;
}
?>
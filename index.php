<?php
require_once "model.php";
require_once "controller.php";
require_once "view.php";

// Lataa data ja käsittele pyyntö
$alkoData = initModel();
$filters = handleRequest();
$rowsPerPage = 25;

// Laske sivutustiedot
global $columnNamesMap;
$filteredData = getFilteredDataWithSizeGroups($alkoData, $filters, $columnNamesMap);
$visibleCount = count($filteredData);
$totalPages = ceil($visibleCount / $rowsPerPage);
$currentPage = $filters['PAGE'];

// Hae arvot suodattimia varten
$uniqueTypes = getUniqueValues($alkoData, 'Tyyppi');
$uniqueCountries = getUniqueValues($alkoData, 'Valmistusmaa');
$uniqueSizes = getUniqueValues($alkoData, 'Pullokoko');

// Koko tuotemäärä ilman suodatuksia
$allProductsWithoutFilters = getFilteredDataWithSizeGroups($alkoData, [], $columnNamesMap);
$totalProductCount = count($allProductsWithoutFilters);
?>

<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <title>Alkon hinnasto</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
</head>
<body>

<div class="container">
    <div class="main-container">

        <!-- YLÄPALKIN OTSIKKO -->
        <div class="header mb-4">
            <h1 class="display-4 mb-0">
                <i class="bi bi-shop me-3"></i>
                Alkon hinnasto <?= htmlspecialchars($priceListDate) ?>
            </h1>
        </div>

        <!-- PÄIVITYSNAPPI -->
        <div class="text-center mb-4">
            <p class="lead mb-3">
                <i class="bi bi-database me-2"></i>
                Tuotteita yhteensä: <strong><?= number_format($totalProductCount) ?></strong>
            </p>
            <a href="update.php" class="btn-custom update-btn shadow">
                <i class="bi bi-arrow-clockwise me-2"></i>
                Päivitä hinnasto Alkon sivuilta
            </a>
            <br><small class="text-muted mt-2 d-block">Päivitys kestää yleensä 8–15 sekuntia</small>
        </div>

        <!-- SUODATINLOMAKE -->
        <div class="card filter-card mb-4 hover-lift">
            <div class="card-body">
                <h5 class="card-title mb-3">
                    <i class="bi bi-funnel me-2"></i>
                    Suodattimet
                </h5>
                <form method="get" class="row g-3 align-items-end">
                    <div class="col-12 col-md-2">
                        <label class="form-label fw-bold">
                            <i class="bi bi-tag me-1"></i>
                            Tyyppi
                        </label>
                        <?= createSelectOptions('type', $uniqueTypes, $_GET['type'] ?? '') ?>
                    </div>
                    <div class="col-12 col-md-2">
                        <label class="form-label fw-bold">
                            <i class="bi bi-geo me-1"></i>
                            Maa
                        </label>
                        <?= createSelectOptions('country', $uniqueCountries, $_GET['country'] ?? '') ?>
                    </div>
                    <div class="col-12 col-md-2">
                        <label class="form-label fw-bold">
                            <i class="bi bi-cup-straw me-1"></i>
                            Pullokoko
                        </label>
                        <?= createSizeGroupSelect('size_group', $_GET['size_group'] ?? '') ?>
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label fw-bold">
                            <i class="bi bi-currency-euro me-1"></i>
                            Hinta €
                        </label>
                        <div class="input-group">
                            <input type="number" step="0.01" name="priceLow" class="form-control" placeholder="min" value="<?=$_GET['priceLow']??''?>">
                            <span class="input-group-text">–</span>
                            <input type="number" step="0.01" name="priceHigh" class="form-control" placeholder="max" value="<?=$_GET['priceHigh']??''?>">
                        </div>
                    </div>
                    <div class="col-12 col-md-2">
                        <label class="form-label fw-bold">
                            <i class="bi bi-lightning me-1"></i>
                            Energia kcal/100ml
                        </label>
                        <div class="input-group">
                            <input type="number" name="energylow" class="form-control" placeholder="min" value="<?=$_GET['energylow']??''?>">
                            <span class="input-group-text">–</span>
                            <input type="number" name="energyhigh" class="form-control" placeholder="max" value="<?=$_GET['energyhigh']??''?>">
                        </div>
                    </div>
                    <div class="col-12 col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn-custom w-100">
                            <i class="bi bi-search me-1"></i>
                            <span>Hae</span>
                        </button>
                    </div>
                </form>

                <?php if (!empty(array_filter($_GET))): ?>
                    <div class="mt-3 text-end">
                        <a href="index.php" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-x-circle me-1"></i>
                            Tyhjennä suodattimet
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- TULOSTEN MÄÄRÄ -->
        <div class="alert alert-info text-center mb-4">
            <?php if ($visibleCount == $totalProductCount): ?>
                <i class="bi bi-check-circle me-2"></i>
                Kaikki <strong><?= number_format($visibleCount) ?></strong> tuotetta näkyvissä
            <?php else: ?>
                <i class="bi bi-funnel me-2"></i>
                Näytetään <strong><?= number_format($visibleCount) ?></strong> tuotetta suodatuksen jälkeen (yhteensä <?= number_format($totalProductCount) ?>)
            <?php endif; ?>
        </div>    <!-- TUOTETAULUKKO -->
    <?= generateView($alkoData, $filters, 'products') ?>

        <!-- SIVUTUS (25 riviä/sivu) -->
        <?php if ($totalPages > 1): ?>
        <nav aria-label="Sivutus" class="mt-5">
            <!-- Sivutiedot -->
            <div class="text-center mb-3">
                <small class="text-muted">
                    Sivu <strong><?= $currentPage + 1 ?></strong> / <strong><?= $totalPages ?></strong>
                    (<?= number_format($rowsPerPage) ?> tuotetta/sivu)
                </small>
            </div>
            
            <ul class="pagination justify-content-center">
                <!-- ENSIMMÄINEN sivu -->
                <li class="page-item <?= $currentPage == 0 ? 'disabled' : '' ?>">
                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => 0])) ?>" 
                       title="Siirry ensimmäiselle sivulle">
                        <i class="bi bi-chevron-double-left"></i>
                        <span class="d-none d-sm-inline ms-1">Ensimmäinen</span>
                    </a>
                </li>
                
                <!-- EDELLINEN sivu -->
                <li class="page-item <?= $currentPage == 0 ? 'disabled' : '' ?>">
                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => max(0, $currentPage-1)])) ?>">
                        <i class="bi bi-arrow-left me-1"></i>
                        Edellinen
                    </a>
                </li>

            <?php
            // Näytä max 10 sivunumeroa kerralla
            $maxVisible = 10;
            if ($totalPages <= $maxVisible) {
                // Jos sivuja vähän, näytä kaikki
                $startPage = 0;
                $endPage = $totalPages;
            } else {
                // Jos sivuja paljon, näytä nykyisen ympärillä olevat
                $startPage = max(0, $currentPage - intval($maxVisible / 2));
                $endPage = min($totalPages, $startPage + $maxVisible);
                
                // Varmista että näytetään aina 10 sivua jos mahdollista
                if ($endPage - $startPage < $maxVisible && $startPage > 0) {
                    $startPage = max(0, $endPage - $maxVisible);
                }
            }
            
            // Näytä "..." jos ensimmäisiä sivuja ei näytetä
            if ($startPage > 0): ?>
                <li class="page-item disabled">
                    <span class="page-link">...</span>
                </li>
            <?php endif;
            
            // Sivunumerot
            for ($i = $startPage; $i < $endPage; $i++): ?>
                <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i + 1 ?></a>
                </li>
            <?php endfor;
            
            // Näytä "..." jos viimeisiä sivuja ei näytetä
            if ($endPage < $totalPages): ?>
                <li class="page-item disabled">
                    <span class="page-link">...</span>
                </li>
            <?php endif; ?>

            <!-- SEURAAVA sivu -->
            <li class="page-item <?= $currentPage >= $totalPages - 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $currentPage + 1])) ?>">
                    Seuraava
                    <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </li>
            
            <!-- VIIMEINEN sivu -->
            <li class="page-item <?= $currentPage >= $totalPages - 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $totalPages - 1])) ?>" 
                   title="Siirry viimeiselle sivulle">
                    <span class="d-none d-sm-inline me-1">Viimeinen</span>
                    <i class="bi bi-chevron-double-right"></i>
                </a>
            </li>
        </ul>
    </nav>
    <?php endif; ?>

    </div> <!-- päättävä main-container div -->
</div> <!-- päättävä container div -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
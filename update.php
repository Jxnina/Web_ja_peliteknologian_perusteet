<?php
declare(strict_types=1);
set_time_limit(120);

if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    die(
        '<h2 style="color:red">VIRHE: vendor/autoload.php puuttuu!</h2>'
        . '<p>Aja terminaali ja suorita seuraavat komennot asentaaksesi riippuvuudet:</p>'
        . '<pre style="background:#000;color:#0f0;padding:15px;">' . PHP_EOL
        . 'cd "' . __DIR__ . '"' . PHP_EOL
        . 'composer require shuchkin/simplexlsx' . PHP_EOL
        . '</pre>'
    );
}
require_once __DIR__ . '/vendor/autoload.php';
use Shuchkin\SimpleXLSX;

$remote_url = 'https://www.alko.fi/INTERSHOP/static/WFS/Alko-OnlineShop-Site/-/Alko-OnlineShop/fi_FI/Alkon%20Hinnasto%20Tekstitiedostona/alkon-hinnasto-tekstitiedostona.xlsx';
$xlsx_file = __DIR__ . '/data/alkon-hinnasto.xlsx';
$csv_file = __DIR__ . '/data/alkon-hinnasto-ascii.csv';
$combined_file = __DIR__ . '/data/alkon-hinnasto-combined.csv';
?>

<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Alko Hinnasto - Automaattipäivitys</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-8">
                <div class="main-container">
                    <!-- Otsikko -->
                    <div class="header text-center mb-5">
                        <h1 class="display-5 fw-bold mb-3">
                            <i class="bi bi-arrow-clockwise me-3"></i>
                            Alkon Hinnasto
                        </h1>
                        <h2 class="h3 mb-3" style="color: var(--white);">Automaattipäivitys käynnissä</h2>
                        <p class="lead" style="color: var(--white); opacity: 0.9;">Ladataan tuoreinta hinnastodataa Alko Oy:n virallisista lähteistä</p>
                    </div>

                    <!-- Edistymispalkki -->
                    <div class="progress-custom mb-4">
                        <div class="progress-bar-custom" id="progressBar" style="width: 0%"></div>
                    </div>

                    <!-- Päivitysvaiheet -->
                    <div class="update-container">
                        <div id="steps"></div>
                    </div>

<script>
// JavaScript päivitysfunktiot
let currentStep = 0;
const steps = [];

function updateProgress(percent) {
    document.getElementById('progressBar').style.width = percent + '%';
}

function addStep(title, icon = 'bi-info-circle', status = 'waiting') {
    const stepDiv = document.createElement('div');
    stepDiv.className = 'step';
    stepDiv.innerHTML = `
        <div class='d-flex align-items-center'>
            <i class='bi ${icon} me-3 ${status === 'active' ? 'info-icon' : status === 'completed' ? 'success-icon' : status === 'error' ? 'error-icon' : 'text-muted'}'></i>
            <div class='flex-grow-1'>
                <h5 class='mb-1'>${title}</h5>
                <div class='step-content'></div>
            </div>
            ${status === 'active' ? '<div class="spinner-custom"></div>' : ''}
        </div>
    `;
    document.getElementById('steps').appendChild(stepDiv);
    steps.push(stepDiv);
    return stepDiv;
}

function updateStep(stepIndex, content, status = 'active') {
    if (steps[stepIndex]) {
        const stepDiv = steps[stepIndex];
        stepDiv.className = 'step ' + status;
        const contentDiv = stepDiv.querySelector('.step-content');
        if (contentDiv) contentDiv.innerHTML = content;
        
        // Päivitä ikoni
        const icon = stepDiv.querySelector('i');
        if (icon) {
            icon.className = 'bi me-3 ' + 
                (status === 'active' ? 'bi-arrow-clockwise info-icon' : 
                 status === 'completed' ? 'bi-check-circle success-icon' : 
                 status === 'error' ? 'bi-x-circle error-icon' : 
                 'bi-info-circle text-muted');
        }
        
        // Päivitä spinner
        const spinner = stepDiv.querySelector('.spinner-custom');
        if (spinner && status !== 'active') {
            spinner.remove();
        }
    }
}

function showFinalStats(productCount, dateStr, fileSize) {
    const statsHtml = `
        <div class='row mt-5'>
            <div class='col-md-4'>
                <div class='stats-card'>
                    <div class='stats-number'>${productCount}</div>
                    <div class='text-muted'>Tuotetta</div>
                </div>
            </div>
            <div class='col-md-4'>
                <div class='stats-card'>
                    <div class='stats-number'>${Math.round(fileSize/1024)}k</div>
                    <div class='text-muted'>Datatiedosto</div>
                </div>
            </div>
            <div class='col-md-4'>
                <div class='stats-card'>
                    <div class='stats-number'>${dateStr}</div>
                    <div class='text-muted'>Päivämäärä</div>
                </div>
            </div>
        </div>
        <div class='text-center mt-4'>
            <a href='index.php' class='btn-custom'>
                <i class='bi bi-house me-2'></i>Siirry hinnastoon
            </a>
        </div>
    `;
    document.getElementById('steps').insertAdjacentHTML('afterend', statsHtml);
}
</script>

<?php

// KÄYNNISTETÄÄN PÄIVITYS
$dataDir = __DIR__ . '/data';

// Tarkista ja luo data-kansio paremmilla oikeuksilla
if (!is_dir($dataDir)) {
    if (!mkdir($dataDir, 0777, true)) {
        echo "<script>
        addStep('Virhe järjestelmässä', 'bi-exclamation-triangle');
        updateStep(0, 'VIRHE: Data-kansiota ei voitu luoda. Tarkista palvelimen kirjoitusoikeudet.', 'error');
        </script>";
        exit;
    }
    chmod($dataDir, 0777); // Varmista kirjoitusoikeudet
}

// Testaa kirjoitusoikeudet
if (!is_writable($dataDir)) {
    $perms = substr(sprintf('%o', fileperms($dataDir)), -4);
    $owner = function_exists('posix_getpwuid') ? posix_getpwuid(fileowner($dataDir))['name'] ?? 'tuntematon' : 'tuntematon';
    echo "<script>
    addStep('Virhe järjestelmässä', 'bi-exclamation-triangle'); 
    updateStep(0, 'VIRHE: Data-kansio ei ole kirjoitettavissa.<br>Oikeudet: $perms | Omistaja: $owner | PHP-käyttäjä: " . get_current_user() . "', 'error');
    </script>";
    exit;
}

echo "<script>console.log('Data-kansio OK: $dataDir, oikeudet: " . substr(sprintf('%o', fileperms($dataDir)), -4) . "');</script>";
echo str_pad('', 4096) . "\n";
flush();

$startTime = microtime(true);

echo "<script>
addStep('Ladataan Excel-tiedosto Alkon palvelimelta', 'bi-download');
updateStep(0, 'Yhdistetään osoitteeseen: alko.fi...', 'active');
updateProgress(25);
</script>";
echo str_pad('', 4096) . "\n"; // Pakota selain näyttämään sisältö
flush();
sleep(3);

// LATAA EXCEL
$context = stream_context_create([
    'http' => [
        'timeout' => 30,
        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
    ]
]);

echo "<script>console.log('Yritetään ladata: $remote_url');</script>";
echo str_pad('', 4096) . "\n"; // Pakota selain näyttämään sisältö
flush();

$excelData = @file_get_contents($remote_url, false, $context);

if ($excelData === false) {
    $error = error_get_last();
    echo "<script>
    console.log('Latausvirhe: " . ($error['message'] ?? 'Tuntematon virhe') . "');
    updateStep(0, 'VIRHE: Ei yhteyttä Alkon palvelimelle! Tarkista internet-yhteys.', 'error');
    </script>";
    
    echo "<div class='alert alert-warning mt-3'>
        <h5><i class='bi bi-exclamation-triangle me-2'></i>Päivitys epäonnistui</h5>
        <p>Alkon palvelimeen ei saatu yhteyttä. Tämä voi johtua:</p>
        <ul>
            <li>Internet-yhteyden ongelmista</li>
            <li>Alkon palvelimen huoltokatkoista</li>
            <li>Palomuurin estämistä yhteyksistä</li>
        </ul>
        <p class='mb-0'>
            <strong>Ratkaisu:</strong> 
            <a href='index.php' class='btn btn-sm btn-primary ms-2'>
                <i class='bi bi-house me-1'></i>Käytä olemassa olevaa dataa
            </a>
        </p>
    </div>";
    exit;
}

if (empty($excelData)) {
    echo "<script>
    updateStep(0, 'VIRHE: Tyhjä vastaus Alkon palvelimelta!', 'error');
    </script>";
    exit;
}

file_put_contents($xlsx_file, $excelData);
$fileSize = strlen($excelData);

echo "<script>
console.log('Excel tallennettu, koko: ' + " . $fileSize . ");
updateStep(0, 'Excel ladattu onnistuneesti (' + Math.round(" . $fileSize . "/1024) + ' KB)', 'completed');
addStep('Käsitellään hinnaston tietoja', 'bi-file-earmark-spreadsheet');
updateStep(1, 'Puretaan Excel-dataa ja tunnistetaan päivämäärä...', 'active');
updateProgress(50);
console.log('Siirrytään Excel-parsintaan...');
</script>";
echo str_pad('', 4096) . "\n"; // Pakota selain näyttämään sisältö
flush();
sleep(3);

if ($xlsx = SimpleXLSX::parse($xlsx_file)) {
    echo "<script>console.log('Excel parsinta onnistui');</script>";
    $rows = $xlsx->rows();
    $rowCount = count($rows);
    echo "<script>console.log('Rivejä löytyi: $rowCount');</script>";
    preg_match('/Alkon hinnasto\s+(.+)/u', $rows[0][0] ?? '', $m);
    $newDate = trim($m[1] ?? date('d.m.Y'));

    echo "<script>
    updateStep(1, 'Hinnasto päivätty: $newDate ($rowCount riviä)', 'completed');
    addStep('Tallennetaan CSV-tiedostoja', 'bi-file-earmark-text');
    updateStep(2, 'Kirjoitetaan tuotetietoja...', 'active');
    updateProgress(75);
    </script>";
    echo str_pad('', 4096) . "\n"; // Pakota selain näyttämään sisältö
    flush();
    sleep(3);

    // Tallenna ASCII CSV
    $fp = fopen($csv_file, 'w');
    if (!$fp) {
        echo "<script>
        updateStep(2, 'VIRHE: CSV-tiedostoa ei voitu avata kirjoitettavaksi! Tarkista ' + '$csv_file' + ' oikeudet.', 'error');
        </script>";
        exit;
    }
    
    $count = 0;
    for ($i = 4; $i < count($rows); $i++) {
        if (!empty($rows[$i][0] ?? '')) {
            if (fputcsv($fp, $rows[$i], ';', '"', "\\") === false) {
                fclose($fp);
                echo "<script>
                updateStep(2, 'VIRHE: CSV-rivin kirjoitus epäonnistui riviltä $i!', 'error');
                </script>";
                exit;
            }
            $count++;
        }
    }
    fclose($fp);
    
    // Tarkista että tiedosto todella luotiin
    if (!file_exists($csv_file) || filesize($csv_file) == 0) {
        echo "<script>
        updateStep(2, 'VIRHE: CSV-tiedosto on tyhjä tai puuttuu kirjoituksen jälkeen!', 'error');
        </script>";
        exit;
    }

    // Luo yhdistetty tiedosto
    $headerCsv = __DIR__ . '/data/alkon-hinnasto.csv';
    if (file_exists($headerCsv)) {
        $headerLines = array_slice(file($headerCsv), 0, 4);
        if (file_put_contents($combined_file, implode('', $headerLines)) === false) {
            echo "<script>updateStep(2, 'VIRHE: Yhdistetyn tiedoston header-kirjoitus epäonnistui!', 'error');</script>";
            exit;
        }
        if (file_put_contents($combined_file, file_get_contents($csv_file), FILE_APPEND) === false) {
            echo "<script>updateStep(2, 'VIRHE: Yhdistetyn tiedoston data-kirjoitus epäonnistui!', 'error');</script>";
            exit;
        }
    } else {
        if (!copy($csv_file, $combined_file)) {
            echo "<script>updateStep(2, 'VIRHE: CSV-tiedoston kopiointi epäonnistui!', 'error');</script>";
            exit;
        }
    }

    echo "<script>
    updateStep(2, 'CSV-tiedostot tallennettu ($count tuotetta)', 'completed');
    addStep('Päivitys valmis!', 'bi-check-circle');
    updateStep(3, 'Hinnasto päivitetty onnistuneesti', 'completed');
    updateProgress(100);
    showFinalStats('$count', '$newDate', '$fileSize');
    </script>";

} else {
    echo "<script>
    updateStep(1, 'VIRHE: " . SimpleXLSX::parseError() . "', 'error');
    </script>";
}

$endTime = microtime(true);
$duration = round($endTime - $startTime, 1);

echo "<script>
setTimeout(function() {
    document.querySelector('.update-container').insertAdjacentHTML('beforeend', 
    '<div class=\"alert alert-success mt-4\"><i class=\"bi bi-stopwatch me-2 success-icon\"></i>Päivitys valmistui {$duration} sekunnissa</div>');
}, 2000);
</script>";
?>

                    </div> <!-- sulkeva update-container div -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

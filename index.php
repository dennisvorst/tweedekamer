<?php
declare(strict_types=1);

require_once __DIR__ . '/app/config/Database.php';
require_once __DIR__ . '/app/api/PersonApi.php';
require_once __DIR__ . '/app/api/ZaalApi.php';
require_once __DIR__ . '/app/models/ListStateModel.php';
require_once __DIR__ . '/app/models/PersonModel.php';
require_once __DIR__ . '/app/models/ZaalModel.php';
require_once __DIR__ . '/app/api/FractieApi.php';
require_once __DIR__ . '/app/models/FractieModel.php';
require_once __DIR__ . '/app/api/ActiviteitApi.php';
require_once __DIR__ . '/app/models/ActiviteitModel.php';
require_once __DIR__ . '/app/api/ZaakApi.php';
require_once __DIR__ . '/app/models/ZaakModel.php';

use App\Config\Database;
use App\Models\ListStateModel;

use App\Api\ActiviteitApi;
use App\Models\ActiviteitModel;
use App\Api\FractieApi;
use App\Models\FractieModel;
use App\Api\PersonApi;
use App\Models\PersonModel;
use App\Api\ZaakApi;
use App\Models\ZaakModel;
use App\Api\ZaalApi;
use App\Models\ZaalModel;


/*
 * Determine active tab
 */
$activeTab = $_GET['tab'] ?? 'activiteit';

if (!in_array($activeTab, ['activiteit', 'fractie', 'person', 'zaal', 'zaak'], true)) {
    $activeTab = 'activiteit';
}

/*
 * Create shared database connection
 */

$pdo = Database::createConnection();

$activiteitModel = new ActiviteitModel($pdo);
$fractieModel = new FractieModel($pdo);
$personModel = new PersonModel($pdo);
$zaalModel   = new ZaalModel($pdo);
$zaakModel   = new ZaakModel($pdo);

$activiteitApi   = new ActiviteitApi($activiteitModel);
$fractieApi   = new FractieApi($fractieModel);
$personApi = new PersonApi($personModel);
$zaalApi   = new ZaalApi($zaalModel);
$zaakApi   = new ZaakApi($zaakModel);

$activiteitStateManager = new ListStateModel('activiteit');
$fractieStateManager = new ListStateModel('fractie');
$personStateManager = new ListStateModel('person');
$zaalStateManager   = new ListStateModel('zaal');
$zaakStateManager   = new ListStateModel('zaak');

/** defaults */
$activiteitDefaults = [
    'sort' => 'datum',
    'direction' => 'desc',
    'page' => 1,
    'filters' => [
        'soort' => '',
        'nummer' => '',
        'onderwerp' => '',
        'datum' => '',
        'locatie' => '',
    ],
];

$fractieDefaults = [
    'sort' => 'naam_nl',
    'direction' => 'asc',
    'page' => 1,
    'filters' => [
        'nummer' => '',
        'afkorting' => '',
        'naam_nl' => '',
        'naam_en' => '',
        'aantal_zetels' => '',
        'aantal_stemmen' => '',
        'datum_actief' => '',
        'datum_inactief' => '',
    ],
];

$personDefaults = [
    'sort' => 'achternaam',
    'direction' => 'asc',
    'page' => 1,
    'filters' => [
        'nummer' => '',
        'roepnaam' => '',
        'achternaam' => '',
        'geboortedatum' => '',
        'geslacht' => '',
    ],
];

$zaakDefaults = [
    'sort' => 'gestart_op',
    'direction' => 'desc',
    'page' => 1,
    'filters' => [
        'nummer' => '',
        'soort' => '',
        'titel' => '',
        'status' => '',
        'onderwerp' => '',
        'gestart_op' => '',
        'organisatie' => '',
        'vergaderjaar' => '',
        'afgedaan' => '',
        'groot_project' => '',
    ],
];

$zaalDefaults = [
    'sort' => 'naam',
    'direction' => 'asc',
    'page' => 1,
    'filters' => [
        'id' => '',
        'naam' => '',
        'syscode' => '',
    ],
];

/** filters */
$activiteitAllowedFilters = [
    'soort',
    'nummer',
    'onderwerp',
    'datum',
    'locatie',
];
$fractieAllowedFilters = [
    'nummer',
    'afkorting',
    'naam_nl',
    'naam_en',
    'aantal_zetels',
    'aantal_stemmen',
    'datum_actief',
    'datum_inactief',
];
$personAllowedFilters = ['nummer', 'roepnaam', 'achternaam', 'geboortedatum', 'geslacht'];
$zaakAllowedFilters = [
    'nummer',
    'soort',
    'titel',
    'status',
    'onderwerp',
    'gestart_op',
    'organisatie',
    'vergaderjaar',
    'afgedaan',
    'groot_project',
];
$zaalAllowedFilters   = ['id', 'naam', 'syscode'];

// if ($activeTab === 'person') {
//     if (isset($_GET['reset']) && (string)$_GET['reset'] === '1') {
//         $personState = $personStateManager->reset($personDefaults);
//     } else {
//         $personState = $personStateManager->applyRequest($_GET, $personDefaults, $personAllowedFilters);
//     }

//     $zaalState = $zaalStateManager->getState($zaalDefaults);
// } else {
//     if (isset($_GET['reset']) && (string)$_GET['reset'] === '1') {
//         $zaalState = $zaalStateManager->reset($zaalDefaults);
//     } else {
//         $zaalState = $zaalStateManager->applyRequest($_GET, $zaalDefaults, $zaalAllowedFilters);
//     }

//     $personState = $personStateManager->getState($personDefaults);
// }

if ($activeTab === 'activiteit') {
    if (isset($_GET['reset']) && (string)$_GET['reset'] === '1') {
        $activiteitState = $activiteitStateManager->reset($activiteitDefaults);
    } else {
        $activiteitState = $activiteitStateManager->applyRequest($_GET, $activiteitDefaults, $activiteitAllowedFilters);
    }

    $fractieState = $fractieStateManager->getState($fractieDefaults);
    $personState = $personStateManager->getState($personDefaults);
    $zaakState = $zaakStateManager->getState($zaakDefaults);
    $zaalState = $zaalStateManager->getState($zaalDefaults);
    
}  elseif ($activeTab === 'fractie') {
    if (isset($_GET['reset']) && (string)$_GET['reset'] === '1') {
        $fractieState = $fractieStateManager->reset($fractieDefaults);
    } else {
        $fractieState = $fractieStateManager->applyRequest($_GET, $fractieDefaults, $fractieAllowedFilters);
    }

    $activiteitState = $activiteitStateManager->getState($activiteitDefaults);
    $personState = $personStateManager->getState($personDefaults);
    $zaakState = $zaakStateManager->getState($zaakDefaults);
    $zaalState = $zaalStateManager->getState($zaalDefaults);
} elseif ($activeTab === 'person') {
    if (isset($_GET['reset']) && (string)$_GET['reset'] === '1') {
        $personState = $personStateManager->reset($personDefaults);
    } else {
        $personState = $personStateManager->applyRequest($_GET, $personDefaults, $personAllowedFilters);
    }

    $activiteitState = $activiteitStateManager->getState($activiteitDefaults);
    $fractieState = $fractieStateManager->getState($fractieDefaults);
    $zaakState = $zaakStateManager->getState($zaakDefaults);
    $zaalState = $zaalStateManager->getState($zaalDefaults);
} elseif ($activeTab === 'zaak') {
    if (isset($_GET['reset']) && (string)$_GET['reset'] === '1') {
        $zaakState = $zaakStateManager->reset($zaakDefaults);
    } else {
        $zaakState = $zaakStateManager->applyRequest($_GET, $zaakDefaults, $zaakAllowedFilters);
    }

    $personState = $personStateManager->getState($personDefaults);
    $zaalState = $zaalStateManager->getState($zaalDefaults);
    $fractieState = $fractieStateManager->getState($fractieDefaults);
    $activiteitState = $activiteitStateManager->getState($activiteitDefaults);    
} elseif ($activeTab === 'zaal') {
    if (isset($_GET['reset']) && (string)$_GET['reset'] === '1') {
        $zaalState = $zaalStateManager->reset($zaalDefaults);
    } else {
        $zaalState = $zaalStateManager->applyRequest($_GET, $zaalDefaults, $zaalAllowedFilters);
    }

    $activiteitState = $activiteitStateManager->getState($activiteitDefaults);
    $fractieState = $fractieStateManager->getState($fractieDefaults);
    $personState = $personStateManager->getState($personDefaults);
    $zaakState = $zaakStateManager->getState($zaakDefaults);
}

$perPage = 50;

/**
 * Person data
 */
$personSort       = (string)$personState['sort'];
$personDirection  = (string)$personState['direction'];
$personPage       = max(1, (int)$personState['page']);
$personFilters    = $personState['filters'];

$personResult = $personApi->getPersons(
    $personFilters,
    $personSort,
    $personDirection,
    $personPage,
    $perPage
);

$persons = $personResult['data'];
$personTotal = $personResult['total'];
$personTotalPages = (int)ceil($personTotal / $perPage);
$personCurrentPage = min($personPage, max(1, $personTotalPages > 0 ? $personTotalPages : 1));

if ($personCurrentPage !== $personPage) {
    $personState['page'] = $personCurrentPage;
    $personStateManager->saveState($personState);

    $personResult = $personApi->getPersons(
        $personFilters,
        $personSort,
        $personDirection,
        $personCurrentPage,
        $perPage
    );

    $persons = $personResult['data'];
}

/**
 * Activiteit data
 */
$activiteitSort = (string)$activiteitState['sort'];
$activiteitDirection = (string)$activiteitState['direction'];
$activiteitPage = max(1, (int)$activiteitState['page']);
$activiteitFilters = $activiteitState['filters'];

$activiteitResult = $activiteitApi->getActiviteiten(
    $activiteitFilters,
    $activiteitSort,
    $activiteitDirection,
    $activiteitPage,
    $perPage
);

$activiteiten = $activiteitResult['data'];
$activiteitTotal = $activiteitResult['total'];
$activiteitTotalPages = (int)ceil($activiteitTotal / $perPage);
$activiteitCurrentPage = min($activiteitPage, max(1, $activiteitTotalPages > 0 ? $activiteitTotalPages : 1));
/** get the dropdown values */
$activiteitSoorten = $activiteitApi->getActiviteitSoorten();

if ($activiteitCurrentPage !== $activiteitPage) {
    $activiteitState['page'] = $activiteitCurrentPage;
    $activiteitStateManager->saveState($activiteitState);

    $activiteitResult = $activiteitApi->getActiviteiten(
        $activiteitFilters,
        $activiteitSort,
        $activiteitDirection,
        $activiteitCurrentPage,
        $perPage
    );

    $activiteiten = $activiteitResult['data'];
}

/**
 * Fractie data
 */
$fractieSort = (string)$fractieState['sort'];
$fractieDirection = (string)$fractieState['direction'];
$fractiePage = max(1, (int)$fractieState['page']);
$fractieFilters = $fractieState['filters'];

$fractieResult = $fractieApi->getFracties(
    $fractieFilters,
    $fractieSort,
    $fractieDirection,
    $fractiePage,
    $perPage
);

$fracties = $fractieResult['data'];
$fractieTotal = $fractieResult['total'];
$fractieTotalPages = (int)ceil($fractieTotal / $perPage);
$fractieCurrentPage = min($fractiePage, max(1, $fractieTotalPages > 0 ? $fractieTotalPages : 1));

if ($fractieCurrentPage !== $fractiePage) {
    $fractieState['page'] = $fractieCurrentPage;
    $fractieStateManager->saveState($fractieState);

    $fractieResult = $fractieApi->getFracties(
        $fractieFilters,
        $fractieSort,
        $fractieDirection,
        $fractieCurrentPage,
        $perPage
    );

    $fracties = $fractieResult['data'];
}

/**
 * zaak data
 */
$zaakSort = (string)$zaakState['sort'];
$zaakDirection = (string)$zaakState['direction'];
$zaakPage = max(1, (int)$zaakState['page']);
$zaakFilters = $zaakState['filters'];

$zaakResult = $zaakApi->getZaken(
    $zaakFilters,
    $zaakSort,
    $zaakDirection,
    $zaakPage,
    $perPage
);

$zaken = $zaakResult['data'];
$zaakTotal = $zaakResult['total'];
$zaakTotalPages = (int)ceil($zaakTotal / $perPage);
$zaakCurrentPage = min($zaakPage, max(1, $zaakTotalPages > 0 ? $zaakTotalPages : 1));
/** get the dropdown values */
$zaakSoorten = $zaakApi->getZaakSoorten();

if ($zaakCurrentPage !== $zaakPage) {
    $zaakState['page'] = $zaakCurrentPage;
    $zaakStateManager->saveState($zaakState);

    $zaakResult = $zaakApi->getZaken(
        $zaakFilters,
        $zaakSort,
        $zaakDirection,
        $zaakCurrentPage,
        $perPage
    );

    $zaken = $zaakResult['data'];
}

/**
 * Zaal data
 */
$zaalSort       = (string)$zaalState['sort'];
$zaalDirection  = (string)$zaalState['direction'];
$zaalPage       = max(1, (int)$zaalState['page']);
$zaalFilters    = $zaalState['filters'];

$zaalResult = $zaalApi->getZalen(
    $zaalFilters,
    $zaalSort,
    $zaalDirection,
    $zaalPage,
    $perPage
);

$zalen = $zaalResult['data'];
$zaalTotal = $zaalResult['total'];
$zaalTotalPages = (int)ceil($zaalTotal / $perPage);
$zaalCurrentPage = min($zaalPage, max(1, $zaalTotalPages > 0 ? $zaalTotalPages : 1));

if ($zaalCurrentPage !== $zaalPage) {
    $zaalState['page'] = $zaalCurrentPage;
    $zaalStateManager->saveState($zaalState);

    $zaalResult = $zaalApi->getZalen(
        $zaalFilters,
        $zaalSort,
        $zaalDirection,
        $zaalCurrentPage,
        $perPage
    );

    $zalen = $zaalResult['data'];
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Persons / Zalen</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
    <link
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        rel="stylesheet"
    >
</head>
<body class="bg-light">
<div class="container py-4">
    <ul class="nav nav-tabs mb-3" id="mainTab" role="tablist">
        <li class="nav-item" role="presentation">
            <a
                class="nav-link <?= $activeTab === 'activiteit' ? 'active' : '' ?>"
                href="index.php?tab=activiteit"
            >
                Activiteit
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a
                class="nav-link <?= $activeTab === 'person' ? 'active' : '' ?>"
                href="index.php?tab=person"
            >
                Persoon
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a
                class="nav-link <?= $activeTab === 'fractie' ? 'active' : '' ?>"
                href="index.php?tab=fractie"
            >
                Fractie
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a
                class="nav-link <?= $activeTab === 'zaal' ? 'active' : '' ?>"
                href="index.php?tab=zaal"
            >
                Zaal
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a
                class="nav-link <?= $activeTab === 'zaak' ? 'active' : '' ?>"
                href="index.php?tab=zaak"
            >
                Zaak
            </a>
        </li>
    </ul>

    <?php if ($activeTab === 'activiteit'): ?>
        <?php
        $total = $activiteitTotal;
        $totalPages = $activiteitTotalPages;
        $currentPage = $activiteitCurrentPage;
        $sort = $activiteitSort;
        $direction = $activiteitDirection;
        $filters = $activiteitFilters;

        include __DIR__ . '/app/views/activiteit/listview.php';
        ?>
    <?php elseif ($activeTab === 'fractie'): ?>
        <?php
        $total = $fractieTotal;
        $totalPages = $fractieTotalPages;
        $currentPage = $fractieCurrentPage;
        $sort = $fractieSort;
        $direction = $fractieDirection;
        $filters = $fractieFilters;

        include __DIR__ . '/app/views/fractie/listview.php';
        ?>
    <?php elseif ($activeTab === 'person'): ?>
        <?php
        $total = $personTotal;
        $totalPages = $personTotalPages;
        $currentPage = $personCurrentPage;
        $sort = $personSort;
        $direction = $personDirection;
        $filters = $personFilters;

        include __DIR__ . '/app/views/persoon/listview.php';
        ?>
    <?php elseif ($activeTab === 'zaal'): ?>
        <?php
        $total = $zaalTotal;
        $totalPages = $zaalTotalPages;
        $currentPage = $zaalCurrentPage;
        $sort = $zaalSort;
        $direction = $zaalDirection;
        $filters = $zaalFilters;

        include __DIR__ . '/app/views/zaal/listview.php';
        ?>
    <?php elseif ($activeTab === 'zaak'): ?>
        <?php
        $total = $zaakTotal;
        $totalPages = $zaakTotalPages;
        $currentPage = $zaakCurrentPage;
        $sort = $zaakSort;
        $direction = $zaakDirection;
        $filters = $zaakFilters;

        include __DIR__ . '/app/views/zaak/listview.php';
        ?>        
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
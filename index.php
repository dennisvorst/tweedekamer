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
require_once __DIR__ . '/app/api/StatsApi.php';
require_once __DIR__ . '/app/models/StatsModel.php';

use App\Api\ActiviteitApi;
use App\Api\FractieApi;
use App\Api\PersonApi;
use App\Api\StatsApi;
use App\Api\ZaakApi;
use App\Api\ZaalApi;
use App\Config\Database;
use App\Models\ActiviteitModel;
use App\Models\FractieModel;
use App\Models\ListStateModel;
use App\Models\PersonModel;
use App\Models\StatsModel;
use App\Models\ZaakModel;
use App\Models\ZaalModel;

/**
 * Loads list data in a generic way and keeps stored page state in range.
 */
function loadTabDataset(array $tab, int $perPage): array
{
    $stateData = $tab['stateData'];
    $sort = (string)($stateData['sort'] ?? $tab['defaults']['sort']);
    $direction = (string)($stateData['direction'] ?? $tab['defaults']['direction']);
    $page = max(1, (int)($stateData['page'] ?? 1));
    $filters = is_array($stateData['filters'] ?? null) ? $stateData['filters'] : [];

    $fetch = $tab['fetch'];
    $result = $fetch($tab['api'], $filters, $sort, $direction, $page, $perPage);

    $records = is_array($result['data'] ?? null) ? $result['data'] : [];
    $total = (int)($result['total'] ?? 0);
    $totalPages = (int)ceil($total / $perPage);
    $currentPage = min($page, max(1, $totalPages > 0 ? $totalPages : 1));

    if ($currentPage !== $page) {
        $stateData['page'] = $currentPage;
        $tab['stateManager']->saveState($stateData);

        $result = $fetch($tab['api'], $filters, $sort, $direction, $currentPage, $perPage);
        $records = is_array($result['data'] ?? null) ? $result['data'] : [];
    }

    $extras = [];
    if (isset($tab['loadExtras'])) {
        $loadExtras = $tab['loadExtras'];
        $extras = $loadExtras($tab['api']);
    }

    return [
        'sort' => $sort,
        'direction' => $direction,
        'page' => $page,
        'currentPage' => $currentPage,
        'filters' => $filters,
        'records' => $records,
        'total' => $total,
        'totalPages' => $totalPages,
        'extras' => $extras,
    ];
}

$pdo = Database::createConnection();

$tabs = [
    'activiteit' => [
        'label' => 'Activiteit',
        'defaults' => ActiviteitModel::getListDefaults(),
        'allowedFilters' => ActiviteitModel::getAllowedFilters(),
        'stateManager' => new ListStateModel('activiteit'),
        'api' => new ActiviteitApi(new ActiviteitModel($pdo)),
        'fetch' => static fn(ActiviteitApi $api, array $filters, string $sort, string $direction, int $page, int $perPage): array
            => $api->getActiviteiten($filters, $sort, $direction, $page, $perPage),
        'loadExtras' => static fn(ActiviteitApi $api): array
            => ['activiteitSoorten' => $api->getActiviteitSoorten()],
        'recordsVar' => 'activiteiten',
        'view' => __DIR__ . '/app/views/Activiteit/listview.php',
    ],
    'fractie' => [
        'label' => 'Fractie',
        'defaults' => FractieModel::getListDefaults(),
        'allowedFilters' => FractieModel::getAllowedFilters(),
        'stateManager' => new ListStateModel('fractie'),
        'api' => new FractieApi(new FractieModel($pdo)),
        'fetch' => static fn(FractieApi $api, array $filters, string $sort, string $direction, int $page, int $perPage): array
            => $api->getFracties($filters, $sort, $direction, $page, $perPage),
        'recordsVar' => 'fracties',
        'view' => __DIR__ . '/app/views/Fractie/listview.php',
    ],
    'person' => [
        'label' => 'Persoon',
        'defaults' => PersonModel::getListDefaults(),
        'allowedFilters' => PersonModel::getAllowedFilters(),
        'stateManager' => new ListStateModel('person'),
        'api' => new PersonApi(new PersonModel($pdo)),
        'fetch' => static fn(PersonApi $api, array $filters, string $sort, string $direction, int $page, int $perPage): array
            => $api->getPersons($filters, $sort, $direction, $page, $perPage),
        'recordsVar' => 'persons',
        'view' => __DIR__ . '/app/views/Persoon/ListView.php',
    ],
    'zaal' => [
        'label' => 'Zaal',
        'defaults' => ZaalModel::getListDefaults(),
        'allowedFilters' => ZaalModel::getAllowedFilters(),
        'stateManager' => new ListStateModel('zaal'),
        'api' => new ZaalApi(new ZaalModel($pdo)),
        'fetch' => static fn(ZaalApi $api, array $filters, string $sort, string $direction, int $page, int $perPage): array
            => $api->getZalen($filters, $sort, $direction, $page, $perPage),
        'recordsVar' => 'zalen',
        'view' => __DIR__ . '/app/views/Zaal/ListView.php',
    ],
    'zaak' => [
        'label' => 'Zaak',
        'defaults' => ZaakModel::getListDefaults(),
        'allowedFilters' => ZaakModel::getAllowedFilters(),
        'stateManager' => new ListStateModel('zaak'),
        'api' => new ZaakApi(new ZaakModel($pdo)),
        'fetch' => static fn(ZaakApi $api, array $filters, string $sort, string $direction, int $page, int $perPage): array
            => $api->getZaken($filters, $sort, $direction, $page, $perPage),
        'loadExtras' => static fn(ZaakApi $api): array
            => ['zaakSoorten' => $api->getZaakSoorten()],
        'recordsVar' => 'zaken',
        'view' => __DIR__ . '/app/views/Zaak/listview.php',
    ],
    'statistiek' => [
        'label' => 'Statistieken',
        'defaults' => [
            'sort' => '',
            'direction' => 'asc',
            'page' => 1,
            'filters' => [],
        ],
        'allowedFilters' => [],
        'stateManager' => new ListStateModel('statistiek'),
        'api' => new StatsApi(new StatsModel($pdo)),
        'fetch' => static fn(StatsApi $api, array $filters, string $sort, string $direction, int $page, int $perPage): array
            => ['data' => [], 'total' => 0],
        'loadExtras' => static function (StatsApi $api): array {
            $statsSection = (string)($_GET['stats_section'] ?? 'active_persons');
            $statsSort = (string)($_GET['stats_sort'] ?? 'jaren_ervaring');
            $statsDirection = (string)($_GET['stats_direction'] ?? 'desc');

            $defaultSorts = [
                'active_persons' => 'jaren_ervaring',
                'experience_map' => 'jaren_ervaring',
                'persons' => 'totaal_stemmen',
                'fracties' => 'totaal_stemmen',
                'besluiten' => 'totaal_stemmen',
            ];

            if (!array_key_exists($statsSection, $defaultSorts)) {
                $statsSection = 'active_persons';
            }

            if ($statsSort === '') {
                $statsSort = $defaultSorts[$statsSection];
            }

            if (!in_array(strtolower($statsDirection), ['asc', 'desc'], true)) {
                $statsDirection = 'desc';
            }

            return [
                'statsSection' => $statsSection,
                'statsSort' => $statsSort,
                'statsDirection' => $statsDirection,
                'activePersonStats' => $api->getActivePersonStats(
                    in_array($statsSection, ['active_persons', 'experience_map'], true) ? $statsSort : 'jaren_ervaring',
                    in_array($statsSection, ['active_persons', 'experience_map'], true) ? $statsDirection : 'desc'
                ),
                'personStatsList' => $api->getPersonStatsList(
                    $statsSection === 'persons' ? $statsSort : 'totaal_stemmen',
                    $statsSection === 'persons' ? $statsDirection : 'desc'
                ),
                'fractieStatsList' => $api->getFractieStatsList(
                    $statsSection === 'fracties' ? $statsSort : 'totaal_stemmen',
                    $statsSection === 'fracties' ? $statsDirection : 'desc'
                ),
                'besluitStatsList' => $api->getBesluitStatsList(
                    $statsSection === 'besluiten' ? $statsSort : 'totaal_stemmen',
                    $statsSection === 'besluiten' ? $statsDirection : 'desc'
                ),
            ];
        },
        'recordsVar' => 'statsRows',
        'view' => __DIR__ . '/app/views/Statistiek/listview.php',
    ],
];

$activeTab = $_GET['tab'] ?? 'activiteit';
if (!isset($tabs[$activeTab])) {
    $activeTab = 'activiteit';
}

$request = $_GET;
$resetRequested = isset($request['reset']) && (string)$request['reset'] === '1';

if ($activeTab === 'person') {
    $personMode = (string)($request['person_view'] ?? '');

    if ($personMode === 'all') {
        $request['active_only'] = '';
    } elseif ($personMode === 'active') {
        $request['active_only'] = '1';
    }
}

$perPage = 50;

$activeTabConfig = $tabs[$activeTab];

if ($resetRequested) {
    // Reset to the tab defaults first, then apply any explicit mode flags from the request.
    $stateData = $activeTabConfig['defaults'];

    foreach ($activeTabConfig['allowedFilters'] as $filterKey) {
        if (array_key_exists($filterKey, $request)) {
            $stateData['filters'][$filterKey] = is_string($request[$filterKey])
                ? trim($request[$filterKey])
                : $request[$filterKey];
        }
    }

    $activeTabConfig['stateManager']->saveState($stateData);
    $activeTabConfig['stateData'] = $stateData;
} else {
    $activeTabConfig['stateData'] = $activeTabConfig['stateManager']->applyRequest(
        $request,
        $activeTabConfig['defaults'],
        $activeTabConfig['allowedFilters']
    );
}
$activeDataset = loadTabDataset($activeTabConfig, $perPage);

$total = $activeDataset['total'];
$totalPages = $activeDataset['totalPages'];
$currentPage = $activeDataset['currentPage'];
$sort = $activeDataset['sort'];
$direction = $activeDataset['direction'];
$filters = $activeDataset['filters'];

${$activeTabConfig['recordsVar']} = $activeDataset['records'];

foreach ($activeDataset['extras'] as $extraName => $extraValue) {
    $$extraName = $extraValue;
}

$personActiveOnly = $activeTab === 'person' && (($filters['active_only'] ?? '') === '1');
if ($activeTab === 'person') {
    $personListTitle = $personActiveOnly ? 'Actieve personen' : 'Person List';
}

$pageTitle = $activeTabConfig['label'] . ' overzicht';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
    <link
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        rel="stylesheet"
    >
    <style>
        .sidebar-nav .nav-link {
            border-radius: 0.75rem;
            color: #1f2937;
            font-weight: 600;
            padding: 0.85rem 1rem;
        }

        .sidebar-nav .nav-link:hover {
            background-color: #eef2ff;
            color: #1d4ed8;
        }

        .sidebar-nav .nav-link.active {
            background-color: #1d4ed8;
            color: #fff;
            box-shadow: 0 0.5rem 1rem rgba(29, 78, 216, 0.18);
        }

        .sidebar-nav .nav-link.sub-item {
            margin-left: 1rem;
            font-size: 0.95rem;
            font-weight: 500;
            padding-top: 0.6rem;
            padding-bottom: 0.6rem;
        }
    </style>
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="row g-4 align-items-start">
        <aside class="col-12 col-lg-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h2 class="h5 mb-3">Overzicht</h2>
                    <nav class="nav flex-column gap-2 sidebar-nav" aria-label="Hoofdnavigatie">
                        <?php foreach ($tabs as $tabKey => $tab): ?>
                            <?php
                            $isPersonAllView = $tabKey === 'person' && $activeTab === 'person' && !$personActiveOnly;
                            $navLinkClass = ($tabKey === 'person')
                                ? ($isPersonAllView ? 'active' : '')
                                : ($activeTab === $tabKey ? 'active' : '');
                            $navHref = $tabKey === 'person'
                                ? 'index.php?tab=person&person_view=all'
                                : 'index.php?tab=' . urlencode($tabKey);
                            ?>
                            <a
                                class="nav-link <?= $navLinkClass ?>"
                                href="<?= $navHref ?>"
                            >
                                <?= htmlspecialchars($tab['label']) ?>
                            </a>
                            <?php if ($tabKey === 'person'): ?>
                                <a
                                    class="nav-link sub-item <?= $personActiveOnly ? 'active' : '' ?>"
                                    href="index.php?tab=person&person_view=active"
                                >
                                    Actieve
                                </a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </nav>

                    <div class="mt-4 pt-3 border-top">
                        <a class="btn btn-outline-primary w-100" href="admin/index.php">
                            <i class="fa-solid fa-user-shield"></i> Administrator
                        </a>
                    </div>
                </div>
            </div>
        </aside>

        <main class="col-12 col-lg-9">
            <?php include $activeTabConfig['view']; ?>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

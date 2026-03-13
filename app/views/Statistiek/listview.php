<?php
declare(strict_types=1);

require_once __DIR__ . '/../helpers/list_helpers.php';

$partyStyles = require __DIR__ . '/../../config/party_styles.php';

$statsSection = $statsSection ?? 'active_persons';
$statsSort = $statsSort ?? 'jaren_ervaring';
$statsDirection = $statsDirection ?? 'desc';

function formatExperienceDays(int $days): string
{
    $days = max(0, $days);
    $years = intdiv($days, 365);
    $remainingDays = $days % 365;

    if ($years === 0) {
        return $remainingDays . ' dagen';
    }

    if ($remainingDays === 0) {
        return $years . ' jaar';
    }

    return $years . ' jaar, ' . $remainingDays . ' dagen';
}

function statsTabLink(string $section): string
{
    $defaultSorts = [
        'active_persons' => 'jaren_ervaring',
        'experience_map' => 'jaren_ervaring',
        'persons' => 'totaal_stemmen',
        'fracties' => 'totaal_stemmen',
        'besluiten' => 'totaal_stemmen',
    ];

    return buildQuery([
        'stats_section' => $section,
        'stats_sort' => $defaultSorts[$section] ?? 'totaal_stemmen',
        'stats_direction' => 'desc',
    ]);
}

function statsSortLink(string $section, string $column, string $currentSort, string $currentDirection): string
{
    $nextDirection = 'asc';

    if ($currentSort === $column && $currentDirection === 'asc') {
        $nextDirection = 'desc';
    }

    return buildQuery([
        'stats_section' => $section,
        'stats_sort' => $column,
        'stats_direction' => $nextDirection,
    ]);
}

function statsSortIcon(string $column, string $currentSort, string $currentDirection): string
{
    if ($currentSort !== $column) {
        return '';
    }

    return $currentDirection === 'asc'
        ? ' <i class="fa-solid fa-arrow-up"></i>'
        : ' <i class="fa-solid fa-arrow-down"></i>';
}

function fractieColorPreset(string $afkorting): array
{
    global $partyStyles;

    $key = strtoupper(trim($afkorting));

    return $partyStyles[$key] ?? [
        'primary' => '#64748b',
        'secondary' => '#cbd5e1',
        'name' => $afkorting !== '' ? $afkorting : 'Onbekend',
        'logo' => null,
    ];
}

function renderExperienceAgeScatterPlot(array $rows): void
{
    if (empty($rows)) {
        echo '<p class="text-muted mb-0">Geen resultaten</p>';
        return;
    }

    $seriesMap = [];
    $legend = [];
    $colors = [];
    $seriesOptions = [];

    foreach ($rows as $row) {
        $fractieNaam = trim((string) ($row['fractie_naam'] ?? ''));
        $fractieAfkorting = trim((string) ($row['fractie_afkorting'] ?? ''));
        $partyKey = $fractieAfkorting !== '' ? $fractieAfkorting : ($fractieNaam !== '' ? $fractieNaam : 'ONBEKEND');

        if (!isset($seriesMap[$partyKey])) {
            $partyStyle = fractieColorPreset($partyKey);
            $baseSeriesIndex = count($colors);

            $seriesMap[$partyKey] = [
                'primaryIndex' => $baseSeriesIndex,
                'secondaryIndex' => $baseSeriesIndex + 1,
                'style' => $partyStyle,
                'displayLabel' => $fractieAfkorting !== '' ? $fractieAfkorting : $partyKey,
                'name' => $fractieNaam !== '' ? $fractieNaam : $partyStyle['name'],
            ];

            $colors[] = $partyStyle['primary'];
            $colors[] = $partyStyle['secondary'];
            $seriesOptions[(string) $baseSeriesIndex] = [
                'pointSize' => 14,
                'lineWidth' => 0,
                'pointsVisible' => true,
                'enableInteractivity' => true,
            ];
            $seriesOptions[(string) ($baseSeriesIndex + 1)] = [
                'pointSize' => 8,
                'lineWidth' => 0,
                'pointsVisible' => true,
                'enableInteractivity' => true,
            ];
        }

        $party = $seriesMap[$partyKey];

        if (!isset($legend[$partyKey])) {
            $legend[$partyKey] = [
                'primary' => $party['style']['primary'],
                'secondary' => $party['style']['secondary'],
                'abbreviation' => $party['displayLabel'],
                'display_name' => $party['style']['name'] !== '' ? $party['style']['name'] : $party['name'],
                'logo' => $party['style']['logo'],
            ];
        }
    }

    $googleRows = [];
    $rowTargets = [];

    foreach ($rows as $row) {
        $fractieNaam = trim((string) ($row['fractie_naam'] ?? ''));
        $fractieAfkorting = trim((string) ($row['fractie_afkorting'] ?? ''));
        $partyKey = $fractieAfkorting !== '' ? $fractieAfkorting : ($fractieNaam !== '' ? $fractieNaam : 'ONBEKEND');
        $party = $seriesMap[$partyKey];
        $age = max(0, (int) ($row['leeftijd'] ?? 0));
        $experienceDays = max(0, (int) ($row['ervaring_dagen'] ?? 0));
        $experienceYears = round($experienceDays / 365.25, 2);
        $name = (string) ($row['naam'] ?? '');

        $tooltip = sprintf(
            '<div style="padding:10px 12px; min-width:220px;"><div style="font-weight:700; margin-bottom:4px;">%s</div><div>%s</div><div>Leeftijd: %d</div><div>Ervaring: %s</div></div>',
            htmlspecialchars($name, ENT_QUOTES),
            htmlspecialchars((string) ($party['name'] ?? ''), ENT_QUOTES),
            $age,
            htmlspecialchars(formatExperienceDays($experienceDays), ENT_QUOTES)
        );

        $rowData = [$age];
        foreach ($seriesMap as $mapKey => $map) {
            if ($mapKey === $partyKey) {
                $rowData[] = $experienceYears;
                $rowData[] = $tooltip;
                $rowData[] = $experienceYears;
                $rowData[] = $tooltip;
            } else {
                $rowData[] = null;
                $rowData[] = null;
                $rowData[] = null;
                $rowData[] = null;
            }
        }

        $googleRows[] = $rowData;
        $rowTargets[] = 'persondetails.php?id=' . rawurlencode((string) ($row['id'] ?? ''));
    }

    $columnDefinitions = [['type' => 'number', 'label' => 'Leeftijd']];
    foreach ($seriesMap as $party) {
        $columnDefinitions[] = ['type' => 'number', 'label' => $party['displayLabel'] . ' buiten'];
        $columnDefinitions[] = ['type' => 'string', 'role' => 'tooltip', 'p' => ['html' => true]];
        $columnDefinitions[] = ['type' => 'number', 'label' => $party['displayLabel'] . ' binnen'];
        $columnDefinitions[] = ['type' => 'string', 'role' => 'tooltip', 'p' => ['html' => true]];
    }

    $chartId = 'experience-age-chart';
    $chartDataId = 'experience-age-data';
    $maxAge = max(25, max(array_map(static fn(array $row): int => (int) ($row['leeftijd'] ?? 0), $rows)));
    $maxYears = max(1, max(array_map(static fn(array $row): float => round(((int) ($row['ervaring_dagen'] ?? 0)) / 365.25, 2), $rows)));
    ?>
    <div class="mb-3">
        <h3 class="h5 mb-1">Politieke levenslijnen</h3>
        <p class="text-muted mb-0">Leeftijd op de horizontale as, ervaring op de verticale as. Klik op een datapunt voor de persoonskaart.</p>
    </div>
    <div class="border rounded-4 bg-white p-3">
        <div id="<?= $chartId ?>" style="width: 100%; min-height: 560px;"></div>
    </div>
    <p class="small text-muted mt-2 mb-3">* De ervaringsperiode is bij benadering berekend.</p>
    <div class="d-flex flex-wrap gap-2">
        <?php foreach ($legend as $fractieNaam => $partyStyle): ?>
            <span class="badge text-dark border rounded-pill px-3 py-2 d-inline-flex align-items-center gap-2" style="background: linear-gradient(90deg, <?= htmlspecialchars($partyStyle['primary']) ?> 0 50%, <?= htmlspecialchars($partyStyle['secondary']) ?> 50% 100%);">
                <?php if (!empty($partyStyle['logo'])): ?>
                    <img
                        src="<?= htmlspecialchars((string)$partyStyle['logo']) ?>"
                        alt="<?= htmlspecialchars((string)$partyStyle['display_name']) ?>"
                        style="width: 22px; height: 22px; object-fit: contain; background: rgba(255,255,255,0.88); border-radius: 999px; padding: 2px;"
                    >
                <?php endif; ?>
                <span style="background: rgba(255,255,255,0.78); padding: 0.1rem 0.45rem; border-radius: 999px;">
                    <?= htmlspecialchars($partyStyle['abbreviation'] !== '' ? $partyStyle['abbreviation'] : $fractieNaam) ?>
                </span>
            </span>
        <?php endforeach; ?>
    </div>
    <script src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="application/json" id="<?= $chartDataId ?>"><?= json_encode([
        'columns' => $columnDefinitions,
        'rows' => $googleRows,
        'targets' => $rowTargets,
        'colors' => $colors,
        'series' => $seriesOptions,
        'maxAge' => $maxAge,
        'maxYears' => ceil($maxYears),
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
    <script>
        (function () {
            const chartElement = document.getElementById(<?= json_encode($chartId) ?>);
            const rawDataElement = document.getElementById(<?= json_encode($chartDataId) ?>);

            if (!chartElement || !rawDataElement || typeof google === 'undefined') {
                return;
            }

            const chartPayload = JSON.parse(rawDataElement.textContent);
            let chart;
            let dataTable;

            function buildDataTable() {
                dataTable = new google.visualization.DataTable();

                chartPayload.columns.forEach(function (column) {
                    if (column.role) {
                        dataTable.addColumn(column);
                    } else {
                        dataTable.addColumn(column.type, column.label);
                    }
                });

                dataTable.addRows(chartPayload.rows);
            }

            function drawChart() {
                if (!dataTable) {
                    buildDataTable();
                }

                const options = {
                    backgroundColor: '#fffdf8',
                    chartArea: {
                        left: 70,
                        top: 30,
                        width: '82%',
                        height: '72%'
                    },
                    colors: chartPayload.colors,
                    crosshair: {
                        trigger: 'both',
                        orientation: 'both'
                    },
                    dataOpacity: 0.95,
                    explorer: {
                        actions: ['dragToZoom', 'rightClickToReset'],
                        axis: 'horizontal',
                        keepInBounds: true,
                        maxZoomIn: 0.15
                    },
                    fontName: 'Georgia',
                    hAxis: {
                        title: 'Leeftijd',
                        minValue: 0,
                        maxValue: chartPayload.maxAge + 2,
                        gridlines: {
                            color: '#e5e7eb'
                        },
                        minorGridlines: {
                            color: '#f1f5f9'
                        }
                    },
                    height: 560,
                    legend: 'none',
                    pointShape: 'circle',
                    series: chartPayload.series,
                    tooltip: {
                        isHtml: true
                    },
                    vAxis: {
                        title: 'Ervaring* (jaren)',
                        minValue: 0,
                        maxValue: chartPayload.maxYears + 1,
                        gridlines: {
                            color: '#e5e7eb'
                        },
                        minorGridlines: {
                            color: '#f8fafc'
                        }
                    }
                };

                chart = new google.visualization.ScatterChart(chartElement);
                chart.draw(dataTable, options);

                google.visualization.events.removeAllListeners(chart);
                google.visualization.events.addListener(chart, 'select', function () {
                    const selection = chart.getSelection();

                    if (!selection.length || selection[0].row == null) {
                        return;
                    }

                    const target = chartPayload.targets[selection[0].row];
                    if (target) {
                        window.location.href = target;
                    }
                });
            }

            google.charts.load('current', { packages: ['corechart'] });
            google.charts.setOnLoadCallback(drawChart);
            window.addEventListener('resize', drawChart);
        }());
    </script>
    <?php
}
?>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white border-0 pt-4 pb-0">
        <h2 class="h4 mb-3">Statistieken</h2>

        <ul class="nav nav-tabs card-header-tabs">
            <li class="nav-item">
                <a class="nav-link <?= $statsSection === 'active_persons' ? 'active' : '' ?>" href="<?= statsTabLink('active_persons') ?>">
                    Actieve personen
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $statsSection === 'experience_map' ? 'active' : '' ?>" href="<?= statsTabLink('experience_map') ?>">
                    Leeftijd vs. ervaring
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $statsSection === 'persons' ? 'active' : '' ?>" href="<?= statsTabLink('persons') ?>">
                    Personen
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $statsSection === 'fracties' ? 'active' : '' ?>" href="<?= statsTabLink('fracties') ?>">
                    Fracties
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $statsSection === 'besluiten' ? 'active' : '' ?>" href="<?= statsTabLink('besluiten') ?>">
                    Besluiten
                </a>
            </li>
        </ul>
    </div>

    <div class="card-body">
        <?php if ($statsSection === 'active_persons'): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>
                                <a class="text-white text-decoration-none" href="<?= statsSortLink('active_persons', 'naam', $statsSort, $statsDirection) ?>">
                                    Naam<?= statsSortIcon('naam', $statsSort, $statsDirection) ?>
                                </a>
                            </th>
                            <th>
                                <a class="text-white text-decoration-none" href="<?= statsSortLink('active_persons', 'geslacht', $statsSort, $statsDirection) ?>">
                                    Geslacht<?= statsSortIcon('geslacht', $statsSort, $statsDirection) ?>
                                </a>
                            </th>
                            <th>
                                <a class="text-white text-decoration-none" href="<?= statsSortLink('active_persons', 'fractie_naam', $statsSort, $statsDirection) ?>">
                                    Fractie<?= statsSortIcon('fractie_naam', $statsSort, $statsDirection) ?>
                                </a>
                            </th>
                            <th class="text-end">
                                <a class="text-white text-decoration-none" href="<?= statsSortLink('active_persons', 'leeftijd', $statsSort, $statsDirection) ?>">
                                    Leeftijd<?= statsSortIcon('leeftijd', $statsSort, $statsDirection) ?>
                                </a>
                            </th>
                            <th>
                                <a class="text-white text-decoration-none" href="<?= statsSortLink('active_persons', 'geboorteplaats', $statsSort, $statsDirection) ?>">
                                    Geboorteplaats<?= statsSortIcon('geboorteplaats', $statsSort, $statsDirection) ?>
                                </a>
                            </th>
                            <th class="text-end">
                                <a class="text-white text-decoration-none" href="<?= statsSortLink('active_persons', 'jaren_ervaring', $statsSort, $statsDirection) ?>">
                                    Ervaring*<?= statsSortIcon('jaren_ervaring', $statsSort, $statsDirection) ?>
                                </a>
                            </th>
                            <th class="text-end">
                                <a class="text-white text-decoration-none" href="<?= statsSortLink('active_persons', 'totaal_stemmen', $statsSort, $statsDirection) ?>">
                                    Totaal stemmen<?= statsSortIcon('totaal_stemmen', $statsSort, $statsDirection) ?>
                                </a>
                            </th>
                            <th class="text-end">
                                <a class="text-white text-decoration-none" href="<?= statsSortLink('active_persons', 'percentage_voor', $statsSort, $statsDirection) ?>">
                                    % Voor<?= statsSortIcon('percentage_voor', $statsSort, $statsDirection) ?>
                                </a>
                            </th>
                            <th class="text-end">
                                <a class="text-white text-decoration-none" href="<?= statsSortLink('active_persons', 'percentage_tegen', $statsSort, $statsDirection) ?>">
                                    % Tegen<?= statsSortIcon('percentage_tegen', $statsSort, $statsDirection) ?>
                                </a>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($activePersonStats)): ?>
                            <?php foreach ($activePersonStats as $row): ?>
                                <tr>
                                    <td>
                                        <a href="persondetails.php?id=<?= urlencode((string)($row['id'] ?? '')) ?>">
                                            <?= htmlspecialchars((string)($row['naam'] ?? '')) ?>
                                        </a>
                                    </td>
                                    <td><?= htmlspecialchars((string)($row['geslacht'] ?? '')) ?></td>
                                    <td><?= htmlspecialchars((string)($row['fractie_naam'] ?? '')) ?></td>
                                    <td class="text-end"><?= $row['leeftijd'] !== null ? (int)$row['leeftijd'] : '-' ?></td>
                                    <td><?= htmlspecialchars((string)($row['geboorteplaats'] ?? '')) ?></td>
                                    <td class="text-end"><?= htmlspecialchars(formatExperienceDays((int)($row['ervaring_dagen'] ?? 0))) ?></td>
                                    <td class="text-end"><?= (int)($row['totaal_stemmen'] ?? 0) ?></td>
                                    <td class="text-end"><?= $row['percentage_voor'] !== null ? number_format((float)$row['percentage_voor'], 2) . '%' : '-' ?></td>
                                    <td class="text-end"><?= $row['percentage_tegen'] !== null ? number_format((float)$row['percentage_tegen'], 2) . '%' : '-' ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted">Geen resultaten</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <p class="small text-muted mb-0">* De ervaringsperiode is bij benadering berekend.</p>
        <?php elseif ($statsSection === 'experience_map'): ?>
            <?php renderExperienceAgeScatterPlot($activePersonStats ?? []); ?>
        <?php elseif ($statsSection === 'persons'): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>
                                <a class="text-white text-decoration-none" href="<?= statsSortLink('persons', 'naam', $statsSort, $statsDirection) ?>">
                                    Naam<?= statsSortIcon('naam', $statsSort, $statsDirection) ?>
                                </a>
                            </th>
                            <th class="text-end">
                                <a class="text-white text-decoration-none" href="<?= statsSortLink('persons', 'totaal_stemmen', $statsSort, $statsDirection) ?>">
                                    Totaal<?= statsSortIcon('totaal_stemmen', $statsSort, $statsDirection) ?>
                                </a>
                            </th>
                            <th class="text-end">
                                <a class="text-white text-decoration-none" href="<?= statsSortLink('persons', 'totaal_voor_stemmen', $statsSort, $statsDirection) ?>">
                                    Voor<?= statsSortIcon('totaal_voor_stemmen', $statsSort, $statsDirection) ?>
                                </a>
                            </th>
                            <th class="text-end">
                                <a class="text-white text-decoration-none" href="<?= statsSortLink('persons', 'totaal_tegen_stemmen', $statsSort, $statsDirection) ?>">
                                    Tegen<?= statsSortIcon('totaal_tegen_stemmen', $statsSort, $statsDirection) ?>
                                </a>
                            </th>
                            <th class="text-end">
                                <a class="text-white text-decoration-none" href="<?= statsSortLink('persons', 'totaal_onthouden_stemmen', $statsSort, $statsDirection) ?>">
                                    Onthouden<?= statsSortIcon('totaal_onthouden_stemmen', $statsSort, $statsDirection) ?>
                                </a>
                            </th>
                            <th class="text-end">
                                <a class="text-white text-decoration-none" href="<?= statsSortLink('persons', 'totaal_absent_stemmen', $statsSort, $statsDirection) ?>">
                                    Absent<?= statsSortIcon('totaal_absent_stemmen', $statsSort, $statsDirection) ?>
                                </a>
                            </th>
                            <th class="text-end">
                                <a class="text-white text-decoration-none" href="<?= statsSortLink('persons', 'percentage_voor', $statsSort, $statsDirection) ?>">
                                    % Voor<?= statsSortIcon('percentage_voor', $statsSort, $statsDirection) ?>
                                </a>
                            </th>
                            <th class="text-end">
                                <a class="text-white text-decoration-none" href="<?= statsSortLink('persons', 'percentage_tegen', $statsSort, $statsDirection) ?>">
                                    % Tegen<?= statsSortIcon('percentage_tegen', $statsSort, $statsDirection) ?>
                                </a>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($personStatsList)): ?>
                            <?php foreach ($personStatsList as $row): ?>
                                <tr>
                                    <td>
                                        <a href="persondetails.php?id=<?= urlencode((string)($row['id'] ?? '')) ?>">
                                            <?= htmlspecialchars((string)($row['naam'] ?? '')) ?>
                                        </a>
                                    </td>
                                    <td class="text-end"><?= (int)($row['totaal_stemmen'] ?? 0) ?></td>
                                    <td class="text-end"><?= (int)($row['totaal_voor_stemmen'] ?? 0) ?></td>
                                    <td class="text-end"><?= (int)($row['totaal_tegen_stemmen'] ?? 0) ?></td>
                                    <td class="text-end"><?= (int)($row['totaal_onthouden_stemmen'] ?? 0) ?></td>
                                    <td class="text-end"><?= (int)($row['totaal_absent_stemmen'] ?? 0) ?></td>
                                    <td class="text-end"><?= $row['percentage_voor'] !== null ? number_format((float)$row['percentage_voor'], 2) . '%' : '-' ?></td>
                                    <td class="text-end"><?= $row['percentage_tegen'] !== null ? number_format((float)$row['percentage_tegen'], 2) . '%' : '-' ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted">Geen resultaten</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php elseif ($statsSection === 'fracties'): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>
                                <a class="text-white text-decoration-none" href="<?= statsSortLink('fracties', 'fractie_naam', $statsSort, $statsDirection) ?>">
                                    Fractie<?= statsSortIcon('fractie_naam', $statsSort, $statsDirection) ?>
                                </a>
                            </th>
                            <th class="text-end">
                                <a class="text-white text-decoration-none" href="<?= statsSortLink('fracties', 'totaal_stemmen', $statsSort, $statsDirection) ?>">
                                    Totaal<?= statsSortIcon('totaal_stemmen', $statsSort, $statsDirection) ?>
                                </a>
                            </th>
                            <th class="text-end">
                                <a class="text-white text-decoration-none" href="<?= statsSortLink('fracties', 'voor_stemmen', $statsSort, $statsDirection) ?>">
                                    Voor<?= statsSortIcon('voor_stemmen', $statsSort, $statsDirection) ?>
                                </a>
                            </th>
                            <th class="text-end">
                                <a class="text-white text-decoration-none" href="<?= statsSortLink('fracties', 'tegen_stemmen', $statsSort, $statsDirection) ?>">
                                    Tegen<?= statsSortIcon('tegen_stemmen', $statsSort, $statsDirection) ?>
                                </a>
                            </th>
                            <th class="text-end">
                                <a class="text-white text-decoration-none" href="<?= statsSortLink('fracties', 'onthouden_stemmen', $statsSort, $statsDirection) ?>">
                                    Onthouden<?= statsSortIcon('onthouden_stemmen', $statsSort, $statsDirection) ?>
                                </a>
                            </th>
                            <th class="text-end">
                                <a class="text-white text-decoration-none" href="<?= statsSortLink('fracties', 'absent_stemmen', $statsSort, $statsDirection) ?>">
                                    Absent<?= statsSortIcon('absent_stemmen', $statsSort, $statsDirection) ?>
                                </a>
                            </th>
                            <th class="text-end">
                                <a class="text-white text-decoration-none" href="<?= statsSortLink('fracties', 'voor_percentage', $statsSort, $statsDirection) ?>">
                                    % Voor<?= statsSortIcon('voor_percentage', $statsSort, $statsDirection) ?>
                                </a>
                            </th>
                            <th class="text-end">
                                <a class="text-white text-decoration-none" href="<?= statsSortLink('fracties', 'tegen_percentage', $statsSort, $statsDirection) ?>">
                                    % Tegen<?= statsSortIcon('tegen_percentage', $statsSort, $statsDirection) ?>
                                </a>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($fractieStatsList)): ?>
                            <?php foreach ($fractieStatsList as $row): ?>
                                <tr>
                                    <td>
                                        <a href="fractiedetails.php?id=<?= urlencode((string)($row['id'] ?? '')) ?>">
                                            <?= htmlspecialchars((string)($row['fractie_naam'] ?? '')) ?>
                                        </a>
                                    </td>
                                    <td class="text-end"><?= (int)($row['totaal_stemmen'] ?? 0) ?></td>
                                    <td class="text-end"><?= (int)($row['voor_stemmen'] ?? 0) ?></td>
                                    <td class="text-end"><?= (int)($row['tegen_stemmen'] ?? 0) ?></td>
                                    <td class="text-end"><?= (int)($row['onthouden_stemmen'] ?? 0) ?></td>
                                    <td class="text-end"><?= (int)($row['absent_stemmen'] ?? 0) ?></td>
                                    <td class="text-end"><?= $row['voor_percentage'] !== null ? number_format((float)$row['voor_percentage'], 2) . '%' : '-' ?></td>
                                    <td class="text-end"><?= $row['tegen_percentage'] !== null ? number_format((float)$row['tegen_percentage'], 2) . '%' : '-' ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted">Geen resultaten</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>
                                <a class="text-white text-decoration-none" href="<?= statsSortLink('besluiten', 'besluit', $statsSort, $statsDirection) ?>">
                                    Besluit<?= statsSortIcon('besluit', $statsSort, $statsDirection) ?>
                                </a>
                            </th>
                            <th>
                                <a class="text-white text-decoration-none" href="<?= statsSortLink('besluiten', 'status', $statsSort, $statsDirection) ?>">
                                    Status<?= statsSortIcon('status', $statsSort, $statsDirection) ?>
                                </a>
                            </th>
                            <th>
                                <a class="text-white text-decoration-none" href="<?= statsSortLink('besluiten', 'stemmingssoort', $statsSort, $statsDirection) ?>">
                                    Stemmingssoort<?= statsSortIcon('stemmingssoort', $statsSort, $statsDirection) ?>
                                </a>
                            </th>
                            <th class="text-end">
                                <a class="text-white text-decoration-none" href="<?= statsSortLink('besluiten', 'totaal_stemmen', $statsSort, $statsDirection) ?>">
                                    Totaal<?= statsSortIcon('totaal_stemmen', $statsSort, $statsDirection) ?>
                                </a>
                            </th>
                            <th class="text-end">
                                <a class="text-white text-decoration-none" href="<?= statsSortLink('besluiten', 'voor_stemmen', $statsSort, $statsDirection) ?>">
                                    Voor<?= statsSortIcon('voor_stemmen', $statsSort, $statsDirection) ?>
                                </a>
                            </th>
                            <th class="text-end">
                                <a class="text-white text-decoration-none" href="<?= statsSortLink('besluiten', 'tegen_stemmen', $statsSort, $statsDirection) ?>">
                                    Tegen<?= statsSortIcon('tegen_stemmen', $statsSort, $statsDirection) ?>
                                </a>
                            </th>
                            <th class="text-end">
                                <a class="text-white text-decoration-none" href="<?= statsSortLink('besluiten', 'onthouden_stemmen', $statsSort, $statsDirection) ?>">
                                    Onthouden<?= statsSortIcon('onthouden_stemmen', $statsSort, $statsDirection) ?>
                                </a>
                            </th>
                            <th class="text-end">
                                <a class="text-white text-decoration-none" href="<?= statsSortLink('besluiten', 'absent_stemmen', $statsSort, $statsDirection) ?>">
                                    Absent<?= statsSortIcon('absent_stemmen', $statsSort, $statsDirection) ?>
                                </a>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($besluitStatsList)): ?>
                            <?php foreach ($besluitStatsList as $row): ?>
                                <tr>
                                    <td><?= htmlspecialchars((string)($row['besluit'] ?? '')) ?></td>
                                    <td><?= htmlspecialchars((string)($row['status'] ?? '')) ?></td>
                                    <td><?= htmlspecialchars((string)($row['stemmingssoort'] ?? '')) ?></td>
                                    <td class="text-end"><?= (int)($row['totaal_stemmen'] ?? 0) ?></td>
                                    <td class="text-end"><?= (int)($row['voor_stemmen'] ?? 0) ?></td>
                                    <td class="text-end"><?= (int)($row['tegen_stemmen'] ?? 0) ?></td>
                                    <td class="text-end"><?= (int)($row['onthouden_stemmen'] ?? 0) ?></td>
                                    <td class="text-end"><?= (int)($row['absent_stemmen'] ?? 0) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted">Geen resultaten</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

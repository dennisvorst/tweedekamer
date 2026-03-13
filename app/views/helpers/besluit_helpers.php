<?php
declare(strict_types=1);

if (!function_exists('besluitHasValue')) {
    function besluitHasValue(mixed $value): bool
    {
        return $value !== null && $value !== '';
    }
}

if (!function_exists('besluitFormatDate')) {
    function besluitFormatDate(?string $date): string
    {
        if (!$date) {
            return '';
        }

        try {
            $dt = new DateTime($date);
            return $dt->format('d-m-Y');
        } catch (Exception) {
            return $date;
        }
    }
}

if (!function_exists('besluitFormatTime')) {
    function besluitFormatTime(?string $time): string
    {
        if (!$time) {
            return '';
        }

        try {
            $dt = new DateTime($time);
            return $dt->format('H:i');
        } catch (Exception) {
            return $time;
        }
    }
}

if (!function_exists('renderBesluitValueRow')) {
    function renderBesluitValueRow(string $label, mixed $value): void
    {
        if (!besluitHasValue($value)) {
            return;
        }
        ?>
        <div class="row py-2 border-bottom">
            <div class="col-sm-4 fw-semibold"><?= htmlspecialchars($label) ?></div>
            <div class="col-sm-8"><?= nl2br(htmlspecialchars((string)$value)) ?></div>
        </div>
        <?php
    }
}

if (!function_exists('besluitPersoonNaam')) {
    function besluitPersoonNaam(array $row): ?string
    {
        $parts = array_filter([
            $row['roepnaam'] ?? null,
            $row['achternaam'] ?? null,
        ]);

        if (!empty($parts)) {
            return implode(' ', $parts);
        }

        if (besluitHasValue($row['actor_naam'] ?? null)) {
            return (string)$row['actor_naam'];
        }

        return null;
    }
}

if (!function_exists('besluitFractieNaam')) {
    function besluitFractieNaam(array $row): ?string
    {
        if (besluitHasValue($row['fractie_naam_nl'] ?? null)) {
            return (string)$row['fractie_naam_nl'];
        }

        if (besluitHasValue($row['fractie_naam_en'] ?? null)) {
            return (string)$row['fractie_naam_en'];
        }

        if (besluitHasValue($row['actor_fractie'] ?? null)) {
            return (string)$row['actor_fractie'];
        }

        if (besluitHasValue($row['fractie_afkorting'] ?? null)) {
            return (string)$row['fractie_afkorting'];
        }

        return null;
    }
}

if (!function_exists('besluitVoteBadge')) {
    function besluitVoteBadge(string $soort): string
    {
        $normalized = trim(mb_strtolower($soort));

        return match ($normalized) {
            'voor' => '<span class="text-success"><i class="fa-solid fa-check"></i> Voor</span>',
            'tegen' => '<span class="text-danger"><i class="fa-solid fa-xmark"></i> Tegen</span>',
            'niet deelgenomen' => '<span class="text-muted"><i class="fa-solid fa-minus"></i> Niet deelgenomen</span>',
            default => htmlspecialchars($soort),
        };
    }
}

if (!function_exists('splitStemmingRowsBySoort')) {
    function splitStemmingRowsBySoort(array $rows): array
    {
        $result = [
            'voor' => [],
            'tegen' => [],
            'niet deelgenomen' => [],
            'overig' => [],
        ];

        foreach ($rows as $row) {
            $soort = trim(mb_strtolower((string)($row['soort'] ?? '')));

            if (array_key_exists($soort, $result)) {
                $result[$soort][] = $row;
            } else {
                $result['overig'][] = $row;
            }
        }

        return $result;
    }
}

if (!function_exists('renderStemmingTable')) {
    function renderStemmingTable(array $rows, string $title): void
    {
        if (empty($rows)) {
            return;
        }
        ?>
        <div class="card border-0 bg-light mt-4">
            <div class="card-header bg-transparent">
                <h5 class="mb-0"><?= htmlspecialchars($title) ?></h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Stem</th>
                                <th>Persoon</th>
                                <th>Fractie</th>
                                <th class="text-end">Fractiegrootte</th>
                                <th>Vergissing</th>
                                <th class="text-center">Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rows as $row): ?>
                                <?php
                                $persoonNaam = besluitPersoonNaam($row);
                                $fractieNaam = besluitFractieNaam($row);
                                ?>
                                <tr>
                                    <td><?= besluitVoteBadge((string)($row['soort'] ?? '')) ?></td>
                                    <td><?= htmlspecialchars($persoonNaam ?? '—') ?></td>
                                    <td><?= htmlspecialchars($fractieNaam ?? '—') ?></td>
                                    <td class="text-end text-nowrap"><?= htmlspecialchars((string)($row['fractie_grootte'] ?? '')) ?></td>
                                    <td><?= !empty($row['vergissing']) ? 'Ja' : 'Nee' ?></td>
                                    <td class="text-center">
                                        <?php if (!empty($row['persoon_id'])): ?>
                                            <a
                                                href="persondetails.php?id=<?= urlencode((string)$row['persoon_id']) ?>"
                                                class="btn btn-sm btn-outline-primary me-1"
                                                title="Bekijk persoon"
                                            >
                                                <i class="fa-solid fa-user"></i>
                                            </a>
                                        <?php endif; ?>

                                        <?php if (!empty($row['fractie_id'])): ?>
                                            <a
                                                href="fractiedetails.php?id=<?= urlencode((string)$row['fractie_id']) ?>"
                                                class="btn btn-sm btn-outline-secondary"
                                                title="Bekijk fractie"
                                            >
                                                <i class="fa-solid fa-users"></i>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php
    }
}


if (!function_exists('renderBesluitDetails')) {
    function renderBesluitDetails(
        array $besluit,
        array $stemmingRows = [],
        array $fractieStemSamenvattingRows = []
    ): void
    {
        $title = (string)($besluit['besluittekst'] ?? $besluit['besluit_soort'] ?? 'Besluit');
        ?>
        <div class="card shadow-sm">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div>
                        <h2 class="mb-1"><?= htmlspecialchars($title) ?></h2>
                        <?php if (besluitHasValue($besluit['besluit_soort'] ?? null)): ?>
                            <div class="text-muted"><?= htmlspecialchars((string)$besluit['besluit_soort']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div>
                        <?php if (!empty($besluit['activiteit_id'])): ?>
                            <a href="activiteitdetails.php?id=<?= urlencode((string)$besluit['activiteit_id']) ?>" class="btn btn-outline-primary">
                                <i class="fa-solid fa-arrow-left"></i> Naar activiteit
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="card border-0 bg-light mt-4">
                    <div class="card-header bg-transparent">
                        <h5 class="mb-0">Activiteit</h5>
                    </div>
                    <div class="card-body">
                        <?php renderBesluitValueRow('Soort', $besluit['activiteit_soort'] ?? null); ?>
                        <?php renderBesluitValueRow('Nummer', $besluit['activiteit_nummer'] ?? null); ?>
                        <?php renderBesluitValueRow('Onderwerp', $besluit['activiteit_onderwerp'] ?? null); ?>
                        <?php renderBesluitValueRow('Datum', besluitFormatDate($besluit['activiteit_datum'] ?? null)); ?>
                        <?php renderBesluitValueRow('Aanvangstijd', besluitFormatTime($besluit['activiteit_aanvangstijd'] ?? null)); ?>
                        <?php renderBesluitValueRow('Eindtijd', besluitFormatTime($besluit['activiteit_eindtijd'] ?? null)); ?>
                        <?php renderBesluitValueRow('Locatie', $besluit['activiteit_locatie'] ?? null); ?>
                    </div>
                </div>

                <div class="card border-0 bg-light mt-4">
                    <div class="card-header bg-transparent">
                        <h5 class="mb-0">Agendapunt</h5>
                    </div>
                    <div class="card-body">
                        <?php renderBesluitValueRow('Nummer', $besluit['agendapunt_nummer'] ?? null); ?>
                        <?php renderBesluitValueRow('Onderwerp', $besluit['agendapunt_onderwerp'] ?? null); ?>
                        <?php renderBesluitValueRow('Aanvangstijd', besluitFormatTime($besluit['agendapunt_aanvangstijd'] ?? null)); ?>
                        <?php renderBesluitValueRow('Eindtijd', besluitFormatTime($besluit['agendapunt_eindtijd'] ?? null)); ?>
                        <?php renderBesluitValueRow('Rubriek', $besluit['agendapunt_rubriek'] ?? null); ?>
                        <?php renderBesluitValueRow('Noot', $besluit['agendapunt_noot'] ?? null); ?>
                        <?php renderBesluitValueRow('Status', $besluit['agendapunt_status'] ?? null); ?>
                    </div>
                </div>

                <div class="card border-0 bg-light">
                    <div class="card-header bg-transparent">
                        <h5 class="mb-0">Besluitgegevens</h5>
                    </div>
                    <div class="card-body">
                        <?php renderBesluitValueRow('Besluitsoort', $besluit['besluit_soort'] ?? null); ?>
                        <?php renderBesluitValueRow('Stemmingssoort', $besluit['stemmingssoort'] ?? null); ?>
                        <?php renderBesluitValueRow('Besluittekst', $besluit['besluittekst'] ?? null); ?>
                        <?php renderBesluitValueRow('Opmerking', $besluit['opmerking'] ?? null); ?>
                        <?php renderBesluitValueRow('Status', $besluit['status'] ?? null); ?>
                        <?php renderBesluitValueRow('Volgorde', $besluit['agendapunt_zaak_besluitvolgorde'] ?? null); ?>
                    </div>
                </div>

                <?php renderBesluitStemmingenSection(
                    $besluit['stemmingssoort'] ?? null,
                    $stemmingRows,
                    $fractieStemSamenvattingRows
                ); ?>
            </div>
        </div>
        <?php
    }

if (!function_exists('splitBesluitStemmingRowsPersoonlijk')) {
    function splitBesluitStemmingRowsPersoonlijk(array $rows): array
    {
        $result = [
            'voor' => [],
            'tegen' => [],
            'onthouden' => [],
            'absent' => [],
        ];

        foreach ($rows as $row) {
            $soort = trim(mb_strtolower((string)($row['soort'] ?? '')));

            if ($soort === 'voor') {
                $result['voor'][] = $row;
            } elseif ($soort === 'tegen') {
                $result['tegen'][] = $row;
            } elseif ($soort === 'niet deelgenomen') {
                $result['absent'][] = $row;
            } else {
                $result['onthouden'][] = $row;
            }
        }

        return $result;
    }
    }

if (!function_exists('sortBesluitStemmingRowsByName')) {
    function sortBesluitStemmingRowsByName(array $rows): array
    {
        usort($rows, static function (array $a, array $b): int {
            $nameA = mb_strtolower(trim((string)(besluitPersoonNaam($a) ?? $a['actor_naam'] ?? '')));
            $nameB = mb_strtolower(trim((string)(besluitPersoonNaam($b) ?? $b['actor_naam'] ?? '')));

            return $nameA <=> $nameB;
        });

        return $rows;
    }
}

if (!function_exists('renderBesluitPersoonNaamOnly')) {
    function renderBesluitPersoonNaamOnly(array $row, string $type): void
    {
        $persoonNaam = besluitPersoonNaam($row) ?? (string)($row['actor_naam'] ?? 'Onbekend');
        $fractieAfkorting = (string)($row['fractie_afkorting'] ?? '');

        $iconHtml = '';
        if ($type === 'voor') {
            $iconHtml = '<i class="fa-solid fa-check text-success me-1"></i>';
        } elseif ($type === 'tegen') {
            $iconHtml = '<i class="fa-solid fa-xmark text-danger me-1"></i>';
        } elseif ($type === 'absent') {
            $iconHtml = '<i class="fa-solid fa-minus text-muted me-1"></i>';
        } else {
            $iconHtml = '<i class="fa-solid fa-circle text-secondary me-1"></i>';
        }

        $reason = null;
        if ($type === 'onthouden' || $type === 'absent') {
            $soort = trim((string)($row['soort'] ?? ''));
            if ($type === 'absent') {
                $reason = 'Niet deelgenomen';
            } else {
                $reason = $soort !== '' ? $soort : 'Onthouden';
            }
        }
        ?>
        <div class="mb-2">
            <div>
                <?= $iconHtml ?>
                <?= htmlspecialchars($persoonNaam) ?>
                <?php if ($fractieAfkorting !== ''): ?>
                    <span class="text-muted">(<?= htmlspecialchars($fractieAfkorting) ?>)</span>
                <?php endif; ?>
            </div>
            <?php if ($reason !== null): ?>
                <div class="small text-muted ms-4"><?= htmlspecialchars($reason) ?></div>
            <?php endif; ?>
        </div>
        <?php
    }
}

    if (!function_exists('renderBesluitPersoonlijkStemmenTab')) {
        function renderBesluitPersoonlijkStemmenTab(array $stemmingRows = []): void
        {
            if (empty($stemmingRows)) {
                echo '<div class="text-muted">Geen persoonlijke stemmingen beschikbaar.</div>';
                return;
            }

            $split = splitBesluitStemmingRowsPersoonlijk($stemmingRows);

            $voorRows = sortBesluitStemmingRowsByName($split['voor']);
            $tegenRows = sortBesluitStemmingRowsByName($split['tegen']);
            $onthoudenRows = sortBesluitStemmingRowsByName($split['onthouden']);
            $absentRows = sortBesluitStemmingRowsByName($split['absent']);
            ?>
            <div class="table-responsive">
                <table class="table table-bordered align-top">
                    <thead>
                        <tr>
                            <th class="text-success">Voor (<?php echo count($voorRows);?>)</th>
                            <th class="text-danger">Tegen (<?php echo count($tegenRows);?>)</th>
                            <th class="text-secondary">Onthouden (<?php echo count($onthoudenRows);?>)</th>
                            <th class="text-muted">Absent (<?php echo count($absentRows);?>)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="width:25%;">
                                <?php foreach ($voorRows as $row): ?>
                                    <?php renderBesluitPersoonNaamOnly($row, 'voor'); ?>
                                <?php endforeach; ?>
                            </td>
                            <td style="width:25%;">
                                <?php foreach ($tegenRows as $row): ?>
                                    <?php renderBesluitPersoonNaamOnly($row, 'tegen'); ?>
                                <?php endforeach; ?>
                            </td>
                            <td style="width:25%;">
                                <?php foreach ($onthoudenRows as $row): ?>
                                    <?php renderBesluitPersoonNaamOnly($row, 'onthouden'); ?>
                                <?php endforeach; ?>
                            </td>
                            <td style="width:25%;">
                                <?php foreach ($absentRows as $row): ?>
                                    <?php renderBesluitPersoonNaamOnly($row, 'absent'); ?>
                                <?php endforeach; ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <?php
        }
    }

    if (!function_exists('renderBesluitFractieStemmenTab')) {
        function renderBesluitFractieStemmenTab(array $fractieStemSamenvattingRows = []): void
        {
            if (empty($fractieStemSamenvattingRows)) {
                echo '<div class="text-muted">Geen fractiestemmingen beschikbaar.</div>';
                return;
            }

            $totaalVoor = array_sum(array_map(
                static fn(array $row): int => (int)($row['voor_count'] ?? 0),
                $fractieStemSamenvattingRows
            ));
            $totaalTegen = array_sum(array_map(
                static fn(array $row): int => (int)($row['tegen_count'] ?? 0),
                $fractieStemSamenvattingRows
            ));
            $totaalOnthouden = array_sum(array_map(
                static fn(array $row): int => (int)($row['onthouden_count'] ?? 0),
                $fractieStemSamenvattingRows
            ));
            $totaalAbsent = array_sum(array_map(
                static fn(array $row): int => (int)($row['absent_count'] ?? 0),
                $fractieStemSamenvattingRows
            ));
            $totaalFractiegrootte = array_sum(array_map(
                static fn(array $row): int => (int)($row['fractie_grootte'] ?? 0),
                $fractieStemSamenvattingRows
            ));
            ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Naam</th>
                            <th class="text-end">Fractiegrootte (<?= htmlspecialchars((string)$totaalFractiegrootte) ?>)</th>
                            <th class="text-end">Voor (<?= htmlspecialchars((string)$totaalVoor) ?>)</th>
                            <th class="text-end">Tegen (<?= htmlspecialchars((string)$totaalTegen) ?>)</th>
                            <th class="text-end">Onthouden (<?= htmlspecialchars((string)$totaalOnthouden) ?>)</th>
                            <th class="text-end">Absent (<?= htmlspecialchars((string)$totaalAbsent) ?>)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($fractieStemSamenvattingRows as $row): ?>
                            <?php
                            $naam = (string)($row['fractie_naam'] ?? 'Onbekend');
                            $fractieId = $row['fractie_id'] ?? null;
                            $fractiegrootte = (string)($row['fractie_grootte'] ?? '');
                            ?>
                            <tr>
                                <td>
                                    <?php if ($fractieId !== null && $fractieId !== ''): ?>
                                        <a href="fractiedetails.php?id=<?= urlencode((string)$fractieId) ?>">
                                            <?= htmlspecialchars($naam) ?>
                                        </a>
                                    <?php else: ?>
                                        <?= htmlspecialchars($naam) ?>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end"><?= htmlspecialchars($fractiegrootte) ?></td>
                                <td class="text-end text-success"><?= htmlspecialchars((string)($row['voor_count'] ?? '0')) ?></td>
                                <td class="text-end text-danger"><?= htmlspecialchars((string)($row['tegen_count'] ?? '0')) ?></td>
                                <td class="text-end text-secondary"><?= htmlspecialchars((string)($row['onthouden_count'] ?? '0')) ?></td>
                                <td class="text-end text-muted"><?= htmlspecialchars((string)($row['absent_count'] ?? '0')) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php
        }
    }

    if (!function_exists('normalizeBesluitStemmingssoort')) {
        function normalizeBesluitStemmingssoort(?string $stemmingssoort): string
        {
            return trim(mb_strtolower((string)$stemmingssoort));
        }
    }

    if (!function_exists('renderBesluitStemmingenSection')) {
        function renderBesluitStemmingenSection(
            ?string $stemmingssoort,
            array $stemmingRows = [],
            array $fractieStemSamenvattingRows = []
        ): void 
        {
            $normalizedStemmingssoort = normalizeBesluitStemmingssoort($stemmingssoort);

            if ($normalizedStemmingssoort === '' || $normalizedStemmingssoort === 'zonder stemming') {
                return;
            }

            if ($normalizedStemmingssoort === 'met handopsteken' && empty($fractieStemSamenvattingRows)) {
                return;
            }

            if ($normalizedStemmingssoort === 'hoofdelijk' && empty($stemmingRows) && empty($fractieStemSamenvattingRows)) {
                return;
            }
            ?>
            <div class="card border-0 bg-light mt-4">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0">Stemmingen</h5>
                </div>
                <div class="card-body">
                    <?php if ($normalizedStemmingssoort === 'met handopsteken'): ?>
                        <?php renderBesluitFractieStemmenTab($fractieStemSamenvattingRows); ?>
                    <?php else: ?>
                        <ul class="nav nav-tabs mb-3" id="stemmingTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button
                                    class="nav-link active"
                                    id="persoonlijk-tab"
                                    data-bs-toggle="tab"
                                    data-bs-target="#persoonlijk-pane"
                                    type="button"
                                    role="tab"
                                >
                                    Persoonlijk
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button
                                    class="nav-link"
                                    id="fractie-tab"
                                    data-bs-toggle="tab"
                                    data-bs-target="#fractie-pane"
                                    type="button"
                                    role="tab"
                                >
                                    Fractie
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content">
                            <div
                                class="tab-pane fade show active"
                                id="persoonlijk-pane"
                                role="tabpanel"
                                aria-labelledby="persoonlijk-tab"
                            >
                                <?php renderBesluitPersoonlijkStemmenTab($stemmingRows); ?>
                            </div>

                            <div
                                class="tab-pane fade"
                                id="fractie-pane"
                                role="tabpanel"
                                aria-labelledby="fractie-tab"
                            >
                                <?php renderBesluitFractieStemmenTab($fractieStemSamenvattingRows); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php
        }
    }

}

<?php
declare(strict_types=1);

if (!function_exists('fractieHasValue')) {
    function fractieHasValue(mixed $value): bool
    {
        return $value !== null && $value !== '';
    }
}

if (!function_exists('fractieFormatDate')) {
    function fractieFormatDate(?string $date): string
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

if (!function_exists('fractieFormatNumber')) {
    function fractieFormatNumber(int|float|string|null $number): string
    {
        if ($number === null || $number === '') {
            return '';
        }

        if (!is_numeric($number)) {
            return (string)$number;
        }

        return number_format((float)$number, 0, ',', '.');
    }
}

if (!function_exists('renderFractieValueRow')) {
    function renderFractieValueRow(string $label, mixed $value): void
    {
        if (!fractieHasValue($value)) {
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

if (!function_exists('renderFractieZetelPersonenSection')) {
function renderFractieZetelPersonenSection(array $zetelPersoonRows = []): void
{
    if (empty($zetelPersoonRows)) {
        return;
    }

    $splitRows = splitZetelPersoonRowsByStatus($zetelPersoonRows);
    $activeRows = $splitRows['active'];
    $inactiveRows = $splitRows['inactive'];

    renderFractieZetelPersonenTable($activeRows, 'Actieve zetelbezetting');
    renderFractieZetelPersonenTable($inactiveRows, 'Voormalige zetelbezetting');
}
}

if (!function_exists('renderFractieDetails')) {
    function renderFractieDetails(array $fractie, array $zetelPersoonRows = []): void
    {
        $displayName = (string)($fractie['naam_nl'] ?? $fractie['afkorting'] ?? 'Fractie');
        ?>
        <div class="card shadow-sm">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div>
                        <h2 class="mb-1"><?= htmlspecialchars($displayName) ?></h2>
                        <?php if (fractieHasValue($fractie['afkorting'] ?? null)): ?>
                            <div class="text-muted"><?= htmlspecialchars((string)$fractie['afkorting']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div>
                        <a href="index.php?tab=fractie" class="btn btn-outline-primary">
                            <i class="fa-solid fa-arrow-left"></i> Terug naar overzicht
                        </a>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="card border-0 bg-light">
                    <div class="card-header bg-transparent">
                        <h5 class="mb-0">Fractiegegevens</h5>
                    </div>
                    <div class="card-body">
                        <?php renderFractieValueRow('Nummer', $fractie['nummer'] ?? null); ?>
                        <?php renderFractieValueRow('Afkorting', $fractie['afkorting'] ?? null); ?>
                        <?php renderFractieValueRow('Naam NL', $fractie['naam_nl'] ?? null); ?>
                        <?php renderFractieValueRow('Naam EN', $fractie['naam_en'] ?? null); ?>
                        <?php renderFractieValueRow('Aantal zetels', fractieFormatNumber($fractie['aantal_zetels'] ?? null)); ?>
                        <?php renderFractieValueRow('Aantal stemmen', fractieFormatNumber($fractie['aantal_stemmen'] ?? null)); ?>
                        <?php renderFractieValueRow('Datum actief', fractieFormatDate($fractie['datum_actief'] ?? null)); ?>
                        <?php renderFractieValueRow('Datum inactief', fractieFormatDate($fractie['datum_inactief'] ?? null)); ?>
                    </div>
                </div>

                <?php renderFractieZetelPersonenSection($zetelPersoonRows); ?>
            </div>
        </div>
        <?php
    }

    function splitZetelPersoonRowsByStatus(array $zetelPersoonRows): array
    {
        $activeRows = [];
        $inactiveRows = [];

        foreach ($zetelPersoonRows as $row) {
            $totEnMet = $row['tot_en_met'] ?? null;

            if ($totEnMet === null || $totEnMet === '') {
                $activeRows[] = $row;
            } else {
                $inactiveRows[] = $row;
            }
        }

        return [
            'active' => $activeRows,
            'inactive' => $inactiveRows,
        ];
    }    

    function renderFractieZetelPersonenTable(array $rows, string $title): void
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
                                <th>Persoon</th>
                                <th>Functie</th>
                                <th>Van</th>
                                <th>Tot en met</th>
                                <th class="text-center">Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rows as $row): ?>
                                <?php
                                $persoonNaam = trim(implode(' ', array_filter([
                                    $row['roepnaam'] ?? null,
                                    $row['achternaam'] ?? null,
                                ])));
                                ?>
                                <tr>
                                    <td>
                                        <?= htmlspecialchars($persoonNaam !== '' ? $persoonNaam : (string)($row['persoon_id'] ?? '')) ?>
                                    </td>
                                    <td><?= htmlspecialchars((string)($row['functie'] ?? '')) ?></td>
                                    <td><?= htmlspecialchars(fractieFormatDate($row['van'] ?? null)) ?></td>
                                    <td><?= htmlspecialchars(fractieFormatDate($row['tot_en_met'] ?? null)) ?></td>
                                    <td class="text-center">
                                        <a
                                            href="persondetails.php?id=<?= urlencode((string)$row['persoon_id']) ?>"
                                            class="btn btn-sm btn-outline-primary"
                                            title="Bekijk persoon"
                                        >
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
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
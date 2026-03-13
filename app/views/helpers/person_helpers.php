<?php
declare(strict_types=1);

function hasValue(mixed $value): bool
{
    return $value !== null && $value !== '';
}

function normalizeUrl(?string $value): ?string
{
    if (!hasValue($value)) {
        return null;
    }

    $value = trim((string)$value);

    if (preg_match('~^https?://~i', $value)) {
        return $value;
    }

    return 'https://' . $value;
}

function pickFirstValue(array $source, array $keys): mixed
{
    foreach ($keys as $key) {
        if (array_key_exists($key, $source) && hasValue($source[$key])) {
            return $source[$key];
        }
    }

    return null;
}

function renderValueRow(string $label, mixed $value): void
{
    if (!hasValue($value)) {
        return;
    }
    ?>
    <div class="row py-2 border-bottom">
        <div class="col-sm-4 fw-semibold"><?= htmlspecialchars($label) ?></div>
        <div class="col-sm-8"><?= nl2br(htmlspecialchars((string)$value)) ?></div>
    </div>
    <?php
}

function personGenderIconClass(?string $geslacht): string
{
    $normalized = mb_strtolower(trim((string)$geslacht));

    return match ($normalized) {
        'man' => 'fa-solid fa-mars',
        'vrouw' => 'fa-solid fa-venus',
        'x', 'anders', 'non-binair', 'non-binary' => 'fa-solid fa-genderless',
        default => 'fa-solid fa-question',
    };
}

function personGenderColor(?string $geslacht): string
{
    $normalized = mb_strtolower(trim((string)$geslacht));

    return match ($normalized) {
        'man' => '#2563eb',
        'vrouw' => '#d81b60',
        'x', 'anders', 'non-binair', 'non-binary' => '#0f766e',
        default => '#6b7280',
    };
}

function renderPersonGender(?string $geslacht): string
{
    if (!hasValue($geslacht)) {
        return '';
    }

    $iconClass = personGenderIconClass($geslacht);
    $color = personGenderColor($geslacht);
    $label = htmlspecialchars((string)$geslacht);

    return sprintf(
        '<span title="%s"><i class="%s me-1" style="color: %s;" aria-hidden="true"></i>%s</span>',
        $label,
        htmlspecialchars($iconClass),
        htmlspecialchars($color),
        $label
    );
}

function renderPersonGenderIconOnly(?string $geslacht): string
{
    if (!hasValue($geslacht)) {
        return '';
    }

    $iconClass = personGenderIconClass($geslacht);
    $color = personGenderColor($geslacht);
    $label = htmlspecialchars((string)$geslacht);

    return sprintf(
        '<span title="%s" aria-label="%s"><i class="%s" style="color: %s;" aria-hidden="true"></i></span>',
        $label,
        $label,
        htmlspecialchars($iconClass),
        htmlspecialchars($color)
    );
}

function renderContactIconLink(
    string $iconClass,
    ?string $href,
    string $title,
    bool $external = true
): void 
{
    if (!hasValue($href)) {
        return;
    }
    ?>
    <a
        href="<?= htmlspecialchars((string)$href) ?>"
        class="btn btn-outline-secondary btn-sm me-2 mb-2"
        <?= $external ? 'target="_blank" rel="noopener noreferrer"' : '' ?>
        title="<?= htmlspecialchars($title) ?>"
    >
        <i class="<?= htmlspecialchars($iconClass) ?>"></i>
    </a>
    <?php
}

// function formatDateValue(mixed $value): string
// {
//     if (!hasValue($value)) {
//         return '';
//     }

//     $stringValue = (string)$value;

//     // Keep YYYY-MM-DD readable without timezone noise
//     if (preg_match('/^\d{4}-\d{2}-\d{2}/', $stringValue) === 1) {
//         return substr($stringValue, 0, 10);
//     }

//     return $stringValue;
// }

// if (!function_exists('formatDateValue')) {
//     function formatDateValue(?string $date): string
//     {
//         if (!$date) {
//             return '';
//         }

//         try {
//             $dt = new DateTime($date);
//             return $dt->format('d-m-Y');
//         } catch (Exception) {
//             return $date;
//         }
//     }
// }

if (!function_exists('formatDateValue')) {
    function formatDateValue(?string $date): string
    {
        if ($date === null || trim($date) === '') {
            return '';
        }

        $date = trim($date);

        try {

            // YYYY
            if (preg_match('/^\d{4}$/', $date)) {
                return $date;
            }

            // YYYY-MM
            if (preg_match('/^\d{4}-\d{2}$/', $date)) {
                $dt = DateTime::createFromFormat('Y-m-d', $date . '-01');
                return $dt ? $dt->format('d-m-Y') : $date;
            }

            // YYYY-MM-DD
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                $dt = new DateTime($date);
                return $dt->format('d-m-Y');
            }

            // fallback (ISO timestamps etc.)
            $dt = new DateTime($date);
            return $dt->format('d-m-Y');

        } catch (Exception) {
            return $date;
        }
    }
}

function splitPersonFractieRows(array $fractieRows): array
{
    $currentRows = [];
    $previousRows = [];

    foreach ($fractieRows as $row) {
        $totEnMet = $row['tot_en_met'] ?? null;

        if ($totEnMet === null || $totEnMet === '') {
            $currentRows[] = $row;
        } else {
            $previousRows[] = $row;
        }
    }

    return [
        'huidig' => $currentRows,
        'vorig' => $previousRows,
    ];
}

function personFractieNaam(array $row): string
{
    if (!empty($row['fractie_naam_nl'])) {
        return (string)$row['fractie_naam_nl'];
    }

    if (!empty($row['fractie_naam_en'])) {
        return (string)$row['fractie_naam_en'];
    }

    if (!empty($row['fractie_afkorting'])) {
        return (string)$row['fractie_afkorting'];
    }

    return 'Onbekende fractie';
}

function renderPersonFractieTable(array $rows, string $title): void
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
                            <th>Fractie</th>
                            <th>Functie</th>
                            <th>Van</th>
                            <th>Tot en met</th>
                            <th class="text-center">Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $row): ?>
                            <?php $fractieNaam = personFractieNaam($row); ?>
                            <tr>
                                <td>
                                    <?= htmlspecialchars($fractieNaam) ?>
                                    <?php if (!empty($row['fractie_afkorting'])): ?>
                                        <span class="text-muted ms-1">(<?= htmlspecialchars((string)$row['fractie_afkorting']) ?>)</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars((string)($row['functie'] ?? '')) ?></td>
                                <td><?= htmlspecialchars(formatDateValue($row['van'] ?? null)) ?></td>
                                <td><?= htmlspecialchars(formatDateValue($row['tot_en_met'] ?? null)) ?></td>
                                <td class="text-center">
                                    <?php if (!empty($row['fractie_id'])): ?>
                                        <a
                                            href="fractiedetails.php?id=<?= urlencode((string)$row['fractie_id']) ?>"
                                            class="btn btn-sm btn-outline-primary"
                                            title="Bekijk fractie"
                                        >
                                            <i class="fa-solid fa-eye"></i>
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

function renderPersonFractieSections(array $fractieRows = []): void
{
    if (empty($fractieRows)) {
        return;
    }

    $split = splitPersonFractieRows($fractieRows);

    renderPersonFractieTable($split['huidig'], 'Huidige fractie');
    renderPersonFractieTable($split['vorig'], 'Vorige fracties');
}

function renderOnderwijsSection(array $onderwijsRows): void
{
    if (empty($onderwijsRows)) {
        return;
    }
    ?>
    <div class="card border-0 bg-light mt-4">
        <div class="card-header bg-transparent">
            <h5 class="mb-0">Onderwijs</h5>
        </div>
        <div class="card-body">
            <?php foreach ($onderwijsRows as $opleiding): ?>
                <?php
                $opleidingNaam = pickFirstValue($opleiding, ['OpleidingNl', 'opleiding_nl', 'opleiding', 'naam']);
                $instelling = pickFirstValue($opleiding, ['Instelling', 'instelling']);
                $plaats = pickFirstValue($opleiding, ['Plaats', 'plaats']);
                $van = pickFirstValue($opleiding, ['Van', 'van']);
                $totEnMet = pickFirstValue($opleiding, ['TotEnMet', 'tot_en_met', 'tot']);
                ?>
                <div class="card mb-3 shadow-sm">
                    <div class="card-body">
                        <?php renderValueRow('Opleiding', $opleidingNaam); ?>
                        <?php renderValueRow('Instelling', $instelling); ?>
                        <?php renderValueRow('Plaats', $plaats); ?>
                        <?php renderValueRow('Van', formatDateValue($van)); ?>
                        <?php renderValueRow('Tot en met', formatDateValue($totEnMet)); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
}

function renderLoopbaanSection(array $loopbaanRows = []): void
{
    if (empty($loopbaanRows)) {
        return;
    }
    ?>
    <div class="card border-0 bg-light mt-4">
        <div class="card-header bg-transparent">
            <h5 class="mb-0">Loopbaan</h5>
        </div>
        <div class="card-body">
            <?php foreach ($loopbaanRows as $loopbaan): ?>
                <?php
                $functie = pickFirstValue($loopbaan, ['functie', 'Functie']);
                $werkgever = pickFirstValue($loopbaan, ['werkgever', 'Werkgever']);
                $omschrijving = pickFirstValue($loopbaan, ['omschrijving_nl', 'OmschrijvingNl', 'omschrijving_en', 'OmschrijvingEn']);
                $plaats = pickFirstValue($loopbaan, ['plaats', 'Plaats']);
                $van = pickFirstValue($loopbaan, ['van', 'Van']);
                $totEnMet = pickFirstValue($loopbaan, ['tot_en_met', 'TotEnMet']);
                ?>
                <div class="card mb-3 shadow-sm">
                    <div class="card-body">
                        <?php renderValueRow('Functie', $functie); ?>
                        <?php renderValueRow('Werkgever', $werkgever); ?>
                        <?php renderValueRow('Omschrijving', $omschrijving); ?>
                        <?php renderValueRow('Plaats', $plaats); ?>
                        <?php renderValueRow('Van', (is_int($van) ? $van : formatDateValue($van))); ?>
                        <?php renderValueRow('Tot en met', (is_int($totEnMet) ? $totEnMet : formatDateValue($totEnMet))); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
}

function renderNevenfunctieSection(array $nevenfunctieRows = []): void
{
    if (empty($nevenfunctieRows)) {
        return;
    }
    ?>
    <div class="card border-0 bg-light mt-4">
        <div class="card-header bg-transparent">
            <h5 class="mb-0">Nevenfuncties</h5>
        </div>
        <div class="card-body">
            <?php foreach ($nevenfunctieRows as $nevenfunctie): ?>
                <?php
                $omschrijving = pickFirstValue($nevenfunctie, ['omschrijving', 'Omschrijving']);
                $periodeVan = pickFirstValue($nevenfunctie, ['periode_van', 'Periode_Van', 'periodeVan']);
                $periodeTotEnMet = pickFirstValue($nevenfunctie, ['periode_tot_en_met', 'Periode_Tot_En_Met', 'periodeTotEnMet']);
                $isActief = pickFirstValue($nevenfunctie, ['is_actief', 'Is_Actief', 'isActief']);
                $vergoedingSoort = pickFirstValue($nevenfunctie, ['vergoeding_soort', 'Vergoeding_Soort', 'vergoedingSoort']);
                $vergoedingToelichting = pickFirstValue($nevenfunctie, ['vergoeding_toelichting', 'Vergoeding_Toelichting', 'vergoedingToelichting']);

                $actiefTekst = null;
                if ($isActief !== null && $isActief !== '') {
                    $actiefTekst = ((int)$isActief === 1) ? 'Ja' : 'Nee';
                }
                ?>
                <div class="card mb-3 shadow-sm">
                    <div class="card-body">
                        <?php renderValueRow('Omschrijving', $omschrijving); ?>
                        <?php renderValueRow('Periode van', formatDateValue($periodeVan)); ?>
                        <?php renderValueRow('Periode tot en met', formatDateValue($periodeTotEnMet)); ?>
                        <?php renderValueRow('Actief', $actiefTekst); ?>
                        <?php renderValueRow('Vergoeding soort', $vergoedingSoort); ?>
                        <?php renderValueRow('Vergoeding toelichting', $vergoedingToelichting); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
}

/** social */
//function buildSocialUrl(string $soort, string $waarde): ?string
function buildContactUrl(string $soort, string $waarde): ?string
{
    $waarde = trim($waarde);

    if ($waarde === '') {
        return null;
    }
    $waarde = ltrim($waarde, '@');

    switch ($soort) {

        case 'E-mail':
            return 'mailto:' . $waarde;

        case 'Website':
            if (!preg_match('~^https?://~', $waarde)) {
                return 'https://' . $waarde;
            }
            return $waarde;

        case 'LinkedIn':
            return 'https://www.linkedin.com/in/' . $waarde;

        case 'Twitter':
        case 'X':
            return 'https://x.com/' . $waarde;

        case 'Instagram':
            return 'https://www.instagram.com/' . $waarde;

        case 'Facebook':
            return 'https://www.facebook.com/' . $waarde;

        case 'Bluesky':
            return 'https://bsky.app/profile/' . $waarde;

        default:
            return null;
    }
}

function contactIcon(string $soort): string
{
    return match ($soort) {
        'E-mail'   => 'fa-solid fa-envelope',
        'Website'  => 'fa-solid fa-globe',
        'LinkedIn' => 'fa-brands fa-linkedin',
        'Twitter', 'X' => 'fa-brands fa-x-twitter',
        'Instagram'=> 'fa-brands fa-instagram',
        'Facebook' => 'fa-brands fa-facebook',
        'Bluesky'  => 'fa-solid fa-cloud',
        default    => 'fa-solid fa-link',
    };
}

function renderContactSection(string $label, array $contactRows): void
{
    $html = "";
    if (empty($contactRows)) {
        //$html = "<div class=\"text-muted\">Geen contactinformatie beschikbaar.</div>";
        $html = "";
        return;
    } else {
        foreach ($contactRows as $contact)
        {
            $soort = $contact['soort'] ?? '';
            $waarde = $contact['waarde'] ?? '';

            $href = buildContactUrl($soort, $waarde);
            $icon = contactIcon($soort);

            if (!$href) {
                continue;
            }
            $href = htmlspecialchars($href);
            $soort = htmlspecialchars($soort);
            $html .= "<a
                        href=\"{$href}\"
                        class=\"btn btn-outline-secondary btn-sm me-2 mb-2\"
                        target=\"_blank\"
                        title=\"{$soort}\"
                    >
                        <i class=\"{$icon}\"></i>
                    </a>
            ";
        }
        ?>
        <div class="row py-2 border-bottom">
            <div class="col-sm-4 fw-semibold"><?= htmlspecialchars($label) ?></div>
            <div class="col-sm-8"><?= $html; ?></div>
        </div>
        <?php
    }
}

function groupRowsByField(array $rows, string $field): array
{
    $grouped = [];

    foreach ($rows as $row) {
        $key = $row[$field] ?? null;

        if ($key === null || $key === '') {
            continue;
        }

        $grouped[(string)$key][] = $row;
    }

    return $grouped;
}

function formatIncomeAmount(array $income): string
{
    /**
     * todo replace with generic number formatting 
     */
    $parts = [];

    $voorvoegsel = $income['bedrag_voorvoegsel'] ?? '';
    $valuta = $income['bedrag_valuta'] ?? '';
    $bedrag = $income['bedrag'] ?? '';
    $achtervoegsel = $income['bedrag_achtervoegsel'] ?? '';

    if (hasValue($voorvoegsel)) {
        $parts[] = (string)$voorvoegsel;
    }

    if (hasValue($valuta)) {
        $parts[] = (string)$valuta;
    }

    if (hasValue($bedrag)) {
        $parts[] = (string)$bedrag;
    }

    if (hasValue($achtervoegsel)) {
        $parts[] = (string)$achtervoegsel;
    }

    return trim(implode(' ', $parts));
}

function calculateIncomeSubtotal(array $incomeRows): ?string
{
    if (empty($incomeRows)) {
        return null;
    }

    $sum = 0.0;
    $count = 0;

    $firstPrefix = null;
    $firstCurrency = null;
    $firstSuffix = null;

    foreach ($incomeRows as $index => $row) {
        $amount = $row['bedrag'] ?? null;

        if (!is_numeric($amount)) {
            return null;
        }

        $prefix = (string)($row['bedrag_voorvoegsel'] ?? '');
        $currency = (string)($row['bedrag_valuta'] ?? '');
        $suffix = (string)($row['bedrag_achtervoegsel'] ?? '');

        if ($index === 0) {
            $firstPrefix = $prefix;
            $firstCurrency = $currency;
            $firstSuffix = $suffix;
        } else {
            if ($prefix !== $firstPrefix || $currency !== $firstCurrency || $suffix !== $firstSuffix) {
                return null;
            }
        }

        $sum += (float)$amount;
        $count++;
    }

    if ($count === 0) {
        return null;
    }

    $formattedSum = rtrim(rtrim(number_format($sum, 2, '.', ''), '0'), '.');

    return trim(implode(' ', array_filter([
        $firstPrefix,
        $firstCurrency,
        $formattedSum,
        $firstSuffix,
    ], static fn($v) => $v !== null && $v !== '')));
}

function renderNevenfunctieOverviewSection(
    array $nevenfunctieRows = [],
    array $nevenfunctieInkomstenRows = []
): void {
    if (empty($nevenfunctieRows)) {
        return;
    }

    $incomesByNevenfunctieId = groupRowsByField($nevenfunctieInkomstenRows, 'nevenfunctie_id');
    ?>
    <div class="card border-0 bg-light mt-4">
        <div class="card-header bg-transparent">
            <h5 class="mb-0">Nevenfuncties & inkomsten</h5>
        </div>
        <div class="card-body">
            <?php foreach ($nevenfunctieRows as $nevenfunctie): ?>
                <?php
                $nevenfunctieId = (string)($nevenfunctie['id'] ?? '');
                $omschrijving = pickFirstValue($nevenfunctie, ['omschrijving', 'Omschrijving']);
                $periodeVan = pickFirstValue($nevenfunctie, ['periode_van', 'Periode_Van']);
                $periodeTotEnMet = pickFirstValue($nevenfunctie, ['periode_tot_en_met', 'Periode_Tot_En_Met']);
                $isActief = pickFirstValue($nevenfunctie, ['is_actief', 'Is_Actief']);
                $vergoedingSoort = pickFirstValue($nevenfunctie, ['vergoeding_soort', 'Vergoeding_Soort']);
                $vergoedingToelichting = pickFirstValue($nevenfunctie, ['vergoeding_toelichting', 'Vergoeding_Toelichting']);

                $actiefTekst = null;
                if ($isActief !== null && $isActief !== '') {
                    $actiefTekst = ((int)$isActief === 1) ? 'Ja' : 'Nee';
                }

                $incomeRows = $incomesByNevenfunctieId[$nevenfunctieId] ?? [];
                $subtotal = calculateIncomeSubtotal($incomeRows);
                ?>
                <div class="card mb-3 shadow-sm">
                    <div class="card-body">
                        <?php renderValueRow('Omschrijving', $omschrijving); ?>
                        <?php renderValueRow('Periode van', formatDateValue($periodeVan)); ?>
                        <?php renderValueRow('Periode tot en met', formatDateValue($periodeTotEnMet)); ?>
                        <?php renderValueRow('Actief', $actiefTekst); ?>
                        <?php renderValueRow('Vergoeding soort', $vergoedingSoort); ?>
                        <?php renderValueRow('Vergoeding toelichting', $vergoedingToelichting); ?>

                        <?php if (!empty($incomeRows)): ?>
                            <div class="mt-4">
                                <h6>Inkomsten</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-striped align-middle">
                                        <thead>
                                            <tr>
                                                <th>Jaar</th>
                                                <th>Bedrag</th>
                                                <th>Frequentie</th>
                                                <th>Opmerking</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($incomeRows as $income): ?>
                                                <?php
                                                $jaar = $income['jaar'] ?? null;
                                                $bedragDisplay = formatIncomeAmount($income);
                                                $frequentie = pickFirstValue($income, ['frequentie_beschrijving', 'frequentie', 'FrequentieBeschrijving', 'Frequentie']);
                                                $opmerking = pickFirstValue($income, ['opmerking', 'Opmerking']);
                                                ?>
                                                <tr>
                                                    <td><?= htmlspecialchars((string)$jaar) ?></td>
                                                    <td><?= htmlspecialchars($bedragDisplay) ?></td>
                                                    <td><?= htmlspecialchars((string)$frequentie) ?></td>
                                                    <td><?= htmlspecialchars((string)$opmerking) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <?php if ($subtotal !== null): ?>
                                    <div class="text-end fw-semibold">
                                        Totaal bekende bedragen: <?= htmlspecialchars($subtotal) ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-end text-muted small">
                                        Geen totaal berekend vanwege verschillende valuta of bedragnotaties.
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="mt-3 text-muted">Geen inkomsten geregistreerd.</div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
}

function personVoteBadge(string $soort): string
{
    $normalized = trim(mb_strtolower($soort));

    return match ($normalized) {
        'voor' => '<span class="text-success"><i class="fa-solid fa-check"></i> Voor</span>',
        'tegen' => '<span class="text-danger"><i class="fa-solid fa-xmark"></i> Tegen</span>',
        'niet deelgenomen' => '<span class="text-muted"><i class="fa-solid fa-minus"></i> Niet deelgenomen</span>',
        default => '<span class="text-muted">' . htmlspecialchars($soort) . '</span>',
    };
}

function personVoteFractieNaam(array $row): string
{
    if (!empty($row['fractie_afkorting'])) {
        return (string)$row['fractie_afkorting'];
    }

    if (!empty($row['fractie_naam_nl'])) {
        return (string)$row['fractie_naam_nl'];
    }

    if (!empty($row['fractie_naam_en'])) {
        return (string)$row['fractie_naam_en'];
    }

    return '';
}

function renderPersonBesluitenSection(array $besluitStemRows = []): void
{
    if (empty($besluitStemRows)) {
        return;
    }
    ?>
    <div class="card border-0 bg-light mt-4">
        <div class="card-header bg-transparent">
            <h5 class="mb-0">Besluiten / stemgedrag</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Datum</th>
                            <th>Activiteit</th>
                            <th>Agendapunt</th>
                            <th>Besluit</th>
                            <th>Stem</th>
                            <th>Fractie</th>
                            <th class="text-center">Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($besluitStemRows as $row): ?>
                            <?php
                            $activiteitLabel = trim(implode(' ', array_filter([
                                $row['activiteit_nummer'] ?? null,
                                $row['activiteit_onderwerp'] ?? null,
                            ])));

                            $agendapuntLabel = trim(implode(' ', array_filter([
                                $row['agendapunt_nummer'] ?? null,
                                $row['agendapunt_onderwerp'] ?? null,
                            ])));

                            $besluitLabel = (string)($row['besluittekst'] ?? $row['besluit_soort'] ?? '');
                            $fractieNaam = personVoteFractieNaam($row);
                            ?>
                            <tr>
                                <td><?= htmlspecialchars(formatDateValue($row['activiteit_datum'] ?? null)) ?></td>
                                <td><?= htmlspecialchars($activiteitLabel) ?></td>
                                <td><?= htmlspecialchars($agendapuntLabel) ?></td>
                                <td><?= htmlspecialchars($besluitLabel) ?></td>
                                <td><?= personVoteBadge((string)($row['stem_soort'] ?? '')) ?></td>
                                <td><?= htmlspecialchars($fractieNaam) ?></td>
                                <td class="text-center">
                                    <?php if (!empty($row['besluit_id'])): ?>
                                        <a
                                            href="besluitdetails.php?id=<?= urlencode((string)$row['besluit_id']) ?>"
                                            class="btn btn-sm btn-outline-primary"
                                            title="Bekijk besluit"
                                        >
                                            <i class="fa-solid fa-eye"></i>
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

function renderPersonfullname(array $values) : string
{
    $parts = array_filter([
        $values['titel'] ?? null,
        (array_key_exists('roepnaam', $values) ? $values['roepnaam'] : (array_key_exists('initialen', $values) ? $values['initialen'] : (array_key_exists('voornamen', $values) ? $values['voornamen'] :  "") ) )  ?? null,
        $values['tussenvoegsel'] ?? null,
        $values['achternaam'] ?? null,
    ], static fn($value) => $value !== null && $value !== '');

    return !empty($parts) ? implode(' ', $parts) : 'Persoon';    
}

function renderPersonDetails(
    array $person,
    array $contactRows = [],
    array $onderwijsRows = [],
    array $loopbaanRows = [],
    array $nevenfunctieRows = [],
    array $nevenfunctieInkomstenRows = [],
    array $besluitStemRows = [],
    array $fractieRows = []    
): void
{
    $displayName = renderPersonfullname($person);
    $fotoUrl = pickFirstValue($person, ['foto_url', 'FotoUrl', 'Foto', 'foto']);
    $photoUrl = hasValue($fotoUrl) ? normalizeUrl((string)$fotoUrl) : null;

    $roepnaam = pickFirstValue($person, ['roepnaam', 'Roepnaam']);
    $voornamen = pickFirstValue($person, ['voornamen', 'Voornamen']);
    $tussenvoegsel = pickFirstValue($person, ['tussenvoegsel', 'Tussenvoegsel']);
    $achternaam = pickFirstValue($person, ['achternaam', 'Achternaam']);
    $titel = pickFirstValue($person, ['titel', 'Titel']);
    $functie = pickFirstValue($person, ['functie', 'Functie']);


    // $displayNameParts = array_filter([
    //     hasValue($titel) ? (string)$titel : null,
    //     hasValue($roepnaam) ? (string)$roepnaam : (hasValue($voornamen) ? (string)$voornamen : null),
        
    //     hasValue($achternaam) ? (string)$achternaam : null,
    // ]);

    // $displayName = !empty($displayNameParts) ? implode(' ', $displayNameParts) : 'Persoon';
    $subTitle = hasValue($functie) ? (string)$functie : null;


    ?>
    <div class="card shadow-sm">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <h2 class="mb-1"><?= htmlspecialchars($displayName) ?></h2>
                    <?php if (hasValue($subTitle)): ?>
                        <div class="text-muted"><?= htmlspecialchars((string)$subTitle) ?></div>
                    <?php endif; ?>
                </div>

                <div>
                    <a href="index.php?tab=person" class="btn btn-outline-primary">
                        <i class="fa-solid fa-arrow-left"></i> Terug naar overzicht
                    </a>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="row g-4">
                <div class="col-md-4 col-lg-3">
                    <div class="text-center">
                        <?php if ($photoUrl): ?>
                            <img
                                src="<?= htmlspecialchars($photoUrl) ?>"
                                alt="Foto van <?= htmlspecialchars($displayName) ?>"
                                class="img-fluid rounded shadow-sm border"
                                style="max-height: 320px; object-fit: cover;"
                            >
                        <?php else: ?>
                            <div
                                class="border rounded bg-light d-flex align-items-center justify-content-center shadow-sm"
                                style="height: 320px;"
                            >
                                <div class="text-muted text-center">
                                    <i class="fa-solid fa-user fa-3x mb-3"></i>
                                    <div>Geen foto beschikbaar</div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-md-8 col-lg-9">
                    <div class="card border-0 bg-light">
                        <div class="card-header bg-transparent">
                            <h5 class="mb-0">Persoongegevens</h5>
                        </div>
                        <div class="card-body">
                            <?php renderValueRow('Nummer', pickFirstValue($person, ['nummer', 'Nummer'])); ?>
                            <?php renderValueRow('Roepnaam', $roepnaam); ?>
                            <?php renderValueRow('Voornamen', $voornamen); ?>
                            <?php renderValueRow('Tussenvoegsel', $tussenvoegsel); ?>
                            <?php renderValueRow('Achternaam', $achternaam); ?>
                            <?php
                            $geslacht = pickFirstValue($person, ['geslacht', 'Geslacht']);
                            if (hasValue($geslacht)):
                            ?>
                                <div class="row py-2 border-bottom">
                                    <div class="col-sm-4 fw-semibold">Geslacht</div>
                                    <div class="col-sm-8"><?= renderPersonGender((string)$geslacht) ?></div>
                                </div>
                            <?php endif; ?>
                            <?php renderValueRow('Geboortedatum', formatDateValue(pickFirstValue($person, ['geboortedatum', 'Geboortedatum']))); ?>
                            <?php renderValueRow('Geboorteplaats', pickFirstValue($person, ['geboorteplaats', 'Geboorteplaats'])); ?>
                            <?php renderValueRow('Overlijdensdatum', formatDateValue(pickFirstValue($person, ['overlijdensdatum', 'Overlijdensdatum']))); ?>
                            <?php renderValueRow('Overlijdensplaats', pickFirstValue($person, ['overlijdensplaats', 'Overlijdensplaats'])); ?>
                            <?php renderValueRow('Titel', $titel); ?>
                            <?php renderValueRow('Functie', $functie); ?>
                            <?php renderValueRow('Partij', pickFirstValue($person, ['partij', 'Partij'])); ?>
                            <?php renderValueRow('Biografie', pickFirstValue($person, ['biografie', 'Biografie'])); ?>

                            <?php renderContactSection('Contact & Social Media', $contactRows); ?>
                        </div>
                    </div>
                </div>
            </div>


            <?php 
            renderPersonFractieSections($fractieRows);
            
            renderOnderwijsSection($onderwijsRows);
            renderLoopbaanSection($loopbaanRows);
            renderNevenfunctieOverviewSection($nevenfunctieRows, $nevenfunctieInkomstenRows); 
            renderPersonBesluitenSection($besluitStemRows);
            ?>
        </div>
    </div>
    <?php
}

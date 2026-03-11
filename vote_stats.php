<?php
declare(strict_types=1);

require_once __DIR__ . '/app/config/Database.php';

use App\Config\Database;


$pdo = Database::createConnection();

function fetchAllAssoc(PDO $pdo, string $sql): array
{
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$personSelect = "
    SELECT
        p.id,
        p.roepnaam,
        p.achternaam,
        ps.totaal_nevenfuncties,
        ps.totaal_opleidingen,
        ps.totaal_loopbanen,
        ps.totaal_stemmen,
        ps.totaal_voor_stemmen,
        ps.totaal_tegen_stemmen,
        ps.totaal_anders_stemmen,
        ps.percentage_voor,
        ps.percentage_tegen
    FROM persoon_stats ps
    INNER JOIN persoon p
        ON p.id = ps.persoon_id
    WHERE (p.is_verwijderd = 0 OR p.is_verwijderd IS NULL)
";

$personQueries = [
    'Most votes' => $personSelect . " ORDER BY ps.totaal_stemmen DESC, p.achternaam ASC, p.roepnaam ASC LIMIT 5",
    'Most voor votes' => $personSelect . " ORDER BY ps.totaal_voor_stemmen DESC, p.achternaam ASC, p.roepnaam ASC LIMIT 5",
    'Most tegen votes' => $personSelect . " ORDER BY ps.totaal_tegen_stemmen DESC, p.achternaam ASC, p.roepnaam ASC LIMIT 5",
    'Most anders votes' => $personSelect . " ORDER BY ps.totaal_anders_stemmen DESC, p.achternaam ASC, p.roepnaam ASC LIMIT 5",
    'Best % voor' => $personSelect . " AND ps.percentage_voor IS NOT NULL ORDER BY ps.percentage_voor DESC, ps.totaal_voor_stemmen DESC, p.achternaam ASC, p.roepnaam ASC LIMIT 5",
    'Best % tegen' => $personSelect . " AND ps.percentage_tegen IS NOT NULL ORDER BY ps.percentage_tegen DESC, ps.totaal_tegen_stemmen DESC, p.achternaam ASC, p.roepnaam ASC LIMIT 5",
];

$fractieQueries = [
    'Most votes' => "
        SELECT
            f.*,
            s.totaal_stemmen,
            s.voor_stemmen,
            s.tegen_stemmen,
            s.anders_stemmen,
            s.voor_percentage,
            s.tegen_percentage
        FROM fractie_stem_stats s
        INNER JOIN fractie f
            ON f.id = s.fractie_id
        WHERE (f.is_verwijderd = 0 OR f.is_verwijderd IS NULL)
        ORDER BY s.totaal_stemmen DESC, f.naam_nl ASC, f.naam_en ASC
        LIMIT 5
    ",
    'Most voor votes' => "
        SELECT
            f.*,
            s.totaal_stemmen,
            s.voor_stemmen,
            s.tegen_stemmen,
            s.anders_stemmen,
            s.voor_percentage,
            s.tegen_percentage
        FROM fractie_stem_stats s
        INNER JOIN fractie f
            ON f.id = s.fractie_id
        WHERE (f.is_verwijderd = 0 OR f.is_verwijderd IS NULL)
        ORDER BY s.voor_stemmen DESC, f.naam_nl ASC, f.naam_en ASC
        LIMIT 5
    ",
    'Most tegen votes' => "
        SELECT
            f.*,
            s.totaal_stemmen,
            s.voor_stemmen,
            s.tegen_stemmen,
            s.anders_stemmen,
            s.voor_percentage,
            s.tegen_percentage
        FROM fractie_stem_stats s
        INNER JOIN fractie f
            ON f.id = s.fractie_id
        WHERE (f.is_verwijderd = 0 OR f.is_verwijderd IS NULL)
        ORDER BY s.tegen_stemmen DESC, f.naam_nl ASC, f.naam_en ASC
        LIMIT 5
    ",
    'Most anders votes' => "
        SELECT
            f.*,
            s.totaal_stemmen,
            s.voor_stemmen,
            s.tegen_stemmen,
            s.anders_stemmen,
            s.voor_percentage,
            s.tegen_percentage
        FROM fractie_stem_stats s
        INNER JOIN fractie f
            ON f.id = s.fractie_id
        WHERE (f.is_verwijderd = 0 OR f.is_verwijderd IS NULL)
        ORDER BY s.anders_stemmen DESC, f.naam_nl ASC, f.naam_en ASC
        LIMIT 5
    ",
    'Best % voor' => "
        SELECT
            f.*,
            s.totaal_stemmen,
            s.voor_stemmen,
            s.tegen_stemmen,
            s.anders_stemmen,
            s.voor_percentage,
            s.tegen_percentage
        FROM fractie_stem_stats s
        INNER JOIN fractie f
            ON f.id = s.fractie_id
        WHERE (f.is_verwijderd = 0 OR f.is_verwijderd IS NULL)
          AND s.voor_percentage IS NOT NULL
        ORDER BY s.voor_percentage DESC, s.voor_stemmen DESC, f.naam_nl ASC, f.naam_en ASC
        LIMIT 5
    ",
    'Best % tegen' => "
        SELECT
            f.*,
            s.totaal_stemmen,
            s.voor_stemmen,
            s.tegen_stemmen,
            s.anders_stemmen,
            s.voor_percentage,
            s.tegen_percentage
        FROM fractie_stem_stats s
        INNER JOIN fractie f
            ON f.id = s.fractie_id
        WHERE (f.is_verwijderd = 0 OR f.is_verwijderd IS NULL)
          AND s.tegen_percentage IS NOT NULL
        ORDER BY s.tegen_percentage DESC, s.tegen_stemmen DESC, f.naam_nl ASC, f.naam_en ASC
        LIMIT 5
    ",
];

$personResults = [];
foreach ($personQueries as $label => $sql) {
    $personResults[$label] = fetchAllAssoc($pdo, $sql);
}

$fractieResults = [];
foreach ($fractieQueries as $label => $sql) {
    $fractieResults[$label] = fetchAllAssoc($pdo, $sql);
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stemstatistieken</title>
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>
<body class="bg-light">
<div class="container py-4">
    <h1 class="mb-4">Stemstatistieken</h1>

    <ul class="nav nav-tabs mb-3" id="statsTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button
                class="nav-link active"
                data-bs-toggle="tab"
                data-bs-target="#personen-pane"
                type="button"
            >
                Personen
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button
                class="nav-link"
                data-bs-toggle="tab"
                data-bs-target="#fracties-pane"
                type="button"
            >
                Fracties
            </button>
        </li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade show active" id="personen-pane">
            <?php foreach ($personResults as $title => $rows): ?>
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><?= htmlspecialchars($title) ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Naam</th>
                                        <th class="text-end">Totaal</th>
                                        <th class="text-end">Voor</th>
                                        <th class="text-end">Tegen</th>
                                        <th class="text-end">Anders</th>
                                        <th class="text-end">% Voor</th>
                                        <th class="text-end">% Tegen</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($rows as $row): ?>
                                        <tr>
                                            <td>
                                                <a href="persondetails.php?id=<?= urlencode((string)$row['id']) ?>">
                                                    <?= htmlspecialchars(trim(($row['roepnaam'] ?? '') . ' ' . ($row['achternaam'] ?? ''))) ?>
                                                </a>
                                            </td>
                                            <td class="text-end"><?= htmlspecialchars((string)$row['totaal_stemmen']) ?></td>
                                            <td class="text-end"><?= htmlspecialchars((string)$row['totaal_voor_stemmen']) ?></td>
                                            <td class="text-end"><?= htmlspecialchars((string)$row['totaal_tegen_stemmen']) ?></td>
                                            <td class="text-end"><?= htmlspecialchars((string)$row['totaal_anders_stemmen']) ?></td>
                                            <td class="text-end"><?= htmlspecialchars((string)$row['percentage_voor']) ?></td>
                                            <td class="text-end"><?= htmlspecialchars((string)$row['percentage_tegen']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="tab-pane fade" id="fracties-pane">
            <?php foreach ($fractieResults as $title => $rows): ?>
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><?= htmlspecialchars($title) ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Naam</th>
                                        <th class="text-end">Totaal</th>
                                        <th class="text-end">Voor</th>
                                        <th class="text-end">Tegen</th>
                                        <th class="text-end">Anders</th>
                                        <th class="text-end">% Voor</th>
                                        <th class="text-end">% Tegen</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($rows as $row): ?>
                                        <?php
                                        $fractieName = $row['naam_nl'] ?: ($row['naam_en'] ?: ($row['afkorting'] ?? ''));
                                        ?>
                                        <tr>
                                            <td>
                                                <a href="fractiedetails.php?id=<?= urlencode((string)$row['id']) ?>">
                                                    <?= htmlspecialchars((string)$fractieName) ?>
                                                </a>
                                            </td>
                                            <td class="text-end"><?= htmlspecialchars((string)$row['totaal_stemmen']) ?></td>
                                            <td class="text-end"><?= htmlspecialchars((string)$row['voor_stemmen']) ?></td>
                                            <td class="text-end"><?= htmlspecialchars((string)$row['tegen_stemmen']) ?></td>
                                            <td class="text-end"><?= htmlspecialchars((string)$row['anders_stemmen']) ?></td>
                                            <td class="text-end"><?= htmlspecialchars((string)$row['voor_percentage']) ?></td>
                                            <td class="text-end"><?= htmlspecialchars((string)$row['tegen_percentage']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
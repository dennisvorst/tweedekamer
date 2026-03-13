<?php
declare(strict_types=1);

require_once __DIR__ . '/../helpers/list_helpers.php';
?>

<div class="card shadow-sm">
    <div class="card-header">
        <h5 class="mb-0">Zaak List</h5>
    </div>

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


    </div>
</div>
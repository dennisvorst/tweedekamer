<?php
declare(strict_types=1);

require_once __DIR__ . '/../helpers/list_helpers.php';
?>

<div class="card shadow-sm">
    <div class="card-header">
        <h5 class="mb-0">Activiteit List</h5>
    </div>

    <div class="card-body">
        <form method="get" class="row g-3 mb-4">
            <input type="hidden" name="tab" value="activiteit">
            <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
            <input type="hidden" name="direction" value="<?= htmlspecialchars($direction) ?>">

            <div class="col-md-2">
                <label for="soort" class="form-label">Soort</label>
                <select class="form-select" id="soort" name="soort">
                    <option value="">Alle</option>
                    <?php foreach (($activiteitSoorten ?? []) as $soortOption): ?>
                        <option
                            value="<?= htmlspecialchars((string)$soortOption) ?>"
                            <?= (($filters['soort'] ?? '') === (string)$soortOption) ? 'selected' : '' ?>
                        >
                            <?= htmlspecialchars((string)$soortOption) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2">
                <label for="nummer" class="form-label">Nummer</label>
                <input
                    type="text"
                    class="form-control"
                    id="nummer"
                    name="nummer"
                    value="<?= htmlspecialchars((string)($filters['nummer'] ?? '')) ?>"
                >
            </div>

            <div class="col-md-4">
                <label for="onderwerp" class="form-label">Onderwerp</label>
                <input
                    type="text"
                    class="form-control"
                    id="onderwerp"
                    name="onderwerp"
                    value="<?= htmlspecialchars((string)($filters['onderwerp'] ?? '')) ?>"
                >
            </div>

            <div class="col-md-2">
                <label for="datum" class="form-label">Datum</label>
                <input
                    type="date"
                    class="form-control"
                    id="datum"
                    name="datum"
                    value="<?= htmlspecialchars(formatDateForInput($filters['datum'] ?? null)) ?>"
                >
            </div>

            <div class="col-md-2">
                <label for="locatie" class="form-label">Locatie</label>
                <input
                    type="text"
                    class="form-control"
                    id="locatie"
                    name="locatie"
                    value="<?= htmlspecialchars((string)($filters['locatie'] ?? '')) ?>"
                >
            </div>

            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-filter"></i> Filter
                </button>
                <a href="index.php?tab=activiteit&reset=1" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-rotate-left"></i> Reset
                </a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>
                            <a class="text-white text-decoration-none" href="<?= sortLink('soort', $sort, $direction) ?>">
                                Soort<?= sortIcon('soort', $sort, $direction) ?>
                            </a>
                        </th>
                        <th>
                            <a class="text-white text-decoration-none" href="<?= sortLink('nummer', $sort, $direction) ?>">
                                Nummer<?= sortIcon('nummer', $sort, $direction) ?>
                            </a>
                        </th>
                        <th>
                            <a class="text-white text-decoration-none" href="<?= sortLink('onderwerp', $sort, $direction) ?>">
                                Onderwerp<?= sortIcon('onderwerp', $sort, $direction) ?>
                            </a>
                        </th>
                        <th>
                            <a class="text-white text-decoration-none" href="<?= sortLink('datum', $sort, $direction) ?>">
                                Datum<?= sortIcon('datum', $sort, $direction) ?>
                            </a>
                        </th>
                        <th class="text-end">
                            <a class="text-white text-decoration-none" href="<?= sortLink('aanvangstijd', $sort, $direction) ?>">
                                Aanvang<?= sortIcon('aanvangstijd', $sort, $direction) ?>
                            </a>
                        </th>
                        <th class="text-end">
                            <a class="text-white text-decoration-none" href="<?= sortLink('eindtijd', $sort, $direction) ?>">
                                Einde<?= sortIcon('eindtijd', $sort, $direction) ?>
                            </a>
                        </th>
                        <th>
                            <a class="text-white text-decoration-none" href="<?= sortLink('locatie', $sort, $direction) ?>">
                                Locatie<?= sortIcon('locatie', $sort, $direction) ?>
                            </a>
                        </th>
                        <th class="text-center">Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($activiteiten)): ?>
                        <?php foreach ($activiteiten as $activiteit): ?>
                            <tr>
                                <td><?= htmlspecialchars((string)($activiteit['soort'] ?? '')) ?></td>
                                <td><?= htmlspecialchars((string)($activiteit['nummer'] ?? '')) ?></td>
                                <td><?= htmlspecialchars((string)($activiteit['onderwerp'] ?? '')) ?></td>
                                <td><?= htmlspecialchars(formatDate($activiteit['datum'] ?? null)) ?></td>
                                <td class="text-end text-nowrap"><?= htmlspecialchars(formatTime($activiteit['aanvangstijd'] ?? null)) ?></td>
                                <td class="text-end text-nowrap"><?= htmlspecialchars(formatTime($activiteit['eindtijd'] ?? null)) ?></td>
                                <td><?= htmlspecialchars((string)($activiteit['locatie'] ?? '')) ?></td>
                                <td class="text-center">
                                    <a
                                        href="activiteitdetails.php?id=<?= urlencode((string)$activiteit['id']) ?>"
                                        class="btn btn-sm btn-outline-primary"
                                        title="Bekijk details"
                                    >
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted">No activiteiten found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php renderPagination($currentPage, $totalPages, 25, 'Activiteit pagination'); ?>

        <div class="mt-3 text-muted small">
            Showing <?= count($activiteiten) ?> of <?= (int)$total ?> activiteiten
        </div>
    </div>
</div>
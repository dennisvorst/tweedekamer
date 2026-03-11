<?php
declare(strict_types=1);

require_once __DIR__ . '/../helpers/list_helpers.php';
?>

<div class="card shadow-sm">
    <div class="card-header">
        <h5 class="mb-0">Fractie List</h5>
    </div>

    <div class="card-body">
        <form method="get" class="row g-3 mb-4">
            <input type="hidden" name="tab" value="fractie">
            <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
            <input type="hidden" name="direction" value="<?= htmlspecialchars($direction) ?>">

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

            <div class="col-md-2">
                <label for="afkorting" class="form-label">Afkorting</label>
                <input
                    type="text"
                    class="form-control"
                    id="afkorting"
                    name="afkorting"
                    value="<?= htmlspecialchars((string)($filters['afkorting'] ?? '')) ?>"
                >
            </div>

            <div class="col-md-3">
                <label for="naam_nl" class="form-label">Naam NL</label>
                <input
                    type="text"
                    class="form-control"
                    id="naam_nl"
                    name="naam_nl"
                    value="<?= htmlspecialchars((string)($filters['naam_nl'] ?? '')) ?>"
                >
            </div>

            <div class="col-md-3">
                <label for="naam_en" class="form-label">Naam EN</label>
                <input
                    type="text"
                    class="form-control"
                    id="naam_en"
                    name="naam_en"
                    value="<?= htmlspecialchars((string)($filters['naam_en'] ?? '')) ?>"
                >
            </div>

            <div class="col-md-2">
                <label for="aantal_zetels" class="form-label">Zetels</label>
                <input
                    type="number"
                    class="form-control"
                    id="aantal_zetels"
                    name="aantal_zetels"
                    value="<?= htmlspecialchars((string)($filters['aantal_zetels'] ?? '')) ?>"
                >
            </div>

            <div class="col-md-2">
                <label for="aantal_stemmen" class="form-label">Stemmen</label>
                <input
                    type="number"
                    class="form-control"
                    id="aantal_stemmen"
                    name="aantal_stemmen"
                    value="<?= htmlspecialchars(formatDate($fractie['datum_actief'] ?? null)) ?>"
                >
            </div>

            <div class="col-md-2">
                <label for="datum_actief" class="form-label">Datum actief</label>
                <input
                    type="date"
                    class="form-control"
                    id="datum_actief"
                    name="datum_actief"
                    value="<?= htmlspecialchars(formatDateForInput($fractie['datum_actief'] ?? null)) ?>"
                >
            </div>

            <div class="col-md-2">
                <label for="datum_inactief" class="form-label">Datum inactief</label>
                <input
                    type="date"
                    class="form-control"
                    id="datum_inactief"
                    name="datum_inactief"
                    value="<?= htmlspecialchars(formatDateForInput($filters['datum_inactief'] ?? '')) ?>"
                >
            </div>

            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-filter"></i> Filter
                </button>
                <a href="index.php?tab=fractie&reset=1" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-rotate-left"></i> Reset
                </a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>
                            <a class="text-white text-decoration-none" href="<?= sortLink('nummer', $sort, $direction) ?>">
                                Nummer<?= sortIcon('nummer', $sort, $direction) ?>
                            </a>
                        </th>
                        <th>
                            <a class="text-white text-decoration-none" href="<?= sortLink('afkorting', $sort, $direction) ?>">
                                Afkorting<?= sortIcon('afkorting', $sort, $direction) ?>
                            </a>
                        </th>
                        <th>
                            <a class="text-white text-decoration-none" href="<?= sortLink('naam_nl', $sort, $direction) ?>">
                                Naam NL<?= sortIcon('naam_nl', $sort, $direction) ?>
                            </a>
                        </th>
                        <th>
                            <a class="text-white text-decoration-none" href="<?= sortLink('naam_en', $sort, $direction) ?>">
                                Naam EN<?= sortIcon('naam_en', $sort, $direction) ?>
                            </a>
                        </th>
                        <th>
                            <a class="text-white text-decoration-none" href="<?= sortLink('aantal_zetels', $sort, $direction) ?>">
                                Zetels<?= sortIcon('aantal_zetels', $sort, $direction) ?>
                            </a>
                        </th>
                        <th>
                            <a class="text-white text-decoration-none" href="<?= sortLink('aantal_stemmen', $sort, $direction) ?>">
                                Stemmen<?= sortIcon('aantal_stemmen', $sort, $direction) ?>
                            </a>
                        </th>
                        <th>
                            <a class="text-white text-decoration-none" href="<?= sortLink('datum_actief', $sort, $direction) ?>">
                                Actief vanaf<?= sortIcon('datum_actief', $sort, $direction) ?>
                            </a>
                        </th>
                        <th>
                            <a class="text-white text-decoration-none" href="<?= sortLink('datum_inactief', $sort, $direction) ?>">
                                Inactief vanaf<?= sortIcon('datum_inactief', $sort, $direction) ?>
                            </a>
                        </th>
                        <th class="text-center">Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($fracties)): ?>
                        <?php foreach ($fracties as $fractie): ?>
                            <tr>
                                <td><?= htmlspecialchars((string)($fractie['nummer'] ?? '')) ?></td>
                                <td><?= htmlspecialchars((string)($fractie['afkorting'] ?? '')) ?></td>
                                <td><?= htmlspecialchars((string)($fractie['naam_nl'] ?? '')) ?></td>
                                <td><?= htmlspecialchars((string)($fractie['naam_en'] ?? '')) ?></td>
                                <td class="text-end"><?= htmlspecialchars(formatNumber($fractie['aantal_zetels'] ?? '')) ?></td>
                                <td class="text-end"><?= htmlspecialchars(formatNumber($fractie['aantal_stemmen'] ?? '')) ?></td>
                                <td><?= htmlspecialchars(formatDate($fractie['datum_actief'] ?? '')) ?></td>
                                <td><?= htmlspecialchars(formatDate($fractie['datum_inactief'] ?? '')) ?></td>
                                <td class="text-center">
                                    <a
                                        href="fractiedetails.php?id=<?= urlencode((string)$fractie['id']) ?>"
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
                            <td colspan="9" class="text-center text-muted">No fracties found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php renderPagination($currentPage, $totalPages, 25, 'Fractie pagination'); ?>

        <div class="mt-3 text-muted small">
            Showing <?= count($fracties) ?> of <?= (int)$total ?> fracties
        </div>
    </div>
</div>
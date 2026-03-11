<?php
declare(strict_types=1);

require_once __DIR__ . '/../helpers/list_helpers.php';
?>

<div class="card shadow-sm">
    <div class="card-header">
        <h5 class="mb-0">Person List</h5>
    </div>

    <div class="card-body">
        <form method="get" class="row g-3 mb-4">
            <input type="hidden" name="tab" value="person">
            <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
            <input type="hidden" name="direction" value="<?= htmlspecialchars($direction) ?>">

            <div class="col-md-2">
                <label for="nummer" class="form-label">Nummer</label>
                <input
                    type="text"
                    class="form-control"
                    id="nummer"
                    name="nummer"
                    value="<?= htmlspecialchars($filters['nummer'] ?? '') ?>"
                >
            </div>

            <div class="col-md-3">
                <label for="roepnaam" class="form-label">Roepnaam</label>
                <input
                    type="text"
                    class="form-control"
                    id="roepnaam"
                    name="roepnaam"
                    value="<?= htmlspecialchars($filters['roepnaam'] ?? '') ?>"
                >
            </div>

            <div class="col-md-3">
                <label for="achternaam" class="form-label">Achternaam</label>
                <input
                    type="text"
                    class="form-control"
                    id="achternaam"
                    name="achternaam"
                    value="<?= htmlspecialchars($filters['achternaam'] ?? '') ?>"
                >
            </div>

            <div class="col-md-2">
                <label for="geboortedatum" class="form-label">Geboortedatum</label>
                <input
                    type="date"
                    class="form-control"
                    id="geboortedatum"
                    name="geboortedatum"
                    value="<?= htmlspecialchars(formatDateForInput($filters['geboortedatum'] ?? null)) ?>"
                >
            </div>

            <div class="col-md-2">
                <label for="geslacht" class="form-label">Geslacht</label>
                <select class="form-select" id="geslacht" name="geslacht">
                    <option value="">Alle</option>
                    <option value="Man" <?= (($filters['geslacht'] ?? '') === 'Man') ? 'selected' : '' ?>>Man</option>
                    <option value="Vrouw" <?= (($filters['geslacht'] ?? '') === 'Vrouw') ? 'selected' : '' ?>>Vrouw</option>
                    <option value="Anders" <?= (($filters['geslacht'] ?? '') === 'Anders') ? 'selected' : '' ?>>Anders</option>
                </select>
            </div>

            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-filter"></i> Filter
                </button>
                <a href="index.php?tab=person&reset=1" class="btn btn-outline-secondary">
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
                            <a class="text-white text-decoration-none" href="<?= sortLink('roepnaam', $sort, $direction) ?>">
                                Roepnaam<?= sortIcon('roepnaam', $sort, $direction) ?>
                            </a>
                        </th>
                        <th>
                            <a class="text-white text-decoration-none" href="<?= sortLink('achternaam', $sort, $direction) ?>">
                                Achternaam<?= sortIcon('achternaam', $sort, $direction) ?>
                            </a>
                        </th>
                        <th>
                            <a class="text-white text-decoration-none" href="<?= sortLink('geboortedatum', $sort, $direction) ?>">
                                Geboortedatum<?= sortIcon('geboortedatum', $sort, $direction) ?>
                            </a>
                        </th>
                        <th>
                            <a class="text-white text-decoration-none" href="<?= sortLink('geslacht', $sort, $direction) ?>">
                                Geslacht<?= sortIcon('geslacht', $sort, $direction) ?>
                            </a>
                        </th>
                        <th class="text-center">Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($persons)): ?>
                        <?php foreach ($persons as $person): ?>
                            <tr>
                                <td><?= htmlspecialchars((string)($person['nummer'] ?? '')) ?></td>
                                <td><?= htmlspecialchars((string)($person['roepnaam'] ?? '')) ?></td>
                                <td><?= htmlspecialchars((string)($person['achternaam'] ?? '')) ?></td>
                                <td><?= htmlspecialchars(formatDate($person['geboortedatum'] ?? '')) ?></td>
                                <td><?= htmlspecialchars((string)($person['geslacht'] ?? '')) ?></td>
                                <td class="text-center">
                                    <a
                                        href="persondetails.php?id=<?= urlencode((string)$person['id']) ?>"
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
                            <td colspan="6" class="text-center text-muted">No persons found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php renderPagination($currentPage, $totalPages, 25, 'Person pagination'); ?>

        <div class="mt-3 text-muted small">
            Showing <?= count($persons) ?> of <?= (int)$total ?> persons
        </div>
    </div>
</div>
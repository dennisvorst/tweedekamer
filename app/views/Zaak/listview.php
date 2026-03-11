<?php
declare(strict_types=1);

require_once __DIR__ . '/../helpers/list_helpers.php';
?>

<div class="card shadow-sm">
    <div class="card-header">
        <h5 class="mb-0">Zaak List</h5>
    </div>

    <div class="card-body">
        <form method="get" class="row g-3 mb-4">
            <input type="hidden" name="tab" value="zaak">
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
                <label for="soort" class="form-label">Soort</label>
                <select class="form-select" id="soort" name="soort">
                    <option value="">Alle</option>
                    <?php foreach (($zaakSoorten ?? []) as $soortOption): ?>
                        <option
                            value="<?= htmlspecialchars((string)$soortOption) ?>"
                            <?= (($filters['soort'] ?? '') === (string)$soortOption) ? 'selected' : '' ?>
                        >
                            <?= htmlspecialchars((string)$soortOption) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label for="titel" class="form-label">Titel</label>
                <input
                    type="text"
                    class="form-control"
                    id="titel"
                    name="titel"
                    value="<?= htmlspecialchars((string)($filters['titel'] ?? '')) ?>"
                >
            </div>

            <div class="col-md-2">
                <label for="status" class="form-label">Status</label>
                <input
                    type="text"
                    class="form-control"
                    id="status"
                    name="status"
                    value="<?= htmlspecialchars((string)($filters['status'] ?? '')) ?>"
                >
            </div>

            <div class="col-md-3">
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
                <label for="gestart_op" class="form-label">Gestart op</label>
                <input
                    type="date"
                    class="form-control"
                    id="gestart_op"
                    name="gestart_op"
                    value="<?= htmlspecialchars(formatDateForInput($filters['gestart_op'] ?? null)) ?>"
                >
            </div>

            <div class="col-md-2">
                <label for="organisatie" class="form-label">Organisatie</label>
                <input
                    type="text"
                    class="form-control"
                    id="organisatie"
                    name="organisatie"
                    value="<?= htmlspecialchars((string)($filters['organisatie'] ?? '')) ?>"
                >
            </div>

            <div class="col-md-2">
                <label for="vergaderjaar" class="form-label">Vergaderjaar</label>
                <input
                    type="text"
                    class="form-control"
                    id="vergaderjaar"
                    name="vergaderjaar"
                    value="<?= htmlspecialchars((string)($filters['vergaderjaar'] ?? '')) ?>"
                >
            </div>

            <div class="col-md-2">
                <label for="afgedaan" class="form-label">Afgedaan</label>
                <select class="form-select" id="afgedaan" name="afgedaan">
                    <option value="">Alle</option>
                    <option value="1" <?= (($filters['afgedaan'] ?? '') === '1') ? 'selected' : '' ?>>Ja</option>
                    <option value="0" <?= (($filters['afgedaan'] ?? '') === '0') ? 'selected' : '' ?>>Nee</option>
                </select>
            </div>

            <div class="col-md-2">
                <label for="groot_project" class="form-label">Groot project</label>
                <select class="form-select" id="groot_project" name="groot_project">
                    <option value="">Alle</option>
                    <option value="1" <?= (($filters['groot_project'] ?? '') === '1') ? 'selected' : '' ?>>Ja</option>
                    <option value="0" <?= (($filters['groot_project'] ?? '') === '0') ? 'selected' : '' ?>>Nee</option>
                </select>
            </div>

            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-filter"></i> Filter
                </button>
                <a href="index.php?tab=zaak&reset=1" class="btn btn-outline-secondary">
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
                            <a class="text-white text-decoration-none" href="<?= sortLink('status', $sort, $direction) ?>">
                                Status<?= sortIcon('status', $sort, $direction) ?>
                            </a>
                        </th>
                        <th>
                            <a class="text-white text-decoration-none" href="<?= sortLink('onderwerp', $sort, $direction) ?>">
                                Onderwerp<?= sortIcon('onderwerp', $sort, $direction) ?>
                            </a>
                        </th>
                        <th>
                            <a class="text-white text-decoration-none" href="<?= sortLink('gestart_op', $sort, $direction) ?>">
                                Gestart op<?= sortIcon('gestart_op', $sort, $direction) ?>
                            </a>
                        </th>
                        <th>
                            <a class="text-white text-decoration-none" href="<?= sortLink('organisatie', $sort, $direction) ?>">
                                Organisatie<?= sortIcon('organisatie', $sort, $direction) ?>
                            </a>
                        </th>
                        <th class="text-center">Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($zaken)): ?>
                        <?php foreach ($zaken as $zaak): ?>
                            <tr>
                                <td><?= htmlspecialchars((string)($zaak['soort'] ?? '')) ?></td>
                                <td><?= htmlspecialchars((string)($zaak['nummer'] ?? '')) ?></td>

                                <td><?= htmlspecialchars((string)($zaak['status'] ?? '')) ?></td>
                                <td><?= htmlspecialchars((string)($zaak['onderwerp'] ?? '')) ?></td>
                                <td><?= htmlspecialchars(formatDate($zaak['gestart_op'] ?? null)) ?></td>
                                <td><?= htmlspecialchars((string)($zaak['organisatie'] ?? '')) ?></td>
                                <td class="text-center">
                                    <a
                                        href="zaakdetails.php?id=<?= urlencode((string)$zaak['id']) ?>"
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
                            <td colspan="8" class="text-center text-muted">No zaken found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php renderPagination($currentPage, $totalPages, 25, 'Zaak pagination'); ?>

        <div class="mt-3 text-muted small">
            Showing <?= count($zaken) ?> of <?= (int)$total ?> zaken
        </div>
    </div>
</div>
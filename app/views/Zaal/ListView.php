<?php
declare(strict_types=1);

require_once __DIR__ . '/../helpers/list_helpers.php';

/**
 * Expected variables:
 * $zalen
 * $total
 * $totalPages
 * $currentPage
 * $sort
 * $direction
 * $filters
 */

if (!function_exists('buildQuery')) {
    function buildQuery(array $overrides = []): string
    {
        $query = array_merge($_GET, $overrides);

        foreach ($query as $key => $value) {
            if ($value === '' || $value === null) {
                unset($query[$key]);
            }
        }

        return '?' . http_build_query($query);
    }
}

if (!function_exists('sortLink')) {
    function sortLink(string $column, string $currentSort, string $currentDirection): string
    {
        $nextDirection = 'asc';

        if ($currentSort === $column && $currentDirection === 'asc') {
            $nextDirection = 'desc';
        }

        return buildQuery([
            'sort' => $column,
            'direction' => $nextDirection,
            'page' => 1
        ]);
    }
}

if (!function_exists('sortIcon')) {
    function sortIcon(string $column, string $currentSort, string $currentDirection): string
    {
        if ($currentSort !== $column) {
            return '';
        }

        return $currentDirection === 'asc'
            ? ' <i class="fa-solid fa-arrow-up"></i>'
            : ' <i class="fa-solid fa-arrow-down"></i>';
    }
}
?>

<div class="card shadow-sm">
    <div class="card-header">
        <h5 class="mb-0">Zaal List</h5>
    </div>

    <div class="card-body">
        <form method="get" class="row g-3 mb-4">
            <input type="hidden" name="tab" value="zaal">
            <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
            <input type="hidden" name="direction" value="<?= htmlspecialchars($direction) ?>">

            <div class="col-md-4">
                <label for="naam" class="form-label">Naam</label>
                <input
                    type="text"
                    class="form-control"
                    id="naam"
                    name="naam"
                    value="<?= htmlspecialchars($filters['naam'] ?? '') ?>"
                >
            </div>

            <div class="col-md-4">
                <label for="syscode" class="form-label">Syscode</label>
                <input
                    type="number"
                    class="form-control"
                    id="syscode"
                    name="syscode"
                    value="<?= htmlspecialchars((string)($filters['syscode'] ?? '')) ?>"
                >
            </div>

            <div class="col-md-4">
                <label for="id" class="form-label">ID</label>
                <input
                    type="text"
                    class="form-control"
                    id="id"
                    name="id"
                    value="<?= htmlspecialchars($filters['id'] ?? '') ?>"
                >
            </div>

            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-filter"></i> Filter
                </button>
                <a href="index.php?tab=zaal&reset=1" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-rotate-left"></i> Reset
                </a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>
                            <a class="text-white text-decoration-none" href="<?= sortLink('id', $sort, $direction) ?>">
                                ID<?= sortIcon('id', $sort, $direction) ?>
                            </a>
                        </th>
                        <th>
                            <a class="text-white text-decoration-none" href="<?= sortLink('naam', $sort, $direction) ?>">
                                Naam<?= sortIcon('naam', $sort, $direction) ?>
                            </a>
                        </th>
                        <th>
                            <a class="text-white text-decoration-none" href="<?= sortLink('syscode', $sort, $direction) ?>">
                                Syscode<?= sortIcon('syscode', $sort, $direction) ?>
                            </a>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($zalen)): ?>
                        <?php foreach ($zalen as $zaal): ?>
                            <tr>
                                <td><?= htmlspecialchars((string)$zaal['id']) ?></td>
                                <td><?= htmlspecialchars((string)($zaal['naam'] ?? '')) ?></td>
                                <td><?= htmlspecialchars((string)($zaal['syscode'] ?? '')) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="text-center text-muted">No zalen found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php renderPagination($currentPage, $totalPages, 25, 'Zaal pagination'); ?>

        <div class="mt-3 text-muted small">
            Showing <?= count($zalen) ?> of <?= (int)$total ?> zalen
        </div>
    </div>
</div>
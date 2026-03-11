<?php
declare(strict_types=1);

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
            'page' => 1,
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

if (!function_exists('buildCompactPaginationItems')) {
    /**
     * Builds page items so the TOTAL visible controls stays within $maxVisibleItems.
     *
     * Total controls include:
     * - First
     * - Previous
     * - page buttons
     * - ellipsis items
     * - Next
     * - Last
     */
    function buildCompactPaginationItems(int $currentPage, int $totalPages, int $maxVisibleItems = 25): array
    {
        $currentPage = max(1, $currentPage);
        $totalPages = max(1, $totalPages);
        $maxVisibleItems = max(7, $maxVisibleItems);

        // 4 fixed controls: First, Previous, Next, Last
        $maxCenterItems = $maxVisibleItems - 4;

        // If everything fits, show all page buttons
        if ($totalPages <= $maxCenterItems) {
            $items = [];
            for ($i = 1; $i <= $totalPages; $i++) {
                $items[] = ['type' => 'page', 'page' => $i];
            }
            return $items;
        }

        $pagesToShow = [];

        // Always include first, current, last
        $pagesToShow[1] = true;
        $pagesToShow[$currentPage] = true;
        $pagesToShow[$totalPages] = true;

        $left = $currentPage - 1;
        $right = $currentPage + 1;

        while (true) {
            $candidatePages = array_keys($pagesToShow);
            sort($candidatePages);

            $items = [];
            $prevPage = null;

            foreach ($candidatePages as $page) {
                if ($prevPage !== null && $page > $prevPage + 1) {
                    $items[] = ['type' => 'ellipsis'];
                }

                $items[] = ['type' => 'page', 'page' => $page];
                $prevPage = $page;
            }

            if (count($items) >= $maxCenterItems) {
                break;
            }

            $added = false;

            if ($left >= 2) {
                $pagesToShow[$left] = true;
                $left--;
                $added = true;
            }

            $candidatePages = array_keys($pagesToShow);
            sort($candidatePages);

            $items = [];
            $prevPage = null;

            foreach ($candidatePages as $page) {
                if ($prevPage !== null && $page > $prevPage + 1) {
                    $items[] = ['type' => 'ellipsis'];
                }

                $items[] = ['type' => 'page', 'page' => $page];
                $prevPage = $page;
            }

            if (count($items) >= $maxCenterItems) {
                break;
            }

            if ($right <= $totalPages - 1) {
                $pagesToShow[$right] = true;
                $right++;
                $added = true;
            }

            if (!$added) {
                break;
            }
        }

        $candidatePages = array_keys($pagesToShow);
        sort($candidatePages);

        $items = [];
        $prevPage = null;

        foreach ($candidatePages as $page) {
            if ($prevPage !== null && $page > $prevPage + 1) {
                $items[] = ['type' => 'ellipsis'];
            }

            $items[] = ['type' => 'page', 'page' => $page];
            $prevPage = $page;
        }

        // Safety trim if needed
        while (count($items) > $maxCenterItems) {
            $removableIndexes = [];

            foreach ($items as $index => $item) {
                if (
                    $item['type'] === 'page'
                    && $item['page'] !== 1
                    && $item['page'] !== $currentPage
                    && $item['page'] !== $totalPages
                ) {
                    $removableIndexes[] = $index;
                }
            }

            if (empty($removableIndexes)) {
                break;
            }

            $middleIndex = $removableIndexes[(int) floor(count($removableIndexes) / 2)];
            unset($items[$middleIndex]);
            $items = array_values($items);

            // rebuild ellipses cleanly
            $pages = [];
            foreach ($items as $item) {
                if ($item['type'] === 'page') {
                    $pages[] = $item['page'];
                }
            }

            sort($pages);
            $items = [];
            $prevPage = null;

            foreach ($pages as $page) {
                if ($prevPage !== null && $page > $prevPage + 1) {
                    $items[] = ['type' => 'ellipsis'];
                }

                $items[] = ['type' => 'page', 'page' => $page];
                $prevPage = $page;
            }
        }

        return $items;
    }
}

if (!function_exists('renderPagination')) {
    function renderPagination(
        int $currentPage,
        int $totalPages,
        int $maxVisibleItems = 25,
        string $ariaLabel = 'Pagination'
    ): void {
        if ($totalPages <= 1) {
            return;
        }

        $items = buildCompactPaginationItems($currentPage, $totalPages, $maxVisibleItems);
        ?>
        <nav aria-label="<?= htmlspecialchars($ariaLabel) ?>">
            <ul class="pagination justify-content-center mt-4 flex-wrap">
                <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= $currentPage <= 1 ? '#' : buildQuery(['page' => 1]) ?>">First</a>
                </li>

                <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= $currentPage <= 1 ? '#' : buildQuery(['page' => $currentPage - 1]) ?>">Previous</a>
                </li>

                <?php foreach ($items as $item): ?>
                    <?php if ($item['type'] === 'ellipsis'): ?>
                        <li class="page-item disabled">
                            <span class="page-link">...</span>
                        </li>
                    <?php else: ?>
                        <li class="page-item <?= $item['page'] === $currentPage ? 'active' : '' ?>">
                            <a class="page-link" href="<?= buildQuery(['page' => $item['page']]) ?>">
                                <?= $item['page'] ?>
                            </a>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>

                <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= $currentPage >= $totalPages ? '#' : buildQuery(['page' => $currentPage + 1]) ?>">Next</a>
                </li>

                <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= $currentPage >= $totalPages ? '#' : buildQuery(['page' => $totalPages]) ?>">Last</a>
                </li>
            </ul>
        </nav>
        <?php
    }
}

/** todo make one generic formatdate */
function formatDate(?string $date): string
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

function formatDateForInput(?string $date): string
{
    if (!$date) {
        return '';
    }

    try {
        $dt = new DateTime($date);
        return $dt->format('Y-m-d');
    } catch (Exception) {
        return $date;
    }
}

function formatNumber(int|float|string|null $number): string
{
    if ($number === null || $number === '') {
        return '';
    }

    if (!is_numeric($number)) {
        return (string)$number;
    }

    return number_format((float)$number, 0, ',', '.');
}

function formatTime(?string $time): string
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
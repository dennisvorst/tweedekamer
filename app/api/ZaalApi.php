<?php
declare(strict_types=1);

namespace App\Api;

use App\Models\ZaalModel;

class ZaalApi
{
    public function __construct(
        private ZaalModel $zaalModel
    ) {
    }

    public function getZalen(
        array $filters = [],
        string $sort = 'naam',
        string $direction = 'asc',
        int $page = 1,
        int $perPage = 50
    ): array {
        $allowedSorts = [
            'id',
            'naam',
            'syscode',
        ];

        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'naam';
        }

        $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';
        $page = max(1, $page);
        $perPage = max(1, min(200, $perPage));

        return [
            'data' => $this->zaalModel->getZalen($filters, $sort, $direction, $page, $perPage),
            'total' => $this->zaalModel->countZalen($filters),
        ];
    }
}
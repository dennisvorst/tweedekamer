<?php
declare(strict_types=1);

namespace App\Api;

use App\Models\ZaakModel;

class ZaakApi
{
    public function __construct(
        private ZaakModel $zaakModel
    ) {
    }

    public function getZaken(
        array $filters = [],
        string $sort = 'gestart_op',
        string $direction = 'desc',
        int $page = 1,
        int $perPage = 50
    ): array {
        $allowedSorts = [
            'nummer',
            'soort',
            'titel',
            'status',
            'onderwerp',
            'gestart_op',
            'organisatie',
            'vergaderjaar',
            'afgedaan',
            'groot_project',
        ];

        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'gestart_op';
        }

        $direction = strtolower($direction) === 'asc' ? 'asc' : 'desc';
        $page = max(1, $page);
        $perPage = max(1, min(200, $perPage));

        return [
            'data' => $this->zaakModel->getZaken($filters, $sort, $direction, $page, $perPage),
            'total' => $this->zaakModel->countZaken($filters),
        ];
    }

    public function getZaakDetails(string $id): ?array
    {
        return $this->zaakModel->getZaakDetails($id);
    }

    public function getZaakActorRows(string $zaakId): array
    {
        return $this->zaakModel->getZaakActorRows($zaakId);
    }

    public function getZaakSoorten(): array
    {
        return $this->zaakModel->getZaakSoorten();
    }
}
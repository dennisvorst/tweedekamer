<?php
declare(strict_types=1);

namespace App\Api;

use App\Models\ActiviteitModel;

class ActiviteitApi
{
    public function __construct(
        private ActiviteitModel $activiteitModel
    ) {
    }

    public function getActiviteiten(
        array $filters = [],
        string $sort = 'datum',
        string $direction = 'desc',
        int $page = 1,
        int $perPage = 50
    ): array {
        $allowedSorts = [
            'soort',
            'nummer',
            'onderwerp',
            'datum',
            'aanvangstijd',
            'eindtijd',
            'locatie',
        ];

        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'datum';
        }

        $direction = strtolower($direction) === 'asc' ? 'asc' : 'desc';
        $page = max(1, $page);
        $perPage = max(1, min(200, $perPage));

        return [
            'data' => $this->activiteitModel->getActiviteiten($filters, $sort, $direction, $page, $perPage),
            'total' => $this->activiteitModel->countActiviteiten($filters),
        ];
    }

    public function getActiviteitDetails(string $id): ?array
    {
        return $this->activiteitModel->getActiviteitDetails($id);
    }

    public function getActiviteitActorRows(string $activiteitId): array
    {
        return $this->activiteitModel->getActiviteitActorRows($activiteitId);
    }

    public function getActiviteitAgendapuntRows(string $activiteitId): array
    {
        return $this->activiteitModel->getActiviteitAgendapuntRows($activiteitId);
    }

    public function getActiviteitSoorten(): array
    {
        return $this->activiteitModel->getActiviteitSoorten();
    }    
}
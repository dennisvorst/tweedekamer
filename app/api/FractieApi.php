<?php
declare(strict_types=1);

namespace App\Api;

use App\Models\FractieModel;

class FractieApi
{
    public function __construct(
        private FractieModel $fractieModel
    ) {
    }

    public function getFracties(
        array $filters = [],
        string $sort = 'naam_nl',
        string $direction = 'asc',
        int $page = 1,
        int $perPage = 50
    ): array {
        $allowedSorts = [
            'nummer',
            'afkorting',
            'naam_nl',
            'naam_en',
            'aantal_zetels',
            'aantal_stemmen',
            'datum_actief',
            'datum_inactief',
        ];

        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'naam_nl';
        }

        $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';
        $page = max(1, $page);
        $perPage = max(1, min(200, $perPage));

        return [
            'data' => $this->fractieModel->getFracties($filters, $sort, $direction, $page, $perPage),
            'total' => $this->fractieModel->countFracties($filters),
        ];
    }

    public function getFractieDetails(string $id): ?array
    {
        return $this->fractieModel->getFractieDetails($id);
    }

    public function getFractieZetelPersonen(string $fractieId): array
    {
        return $this->fractieModel->getFractieZetelPersonen($fractieId);
    }

    
}
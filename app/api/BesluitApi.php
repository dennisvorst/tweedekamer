<?php
declare(strict_types=1);

namespace App\Api;

use App\Models\BesluitModel;

class BesluitApi
{
    public function __construct(
        private BesluitModel $besluitModel
    ) {
    }

    public function getBesluitDetails(string $id): ?array
    {
        return $this->besluitModel->getBesluitDetails($id);
    }

    public function getBesluitStemmingRows(string $besluitId): array
    {
        return $this->besluitModel->getBesluitStemmingRows($besluitId);
    }

    public function getBesluitStemmingFractieSamenvatting(string $besluitId): array
    {
        return $this->besluitModel->getBesluitStemmingFractieSamenvatting($besluitId);
    }
}
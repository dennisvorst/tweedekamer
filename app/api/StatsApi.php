<?php
declare(strict_types=1);

namespace App\Api;

use App\Models\StatsModel;

final class StatsApi
{
    public function __construct(private StatsModel $voteStatsModel)
    {
    }

    public function getPersonRankings(): array
    {
        return $this->voteStatsModel->getPersonRankings();
    }

    public function getFractieRankings(): array
    {
        return $this->voteStatsModel->getFractieRankings();
    }

    public function getActivePersonStats(string $sort = 'jaren_ervaring', string $direction = 'desc'): array
    {
        return $this->voteStatsModel->getActivePersonStats($sort, $direction);
    }

    public function getPersonStatsList(string $sort = 'totaal_stemmen', string $direction = 'desc'): array
    {
        return $this->voteStatsModel->getPersonStatsList($sort, $direction);
    }

    public function getFractieStatsList(string $sort = 'totaal_stemmen', string $direction = 'desc'): array
    {
        return $this->voteStatsModel->getFractieStatsList($sort, $direction);
    }

    public function getBesluitStatsList(string $sort = 'totaal_stemmen', string $direction = 'desc'): array
    {
        return $this->voteStatsModel->getBesluitStatsList($sort, $direction);
    }
}

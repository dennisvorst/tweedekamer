<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

class ActiviteitModel
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    public function getActiviteiten(
        array $filters,
        string $sort,
        string $direction,
        int $page,
        int $perPage
    ): array {
        $params = [];
        $where = $this->buildWhereClause($filters, $params);
        $offset = ($page - 1) * $perPage;

        $sql = "
            SELECT
                id,
                soort,
                nummer,
                onderwerp,
                datum,
                aanvangstijd,
                eindtijd,
                locatie
            FROM activiteit
            WHERE 1 = 1
            {$where}
            ORDER BY {$sort} {$direction}
            LIMIT :limit OFFSET :offset
        ";

        $stmt = $this->pdo->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function countActiviteiten(array $filters): int
    {
        $params = [];
        $where = $this->buildWhereClause($filters, $params);

        $sql = "
            SELECT COUNT(*)
            FROM activiteit
            WHERE 1 = 1
            {$where}
        ";

        $stmt = $this->pdo->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();

        return (int)$stmt->fetchColumn();
    }

    public function getActiviteitDetails(string $id): ?array
    {
        $sql = "
            SELECT
                a.*,
                c.id AS commissie_id,
                c.naam_nl AS commissie_naam_nl,
                c.naam_en AS commissie_naam_en
            FROM activiteit a
            LEFT JOIN commissie c
                ON c.id = a.voortouwcommissie_id
                AND (c.is_verwijderd = 0 OR c.is_verwijderd IS NULL)
            WHERE a.id = :id
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();

        $result = $stmt->fetch();

        return $result === false ? null : $result;
    }

    private function buildWhereClause(array $filters, array &$params): string
    {
        $conditions = [];

        if (($filters['soort'] ?? '') !== '') {
            $conditions[] = 'soort = :soort';
            $params[':soort'] = $filters['soort'];
        }

        if (($filters['nummer'] ?? '') !== '') {
            $conditions[] = 'nummer LIKE :nummer';
            $params[':nummer'] = '%' . $filters['nummer'] . '%';
        }

        if (($filters['onderwerp'] ?? '') !== '') {
            $conditions[] = 'onderwerp LIKE :onderwerp';
            $params[':onderwerp'] = '%' . $filters['onderwerp'] . '%';
        }

        if (($filters['datum'] ?? '') !== '') {
            $conditions[] = 'datum = :datum';
            $params[':datum'] = $filters['datum'];
        }

        if (($filters['locatie'] ?? '') !== '') {
            $conditions[] = 'locatie LIKE :locatie';
            $params[':locatie'] = '%' . $filters['locatie'] . '%';
        }

        return !empty($conditions)
            ? ' AND ' . implode(' AND ', $conditions)
            : '';
    }

    public function getActiviteitActorRows(string $activiteitId): array
    {
        $sql = "
            SELECT *
            FROM activiteit_actor
            WHERE activiteit_id = :activiteit_id
            AND (is_verwijderd = 0 OR is_verwijderd IS NULL)
            ORDER BY
                CASE WHEN volgorde IS NULL THEN 999999 ELSE volgorde END,
                actor_naam ASC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':activiteit_id', $activiteitId);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getActiviteitAgendapuntRows(string $activiteitId): array
    {
        $sql = "
            SELECT * 
            FROM agendapunt
            WHERE activiteit_id = :activiteit_id
            AND (is_verwijderd = 0 OR is_verwijderd IS NULL)
            ORDER BY
                CASE WHEN volgorde IS NULL THEN 999999 ELSE volgorde END,
                CASE WHEN nummer IS NULL THEN 999999 ELSE nummer END,
                aanvangstijd ASC,
                onderwerp ASC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':activiteit_id', $activiteitId);
        $stmt->execute();

        return $stmt->fetchAll();
    }    

    public function getActiviteitSoorten(): array
    {
        $sql = "
            SELECT DISTINCT soort
            FROM activiteit
            WHERE soort IS NOT NULL
            AND TRIM(soort) <> ''
            ORDER BY soort
        ";

        $stmt = $this->pdo->query($sql);

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
}
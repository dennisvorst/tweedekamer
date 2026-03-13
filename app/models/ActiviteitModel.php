<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

class ActiviteitModel
{
    public static function getListDefaults(): array
    {
        return [
            'sort' => 'datum',
            'direction' => 'desc',
            'page' => 1,
            'filters' => array_fill_keys(self::getAllowedFilters(), ''),
        ];
    }

    public static function getAllowedFilters(): array
    {
        return [
            'soort',
            'nummer',
            'onderwerp',
            'datum',
            'locatie',
        ];
    }

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
            WHERE (is_verwijderd = 0 OR is_verwijderd IS NULL)
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
            WHERE (is_verwijderd = 0 OR is_verwijderd IS NULL)
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
            AND (a.is_verwijderd = 0 OR a.is_verwijderd IS NULL)
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

    public function getAgendapuntDetails(string $id): ?array
    {
        $sql = "
            SELECT
                a.*,
                act.id AS activiteit_id,
                act.nummer AS activiteit_nummer,
                act.onderwerp AS activiteit_onderwerp,
                act.datum AS activiteit_datum
            FROM agendapunt a
            LEFT JOIN activiteit act
                ON act.id = a.activiteit_id
               AND (act.is_verwijderd = 0 OR act.is_verwijderd IS NULL)
            WHERE a.id = :id
              AND (a.is_verwijderd = 0 OR a.is_verwijderd IS NULL)
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();

        $result = $stmt->fetch();

        return $result === false ? null : $result;
    }

    public function getAgendapuntBesluitRows(string $agendapuntId): array
    {
        $sql = "
            SELECT
                id,
                agendapunt_id,
                besluit_soort,
                stemmingssoort,
                besluittekst,
                opmerking,
                status,
                agendapunt_zaak_besluitvolgorde
            FROM besluit
            WHERE agendapunt_id = :agendapunt_id
              AND (is_verwijderd = 0 OR is_verwijderd IS NULL)
            ORDER BY
                CASE WHEN agendapunt_zaak_besluitvolgorde IS NULL THEN 999999 ELSE agendapunt_zaak_besluitvolgorde END,
                besluit_soort ASC,
                id ASC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':agendapunt_id', $agendapuntId);
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
            AND (is_verwijderd = 0 OR is_verwijderd IS NULL)
            ORDER BY soort
        ";

        $stmt = $this->pdo->query($sql);

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
}

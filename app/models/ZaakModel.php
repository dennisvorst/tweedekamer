<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

class ZaakModel
{
    public static function getListDefaults(): array
    {
        return [
            'sort' => 'gestart_op',
            'direction' => 'desc',
            'page' => 1,
            'filters' => array_fill_keys(self::getAllowedFilters(), ''),
        ];
    }

    public static function getAllowedFilters(): array
    {
        return [
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
    }

    public function __construct(
        private PDO $pdo
    ) {
    }

    public function getZaken(
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
                nummer,
                soort,
                titel,
                status,
                onderwerp,
                gestart_op,
                organisatie,
                vergaderjaar,
                afgedaan,
                groot_project
            FROM zaak
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

    public function countZaken(array $filters): int
    {
        $params = [];
        $where = $this->buildWhereClause($filters, $params);

        $sql = "
            SELECT COUNT(*)
            FROM zaak
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

    private function buildWhereClause(array $filters, array &$params): string
    {
        $conditions = [];

        if (($filters['nummer'] ?? '') !== '') {
            $conditions[] = 'nummer LIKE :nummer';
            $params[':nummer'] = '%' . $filters['nummer'] . '%';
        }

        if (($filters['soort'] ?? '') !== '') {
            $conditions[] = 'soort = :soort';
            $params[':soort'] = $filters['soort'];
        }

        if (($filters['titel'] ?? '') !== '') {
            $conditions[] = 'titel LIKE :titel';
            $params[':titel'] = '%' . $filters['titel'] . '%';
        }

        if (($filters['status'] ?? '') !== '') {
            $conditions[] = 'status LIKE :status';
            $params[':status'] = '%' . $filters['status'] . '%';
        }

        if (($filters['onderwerp'] ?? '') !== '') {
            $conditions[] = 'onderwerp LIKE :onderwerp';
            $params[':onderwerp'] = '%' . $filters['onderwerp'] . '%';
        }

        if (($filters['gestart_op'] ?? '') !== '') {
            $conditions[] = 'gestart_op = :gestart_op';
            $params[':gestart_op'] = $filters['gestart_op'];
        }

        if (($filters['organisatie'] ?? '') !== '') {
            $conditions[] = 'organisatie LIKE :organisatie';
            $params[':organisatie'] = '%' . $filters['organisatie'] . '%';
        }

        if (($filters['vergaderjaar'] ?? '') !== '') {
            $conditions[] = 'vergaderjaar LIKE :vergaderjaar';
            $params[':vergaderjaar'] = '%' . $filters['vergaderjaar'] . '%';
        }

        if (($filters['afgedaan'] ?? '') !== '') {
            $conditions[] = 'afgedaan = :afgedaan';
            $params[':afgedaan'] = $filters['afgedaan'];
        }

        if (($filters['groot_project'] ?? '') !== '') {
            $conditions[] = 'groot_project = :groot_project';
            $params[':groot_project'] = $filters['groot_project'];
        }

        return !empty($conditions)
            ? ' AND ' . implode(' AND ', $conditions)
            : '';
    }

    public function getZaakDetails(string $id): ?array
    {
        $sql = "
            SELECT *
            FROM zaak
            WHERE id = :id
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();

        $result = $stmt->fetch();

        return $result === false ? null : $result;
    }

    public function getZaakActorRows(string $zaakId): array
    {
        $sql = "
            SELECT
                za.id,
                za.zaak_id,
                za.persoon_id,
                za.fractie_id,
                za.commissie_id,
                za.actor_naam,
                za.actor_fractie,
                za.actor_afkorting,
                za.functie,
                za.relatie,
                za.sid_actor,

                p.roepnaam,
                p.achternaam,

                f.naam_nl AS fractie_naam_nl,
                f.naam_en AS fractie_naam_en,
                f.afkorting AS fractie_afkorting,

                c.naam_nl AS commissie_naam_nl,
                c.naam_en AS commissie_naam_en,
                c.afkorting AS commissie_afkorting
            FROM zaak_actor za
            LEFT JOIN persoon p
                ON p.id = za.persoon_id
            AND (p.is_verwijderd = 0 OR p.is_verwijderd IS NULL)
            LEFT JOIN fractie f
                ON f.id = za.fractie_id
            AND (f.is_verwijderd = 0 OR f.is_verwijderd IS NULL)
            LEFT JOIN commissie c
                ON c.id = za.commissie_id
            AND (c.is_verwijderd = 0 OR c.is_verwijderd IS NULL)
            WHERE za.zaak_id = :zaak_id
            AND (za.is_verwijderd = 0 OR za.is_verwijderd IS NULL)
            ORDER BY
                za.relatie ASC,
                za.functie ASC,
                za.actor_naam ASC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':zaak_id', $zaakId);
        $stmt->execute();

        return $stmt->fetchAll();
    }


    public function getZaakSoorten(): array
    {
        $sql = "
            SELECT DISTINCT soort
            FROM zaak
            WHERE soort IS NOT NULL
            AND TRIM(soort) <> ''
            ORDER BY soort
        ";

        $stmt = $this->pdo->query($sql);

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}

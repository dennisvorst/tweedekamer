<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

class FractieModel
{
    public static function getListDefaults(): array
    {
        return [
            'sort' => 'naam_nl',
            'direction' => 'asc',
            'page' => 1,
            'filters' => array_fill_keys(self::getAllowedFilters(), ''),
        ];
    }

    public static function getAllowedFilters(): array
    {
        return [
            'nummer',
            'afkorting',
            'naam_nl',
            'aantal_zetels',
            'aantal_stemmen',
            'datum_actief',
            'datum_inactief',
        ];
    }

    public function __construct(
        private PDO $pdo
    ) {
    }

    public function getFracties(
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
            SELECT * 
            FROM fractie
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

    public function countFracties(array $filters): int
    {
        /** TODO the fractie table has a slightly different configuration 
         * We fixed this by doing a manual update 
         * SELECT * FROM `fractie` WHERE is_verwijderd IS NULL AND nummer IS NULL;
         * UPDATE fractie SET is_verwijderd = 1 WHERE is_verwijderd IS NULL AND nummer IS NULL;
         * SELECT * FROM `fractie` WHERE is_verwijderd IS NULL AND nummer IS NULL;
        */
        $params = [];
        $where = $this->buildWhereClause($filters, $params);

        $sql = "
            SELECT COUNT(*)
            FROM fractie
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

    private function buildWhereClause(array $filters, array &$params): string
    {
        $conditions = [];

        if (($filters['nummer'] ?? '') !== '') {
            $conditions[] = 'nummer = :nummer';
            $params[':nummer'] = $filters['nummer'];
        }

        if (($filters['afkorting'] ?? '') !== '') {
            $conditions[] = 'afkorting LIKE :afkorting';
            $params[':afkorting'] = '%' . $filters['afkorting'] . '%';
        }

        if (($filters['naam_nl'] ?? '') !== '') {
            $conditions[] = 'naam_nl LIKE :naam_nl';
            $params[':naam_nl'] = '%' . $filters['naam_nl'] . '%';
        }

        if (($filters['aantal_zetels'] ?? '') !== '') {
            $conditions[] = 'aantal_zetels = :aantal_zetels';
            $params[':aantal_zetels'] = $filters['aantal_zetels'];
        }

        if (($filters['aantal_stemmen'] ?? '') !== '') {
            $conditions[] = 'aantal_stemmen = :aantal_stemmen';
            $params[':aantal_stemmen'] = $filters['aantal_stemmen'];
        }

        if (($filters['datum_actief'] ?? '') !== '') {
            $conditions[] = 'datum_actief = :datum_actief';
            $params[':datum_actief'] = $filters['datum_actief'];
        }

        if (($filters['datum_inactief'] ?? '') !== '') {
            $conditions[] = 'datum_inactief = :datum_inactief';
            $params[':datum_inactief'] = $filters['datum_inactief'];
        }

        return !empty($conditions)
            ? ' AND ' . implode(' AND ', $conditions)
            : '';
    }

    public function getFractieDetails(string $id): ?array
    {
        $sql = "
            SELECT *
            FROM fractie
            WHERE id = :id
            AND (is_verwijderd = 0 OR is_verwijderd IS NULL)
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();

        $result = $stmt->fetch();

        return $result === false ? null : $result;
    }    

    public function getFractieZetelPersonen(string $fractieId): array
    {
        $sql = "
            SELECT
                fzp.id,
                fzp.fractie_zetel_id,
                fzp.persoon_id,
                fzp.functie,
                fzp.van,
                fzp.tot_en_met,
                p.roepnaam,
                p.achternaam,
                p.nummer
            FROM fractie_zetel fz
            INNER JOIN fractie_zetel_persoon fzp
                ON fzp.fractie_zetel_id = fz.id
            INNER JOIN persoon p
                ON p.id = fzp.persoon_id
            WHERE fz.fractie_id = :fractie_id
            AND (fz.is_verwijderd = 0 OR fz.is_verwijderd IS NULL)
            AND (fzp.is_verwijderd = 0 OR fzp.is_verwijderd IS NULL)
            AND (p.is_verwijderd = 0 OR p.is_verwijderd IS NULL)
            ORDER BY
                CASE WHEN fz.gewicht IS NULL THEN 999999 ELSE fz.gewicht END,
                fzp.van ASC,
                p.achternaam ASC,
                p.roepnaam ASC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':fractie_id', $fractieId);
        $stmt->execute();

        return $stmt->fetchAll();
    }    
}

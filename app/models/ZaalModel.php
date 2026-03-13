<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

class ZaalModel
{
    public static function getListDefaults(): array
    {
        return [
            'sort' => 'naam',
            'direction' => 'asc',
            'page' => 1,
            'filters' => array_fill_keys(self::getAllowedFilters(), ''),
        ];
    }

    public static function getAllowedFilters(): array
    {
        return ['id', 'naam', 'syscode'];
    }

    public function __construct(
        private PDO $pdo
    ) {
    }

    public function getZalen(
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
            SELECT id, naam, syscode
            FROM zaal
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

    public function countZalen(array $filters): int
    {
        $params = [];
        $where = $this->buildWhereClause($filters, $params);

        $sql = "SELECT COUNT(*) FROM zaal {$where}";
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

        if (!empty($filters['id'])) {
            $conditions[] = 'id LIKE :id';
            $params[':id'] = '%' . $filters['id'] . '%';
        }

        if (!empty($filters['naam'])) {
            $conditions[] = 'naam LIKE :naam';
            $params[':naam'] = '%' . $filters['naam'] . '%';
        }

        if ($filters['syscode'] !== '' && $filters['syscode'] !== null) {
            $conditions[] = 'syscode = :syscode';
            $params[':syscode'] = (int)$filters['syscode'];
        }

        return !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
    }
}

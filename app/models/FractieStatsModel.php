<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

final class FractieStatsModel
{
    public function __construct(private PDO $conn)
    {
    }

    public function createTable(): void
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS fractie_stats (
                fractie_id VARCHAR(36) NOT NULL,
                voor_stemmen INT NOT NULL DEFAULT 0,
                tegen_stemmen INT NOT NULL DEFAULT 0,
                onthouden_stemmen INT NOT NULL DEFAULT 0,
                absent_stemmen INT NOT NULL DEFAULT 0,
                totaal_stemmen INT NOT NULL DEFAULT 0,
                voor_percentage DECIMAL(6,2) NOT NULL DEFAULT 0,
                tegen_percentage DECIMAL(6,2) NOT NULL DEFAULT 0,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
                    ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (fractie_id),
                CONSTRAINT fk_fractie_stats_fractie
                    FOREIGN KEY (fractie_id)
                    REFERENCES fractie(id)
                    ON DELETE CASCADE
                    ON UPDATE CASCADE
            ) ENGINE=InnoDB;
        ";

        $this->conn->exec($sql);
    }

    public function dropTable(): void
    {
        $this->conn->exec('DROP TABLE IF EXISTS fractie_stats');
    }

    public function clearTable(): void
    {
        $this->conn->exec('TRUNCATE TABLE fractie_stats');
    }

    public function countForBuild(): int
    {
        $stmt = $this->conn->query('SELECT COUNT(*) FROM fractie WHERE (is_verwijderd = 0 OR is_verwijderd IS NULL)');
        return (int) $stmt->fetchColumn();
    }

    public function buildSumsBatch(int $offset, int $limit): int
    {
        $stmt = $this->conn->prepare("
            SELECT id
            FROM fractie
            WHERE (is_verwijderd = 0 OR is_verwijderd IS NULL)
            ORDER BY id
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $fractieIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (!$fractieIds) {
            return 0;
        }

        $voteStmt = $this->conn->prepare("
            SELECT
                SUM(CASE WHEN LOWER(TRIM(COALESCE(soort, ''))) = 'voor' THEN 1 ELSE 0 END) AS voor,
                SUM(CASE WHEN LOWER(TRIM(COALESCE(soort, ''))) = 'tegen' THEN 1 ELSE 0 END) AS tegen,
                SUM(CASE WHEN LOWER(TRIM(COALESCE(soort, ''))) = '' THEN 1 ELSE 0 END) AS onthouden,
                SUM(CASE WHEN LOWER(TRIM(COALESCE(soort, ''))) = 'niet deelgenomen' THEN 1 ELSE 0 END) AS absent
            FROM stemming
            WHERE fractie_id = :fractie_id
              AND (is_verwijderd = 0 OR is_verwijderd IS NULL)
        ");

        $upsertStmt = $this->conn->prepare("
            INSERT INTO fractie_stats (
                fractie_id,
                voor_stemmen,
                tegen_stemmen,
                onthouden_stemmen,
                absent_stemmen,
                totaal_stemmen,
                voor_percentage,
                tegen_percentage
            ) VALUES (
                :fractie_id,
                :voor,
                :tegen,
                :onthouden,
                :absent,
                :totaal,
                :voor_percentage,
                :tegen_percentage
            )
            ON DUPLICATE KEY UPDATE
                voor_stemmen = VALUES(voor_stemmen),
                tegen_stemmen = VALUES(tegen_stemmen),
                onthouden_stemmen = VALUES(onthouden_stemmen),
                absent_stemmen = VALUES(absent_stemmen),
                totaal_stemmen = VALUES(totaal_stemmen),
                voor_percentage = VALUES(voor_percentage),
                tegen_percentage = VALUES(tegen_percentage)
        ");

        foreach ($fractieIds as $fractieId) {
            $voteStmt->execute(['fractie_id' => $fractieId]);
            $votes = $voteStmt->fetch(PDO::FETCH_ASSOC) ?: [];

            $voor = (int) ($votes['voor'] ?? 0);
            $tegen = (int) ($votes['tegen'] ?? 0);
            $onthouden = (int) ($votes['onthouden'] ?? 0);
            $absent = (int) ($votes['absent'] ?? 0);
            $totaal = $voor + $tegen + $onthouden;
            $voorPercentage = $totaal > 0 ? ($voor / $totaal) * 100 : 0;
            $tegenPercentage = $totaal > 0 ? ($tegen / $totaal) * 100 : 0;

            $upsertStmt->execute([
                'fractie_id' => $fractieId,
                'voor' => $voor,
                'tegen' => $tegen,
                'onthouden' => $onthouden,
                'absent' => $absent,
                'totaal' => $totaal,
                'voor_percentage' => $voorPercentage,
                'tegen_percentage' => $tegenPercentage,
            ]);
        }

        return count($fractieIds);
    }

    public function buildPercentagesBatch(int $offset, int $limit): int
    {
        $stmt = $this->conn->prepare("
            SELECT
                fractie_id,
                totaal_stemmen,
                voor_stemmen,
                tegen_stemmen
            FROM fractie_stats
            ORDER BY fractie_id
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$rows) {
            return 0;
        }

        $updateStmt = $this->conn->prepare("
            UPDATE fractie_stats
            SET
                voor_percentage = :voor_percentage,
                tegen_percentage = :tegen_percentage
            WHERE fractie_id = :fractie_id
        ");

        foreach ($rows as $row) {
            $totaalStemmen = (int) $row['totaal_stemmen'];

            $voorPercentage = $totaalStemmen > 0
                ? (((int) $row['voor_stemmen']) / $totaalStemmen) * 100
                : 0;

            $tegenPercentage = $totaalStemmen > 0
                ? (((int) $row['tegen_stemmen']) / $totaalStemmen) * 100
                : 0;

            $updateStmt->execute([
                'fractie_id' => $row['fractie_id'],
                'voor_percentage' => $voorPercentage,
                'tegen_percentage' => $tegenPercentage,
            ]);
        }

        return count($rows);
    }
}

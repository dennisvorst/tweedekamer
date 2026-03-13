<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

final class FractieBesluitStatsModel
{
    public function __construct(private PDO $conn)
    {
    }

    public function createTable(): void
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS fractie_besluit_stats (
                fractie_id VARCHAR(36) NOT NULL,
                besluit_id VARCHAR(36) NOT NULL,
                totaal_voor INT NOT NULL DEFAULT 0,
                totaal_tegen INT NOT NULL DEFAULT 0,
                totaal_onthouden INT NOT NULL DEFAULT 0,
                totaal_absent INT NOT NULL DEFAULT 0,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
                    ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (fractie_id, besluit_id),
                KEY idx_fractie_besluit_stat_besluit_id (besluit_id),
                CONSTRAINT fk_fractie_besluit_stat_fractie
                    FOREIGN KEY (fractie_id)
                    REFERENCES fractie(id)
                    ON DELETE CASCADE
                    ON UPDATE CASCADE,
                CONSTRAINT fk_fractie_besluit_stat_besluit
                    FOREIGN KEY (besluit_id)
                    REFERENCES besluit(id)
                    ON DELETE CASCADE
                    ON UPDATE CASCADE
            ) ENGINE=InnoDB;
        ";

        $this->conn->exec($sql);
    }

    public function dropTable(): void
    {
        $this->conn->exec('DROP TABLE IF EXISTS fractie_besluit_stats');
    }

    public function clearTable(): void
    {
        $this->conn->exec('TRUNCATE TABLE fractie_besluit_stats');
    }

    public function countForBuild(): int
    {
        $stmt = $this->conn->query("
            SELECT COUNT(*)
            FROM (
                SELECT DISTINCT besluit_id
                FROM stemming
                WHERE besluit_id IS NOT NULL
                  AND (is_verwijderd = 0 OR is_verwijderd IS NULL)
            ) t
        ");

        return (int) $stmt->fetchColumn();
    }

    public function buildSumsBatch(int $offset, int $limit): int
    {
        $stmt = $this->conn->prepare("
            SELECT DISTINCT besluit_id
            FROM stemming
            WHERE besluit_id IS NOT NULL
              AND (is_verwijderd = 0 OR is_verwijderd IS NULL)
            ORDER BY besluit_id
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $besluitIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (!$besluitIds) {
            return 0;
        }

        $voteStmt = $this->conn->prepare("
            SELECT
                fractie_id,
                SUM(CASE WHEN LOWER(TRIM(COALESCE(soort, ''))) = 'voor' THEN 1 ELSE 0 END) AS voor,
                SUM(CASE WHEN LOWER(TRIM(COALESCE(soort, ''))) = 'tegen' THEN 1 ELSE 0 END) AS tegen,
                SUM(CASE WHEN LOWER(TRIM(COALESCE(soort, ''))) = '' THEN 1 ELSE 0 END) AS onthouden,
                SUM(CASE WHEN LOWER(TRIM(COALESCE(soort, ''))) = 'niet deelgenomen' THEN 1 ELSE 0 END) AS absent
            FROM stemming
            WHERE besluit_id = :besluit_id
              AND fractie_id IS NOT NULL
              AND (is_verwijderd = 0 OR is_verwijderd IS NULL)
            GROUP BY fractie_id
        ");

        $upsertStmt = $this->conn->prepare("
            INSERT INTO fractie_besluit_stats (
                fractie_id,
                besluit_id,
                totaal_voor,
                totaal_tegen,
                totaal_onthouden,
                totaal_absent
            ) VALUES (
                :fractie_id,
                :besluit_id,
                :voor,
                :tegen,
                :onthouden,
                :absent
            )
            ON DUPLICATE KEY UPDATE
                totaal_voor = VALUES(totaal_voor),
                totaal_tegen = VALUES(totaal_tegen),
                totaal_onthouden = VALUES(totaal_onthouden),
                totaal_absent = VALUES(totaal_absent)
        ");

        foreach ($besluitIds as $besluitId) {
            $voteStmt->execute(['besluit_id' => $besluitId]);

            while ($row = $voteStmt->fetch(PDO::FETCH_ASSOC)) {
                $upsertStmt->execute([
                    'fractie_id' => $row['fractie_id'],
                    'besluit_id' => $besluitId,
                    'voor' => (int) ($row['voor'] ?? 0),
                    'tegen' => (int) ($row['tegen'] ?? 0),
                    'onthouden' => (int) ($row['onthouden'] ?? 0),
                    'absent' => (int) ($row['absent'] ?? 0),
                ]);
            }
        }

        return count($besluitIds);
    }
}

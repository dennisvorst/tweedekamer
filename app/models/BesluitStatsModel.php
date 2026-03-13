<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

final class BesluitStatsModel
{
    public function __construct(private PDO $conn)
    {
    }

    public function createTable(): void
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS besluit_stats (
                besluit_id VARCHAR(36) NOT NULL,
                totaal_voor INT NOT NULL DEFAULT 0,
                totaal_tegen INT NOT NULL DEFAULT 0,
                totaal_onthouden INT NOT NULL DEFAULT 0,
                totaal_absent INT NOT NULL DEFAULT 0,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
                    ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (besluit_id),
                CONSTRAINT fk_besluit_stats_besluit
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
        $this->conn->exec('DROP TABLE IF EXISTS besluit_stats');
    }

    public function clearTable(): void
    {
        $this->conn->exec('TRUNCATE TABLE besluit_stats');
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
                SUM(CASE WHEN LOWER(TRIM(COALESCE(soort, ''))) = 'voor' THEN 1 ELSE 0 END) AS voor,
                SUM(CASE WHEN LOWER(TRIM(COALESCE(soort, ''))) = 'tegen' THEN 1 ELSE 0 END) AS tegen,
                SUM(CASE WHEN LOWER(TRIM(COALESCE(soort, ''))) = '' THEN 1 ELSE 0 END) AS onthouden,
                SUM(CASE WHEN LOWER(TRIM(COALESCE(soort, ''))) = 'niet deelgenomen' THEN 1 ELSE 0 END) AS absent
            FROM stemming
            WHERE besluit_id = :besluit_id
              AND (is_verwijderd = 0 OR is_verwijderd IS NULL)
        ");

        $upsertStmt = $this->conn->prepare("
            INSERT INTO besluit_stats (
                besluit_id,
                totaal_voor,
                totaal_tegen,
                totaal_onthouden,
                totaal_absent
            ) VALUES (
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
            $votes = $voteStmt->fetch(PDO::FETCH_ASSOC) ?: [];

            $upsertStmt->execute([
                'besluit_id' => $besluitId,
                'voor' => (int) ($votes['voor'] ?? 0),
                'tegen' => (int) ($votes['tegen'] ?? 0),
                'onthouden' => (int) ($votes['onthouden'] ?? 0),
                'absent' => (int) ($votes['absent'] ?? 0),
            ]);
        }

        return count($besluitIds);
    }

    /** stats */
    public function getBesluitRankings(): array
    {
        $base = "
            SELECT
                b.id AS besluit_id,
                b.naam,
                bs.totaal_voor,
                bs.totaal_tegen,
                bs.totaal_onthouden,
                bs.totaal_absent,
                ABS(bs.totaal_voor - bs.totaal_tegen) AS stemverschil
            FROM besluit_stats bs
            INNER JOIN besluit b
                ON b.id = bs.besluit_id
        ";

        $queries = [
            'Biggest difference' => $base . "
                ORDER BY stemverschil DESC, b.id ASC
                LIMIT 5
            ",
            'Closest vote in a besluit' => $base . "
                ORDER BY stemverschil ASC, b.id ASC
                LIMIT 5
            ",

        ];

        $results = [];
        foreach ($queries as $title => $sql) {
            $stmt = $this->conn->query($sql);
            $results[$title] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        return $results;
    }        
}

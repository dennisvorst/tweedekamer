<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

class StatsAdminModel
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    /** persoon */
    public function createPersoonStatsTable(): void
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS persoon_stats (
                persoon_id VARCHAR(36) NOT NULL PRIMARY KEY,
                totaal_nevenfuncties INT NOT NULL DEFAULT 0,
                totaal_opleidingen INT NOT NULL DEFAULT 0,
                totaal_loopbanen INT NOT NULL DEFAULT 0,
                totaal_stemmen INT NOT NULL DEFAULT 0,
                totaal_voor_stemmen INT NOT NULL DEFAULT 0,
                totaal_tegen_stemmen INT NOT NULL DEFAULT 0,
                totaal_anders_stemmen INT NOT NULL DEFAULT 0,
                percentage_voor DECIMAL(5,2) DEFAULT NULL,
                percentage_tegen DECIMAL(5,2) DEFAULT NULL,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
                    ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ";

        $this->pdo->exec($sql);

        $indexes = [
            "CREATE INDEX idx_persoon_stats_totaal_stemmen ON persoon_stats (totaal_stemmen)",
            "CREATE INDEX idx_persoon_stats_totaal_voor_stemmen ON persoon_stats (totaal_voor_stemmen)",
            "CREATE INDEX idx_persoon_stats_totaal_tegen_stemmen ON persoon_stats (totaal_tegen_stemmen)",
            "CREATE INDEX idx_persoon_stats_totaal_anders_stemmen ON persoon_stats (totaal_anders_stemmen)",
            "CREATE INDEX idx_persoon_stats_percentage_voor ON persoon_stats (percentage_voor)",
            "CREATE INDEX idx_persoon_stats_percentage_tegen ON persoon_stats (percentage_tegen)",
            "CREATE INDEX idx_persoon_stats_totaal_nevenfuncties ON persoon_stats (totaal_nevenfuncties)",
            "CREATE INDEX idx_persoon_stats_totaal_opleidingen ON persoon_stats (totaal_opleidingen)",
            "CREATE INDEX idx_persoon_stats_totaal_loopbanen ON persoon_stats (totaal_loopbanen)",
        ];

        foreach ($indexes as $indexSql) {
            try {
                $this->pdo->exec($indexSql);
            } catch (\PDOException $e) {
                // ignore duplicate-index style errors
            }
        }
    }

    public function createFractieStatsTable(): void
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS fractie_stats (
                fractie_id VARCHAR(36) NOT NULL PRIMARY KEY,
                totaal_stemmen INT NOT NULL DEFAULT 0,
                voor_stemmen INT NOT NULL DEFAULT 0,
                tegen_stemmen INT NOT NULL DEFAULT 0,
                anders_stemmen INT NOT NULL DEFAULT 0,
                voor_percentage DECIMAL(5,2) DEFAULT NULL,
                tegen_percentage DECIMAL(5,2) DEFAULT NULL,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
                    ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ";

        $this->pdo->exec($sql);

        $indexes = [
            "CREATE INDEX idx_fractie_stats_totaal ON fractie_stats (totaal_stemmen)",
            "CREATE INDEX idx_fractie_stats_voor ON fractie_stats (voor_stemmen)",
            "CREATE INDEX idx_fractie_stats_tegen ON fractie_stats (tegen_stemmen)",
            "CREATE INDEX idx_fractie_stats_anders ON fractie_stats (anders_stemmen)",
            "CREATE INDEX idx_fractie_stats_voor_pct ON fractie_stats (voor_percentage)",
            "CREATE INDEX idx_fractie_stats_tegen_pct ON fractie_stats (tegen_percentage)",
        ];

        foreach ($indexes as $indexSql) {
            try {
                $this->pdo->exec($indexSql);
            } catch (\PDOException $e) {
                // ignore duplicate-index style errors
            }
        }
    }

    public function dropTable(string $tableName): void
    {
        $this->pdo->exec(sprintf('DROP TABLE IF EXISTS `%s`', $tableName));
    }

    public function clearTable(string $tableName): void
    {
        $this->pdo->exec(sprintf('TRUNCATE TABLE `%s`', $tableName));
    }

    public function getAllPersoonIds(): array
    {
        $stmt = $this->pdo->query("
            SELECT id
            FROM persoon
            ORDER BY id
        ");

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getAllFractieIds(): array
    {
        $stmt = $this->pdo->query("
            SELECT DISTINCT fractie_id
            FROM stemming
            WHERE fractie_id IS NOT NULL
              AND (is_verwijderd = 0 OR is_verwijderd IS NULL)
            ORDER BY fractie_id
        ");

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getAllPersonen(): array
    {
        $sql = "
            SELECT *
            FROM persoon
            WHERE (is_verwijderd = 0 OR is_verwijderd IS NULL)
            ORDER BY id
        ";

        $stmt = $this->pdo->query($sql);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function buildPersoonStatsRow(string $persoonId): array
    {
        $stats = [
            'persoon_id' => $persoonId,
            'totaal_nevenfuncties' => 0,
            'totaal_opleidingen' => 0,
            'totaal_loopbanen' => 0,
            'totaal_stemmen' => 0,
            'totaal_voor_stemmen' => 0,
            'totaal_tegen_stemmen' => 0,
            'totaal_anders_stemmen' => 0,
            'percentage_voor' => null,
            'percentage_tegen' => null,
        ];

        // stemmen
        $sqlStemmen = "
            SELECT
                COUNT(*) AS totaal_stemmen,
                SUM(CASE WHEN LOWER(TRIM(COALESCE(soort, ''))) = 'voor' THEN 1 ELSE 0 END) AS totaal_voor_stemmen,
                SUM(CASE WHEN LOWER(TRIM(COALESCE(soort, ''))) = 'tegen' THEN 1 ELSE 0 END) AS totaal_tegen_stemmen,
                SUM(
                    CASE
                        WHEN soort IS NULL
                        OR LOWER(TRIM(COALESCE(soort, ''))) NOT IN ('voor', 'tegen')
                        THEN 1 ELSE 0
                    END
                ) AS totaal_anders_stemmen
            FROM stemming
            WHERE persoon_id = :persoon_id
            AND (is_verwijderd = 0 OR is_verwijderd IS NULL)
        ";

        $stmtStemmen = $this->pdo->prepare($sqlStemmen);
        $stmtStemmen->bindValue(':persoon_id', $persoonId);
        $stmtStemmen->execute();
        $stemData = $stmtStemmen->fetch(\PDO::FETCH_ASSOC) ?: [];

        $stats['totaal_stemmen'] = (int)($stemData['totaal_stemmen'] ?? 0);
        $stats['totaal_voor_stemmen'] = (int)($stemData['totaal_voor_stemmen'] ?? 0);
        $stats['totaal_tegen_stemmen'] = (int)($stemData['totaal_tegen_stemmen'] ?? 0);
        $stats['totaal_anders_stemmen'] = (int)($stemData['totaal_anders_stemmen'] ?? 0);

        $totaalVoorTegen = $stats['totaal_voor_stemmen'] + $stats['totaal_tegen_stemmen'];

        if ($totaalVoorTegen > 0) {
            $stats['percentage_voor'] = round(($stats['totaal_voor_stemmen'] * 100) / $totaalVoorTegen, 2);
            $stats['percentage_tegen'] = round(($stats['totaal_tegen_stemmen'] * 100) / $totaalVoorTegen, 2);
        }

        // nevenfuncties
        $sqlNevenfuncties = "
            SELECT COUNT(*) AS totaal_nevenfuncties
            FROM persoon_nevenfunctie
            WHERE persoon_id = :persoon_id
            AND (is_verwijderd = 0 OR is_verwijderd IS NULL)
        ";

        $stmtNevenfuncties = $this->pdo->prepare($sqlNevenfuncties);
        $stmtNevenfuncties->bindValue(':persoon_id', $persoonId);
        $stmtNevenfuncties->execute();
        $stats['totaal_nevenfuncties'] = (int)$stmtNevenfuncties->fetchColumn();

        // opleidingen
        $sqlOpleidingen = "
            SELECT COUNT(*) AS totaal_opleidingen
            FROM persoon_onderwijs
            WHERE persoon_id = :persoon_id
            AND (is_verwijderd = 0 OR is_verwijderd IS NULL)
        ";

        $stmtOpleidingen = $this->pdo->prepare($sqlOpleidingen);
        $stmtOpleidingen->bindValue(':persoon_id', $persoonId);
        $stmtOpleidingen->execute();
        $stats['totaal_opleidingen'] = (int)$stmtOpleidingen->fetchColumn();

        // loopbanen
        $sqlLoopbanen = "
            SELECT COUNT(*) AS totaal_loopbanen
            FROM persoon_loopbaan
            WHERE persoon_id = :persoon_id
            AND (is_verwijderd = 0 OR is_verwijderd IS NULL)
        ";

        $stmtLoopbanen = $this->pdo->prepare($sqlLoopbanen);
        $stmtLoopbanen->bindValue(':persoon_id', $persoonId);
        $stmtLoopbanen->execute();
        $stats['totaal_loopbanen'] = (int)$stmtLoopbanen->fetchColumn();

        return $stats;
    }

    public function savePersoonStatsRow(array $stats): void
    {
        $sql = "
            INSERT INTO persoon_stats (
                persoon_id,
                totaal_nevenfuncties,
                totaal_opleidingen,
                totaal_loopbanen,
                totaal_stemmen,
                totaal_voor_stemmen,
                totaal_tegen_stemmen,
                totaal_anders_stemmen,
                percentage_voor,
                percentage_tegen
            ) VALUES (
                :persoon_id,
                :totaal_nevenfuncties,
                :totaal_opleidingen,
                :totaal_loopbanen,
                :totaal_stemmen,
                :totaal_voor_stemmen,
                :totaal_tegen_stemmen,
                :totaal_anders_stemmen,
                :percentage_voor,
                :percentage_tegen
            )
            ON DUPLICATE KEY UPDATE
                totaal_nevenfuncties = VALUES(totaal_nevenfuncties),
                totaal_opleidingen = VALUES(totaal_opleidingen),
                totaal_loopbanen = VALUES(totaal_loopbanen),
                totaal_stemmen = VALUES(totaal_stemmen),
                totaal_voor_stemmen = VALUES(totaal_voor_stemmen),
                totaal_tegen_stemmen = VALUES(totaal_tegen_stemmen),
                totaal_anders_stemmen = VALUES(totaal_anders_stemmen),
                percentage_voor = VALUES(percentage_voor),
                percentage_tegen = VALUES(percentage_tegen)
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':persoon_id' => $stats['persoon_id'],
            ':totaal_nevenfuncties' => $stats['totaal_nevenfuncties'],
            ':totaal_opleidingen' => $stats['totaal_opleidingen'],
            ':totaal_loopbanen' => $stats['totaal_loopbanen'],
            ':totaal_stemmen' => $stats['totaal_stemmen'],
            ':totaal_voor_stemmen' => $stats['totaal_voor_stemmen'],
            ':totaal_tegen_stemmen' => $stats['totaal_tegen_stemmen'],
            ':totaal_anders_stemmen' => $stats['totaal_anders_stemmen'],
            ':percentage_voor' => $stats['percentage_voor'],
            ':percentage_tegen' => $stats['percentage_tegen'],
        ]);
    }

    public function rebuildPersoonStatsBatch(int $offset = 0, int $limit = 100): array
    {
        $personen = $this->getAllPersonen();
        $total = count($personen);
        $batch = array_slice($personen, $offset, $limit);

        foreach ($batch as $persoon) {
            $persoonId = (string)$persoon['id'];
            $this->rebuildPersoonStat($persoonId);
        }

        return [
            'processed' => count($batch),
            'offset' => $offset,
            'next_offset' => $offset + count($batch),
            'total' => $total,
            'done' => ($offset + count($batch)) >= $total,
        ];
    }

    public function rebuildPersoonStat(string $persoonId): void
    {
        $stats = $this->buildPersoonStatsRow($persoonId);
        $this->savePersoonStatsRow($stats);
    }    

    /** fractie */
    public function rebuildFractieStatsBatch(int $offset = 0, int $limit = 100): array
    {
        $fractieIds = $this->getAllFractieIds();
        $total = count($fractieIds);
        $batch = array_slice($fractieIds, $offset, $limit);

        foreach ($batch as $fractieId) {
            $this->rebuildFractieStat((string)$fractieId);
        }

        return [
            'processed' => count($batch),
            'offset' => $offset,
            'next_offset' => $offset + count($batch),
            'total' => $total,
            'done' => ($offset + count($batch)) >= $total,
        ];
    }

    public function rebuildFractieStat(string $fractieId): void
    {
        $sql = "
            INSERT INTO fractie_stats (
                fractie_id,
                totaal_stemmen,
                voor_stemmen,
                tegen_stemmen,
                anders_stemmen,
                voor_percentage,
                tegen_percentage
            )
            SELECT
                s.fractie_id,
                COUNT(*) AS totaal_stemmen,
                SUM(CASE WHEN LOWER(TRIM(COALESCE(s.soort, ''))) = 'voor' THEN 1 ELSE 0 END) AS voor_stemmen,
                SUM(CASE WHEN LOWER(TRIM(COALESCE(s.soort, ''))) = 'tegen' THEN 1 ELSE 0 END) AS tegen_stemmen,
                SUM(
                    CASE
                        WHEN s.soort IS NULL
                          OR LOWER(TRIM(COALESCE(s.soort, ''))) NOT IN ('voor', 'tegen')
                        THEN 1 ELSE 0
                    END
                ) AS anders_stemmen,
                ROUND(
                    100.0 * SUM(CASE WHEN LOWER(TRIM(COALESCE(s.soort, ''))) = 'voor' THEN 1 ELSE 0 END)
                    / NULLIF(
                        SUM(CASE WHEN LOWER(TRIM(COALESCE(s.soort, ''))) IN ('voor', 'tegen') THEN 1 ELSE 0 END),
                        0
                    ),
                    2
                ) AS voor_percentage,
                ROUND(
                    100.0 * SUM(CASE WHEN LOWER(TRIM(COALESCE(s.soort, ''))) = 'tegen' THEN 1 ELSE 0 END)
                    / NULLIF(
                        SUM(CASE WHEN LOWER(TRIM(COALESCE(s.soort, ''))) IN ('voor', 'tegen') THEN 1 ELSE 0 END),
                        0
                    ),
                    2
                ) AS tegen_percentage
            FROM stemming s
            WHERE s.fractie_id = :fractie_id
              AND (s.is_verwijderd = 0 OR s.is_verwijderd IS NULL)
            GROUP BY s.fractie_id
            ON DUPLICATE KEY UPDATE
                totaal_stemmen = VALUES(totaal_stemmen),
                voor_stemmen = VALUES(voor_stemmen),
                tegen_stemmen = VALUES(tegen_stemmen),
                anders_stemmen = VALUES(anders_stemmen),
                voor_percentage = VALUES(voor_percentage),
                tegen_percentage = VALUES(tegen_percentage)
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':fractie_id', $fractieId);
        $stmt->execute();
    }
}

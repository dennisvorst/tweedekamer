<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

final class PersoonStatsModel
{
    public function __construct(private PDO $conn)
    {
    }

    public function createTable(): void
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS persoon_stats (
                persoon_id VARCHAR(36) NOT NULL,
                totaal_voor_stemmen INT NOT NULL DEFAULT 0,
                totaal_tegen_stemmen INT NOT NULL DEFAULT 0,
                totaal_onthouden_stemmen INT NOT NULL DEFAULT 0,
                totaal_absent_stemmen INT NOT NULL DEFAULT 0,
                totaal_stemmen INT NOT NULL DEFAULT 0,
                totaal_nevenfuncties INT NOT NULL DEFAULT 0,
                totaal_nevenfuncties_betaald INT NOT NULL DEFAULT 0,
                totaal_opleidingen INT NOT NULL DEFAULT 0,
                totaal_loopbanen INT NOT NULL DEFAULT 0,
                ervaring_dagen INT NOT NULL DEFAULT 0,
                ervaring_jaren DECIMAL(8,2) NOT NULL DEFAULT 0,
                is_active TINYINT(1) NOT NULL DEFAULT 0,
                percentage_voor DECIMAL(6,2) NOT NULL DEFAULT 0,
                percentage_tegen DECIMAL(6,2) NOT NULL DEFAULT 0,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
                    ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (persoon_id),
                CONSTRAINT fk_persoon_stats_persoon
                    FOREIGN KEY (persoon_id)
                    REFERENCES persoon(id)
                    ON DELETE CASCADE
                    ON UPDATE CASCADE
            ) ENGINE=InnoDB;
        ";

        $this->conn->exec($sql);
    }

    public function dropTable(): void
    {
        $this->conn->exec('DROP TABLE IF EXISTS persoon_stats');
    }

    public function clearTable(): void
    {
        $this->conn->exec('TRUNCATE TABLE persoon_stats');
    }

    public function countForBuild(): int
    {
        $stmt = $this->conn->query('SELECT COUNT(*) FROM persoon WHERE (is_verwijderd = 0 OR is_verwijderd IS NULL)');
        return (int) $stmt->fetchColumn();
    }

    public function buildSumsBatch(int $offset, int $limit): int
    {
        $stmt = $this->conn->prepare("
            SELECT id
            FROM persoon
            WHERE (is_verwijderd = 0 OR is_verwijderd IS NULL)
            ORDER BY id
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $persoonIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (!$persoonIds) {
            return 0;
        }

        $voteStmt = $this->conn->prepare("
            SELECT
                SUM(CASE WHEN LOWER(TRIM(COALESCE(soort, ''))) = 'voor' THEN 1 ELSE 0 END) AS voor,
                SUM(CASE WHEN LOWER(TRIM(COALESCE(soort, ''))) = 'tegen' THEN 1 ELSE 0 END) AS tegen,
                SUM(CASE WHEN LOWER(TRIM(COALESCE(soort, ''))) = '' THEN 1 ELSE 0 END) AS onthouden,
                SUM(CASE WHEN LOWER(TRIM(COALESCE(soort, ''))) = 'niet deelgenomen' THEN 1 ELSE 0 END) AS absent
            FROM stemming
            WHERE persoon_id = :persoon_id
              AND (is_verwijderd = 0 OR is_verwijderd IS NULL)
        ");

        $nevenfunctieStmt = $this->conn->prepare("
            SELECT COUNT(*)
            FROM persoon_nevenfunctie
            WHERE persoon_id = :persoon_id
              AND (is_verwijderd = 0 OR is_verwijderd IS NULL)
        ");

        $nevenfunctieBetaaldStmt = $this->conn->prepare("
            SELECT COUNT(DISTINCT pn.id)
            FROM persoon_nevenfunctie pn
            INNER JOIN persoon_nevenfunctie_inkomsten pni
                ON pni.nevenfunctie_id = pn.id
            WHERE pn.persoon_id = :persoon_id
              AND (pn.is_verwijderd = 0 OR pn.is_verwijderd IS NULL)
              AND (pni.is_verwijderd = 0 OR pni.is_verwijderd IS NULL)
        ");

        $onderwijsStmt = $this->conn->prepare("
            SELECT COUNT(*)
            FROM persoon_onderwijs
            WHERE persoon_id = :persoon_id
              AND (is_verwijderd = 0 OR is_verwijderd IS NULL)
        ");

        $loopbaanStmt = $this->conn->prepare("
            SELECT COUNT(*)
            FROM persoon_loopbaan
            WHERE persoon_id = :persoon_id
              AND (is_verwijderd = 0 OR is_verwijderd IS NULL)
        ");

        $ervaringStmt = $this->conn->prepare("
            SELECT
                COALESCE(SUM(DATEDIFF(COALESCE(tot_en_met, CURDATE()), van)), 0)
            FROM fractie_zetel_persoon
            WHERE persoon_id = :persoon_id
              AND (is_verwijderd = 0 OR is_verwijderd IS NULL)
              AND van IS NOT NULL
        ");

        $activeStmt = $this->conn->prepare("
            SELECT EXISTS (
                SELECT 1
                FROM fractie_zetel_persoon
                WHERE persoon_id = :persoon_id
                  AND (is_verwijderd = 0 OR is_verwijderd IS NULL)
                  AND tot_en_met IS NULL
            )
        ");

        $upsertStmt = $this->conn->prepare("
            INSERT INTO persoon_stats (
                persoon_id,
                totaal_voor_stemmen,
                totaal_tegen_stemmen,
                totaal_onthouden_stemmen,
                totaal_absent_stemmen,
                totaal_stemmen,
                totaal_nevenfuncties,
                totaal_nevenfuncties_betaald,
                totaal_opleidingen,
                totaal_loopbanen,
                ervaring_dagen,
                ervaring_jaren,
                percentage_voor,
                percentage_tegen,
                is_active
            ) VALUES (
                :persoon_id,
                :voor,
                :tegen,
                :onthouden,
                :absent,
                :totaal,
                :nevenfuncties,
                :nevenfuncties_betaald,
                :opleidingen,
                :loopbanen,
                :ervaring_dagen,
                :ervaring_jaren,
                :percentage_voor,
                :percentage_tegen,
                :is_active
            )
            ON DUPLICATE KEY UPDATE
                totaal_voor_stemmen = VALUES(totaal_voor_stemmen),
                totaal_tegen_stemmen = VALUES(totaal_tegen_stemmen),
                totaal_onthouden_stemmen = VALUES(totaal_onthouden_stemmen),
                totaal_absent_stemmen = VALUES(totaal_absent_stemmen),
                totaal_stemmen = VALUES(totaal_stemmen),
                totaal_nevenfuncties = VALUES(totaal_nevenfuncties),
                totaal_nevenfuncties_betaald = VALUES(totaal_nevenfuncties_betaald),
                totaal_opleidingen = VALUES(totaal_opleidingen),
                totaal_loopbanen = VALUES(totaal_loopbanen),
                ervaring_dagen = VALUES(ervaring_dagen),
                ervaring_jaren = VALUES(ervaring_jaren),
                percentage_voor = VALUES(percentage_voor),
                percentage_tegen = VALUES(percentage_tegen),
                is_active = VALUES(is_active)
        ");

        foreach ($persoonIds as $persoonId) {
            $voteStmt->execute(['persoon_id' => $persoonId]);
            $votes = $voteStmt->fetch(PDO::FETCH_ASSOC) ?: [];

            $voor = (int) ($votes['voor'] ?? 0);
            $tegen = (int) ($votes['tegen'] ?? 0);
            $onthouden = (int) ($votes['onthouden'] ?? 0);
            $absent = (int) ($votes['absent'] ?? 0);
            $totaal = $voor + $tegen + $onthouden;

            $nevenfunctieStmt->execute(['persoon_id' => $persoonId]);
            $totaalNevenfuncties = (int) $nevenfunctieStmt->fetchColumn();

            $nevenfunctieBetaaldStmt->execute(['persoon_id' => $persoonId]);
            $totaalNevenfunctiesBetaald = (int) $nevenfunctieBetaaldStmt->fetchColumn();

            $onderwijsStmt->execute(['persoon_id' => $persoonId]);
            $totaalOpleidingen = (int) $onderwijsStmt->fetchColumn();

            $loopbaanStmt->execute(['persoon_id' => $persoonId]);
            $totaalLoopbanen = (int) $loopbaanStmt->fetchColumn();

            $ervaringStmt->execute(['persoon_id' => $persoonId]);
            $ervaringDagen = (int) $ervaringStmt->fetchColumn();
            $ervaringJaren = round($ervaringDagen / 365.25, 2);

            $activeStmt->execute(['persoon_id' => $persoonId]);
            $isActive = (int) $activeStmt->fetchColumn();

            $percentageVoor = $totaal > 0 ? ($voor / $totaal) * 100 : 0;
            $percentageTegen = $totaal > 0 ? ($tegen / $totaal) * 100 : 0;

            $upsertStmt->execute([
                'persoon_id' => $persoonId,
                'voor' => $voor,
                'tegen' => $tegen,
                'onthouden' => $onthouden,
                'absent' => $absent,
                'totaal' => $totaal,
                'nevenfuncties' => $totaalNevenfuncties,
                'nevenfuncties_betaald' => $totaalNevenfunctiesBetaald,
                'opleidingen' => $totaalOpleidingen,
                'loopbanen' => $totaalLoopbanen,
                'ervaring_dagen' => $ervaringDagen,
                'ervaring_jaren' => $ervaringJaren,
                'percentage_voor' => $percentageVoor,
                'percentage_tegen' => $percentageTegen,
                'is_active' => $isActive,
            ]);
        }

        return count($persoonIds);
    }

    public function buildPercentagesBatch(int $offset, int $limit): int
    {
        $stmt = $this->conn->prepare("
            SELECT
                persoon_id,
                totaal_stemmen,
                totaal_voor_stemmen,
                totaal_tegen_stemmen
            FROM persoon_stats
            ORDER BY persoon_id
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
            UPDATE persoon_stats
            SET
                percentage_voor = :percentage_voor,
                percentage_tegen = :percentage_tegen
            WHERE persoon_id = :persoon_id
        ");

        foreach ($rows as $row) {
            $totaalStemmen = (int) $row['totaal_stemmen'];

            $percentageVoor = $totaalStemmen > 0
                ? (((int) $row['totaal_voor_stemmen']) / $totaalStemmen) * 100
                : 0;

            $percentageTegen = $totaalStemmen > 0
                ? (((int) $row['totaal_tegen_stemmen']) / $totaalStemmen) * 100
                : 0;

            $updateStmt->execute([
                'persoon_id' => $row['persoon_id'],
                'percentage_voor' => $percentageVoor,
                'percentage_tegen' => $percentageTegen,
            ]);
        }

        return count($rows);
    }
}

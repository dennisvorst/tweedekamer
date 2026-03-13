<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

final class StatsModel
{
    private const DEFAULT_DIRECTION = 'desc';

    public function __construct(private PDO $pdo)
    {
    }

    private function fetchAll(string $sql): array
    {
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function normalizeDirection(string $direction, string $default = self::DEFAULT_DIRECTION): string
    {
        $direction = strtolower(trim($direction));

        return in_array($direction, ['asc', 'desc'], true) ? $direction : $default;
    }

    private function sanitizeSort(string $sort, array $allowedSorts, string $default): string
    {
        return array_key_exists($sort, $allowedSorts) ? $allowedSorts[$sort] : $allowedSorts[$default];
    }

    private function getPersonBaseSelect(): string
    {
        return "
            SELECT
                p.id,
                p.roepnaam,
                p.achternaam,
                ps.totaal_nevenfuncties,
                ps.totaal_nevenfuncties_betaald,
                ps.totaal_opleidingen,
                ps.totaal_loopbanen,
                ps.ervaring_dagen,
                ps.ervaring_jaren,
                ps.is_active,
                ps.totaal_stemmen,
                ps.totaal_voor_stemmen,
                ps.totaal_tegen_stemmen,
                ps.totaal_onthouden_stemmen,
                ps.totaal_absent_stemmen,
                ps.percentage_voor,
                ps.percentage_tegen
            FROM persoon_stats ps
            INNER JOIN persoon p ON p.id = ps.persoon_id
            WHERE (p.is_verwijderd = 0 OR p.is_verwijderd IS NULL)
        ";
    }

    private function getFractieBaseSelect(): string
    {
        return "
            SELECT
                f.id,
                f.afkorting,
                f.naam_nl,
                f.naam_en,
                s.totaal_stemmen,
                s.voor_stemmen,
                s.tegen_stemmen,
                s.onthouden_stemmen,
                s.absent_stemmen,
                s.voor_percentage,
                s.tegen_percentage
            FROM fractie_stats s
            INNER JOIN fractie f
                ON f.id = s.fractie_id
            WHERE (f.is_verwijderd = 0 OR f.is_verwijderd IS NULL)
        ";
    }

    public function getPersonRankings(): array
    {
        $base = $this->getPersonBaseSelect();

        $queries = [
            'Most votes' => $base . "
                ORDER BY ps.totaal_stemmen DESC, p.achternaam ASC, p.roepnaam ASC
                LIMIT 5
            ",
            'Most voor votes' => $base . "
                ORDER BY ps.totaal_voor_stemmen DESC, p.achternaam ASC, p.roepnaam ASC
                LIMIT 5
            ",
            'Most tegen votes' => $base . "
                ORDER BY ps.totaal_tegen_stemmen DESC, p.achternaam ASC, p.roepnaam ASC
                LIMIT 5
            ",
            'Most onthoudingen' => $base . "
                ORDER BY ps.totaal_onthouden_stemmen DESC, p.achternaam ASC, p.roepnaam ASC
                LIMIT 5
            ",
            'Best % voor' => $base . "
                AND ps.percentage_voor IS NOT NULL
                ORDER BY ps.percentage_voor DESC, ps.totaal_voor_stemmen DESC, p.achternaam ASC, p.roepnaam ASC
                LIMIT 5
            ",
            'Best % tegen' => $base . "
                AND ps.percentage_tegen IS NOT NULL
                ORDER BY ps.percentage_tegen DESC, ps.totaal_tegen_stemmen DESC, p.achternaam ASC, p.roepnaam ASC
                LIMIT 5
            ",
        ];

        $results = [];
        foreach ($queries as $title => $sql) {
            $results[$title] = $this->fetchAll($sql);
        }

        return $results;
    }

    public function getFractieRankings(): array
    {
        $base = $this->getFractieBaseSelect();

        $queries = [
            'Most votes' => $base . "
                ORDER BY s.totaal_stemmen DESC, f.naam_nl ASC, f.naam_en ASC
                LIMIT 5
            ",
            'Most voor votes' => $base . "
                ORDER BY s.voor_stemmen DESC, f.naam_nl ASC, f.naam_en ASC
                LIMIT 5
            ",
            'Most tegen votes' => $base . "
                ORDER BY s.tegen_stemmen DESC, f.naam_nl ASC, f.naam_en ASC
                LIMIT 5
            ",
            'Most onthoudingen' => $base . "
                ORDER BY s.onthouden_stemmen DESC, f.naam_nl ASC, f.naam_en ASC
                LIMIT 5
            ",
            'Best % voor' => $base . "
                AND s.voor_percentage IS NOT NULL
                ORDER BY s.voor_percentage DESC, s.voor_stemmen DESC, f.naam_nl ASC, f.naam_en ASC
                LIMIT 5
            ",
            'Best % tegen' => $base . "
                AND s.tegen_percentage IS NOT NULL
                ORDER BY s.tegen_percentage DESC, s.tegen_stemmen DESC, f.naam_nl ASC, f.naam_en ASC
                LIMIT 5
            ",
        ];

        $results = [];
        foreach ($queries as $title => $sql) {
            $results[$title] = $this->fetchAll($sql);
        }

        return $results;
    }

    public function getActivePersonStats(string $sort = 'jaren_ervaring', string $direction = 'desc'): array
    {
        $allowedSorts = [
            'naam' => 'naam',
            'geslacht' => 'geslacht',
            'leeftijd' => 'leeftijd',
            'geboorteplaats' => 'geboorteplaats',
            'fractie_naam' => 'fractie_naam',
            'jaren_ervaring' => 'ervaring_dagen',
            'totaal_stemmen' => 'totaal_stemmen',
            'percentage_voor' => 'percentage_voor',
            'percentage_tegen' => 'percentage_tegen',
        ];

        $orderBy = $this->sanitizeSort($sort, $allowedSorts, 'jaren_ervaring');
        $direction = $this->normalizeDirection($direction);

        $sql = "
            SELECT
                p.id,
                TRIM(CONCAT(COALESCE(p.roepnaam, ''), ' ', COALESCE(p.achternaam, ''))) AS naam,
                COALESCE(p.geslacht, '') AS geslacht,
                CASE
                    WHEN p.geboortedatum IS NULL THEN NULL
                    ELSE TIMESTAMPDIFF(YEAR, p.geboortedatum, CURDATE())
                END AS leeftijd,
                COALESCE(p.geboorteplaats, '') AS geboorteplaats,
                COALESCE(active_fractie.afkorting, '') AS fractie_afkorting,
                COALESCE(active_fractie.naam_nl, active_fractie.afkorting, active_fractie.naam_en, '') AS fractie_naam,
                COALESCE(ps.ervaring_dagen, 0) AS ervaring_dagen,
                ps.ervaring_jaren AS jaren_ervaring,
                COALESCE(ps.totaal_stemmen, 0) AS totaal_stemmen,
                ps.percentage_voor,
                ps.percentage_tegen
            FROM persoon_stats ps
            INNER JOIN persoon p
                ON p.id = ps.persoon_id
            LEFT JOIN fractie_zetel active_fz
                ON active_fz.id = (
                    SELECT active_fzp.fractie_zetel_id
                    FROM fractie_zetel_persoon active_fzp
                    WHERE active_fzp.persoon_id = p.id
                      AND (active_fzp.is_verwijderd = 0 OR active_fzp.is_verwijderd IS NULL)
                      AND active_fzp.tot_en_met IS NULL
                    ORDER BY active_fzp.van DESC, active_fzp.id DESC
                    LIMIT 1
                )
            LEFT JOIN fractie active_fractie
                ON active_fractie.id = active_fz.fractie_id
               AND (active_fractie.is_verwijderd = 0 OR active_fractie.is_verwijderd IS NULL)
            WHERE (p.is_verwijderd = 0 OR p.is_verwijderd IS NULL)
              AND ps.is_active = 1
            ORDER BY {$orderBy} {$direction}, naam ASC
        ";

        return $this->fetchAll($sql);
    }

    public function getPersonStatsList(string $sort = 'totaal_stemmen', string $direction = 'desc'): array
    {
        $allowedSorts = [
            'naam' => 'naam',
            'totaal_stemmen' => 'totaal_stemmen',
            'totaal_voor_stemmen' => 'totaal_voor_stemmen',
            'totaal_tegen_stemmen' => 'totaal_tegen_stemmen',
            'totaal_onthouden_stemmen' => 'totaal_onthouden_stemmen',
            'totaal_absent_stemmen' => 'totaal_absent_stemmen',
            'percentage_voor' => 'percentage_voor',
            'percentage_tegen' => 'percentage_tegen',
        ];

        $orderBy = $this->sanitizeSort($sort, $allowedSorts, 'totaal_stemmen');
        $direction = $this->normalizeDirection($direction);

        $sql = "
            SELECT
                p.id,
                TRIM(CONCAT(COALESCE(p.roepnaam, ''), ' ', COALESCE(p.achternaam, ''))) AS naam,
                ps.totaal_stemmen,
                ps.totaal_voor_stemmen,
                ps.totaal_tegen_stemmen,
                ps.totaal_onthouden_stemmen,
                ps.totaal_absent_stemmen,
                ps.percentage_voor,
                ps.percentage_tegen
            FROM persoon_stats ps
            INNER JOIN persoon p
                ON p.id = ps.persoon_id
            WHERE (p.is_verwijderd = 0 OR p.is_verwijderd IS NULL)
            ORDER BY {$orderBy} {$direction}, naam ASC
        ";

        return $this->fetchAll($sql);
    }

    public function getFractieStatsList(string $sort = 'totaal_stemmen', string $direction = 'desc'): array
    {
        $allowedSorts = [
            'fractie_naam' => 'fractie_naam',
            'totaal_stemmen' => 'totaal_stemmen',
            'voor_stemmen' => 'voor_stemmen',
            'tegen_stemmen' => 'tegen_stemmen',
            'onthouden_stemmen' => 'onthouden_stemmen',
            'absent_stemmen' => 'absent_stemmen',
            'voor_percentage' => 'voor_percentage',
            'tegen_percentage' => 'tegen_percentage',
        ];

        $orderBy = $this->sanitizeSort($sort, $allowedSorts, 'totaal_stemmen');
        $direction = $this->normalizeDirection($direction);

        $sql = "
            SELECT
                f.id,
                COALESCE(f.naam_nl, f.afkorting, f.naam_en, '') AS fractie_naam,
                s.totaal_stemmen,
                s.voor_stemmen,
                s.tegen_stemmen,
                s.onthouden_stemmen,
                s.absent_stemmen,
                s.voor_percentage,
                s.tegen_percentage
            FROM fractie_stats s
            INNER JOIN fractie f
                ON f.id = s.fractie_id
            WHERE (f.is_verwijderd = 0 OR f.is_verwijderd IS NULL)
            ORDER BY {$orderBy} {$direction}, fractie_naam ASC
        ";

        return $this->fetchAll($sql);
    }

    public function getBesluitStatsList(string $sort = 'totaal_stemmen', string $direction = 'desc'): array
    {
        $allowedSorts = [
            'besluit' => 'besluit',
            'status' => 'status',
            'stemmingssoort' => 'stemmingssoort',
            'totaal_stemmen' => 'totaal_stemmen',
            'voor_stemmen' => 'voor_stemmen',
            'tegen_stemmen' => 'tegen_stemmen',
            'onthouden_stemmen' => 'onthouden_stemmen',
            'absent_stemmen' => 'absent_stemmen',
        ];

        $orderBy = $this->sanitizeSort($sort, $allowedSorts, 'totaal_stemmen');
        $direction = $this->normalizeDirection($direction);

        $sql = "
            SELECT
                b.id,
                COALESCE(NULLIF(TRIM(b.besluittekst), ''), NULLIF(TRIM(b.besluit_soort), ''), CONCAT('Besluit ', b.id)) AS besluit,
                COALESCE(b.status, '') AS status,
                COALESCE(b.stemmingssoort, '') AS stemmingssoort,
                COALESCE(bs.totaal_voor, 0) + COALESCE(bs.totaal_tegen, 0) + COALESCE(bs.totaal_onthouden, 0) AS totaal_stemmen,
                COALESCE(bs.totaal_voor, 0) AS voor_stemmen,
                COALESCE(bs.totaal_tegen, 0) AS tegen_stemmen,
                COALESCE(bs.totaal_onthouden, 0) AS onthouden_stemmen,
                COALESCE(bs.totaal_absent, 0) AS absent_stemmen
            FROM besluit_stats bs
            INNER JOIN besluit b
                ON bs.besluit_id = b.id
            WHERE (b.is_verwijderd = 0 OR b.is_verwijderd IS NULL)
            ORDER BY {$orderBy} {$direction}, besluit ASC
        ";

        return $this->fetchAll($sql);
    }
}

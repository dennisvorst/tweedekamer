<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

class PersonModel
{
    public static function getListDefaults(): array
    {
        return [
            'sort' => 'achternaam',
            'direction' => 'asc',
            'page' => 1,
            'filters' => array_fill_keys(self::getAllowedFilters(), ''),
        ];
    }

    public static function getAllowedFilters(): array
    {
        return ['nummer', 'roepnaam', 'achternaam', 'geboortedatum', 'geboortedatum_tot', 'geslacht', 'active_only'];
    }

    public function __construct(
        private PDO $pdo
    ) {
    }

    public function getPersons(
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
            SELECT id, nummer, roepnaam, achternaam, geboortedatum, geslacht
            FROM persoon
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

    public function countPersons(array $filters): int
    {
        $params = [];
        $where = $this->buildWhereClause($filters, $params);

        $sql = "
            SELECT COUNT(*)
            FROM persoon
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

    public function getPersonDetails(string $id): ?array
    {
        $sql = "
            SELECT * 
            FROM persoon
            WHERE id = :id
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();

        $person = $stmt->fetch();

        if ($person === false) {
            return null;
        }

        $person['contactinformatie'] = $this->getPersonContactInformation($id);
        $person['display_name'] = $this->buildPersonDisplayName($person);

        return $person;
    }

    public function getPersonContactInformation(string $persoonId): array
    {
        $sql = "
            SELECT * 
            FROM persoon_contactinformatie
            WHERE persoon_id = :persoon_id
              AND (is_verwijderd IS NULL OR is_verwijderd = 0)
            ORDER BY
                CASE WHEN gewicht IS NULL THEN 1 ELSE 0 END,
                gewicht ASC,
                soort ASC,
                waarde ASC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':persoon_id', $persoonId);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getPersonOnderwijs(string $persoonId): array
    {
        $sql = "
            SELECT * 
            FROM persoon_onderwijs
            WHERE Persoon_Id = :persoon_id
            AND (is_verwijderd = 0 OR is_verwijderd IS NULL)
            ORDER BY
                CASE WHEN Gewicht IS NULL THEN 999999 ELSE Gewicht END,
                Van ASC,
                tot_en_met ASC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':persoon_id', $persoonId);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getPersonLoopbaan(string $persoonId): array
    {
        $sql = "
            SELECT * 
            FROM persoon_loopbaan
            WHERE persoon_id = :persoon_id
            AND (is_verwijderd = 0 OR is_verwijderd IS NULL)
            ORDER BY
                CASE WHEN gewicht IS NULL THEN 999999 ELSE gewicht END,
                van ASC,
                tot_en_met ASC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':persoon_id', $persoonId);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getPersonNevenfuncties(string $persoonId): array
    {
        $sql = "
            SELECT * 
            FROM persoon_nevenfunctie
            WHERE persoon_id = :persoon_id
            AND (is_verwijderd = 0 OR is_verwijderd IS NULL)
            ORDER BY
                CASE WHEN gewicht IS NULL THEN 999999 ELSE gewicht END,
                periode_van ASC,
                periode_tot_en_met ASC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':persoon_id', $persoonId);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getPersonNevenfunctieInkomsten(string $persoonId): array
    {
        $sql = "
            SELECT i.*
            FROM persoon_nevenfunctie_inkomsten i
            INNER JOIN persoon_nevenfunctie n
                ON n.id = i.nevenfunctie_id
            WHERE n.persoon_id = :persoon_id
            AND (n.is_verwijderd = 0 OR n.is_verwijderd IS NULL)
            AND (i.is_verwijderd = 0 OR i.is_verwijderd IS NULL)
            ORDER BY
                n.gewicht ASC,
                n.periode_van ASC,
                i.jaar ASC,
                i.id ASC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':persoon_id', $persoonId);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    private function buildPersonDisplayName(array $person): string
    {
        $parts = [];

        $titels = trim((string)($person['titels'] ?? ''));
        $roepnaam = trim((string)($person['roepnaam'] ?? ''));
        $voornamen = trim((string)($person['voornamen'] ?? ''));
        $initialen = trim((string)($person['initialen'] ?? ''));
        $tussenvoegsel = trim((string)($person['tussenvoegsel'] ?? ''));
        $achternaam = trim((string)($person['achternaam'] ?? ''));

        if ($titels !== '') {
            $parts[] = $titels;
        }

        if ($roepnaam !== '') {
            $parts[] = $roepnaam;
        } elseif ($voornamen !== '') {
            $parts[] = $voornamen;
        } elseif ($initialen !== '') {
            $parts[] = $initialen;
        }

        if ($tussenvoegsel !== '') {
            $parts[] = $tussenvoegsel;
        }

        if ($achternaam !== '') {
            $parts[] = $achternaam;
        }

        return trim(implode(' ', $parts));
    }

    private function buildWhereClause(array $filters, array &$params): string
    {
        $conditions = [];

        if (!empty($filters['nummer'])) {
            $conditions[] = 'nummer LIKE :nummer';
            $params[':nummer'] = '%' . $filters['nummer'] . '%';
        }

        if (!empty($filters['roepnaam'])) {
            $conditions[] = 'roepnaam LIKE :roepnaam';
            $params[':roepnaam'] = '%' . $filters['roepnaam'] . '%';
        }

        if (!empty($filters['achternaam'])) {
            $conditions[] = 'achternaam LIKE :achternaam';
            $params[':achternaam'] = '%' . $filters['achternaam'] . '%';
        }

        $geboortedatumVan = trim((string)($filters['geboortedatum'] ?? ''));
        $geboortedatumTot = trim((string)($filters['geboortedatum_tot'] ?? ''));

        if ($geboortedatumVan !== '' && $geboortedatumTot !== '') {
            $conditions[] = 'geboortedatum BETWEEN :geboortedatum_van AND :geboortedatum_tot';
            $params[':geboortedatum_van'] = min($geboortedatumVan, $geboortedatumTot);
            $params[':geboortedatum_tot'] = max($geboortedatumVan, $geboortedatumTot);
        } elseif ($geboortedatumVan !== '') {
            $conditions[] = 'geboortedatum >= :geboortedatum_van';
            $params[':geboortedatum_van'] = $geboortedatumVan;
        } elseif ($geboortedatumTot !== '') {
            $conditions[] = 'geboortedatum <= :geboortedatum_tot';
            $params[':geboortedatum_tot'] = $geboortedatumTot;
        }

        if (!empty($filters['geslacht'])) {
            $conditions[] = 'geslacht = :geslacht';
            $params[':geslacht'] = $filters['geslacht'];
        }

        if (($filters['active_only'] ?? '') === '1') {
            $conditions[] = "
                EXISTS (
                    SELECT 1
                    FROM fractie_zetel_persoon fzp
                    WHERE fzp.persoon_id = persoon.id
                    AND (fzp.is_verwijderd = 0 OR fzp.is_verwijderd IS NULL)
                    AND fzp.tot_en_met IS NULL
                )
            ";
        }

        return !empty($conditions) ? ' AND ' . implode(' AND ', $conditions) : '';
    }

    public function getPersonBesluitStemRows(string $persoonId): array
    {
        $sql = "
            SELECT
                s.id AS stemming_id,
                s.persoon_id,
                s.fractie_id,
                s.besluit_id,
                s.soort AS stem_soort,
                s.vergissing,

                f.afkorting AS fractie_afkorting,
                f.naam_nl AS fractie_naam_nl,
                f.naam_en AS fractie_naam_en,

                b.id AS besluit_id,
                b.besluit_soort,
                b.stemmingssoort,
                b.besluittekst,
                b.status AS besluit_status,

                a.id AS agendapunt_id,
                a.nummer AS agendapunt_nummer,
                a.onderwerp AS agendapunt_onderwerp,

                act.id AS activiteit_id,
                act.datum AS activiteit_datum,
                act.soort AS activiteit_soort,
                act.nummer AS activiteit_nummer,
                act.onderwerp AS activiteit_onderwerp
            FROM stemming s
            LEFT JOIN fractie f
                ON f.id = s.fractie_id
            AND (f.is_verwijderd = 0 OR f.is_verwijderd IS NULL)
            LEFT JOIN besluit b
                ON b.id = s.besluit_id
            AND (b.is_verwijderd = 0 OR b.is_verwijderd IS NULL)
            LEFT JOIN agendapunt a
                ON a.id = b.agendapunt_id
            AND (a.is_verwijderd = 0 OR a.is_verwijderd IS NULL)
            LEFT JOIN activiteit act
                ON act.id = a.activiteit_id
            WHERE s.persoon_id = :persoon_id
            AND (s.is_verwijderd = 0 OR s.is_verwijderd IS NULL)
            ORDER BY
                act.datum DESC,
                act.aanvangstijd DESC,
                b.id DESC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':persoon_id', $persoonId);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getPersonFractieRows(string $persoonId): array
    {
        $sql = "
            SELECT
                fzp.id,
                fzp.persoon_id,
                fzp.fractie_zetel_id,
                fzp.functie,
                fzp.van,
                fzp.tot_en_met,

                fz.fractie_id,
                fz.gewicht,

                f.id AS fractie_id,
                f.nummer AS fractie_nummer,
                f.afkorting AS fractie_afkorting,
                f.naam_nl AS fractie_naam_nl,
                f.naam_en AS fractie_naam_en,
                f.aantal_zetels
            FROM fractie_zetel_persoon fzp
            INNER JOIN fractie_zetel fz
                ON fz.id = fzp.fractie_zetel_id
            AND (fz.is_verwijderd = 0 OR fz.is_verwijderd IS NULL)
            INNER JOIN fractie f
                ON f.id = fz.fractie_id
            AND (f.is_verwijderd = 0 OR f.is_verwijderd IS NULL)
            WHERE fzp.persoon_id = :persoon_id
            AND (fzp.is_verwijderd = 0 OR fzp.is_verwijderd IS NULL)
            ORDER BY
                CASE WHEN fzp.tot_en_met IS NULL THEN 0 ELSE 1 END,
                fzp.tot_en_met DESC,
                fzp.van DESC,
                CASE WHEN fz.gewicht IS NULL THEN 999999 ELSE fz.gewicht END,
                f.naam_nl ASC,
                f.naam_en ASC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':persoon_id', $persoonId);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}

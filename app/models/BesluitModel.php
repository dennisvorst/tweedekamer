<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

class BesluitModel
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    public function getBesluitDetails(string $id): ?array
    {
        $sql = "
            SELECT
                b.id,
                b.agendapunt_id,
                b.besluit_soort,
                b.stemmingssoort,
                b.besluittekst,
                b.opmerking,
                b.status,
                b.agendapunt_zaak_besluitvolgorde,

                a.id AS parent_agendapunt_id,
                a.nummer AS agendapunt_nummer,
                a.onderwerp AS agendapunt_onderwerp,
                a.aanvangstijd AS agendapunt_aanvangstijd,
                a.eindtijd AS agendapunt_eindtijd,
                a.rubriek AS agendapunt_rubriek,
                a.noot AS agendapunt_noot,
                a.status AS agendapunt_status,

                act.id AS activiteit_id,
                act.soort AS activiteit_soort,
                act.nummer AS activiteit_nummer,
                act.onderwerp AS activiteit_onderwerp,
                act.datum AS activiteit_datum,
                act.aanvangstijd AS activiteit_aanvangstijd,
                act.eindtijd AS activiteit_eindtijd,
                act.locatie AS activiteit_locatie
            FROM besluit b
            LEFT JOIN agendapunt a
                ON a.id = b.agendapunt_id
               AND (a.is_verwijderd = 0 OR a.is_verwijderd IS NULL)
            LEFT JOIN activiteit act
                ON act.id = a.activiteit_id
            WHERE b.id = :id
              AND (b.is_verwijderd = 0 OR b.is_verwijderd IS NULL)
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();

        $result = $stmt->fetch();

        return $result === false ? null : $result;
    }

    public function getBesluitStemmingRows(string $besluitId): array
    {
        $sql = "
            SELECT
                s.id,
                s.persoon_id,
                s.fractie_id,
                s.besluit_id,
                s.sid_actor_lid,
                s.sid_actor_fractie,
                s.soort,
                s.fractie_grootte,
                s.actor_naam,
                s.actor_fractie,
                s.vergissing,

                p.roepnaam,
                p.achternaam,
                p.nummer AS persoon_nummer,

                f.afkorting AS fractie_afkorting,
                f.naam_nl AS fractie_naam_nl,
                f.naam_en AS fractie_naam_en
            FROM stemming s
            LEFT JOIN persoon p
                ON p.id = s.persoon_id
               AND (p.is_verwijderd = 0 OR p.is_verwijderd IS NULL)
            LEFT JOIN fractie f
                ON f.id = s.fractie_id
               AND (f.is_verwijderd = 0 OR f.is_verwijderd IS NULL)
            WHERE s.besluit_id = :besluit_id
              AND (s.is_verwijderd = 0 OR s.is_verwijderd IS NULL)
            ORDER BY
                CASE s.soort
                    WHEN 'voor' THEN 1
                    WHEN 'tegen' THEN 2
                    WHEN 'niet deelgenomen' THEN 3
                    ELSE 9
                END,
                f.naam_nl ASC,
                p.achternaam ASC,
                p.roepnaam ASC,
                s.actor_naam ASC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':besluit_id', $besluitId);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getBesluitStemmingFractieSamenvatting(string $besluitId): array
    {
        $sql = "
            SELECT
                COALESCE(f.id, s.fractie_id) AS fractie_id,
                COALESCE(f.naam_nl, f.naam_en, s.actor_fractie, f.afkorting, 'Onbekend') AS fractie_naam,
                COALESCE(f.afkorting, '') AS fractie_afkorting,
                MAX(COALESCE(s.fractie_grootte, 0)) AS fractie_grootte,
                LOWER(TRIM(COALESCE(b.stemmingssoort, ''))) AS stemmingssoort_normalized,
                SUM(
                    CASE
                        WHEN LOWER(TRIM(COALESCE(s.soort, ''))) = 'voor' THEN
                            CASE
                                WHEN LOWER(TRIM(COALESCE(b.stemmingssoort, ''))) = 'hoofdelijk' THEN 1
                                WHEN COALESCE(s.fractie_grootte, 0) > 0 THEN s.fractie_grootte
                                ELSE 1
                            END
                        ELSE 0
                    END
                ) AS voor_count,

                SUM(
                    CASE
                        WHEN LOWER(TRIM(COALESCE(s.soort, ''))) = 'tegen' THEN
                            CASE
                                WHEN LOWER(TRIM(COALESCE(b.stemmingssoort, ''))) = 'hoofdelijk' THEN 1
                                WHEN COALESCE(s.fractie_grootte, 0) > 0 THEN s.fractie_grootte
                                ELSE 1
                            END
                        ELSE 0
                    END
                ) AS tegen_count,

                SUM(
                    CASE
                        WHEN LOWER(TRIM(COALESCE(s.soort, ''))) = 'niet deelgenomen'
                        THEN
                            CASE
                                WHEN LOWER(TRIM(COALESCE(b.stemmingssoort, ''))) = 'hoofdelijk' THEN 1
                                WHEN COALESCE(s.fractie_grootte, 0) > 0 THEN s.fractie_grootte
                                ELSE 1
                            END
                        ELSE 0
                    END
                ) AS absent_count,

                SUM(
                    CASE
                        WHEN LOWER(TRIM(COALESCE(s.soort, ''))) = ''
                        THEN
                            CASE
                                WHEN LOWER(TRIM(COALESCE(b.stemmingssoort, ''))) = 'hoofdelijk' THEN 1
                                WHEN COALESCE(s.fractie_grootte, 0) > 0 THEN s.fractie_grootte
                                ELSE 1
                            END
                        ELSE 0
                    END
                ) AS onthouden_count

            FROM stemming s
            INNER JOIN besluit b
                ON b.id = s.besluit_id
            LEFT JOIN fractie f
                ON f.id = s.fractie_id
            AND (f.is_verwijderd = 0 OR f.is_verwijderd IS NULL)
            WHERE s.besluit_id = :besluit_id
            AND (s.is_verwijderd = 0 OR s.is_verwijderd IS NULL)
            GROUP BY
                COALESCE(f.id, s.fractie_id),
                COALESCE(f.naam_nl, f.naam_en, s.actor_fractie, f.afkorting, 'Onbekend'),
                COALESCE(f.afkorting, ''),
                LOWER(TRIM(COALESCE(b.stemmingssoort, '')))
            ORDER BY fractie_naam ASC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':besluit_id', $besluitId);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}

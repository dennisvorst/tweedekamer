INSERT INTO fractie_stem_stats (
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
        / NULLIF(SUM(CASE WHEN LOWER(TRIM(COALESCE(s.soort, ''))) IN ('voor', 'tegen') THEN 1 ELSE 0 END), 0),
        2
    ) AS voor_percentage,
    ROUND(
        100.0 * SUM(CASE WHEN LOWER(TRIM(COALESCE(s.soort, ''))) = 'tegen' THEN 1 ELSE 0 END)
        / NULLIF(SUM(CASE WHEN LOWER(TRIM(COALESCE(s.soort, ''))) IN ('voor', 'tegen') THEN 1 ELSE 0 END), 0),
        2
    ) AS tegen_percentage
FROM stemming s
WHERE s.fractie_id IS NOT NULL
  AND (s.is_verwijderd = 0 OR s.is_verwijderd IS NULL)
GROUP BY s.fractie_id
ON DUPLICATE KEY UPDATE
    totaal_stemmen = VALUES(totaal_stemmen),
    voor_stemmen = VALUES(voor_stemmen),
    tegen_stemmen = VALUES(tegen_stemmen),
    anders_stemmen = VALUES(anders_stemmen),
    voor_percentage = VALUES(voor_percentage),
    tegen_percentage = VALUES(tegen_percentage);
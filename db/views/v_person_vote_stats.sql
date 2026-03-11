CREATE OR REPLACE VIEW v_person_vote_stats AS
SELECT
    p.*,
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
INNER JOIN persoon p ON p.id = s.persoon_id
WHERE (s.is_verwijderd = 0 OR s.is_verwijderd IS NULL)
  AND (p.is_verwijderd = 0 OR p.is_verwijderd IS NULL)
GROUP BY p.id;
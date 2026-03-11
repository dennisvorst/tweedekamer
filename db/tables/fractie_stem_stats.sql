CREATE TABLE IF NOT EXISTS fractie_stem_stats (
    fractie_id VARCHAR(36) NOT NULL PRIMARY KEY,
    totaal_stemmen INT NOT NULL DEFAULT 0,
    voor_stemmen INT NOT NULL DEFAULT 0,
    tegen_stemmen INT NOT NULL DEFAULT 0,
    anders_stemmen INT NOT NULL DEFAULT 0,
    voor_percentage DECIMAL(5,2) DEFAULT NULL,
    tegen_percentage DECIMAL(5,2) DEFAULT NULL,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_fractie_stem_stats_fractie
        FOREIGN KEY (fractie_id) REFERENCES fractie(id)
);

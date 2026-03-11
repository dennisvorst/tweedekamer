-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Gegenereerd op: 11 mrt 2026 om 07:00
-- Serverversie: 8.0.45
-- PHP-versie: 8.2.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `test_gov`
--

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `activiteit`
--

CREATE TABLE `activiteit` (
  `id` varchar(36) COLLATE utf8mb4_general_ci NOT NULL,
  `soort` varchar(128) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nummer` varchar(128) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `onderwerp` text COLLATE utf8mb4_general_ci,
  `datumsoort` varchar(32) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `datum` datetime(6) DEFAULT NULL,
  `aanvangstijd` datetime(6) DEFAULT NULL,
  `eindtijd` datetime(6) DEFAULT NULL,
  `locatie` varchar(191) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `besloten` tinyint(1) DEFAULT NULL,
  `status` varchar(64) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `vergaderjaar` varchar(16) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `kamer` varchar(32) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `noot` text COLLATE utf8mb4_general_ci,
  `vrsnummer` varchar(32) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `sidvoortouw` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `voortouwnaam` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `voortouwafkorting` varchar(64) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `voortouwkortenaam` varchar(64) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `voortouwcommissie_id` varchar(36) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `aanvraagdatum` datetime(6) DEFAULT NULL,
  `datumverzoekeersteverlenging` datetime(6) DEFAULT NULL,
  `datummededelingeersteverlenging` datetime(6) DEFAULT NULL,
  `datumverzoektweedeverlenging` datetime(6) DEFAULT NULL,
  `datummededelingtweedeverlenging` datetime(6) DEFAULT NULL,
  `vervaldatum` datetime(6) DEFAULT NULL,
  `gewijzigd_op` datetime(6) DEFAULT NULL,
  `api_gewijzigd_op` datetime(6) DEFAULT NULL,
  `is_verwijderd` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `activiteit_actor`
--

CREATE TABLE `activiteit_actor` (
  `id` varchar(36) COLLATE utf8mb4_general_ci NOT NULL,
  `activiteit_id` varchar(36) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `actor_naam` tinytext COLLATE utf8mb4_general_ci,
  `actor_fractie` tinytext COLLATE utf8mb4_general_ci,
  `relatie` tinytext COLLATE utf8mb4_general_ci,
  `volgorde` int DEFAULT NULL,
  `functie` tinytext COLLATE utf8mb4_general_ci,
  `spreektijd` tinytext COLLATE utf8mb4_general_ci,
  `sid_actor` varchar(70) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `persoon_id` varchar(36) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `fractie_id` varchar(36) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `commissie_id` varchar(36) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `gewijzigd_op` datetime DEFAULT NULL,
  `api_gewijzigd_op` datetime DEFAULT NULL,
  `is_verwijderd` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `agendapunt`
--

CREATE TABLE `agendapunt` (
  `id` varchar(36) COLLATE utf8mb4_general_ci NOT NULL,
  `activiteit_id` varchar(36) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nummer` varchar(128) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `onderwerp` text COLLATE utf8mb4_general_ci,
  `aanvangstijd` datetime DEFAULT NULL,
  `eindtijd` datetime DEFAULT NULL,
  `volgorde` int DEFAULT NULL,
  `rubriek` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `noot` text COLLATE utf8mb4_general_ci,
  `status` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `gewijzigd_op` datetime DEFAULT NULL,
  `api_gewijzigd_op` datetime DEFAULT NULL,
  `is_verwijderd` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `besluit`
--

CREATE TABLE `besluit` (
  `id` varchar(36) COLLATE utf8mb4_general_ci NOT NULL,
  `agendapunt_id` varchar(36) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `besluit_soort` varchar(128) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `stemmingssoort` enum('Hoofdelijk','Met handopsteken','Zonder stemming') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `besluittekst` text COLLATE utf8mb4_general_ci,
  `opmerking` text COLLATE utf8mb4_general_ci,
  `status` enum('Besluit','Concept voorstel','Nog te verwerken besluit','Voorstel') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `agendapunt_zaak_besluitvolgorde` int DEFAULT NULL,
  `gewijzigd_op` datetime DEFAULT NULL,
  `api_gewijzigd_op` datetime DEFAULT NULL,
  `is_verwijderd` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `commissie`
--

CREATE TABLE `commissie` (
  `id` varchar(36) COLLATE utf8mb4_general_ci NOT NULL,
  `nummer` varchar(128) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `soort` tinytext COLLATE utf8mb4_general_ci,
  `afkorting` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `naam_nl` tinytext COLLATE utf8mb4_general_ci,
  `naam_en` tinytext COLLATE utf8mb4_general_ci,
  `naam_web_nl` tinytext COLLATE utf8mb4_general_ci,
  `naam_web_en` tinytext COLLATE utf8mb4_general_ci,
  `inhoudsopgave` tinytext COLLATE utf8mb4_general_ci,
  `datum_actief` datetime DEFAULT NULL,
  `datum_inactief` datetime DEFAULT NULL,
  `gewijzigd_op` datetime DEFAULT NULL,
  `api_gewijzigd_op` datetime DEFAULT NULL,
  `is_verwijderd` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `commissie_contact_informatie`
--

CREATE TABLE `commissie_contact_informatie` (
  `id` varchar(36) COLLATE utf8mb4_general_ci NOT NULL,
  `commissie_id` varchar(36) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `soort` varchar(128) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `waarde` varchar(128) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `gewijzigd_op` datetime DEFAULT NULL,
  `api_gewijzigd_op` datetime DEFAULT NULL,
  `is_verwijderd` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `commissie_zetel`
--

CREATE TABLE `commissie_zetel` (
  `id` varchar(36) COLLATE utf8mb4_general_ci NOT NULL,
  `commissie_id` varchar(36) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `gewicht` int DEFAULT NULL,
  `gewijzigd_op` datetime DEFAULT NULL,
  `api_gewijzigd_op` datetime DEFAULT NULL,
  `is_verwijderd` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `commissie_zetel_vast_persoon`
--

CREATE TABLE `commissie_zetel_vast_persoon` (
  `id` varchar(36) COLLATE utf8mb4_general_ci NOT NULL,
  `commissie_zetel_id` varchar(36) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `persoon_id` varchar(36) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `functie` varchar(36) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `van` varchar(36) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tot_en_met` varchar(36) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `gewijzigd_op` datetime DEFAULT NULL,
  `api_gewijzigd_op` datetime DEFAULT NULL,
  `is_verwijderd` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `commissie_zetel_vast_vacature`
--

CREATE TABLE `commissie_zetel_vast_vacature` (
  `id` varchar(36) COLLATE utf8mb4_general_ci NOT NULL,
  `commissie_zetel_id` varchar(36) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `fractie_id` varchar(36) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `functie` varchar(36) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `van` varchar(36) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `totenmet` varchar(36) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `gewijzigd_op` datetime DEFAULT NULL,
  `api_gewijzigd_op` datetime DEFAULT NULL,
  `is_verwijderd` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `commissie_zetel_vervanger_persoon`
--

CREATE TABLE `commissie_zetel_vervanger_persoon` (
  `id` varchar(36) COLLATE utf8mb4_general_ci NOT NULL,
  `commissie_zetel_id` varchar(36) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `persoon_id` varchar(36) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `functie` varchar(36) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `van` varchar(36) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `totenmet` varchar(36) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `gewijzigd_op` datetime DEFAULT NULL,
  `api_gewijzigd_op` datetime DEFAULT NULL,
  `is_verwijderd` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `commissie_zetel_vervanger_vacature`
--

CREATE TABLE `commissie_zetel_vervanger_vacature` (
  `id` varchar(36) COLLATE utf8mb4_general_ci NOT NULL,
  `commissie_zetel_id` varchar(36) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `fractie_id` varchar(36) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `functie` varchar(36) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `van` varchar(36) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `totenmet` varchar(36) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `gewijzigd_op` datetime DEFAULT NULL,
  `api_gewijzigd_op` datetime DEFAULT NULL,
  `is_verwijderd` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `document`
--

CREATE TABLE `document` (
  `id` varchar(36) COLLATE utf8mb4_general_ci NOT NULL,
  `soort` varchar(128) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `document_nummer` varchar(64) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `titel` varchar(512) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `onderwerp` varchar(512) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `datum` date DEFAULT NULL,
  `vergaderjaar` varchar(32) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `kamer` tinyint DEFAULT NULL,
  `volgnummer` varchar(32) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `citeertitel` varchar(512) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `alias` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `datum_registratie` date DEFAULT NULL,
  `datum_ontvangst` date DEFAULT NULL,
  `content_type` varchar(64) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `content_length` int DEFAULT NULL,
  `gewijzigd_op` datetime DEFAULT NULL,
  `api_gewijzigd_op` datetime DEFAULT NULL,
  `is_verwijderd` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `document_actor`
--

CREATE TABLE `document_actor` (
  `id` varchar(36) COLLATE utf8mb4_general_ci NOT NULL,
  `document_id` varchar(36) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `persoon_id` varchar(36) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `fractie_id` varchar(36) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `commissie_id` varchar(36) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `actor_naam` tinytext COLLATE utf8mb4_general_ci,
  `actor_fractie` tinytext COLLATE utf8mb4_general_ci,
  `functie` tinytext COLLATE utf8mb4_general_ci,
  `relatie` tinytext COLLATE utf8mb4_general_ci,
  `sidactor` tinytext COLLATE utf8mb4_general_ci,
  `gewijzigd_op` datetime DEFAULT NULL,
  `api_gewijzigd_op` datetime DEFAULT NULL,
  `is_verwijderd` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `document_publicatie`
--

CREATE TABLE `document_publicatie` (
  `id` varchar(36) COLLATE utf8mb4_general_ci NOT NULL,
  `documentversie_id` varchar(36) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `identifier` varchar(36) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `document_type` varchar(36) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `file_name` varchar(36) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `source` varchar(36) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `content_length` int DEFAULT NULL,
  `content_type` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `url` tinytext COLLATE utf8mb4_general_ci,
  `publicatie_datum` datetime DEFAULT NULL,
  `gewijzigd_op` datetime DEFAULT NULL,
  `api_gewijzigd_op` datetime DEFAULT NULL,
  `is_verwijderd` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `document_publicatie_metadata`
--

CREATE TABLE `document_publicatie_metadata` (
  `id` varchar(36) COLLATE utf8mb4_general_ci NOT NULL,
  `documentversie_id` varchar(36) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `publicatie_datum` datetime DEFAULT NULL,
  `identifier` varchar(36) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `document_type` varchar(36) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `file_name` varchar(36) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `source` varchar(36) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `content_length` int DEFAULT NULL,
  `content_type` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `url` tinytext COLLATE utf8mb4_general_ci,
  `gewijzigd_op` datetime DEFAULT NULL,
  `api_gewijzigd_op` datetime DEFAULT NULL,
  `is_verwijderd` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `document_versie`
--

CREATE TABLE `document_versie` (
  `id` varchar(36) COLLATE utf8mb4_general_ci NOT NULL,
  `document_id` varchar(36) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `externe_identifier` tinytext COLLATE utf8mb4_general_ci,
  `status` tinytext COLLATE utf8mb4_general_ci,
  `versie_nummer` int DEFAULT NULL,
  `bestands_grootte` int DEFAULT NULL,
  `extensie` tinytext COLLATE utf8mb4_general_ci,
  `datum` date DEFAULT NULL,
  `gewijzigd_op` datetime DEFAULT NULL,
  `api_gewijzigd_op` datetime DEFAULT NULL,
  `is_verwijderd` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `fractie`
--

CREATE TABLE `fractie` (
  `id` varchar(36) COLLATE utf8mb4_general_ci NOT NULL,
  `nummer` varchar(128) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `afkorting` varchar(64) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `naam_nl` varchar(191) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `naam_en` varchar(191) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `aantal_zetels` smallint DEFAULT NULL,
  `aantal_stemmen` int DEFAULT NULL,
  `datum_actief` varchar(30) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `datum_inactief` datetime DEFAULT NULL,
  `content_type` varchar(64) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `content_length` int DEFAULT NULL,
  `gewijzigd_op` datetime DEFAULT NULL,
  `api_gewijzigd_op` datetime DEFAULT NULL,
  `is_verwijderd` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `fractie_stats`
--

CREATE TABLE `fractie_stats` (
  `fractie_id` varchar(36) COLLATE utf8mb4_general_ci NOT NULL,
  `totaal_stemmen` int NOT NULL DEFAULT '0',
  `voor_stemmen` int NOT NULL DEFAULT '0',
  `tegen_stemmen` int NOT NULL DEFAULT '0',
  `anders_stemmen` int NOT NULL DEFAULT '0',
  `voor_percentage` decimal(5,2) DEFAULT NULL,
  `tegen_percentage` decimal(5,2) DEFAULT NULL,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `fractie_zetel`
--

CREATE TABLE `fractie_zetel` (
  `id` varchar(36) COLLATE utf8mb4_general_ci NOT NULL,
  `fractie_Id` varchar(36) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `gewicht` int DEFAULT NULL,
  `gewijzigd_op` datetime NOT NULL,
  `api_gewijzigd_op` datetime NOT NULL,
  `is_verwijderd` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `fractie_zetel_persoon`
--

CREATE TABLE `fractie_zetel_persoon` (
  `id` varchar(36) COLLATE utf8mb4_general_ci NOT NULL,
  `fractie_zetel_id` varchar(36) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `persoon_id` varchar(36) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `functie` tinytext COLLATE utf8mb4_general_ci,
  `van` datetime DEFAULT NULL,
  `tot_en_met` datetime DEFAULT NULL,
  `gewijzigd_op` datetime DEFAULT NULL,
  `api_gewijzigd_op` datetime DEFAULT NULL,
  `is_verwijderd` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `fractie_zetel_vacature`
--

CREATE TABLE `fractie_zetel_vacature` (
  `id` varchar(36) COLLATE utf8mb4_general_ci NOT NULL,
  `fractie_zetel_id` varchar(36) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `functie` tinytext COLLATE utf8mb4_general_ci,
  `van` datetime DEFAULT NULL,
  `tot_en_met` datetime DEFAULT NULL,
  `gewijzigd_op` datetime DEFAULT NULL,
  `api_gewijzigd_op` datetime DEFAULT NULL,
  `is_verwijderd` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `kamerstukdossier`
--

CREATE TABLE `kamerstukdossier` (
  `id` varchar(36) COLLATE utf8mb4_general_ci NOT NULL,
  `titel` text COLLATE utf8mb4_general_ci,
  `citeer_titel` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `alias` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nummer` varchar(128) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `toevoeging` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `hoogste_volgnummer` int DEFAULT NULL,
  `afgesloten` tinyint(1) DEFAULT NULL,
  `kamer` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `gewijzigd_op` datetime DEFAULT NULL,
  `api_gewijzigd_op` datetime DEFAULT NULL,
  `is_verwijderd` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `persoon`
--

CREATE TABLE `persoon` (
  `id` varchar(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `nummer` varchar(128) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `titels` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `initialen` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tussenvoegsel` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `achternaam` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `voornamen` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `roepnaam` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `geslacht` char(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `functie` enum('Eerste Kamerlid','Tweede Kamerlid','Oud Kamerlid') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `geboortedatum` date DEFAULT NULL,
  `geboorteplaats` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `geboorteland` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `overlijdensdatum` date DEFAULT NULL,
  `overlijdensplaats` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `woonplaats` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `land` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `content_type` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `content_length` int DEFAULT NULL,
  `fractie_label` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `gewijzigd_op` datetime DEFAULT NULL,
  `api_gewijzigd_op` datetime DEFAULT NULL,
  `is_verwijderd` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `persoon_contactinformatie`
--

CREATE TABLE `persoon_contactinformatie` (
  `id` varchar(36) COLLATE utf8mb4_general_ci NOT NULL,
  `persoon_id` varchar(36) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `soort` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `waarde` varchar(128) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `gewicht` int DEFAULT NULL,
  `gewijzigd_op` datetime DEFAULT NULL,
  `api_gewijzigd_op` datetime DEFAULT NULL,
  `is_verwijderd` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `persoon_geschenk`
--

CREATE TABLE `persoon_geschenk` (
  `id` varchar(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `persoon_id` varchar(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `omschrijving` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `datum` datetime DEFAULT NULL,
  `gewicht` int DEFAULT NULL,
  `gewijzigd_op` datetime DEFAULT NULL,
  `api_gewijzigd_op` datetime DEFAULT NULL,
  `is_verwijderd` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `persoon_loopbaan`
--

CREATE TABLE `persoon_loopbaan` (
  `id` varchar(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `persoon_id` varchar(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `functie` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `werkgever` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `omschrijving_nl` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `omschrijving_en` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `plaats` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `van` int DEFAULT NULL,
  `tot_en_met` int DEFAULT NULL,
  `gewicht` int DEFAULT NULL,
  `gewijzigd_op` datetime DEFAULT NULL,
  `api_gewijzigd_op` datetime DEFAULT NULL,
  `is_verwijderd` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `persoon_nevenfunctie`
--

CREATE TABLE `persoon_nevenfunctie` (
  `id` varchar(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `persoon_id` varchar(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `omschrijving` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `periode_van` datetime DEFAULT NULL,
  `periode_tot_en_met` datetime DEFAULT NULL,
  `is_actief` tinyint(1) DEFAULT NULL,
  `vergoeding_soort` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `vergoeding_toelichting` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `gewicht` int DEFAULT NULL,
  `gewijzigd_op` datetime DEFAULT NULL,
  `api_gewijzigd_op` datetime DEFAULT NULL,
  `is_verwijderd` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `persoon_nevenfunctie_inkomsten`
--

CREATE TABLE `persoon_nevenfunctie_inkomsten` (
  `id` varchar(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `nevenfunctie_id` varchar(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `jaar` int DEFAULT NULL,
  `bedrag_soort` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `bedrag_voorvoegsel` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `bedrag_valuta` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `bedrag` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `bedrag_achtervoegsel` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `frequentie` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `frequentie_beschrijving` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `opmerking` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `gewijzigd_op` datetime DEFAULT NULL,
  `api_gewijzigd_op` datetime DEFAULT NULL,
  `is_verwijderd` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `persoon_onderwijs`
--

CREATE TABLE `persoon_onderwijs` (
  `id` varchar(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `persoon_id` varchar(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `opleiding_nl` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `opleiding_en` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `instelling` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `plaats` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `van` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tot_en_met` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `gewicht` int DEFAULT NULL,
  `gewijzigd_op` datetime DEFAULT NULL,
  `api_gewijzigd_op` datetime DEFAULT NULL,
  `is_verwijderd` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `persoon_reis`
--

CREATE TABLE `persoon_reis` (
  `id` varchar(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `persoon_id` varchar(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `doel` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `bestemming` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `van` datetime DEFAULT NULL,
  `tot_en_met` datetime DEFAULT NULL,
  `betaald_door` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `gewicht` int DEFAULT NULL,
  `gewijzigd_op` datetime DEFAULT NULL,
  `api_gewijzigd_op` datetime DEFAULT NULL,
  `is_verwijderd` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `persoon_stats`
--

CREATE TABLE `persoon_stats` (
  `persoon_id` varchar(36) COLLATE utf8mb4_general_ci NOT NULL,
  `totaal_nevenfuncties` int NOT NULL DEFAULT '0',
  `totaal_opleidingen` int NOT NULL DEFAULT '0',
  `totaal_loopbanen` int NOT NULL DEFAULT '0',
  `totaal_stemmen` int NOT NULL DEFAULT '0',
  `totaal_voor_stemmen` int NOT NULL DEFAULT '0',
  `totaal_tegen_stemmen` int NOT NULL DEFAULT '0',
  `totaal_anders_stemmen` int NOT NULL DEFAULT '0',
  `percentage_voor` decimal(5,2) DEFAULT NULL,
  `percentage_tegen` decimal(5,2) DEFAULT NULL,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `reservering`
--

CREATE TABLE `reservering` (
  `id` varchar(36) COLLATE utf8mb4_general_ci NOT NULL,
  `nummer` varchar(128) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status_code` varchar(128) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status_naam` varchar(128) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `activiteit_nummer` varchar(128) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `gewijzigd_op` datetime DEFAULT NULL,
  `api_gewijzigd_op` datetime DEFAULT NULL,
  `is_verwijderd` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `stemming`
--

CREATE TABLE `stemming` (
  `id` varchar(36) COLLATE utf8mb4_general_ci NOT NULL,
  `persoon_id` varchar(36) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `fractie_id` varchar(36) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `besluit_id` varchar(36) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `sid_actor_lid` varchar(191) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `sid_actor_fractie` varchar(191) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `soort` varchar(25) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `fractie_grootte` smallint DEFAULT NULL,
  `actor_naam` varchar(191) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `actor_fractie` varchar(191) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `vergissing` tinyint(1) DEFAULT NULL,
  `gewijzigd_op` datetime DEFAULT NULL,
  `api_gewijzigd_op` datetime DEFAULT NULL,
  `is_verwijderd` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `system_entities`
--

CREATE TABLE `system_entities` (
  `entity_name` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `table_name` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `processing_order` int NOT NULL,
  `reading_reference` tinytext COLLATE utf8mb4_general_ci,
  `processing_reference` tinytext COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `toezegging`
--

CREATE TABLE `toezegging` (
  `id` varchar(36) COLLATE utf8mb4_general_ci NOT NULL,
  `aanmaakdatum` datetime DEFAULT NULL,
  `nummer` varchar(128) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `activiteit_nummer` varchar(128) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `naam` varchar(128) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `achternaam` varchar(128) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `initialen` varchar(128) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `voornaam` varchar(128) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tussenvoegsel` varchar(128) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `achtervoegsel` varchar(128) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `titulatuur` varchar(128) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `functie` varchar(128) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` varchar(128) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `datum_nakoming` varchar(32) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ministerie` varchar(128) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tekst` text COLLATE utf8mb4_general_ci,
  `notities` varchar(128) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `is_verwijderd` tinyint(1) DEFAULT NULL,
  `gewijzigd_op` datetime DEFAULT NULL,
  `api_gewijzigd_op` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `vergadering`
--

CREATE TABLE `vergadering` (
  `id` varchar(36) COLLATE utf8mb4_general_ci NOT NULL,
  `soort` varchar(128) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `titel` tinytext COLLATE utf8mb4_general_ci,
  `zaal` varchar(128) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `vergaderjaar` varchar(128) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `vergaderingnummer` int DEFAULT NULL,
  `datum` date DEFAULT NULL,
  `aanvangstijd` datetime DEFAULT NULL,
  `sluiting` datetime DEFAULT NULL,
  `kamer` varchar(128) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `gewijzigd_op` datetime DEFAULT NULL,
  `api_gewijzigd_op` datetime DEFAULT NULL,
  `is_verwijderd` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `verslag`
--

CREATE TABLE `verslag` (
  `id` varchar(36) COLLATE utf8mb4_general_ci NOT NULL,
  `vergadering_id` varchar(36) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `soort` varchar(128) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` varchar(128) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `content_type` varchar(128) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `content_length` int DEFAULT NULL,
  `gewijzigd_op` datetime DEFAULT NULL,
  `api_gewijzigd_op` datetime DEFAULT NULL,
  `is_verwijderd` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `zaak`
--

CREATE TABLE `zaak` (
  `id` varchar(36) COLLATE utf8mb4_general_ci NOT NULL,
  `nummer` varchar(128) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `soort` varchar(128) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `titel` text COLLATE utf8mb4_general_ci,
  `citeertitel` varchar(512) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `alias` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` varchar(128) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `onderwerp` text COLLATE utf8mb4_general_ci,
  `gestart_op` date DEFAULT NULL,
  `organisatie` varchar(128) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `grondslag_voorhang` mediumtext COLLATE utf8mb4_general_ci,
  `termijn` date DEFAULT NULL,
  `vergaderjaar` varchar(32) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `volgnummer` varchar(32) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `huidige_behandelstatus` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `afgedaan` tinyint(1) DEFAULT NULL,
  `groot_project` tinyint(1) DEFAULT NULL,
  `gewijzigd_op` datetime DEFAULT NULL,
  `api_gewijzigd_op` datetime DEFAULT NULL,
  `is_verwijderd` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `zaak_actor`
--

CREATE TABLE `zaak_actor` (
  `id` varchar(36) COLLATE utf8mb4_general_ci NOT NULL,
  `zaak_id` varchar(36) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `persoon_id` varchar(36) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `fractie_id` varchar(36) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `commissie_id` varchar(36) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `actor_naam` tinytext COLLATE utf8mb4_general_ci,
  `actor_fractie` tinytext COLLATE utf8mb4_general_ci,
  `actor_afkorting` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `functie` tinytext COLLATE utf8mb4_general_ci,
  `relatie` tinytext COLLATE utf8mb4_general_ci,
  `sid_actor` varchar(68) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `gewijzigd_op` datetime DEFAULT NULL,
  `api_gewijzigd_op` datetime DEFAULT NULL,
  `is_verwijderd` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `zaal`
--

CREATE TABLE `zaal` (
  `id` varchar(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `naam` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `syscode` int DEFAULT NULL,
  `gewijzigd_op` datetime DEFAULT NULL,
  `api_gewijzigd_op` datetime DEFAULT NULL,
  `is_verwijderd` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexen voor geëxporteerde tabellen
--

--
-- Indexen voor tabel `activiteit`
--
ALTER TABLE `activiteit`
  ADD PRIMARY KEY (`id`);

--
-- Indexen voor tabel `activiteit_actor`
--
ALTER TABLE `activiteit_actor`
  ADD PRIMARY KEY (`id`);

--
-- Indexen voor tabel `agendapunt`
--
ALTER TABLE `agendapunt`
  ADD PRIMARY KEY (`id`);

--
-- Indexen voor tabel `besluit`
--
ALTER TABLE `besluit`
  ADD PRIMARY KEY (`id`);

--
-- Indexen voor tabel `commissie`
--
ALTER TABLE `commissie`
  ADD PRIMARY KEY (`id`);

--
-- Indexen voor tabel `commissie_contact_informatie`
--
ALTER TABLE `commissie_contact_informatie`
  ADD PRIMARY KEY (`id`);

--
-- Indexen voor tabel `commissie_zetel`
--
ALTER TABLE `commissie_zetel`
  ADD PRIMARY KEY (`id`);

--
-- Indexen voor tabel `commissie_zetel_vast_persoon`
--
ALTER TABLE `commissie_zetel_vast_persoon`
  ADD PRIMARY KEY (`id`);

--
-- Indexen voor tabel `commissie_zetel_vast_vacature`
--
ALTER TABLE `commissie_zetel_vast_vacature`
  ADD PRIMARY KEY (`id`);

--
-- Indexen voor tabel `commissie_zetel_vervanger_persoon`
--
ALTER TABLE `commissie_zetel_vervanger_persoon`
  ADD PRIMARY KEY (`id`);

--
-- Indexen voor tabel `commissie_zetel_vervanger_vacature`
--
ALTER TABLE `commissie_zetel_vervanger_vacature`
  ADD PRIMARY KEY (`id`);

--
-- Indexen voor tabel `document`
--
ALTER TABLE `document`
  ADD PRIMARY KEY (`id`);

--
-- Indexen voor tabel `document_actor`
--
ALTER TABLE `document_actor`
  ADD PRIMARY KEY (`id`);

--
-- Indexen voor tabel `document_publicatie`
--
ALTER TABLE `document_publicatie`
  ADD PRIMARY KEY (`id`);

--
-- Indexen voor tabel `document_publicatie_metadata`
--
ALTER TABLE `document_publicatie_metadata`
  ADD PRIMARY KEY (`id`);

--
-- Indexen voor tabel `document_versie`
--
ALTER TABLE `document_versie`
  ADD PRIMARY KEY (`id`);

--
-- Indexen voor tabel `fractie`
--
ALTER TABLE `fractie`
  ADD PRIMARY KEY (`id`);

--
-- Indexen voor tabel `fractie_stats`
--
ALTER TABLE `fractie_stats`
  ADD PRIMARY KEY (`fractie_id`);

--
-- Indexen voor tabel `fractie_zetel`
--
ALTER TABLE `fractie_zetel`
  ADD PRIMARY KEY (`id`);

--
-- Indexen voor tabel `fractie_zetel_persoon`
--
ALTER TABLE `fractie_zetel_persoon`
  ADD PRIMARY KEY (`id`);

--
-- Indexen voor tabel `fractie_zetel_vacature`
--
ALTER TABLE `fractie_zetel_vacature`
  ADD PRIMARY KEY (`id`);

--
-- Indexen voor tabel `kamerstukdossier`
--
ALTER TABLE `kamerstukdossier`
  ADD PRIMARY KEY (`id`);

--
-- Indexen voor tabel `persoon`
--
ALTER TABLE `persoon`
  ADD PRIMARY KEY (`id`);

--
-- Indexen voor tabel `persoon_contactinformatie`
--
ALTER TABLE `persoon_contactinformatie`
  ADD PRIMARY KEY (`id`);

--
-- Indexen voor tabel `persoon_geschenk`
--
ALTER TABLE `persoon_geschenk`
  ADD PRIMARY KEY (`id`);

--
-- Indexen voor tabel `persoon_loopbaan`
--
ALTER TABLE `persoon_loopbaan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_persoon_loopbaan_persoon_id` (`persoon_id`);

--
-- Indexen voor tabel `persoon_nevenfunctie`
--
ALTER TABLE `persoon_nevenfunctie`
  ADD PRIMARY KEY (`id`);

--
-- Indexen voor tabel `persoon_nevenfunctie_inkomsten`
--
ALTER TABLE `persoon_nevenfunctie_inkomsten`
  ADD PRIMARY KEY (`id`);

--
-- Indexen voor tabel `persoon_onderwijs`
--
ALTER TABLE `persoon_onderwijs`
  ADD PRIMARY KEY (`id`);

--
-- Indexen voor tabel `persoon_reis`
--
ALTER TABLE `persoon_reis`
  ADD PRIMARY KEY (`id`);

--
-- Indexen voor tabel `persoon_stats`
--
ALTER TABLE `persoon_stats`
  ADD PRIMARY KEY (`persoon_id`),
  ADD KEY `idx_persoon_stats_totaal_stemmen` (`totaal_stemmen`),
  ADD KEY `idx_persoon_stats_totaal_voor_stemmen` (`totaal_voor_stemmen`),
  ADD KEY `idx_persoon_stats_totaal_tegen_stemmen` (`totaal_tegen_stemmen`),
  ADD KEY `idx_persoon_stats_totaal_anders_stemmen` (`totaal_anders_stemmen`),
  ADD KEY `idx_persoon_stats_percentage_voor` (`percentage_voor`),
  ADD KEY `idx_persoon_stats_percentage_tegen` (`percentage_tegen`),
  ADD KEY `idx_persoon_stats_totaal_nevenfuncties` (`totaal_nevenfuncties`),
  ADD KEY `idx_persoon_stats_totaal_opleidingen` (`totaal_opleidingen`),
  ADD KEY `idx_persoon_stats_totaal_loopbanen` (`totaal_loopbanen`);

--
-- Indexen voor tabel `reservering`
--
ALTER TABLE `reservering`
  ADD PRIMARY KEY (`id`);

--
-- Indexen voor tabel `stemming`
--
ALTER TABLE `stemming`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_stemming_besluit_id` (`besluit_id`),
  ADD KEY `idx_stemming_persoon_id` (`persoon_id`),
  ADD KEY `idx_stemming_fractie_id` (`fractie_id`);

--
-- Indexen voor tabel `system_entities`
--
ALTER TABLE `system_entities`
  ADD PRIMARY KEY (`entity_name`);

--
-- Indexen voor tabel `toezegging`
--
ALTER TABLE `toezegging`
  ADD PRIMARY KEY (`id`);

--
-- Indexen voor tabel `vergadering`
--
ALTER TABLE `vergadering`
  ADD PRIMARY KEY (`id`);

--
-- Indexen voor tabel `verslag`
--
ALTER TABLE `verslag`
  ADD PRIMARY KEY (`id`);

--
-- Indexen voor tabel `zaak`
--
ALTER TABLE `zaak`
  ADD PRIMARY KEY (`id`);

--
-- Indexen voor tabel `zaak_actor`
--
ALTER TABLE `zaak_actor`
  ADD PRIMARY KEY (`id`),
  ADD KEY `zaak_id` (`zaak_id`),
  ADD KEY `persoon_id` (`persoon_id`),
  ADD KEY `fractie_id` (`fractie_id`),
  ADD KEY `commissie_id` (`commissie_id`);

--
-- Indexen voor tabel `zaal`
--
ALTER TABLE `zaal`
  ADD PRIMARY KEY (`id`);

-- --------------------------------------------------------

--
-- Beperkingen voor geëxporteerde tabellen
--

--
-- Beperkingen voor tabel `fractie_stats`
--
ALTER TABLE `fractie_stats`
  ADD CONSTRAINT `fk_fractie_stats_fractie` FOREIGN KEY (`fractie_id`) REFERENCES `fractie` (`id`);

--
-- Beperkingen voor tabel `persoon_loopbaan`
--
ALTER TABLE `persoon_loopbaan`
  ADD CONSTRAINT `persoon_loopbaan_ibfk_1` FOREIGN KEY (`persoon_id`) REFERENCES `persoon` (`id`);

--
-- Beperkingen voor tabel `stemming`
--
ALTER TABLE `stemming`
  ADD CONSTRAINT `stemming_ibfk_1` FOREIGN KEY (`besluit_id`) REFERENCES `besluit` (`id`),
  ADD CONSTRAINT `stemming_ibfk_2` FOREIGN KEY (`persoon_id`) REFERENCES `persoon` (`id`),
  ADD CONSTRAINT `stemming_ibfk_3` FOREIGN KEY (`fractie_id`) REFERENCES `fractie` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

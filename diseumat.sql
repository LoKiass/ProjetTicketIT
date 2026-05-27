SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Structure de la table `fonction`
--
DROP TABLE IF EXISTS `fonction`;
CREATE TABLE IF NOT EXISTS `fonction` (
                                          `Pk_Fonction` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
                                          `Descr` varchar(4096) NOT NULL,
                                          `Niveau` varchar(128) NOT NULL,
                                          PRIMARY KEY (`Pk_Fonction`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Structure de la table `fonction_tech`
--
DROP TABLE IF EXISTS `fonction_tech`;
CREATE TABLE IF NOT EXISTS `fonction_tech` (
                                               `Fk_Fonction` bigint UNSIGNED NOT NULL,
                                               `Fk_Tech` bigint UNSIGNED NOT NULL,
                                               PRIMARY KEY (`Fk_Fonction`,`Fk_Tech`),
                                               KEY `fk_ft_t` (`Fk_Tech`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Structure de la table `job`
--
DROP TABLE IF EXISTS `job`;
CREATE TABLE IF NOT EXISTS `job` (
                                     `Pk_Job` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
                                     `Fk_Project` bigint UNSIGNED NOT NULL,
                                     `Titre` varchar(2048) NOT NULL,
                                     `Status` varchar(32) NOT NULL,
                                     `Prior` int NOT NULL,
                                     `Dstart` date NOT NULL,
                                     `Dech` date NOT NULL,
                                     `Dclot` date NOT NULL,
                                     `Dscr` varchar(2560) NOT NULL,
                                     PRIMARY KEY (`Pk_Job`),
                                     KEY `fk_job_projet` (`Fk_Project`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Structure de la table `project`
--
DROP TABLE IF EXISTS `project`;
CREATE TABLE IF NOT EXISTS `project` (
                                         `Pk_Project` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
                                         `Ident` varchar(128) NOT NULL,
                                         `Descr` varchar(4096) NOT NULL,
                                         `Dstart` date NOT NULL,
                                         `DClotEst` date NOT NULL,
                                         `Budget` float NOT NULL,
                                         PRIMARY KEY (`Pk_Project`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `project` (`Pk_Project`, `Ident`, `Descr`, `Dstart`, `DClotEst`, `Budget`) VALUES
    (10, 'Projet Ticket System', 'Développeur backend', '2026-05-07', '2026-05-14', 21000);

--
-- Structure de la table `tech`
--
DROP TABLE IF EXISTS `tech`;
CREATE TABLE IF NOT EXISTS `tech` (
                                      `Pk_Tech` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
                                      `Nom` varchar(128) NOT NULL,
                                      `Pren` varchar(128) NOT NULL,
                                      `Email` varchar(128) NOT NULL,
                                      `Actif` tinyint(1) NOT NULL,
                                      PRIMARY KEY (`Pk_Tech`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Structure de la table `tech_jobs`
--
DROP TABLE IF EXISTS `tech_jobs`;
CREATE TABLE IF NOT EXISTS `tech_jobs` (
                                           `Fk_Tech` bigint UNSIGNED NOT NULL,
                                           `Fk_Job` bigint UNSIGNED NOT NULL,
                                           PRIMARY KEY (`Fk_Tech`,`Fk_Job`),
                                           KEY `fk_tj_j` (`Fk_Job`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Structure de la table `user`
--
DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
                                      `Login` varchar(128) NOT NULL,
                                      `Pswd` varbinary(256) NOT NULL,
                                      `Statut` varchar(32) NOT NULL,
                                      `Actif` tinyint(1) NOT NULL,
                                      PRIMARY KEY (`Login`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `user` (`Login`, `Pswd`, `Statut`, `Actif`) VALUES
    ('admin', 0x4590b8eac807932306ba28b582681342, 'admin', 1);


-- 1. Liaison Job -> Project
ALTER TABLE `job`
    ADD CONSTRAINT `fk_job_project_real` FOREIGN KEY (`Fk_Project`) REFERENCES `project` (`Pk_Project`) ON DELETE CASCADE ON UPDATE CASCADE;

-- 2. Liaisons de la table pivot fonction_tech
ALTER TABLE `fonction_tech`
    ADD CONSTRAINT `fk_ft_fonction` FOREIGN KEY (`Fk_Fonction`) REFERENCES `fonction` (`Pk_Fonction`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `fk_ft_tech` FOREIGN KEY (`Fk_Tech`) REFERENCES `tech` (`Pk_Tech`) ON DELETE CASCADE ON UPDATE CASCADE;

-- 3. Liaisons de la table pivot tech_jobs
ALTER TABLE `tech_jobs`
    ADD CONSTRAINT `fk_tj_tech` FOREIGN KEY (`Fk_Tech`) REFERENCES `tech` (`Pk_Tech`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `fk_tj_job` FOREIGN KEY (`Fk_Job`) REFERENCES `job` (`Pk_Job`) ON DELETE CASCADE ON UPDATE CASCADE;

COMMIT;
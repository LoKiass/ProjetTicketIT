-- 1. Table Projet (Parent de Job)
CREATE TABLE Projet (
                        Pk SERIAL PRIMARY KEY,
                        Ident VARCHAR(32),
                        Descr VARCHAR(32),
                        Dstart DATE,
                        DClotEst DATE,
                        Budget FLOAT
) COMMENT='Table parente vers Job';

-- 2. Table Job (Enfant de Projet)
CREATE TABLE Job (
                     Pk_Job SERIAL PRIMARY KEY,
                     Fk_Projet BIGINT UNSIGNED NOT NULL,
                     Titre VARCHAR(32),
                     Statut VARCHAR(32),
                     Prior INT,
                     Dstart DATE,
                     Dech DATE,
                     Dclot DATE,
                     CONSTRAINT fk_job_projet FOREIGN KEY (Fk_Projet) REFERENCES Projet(Pk)
) COMMENT='Table liée vers Projet';

-- 3. Table TechEntity (Parent de Tech_Jobs et Fonctions_Tech)
CREATE TABLE Tech (
                      Pk_Tech SERIAL PRIMARY KEY,
                      Nom VARCHAR(32),
                      Pren VARCHAR(32),
                      Email VARCHAR(64),
                      Actif BOOL
) COMMENT='Table liée vers Tech_Jobs et Fonctions_Tech';

-- 4. Table Fonctions (Parent de Fonctions_Tech)
CREATE TABLE Fonctions (
                           Pk_Fonction SERIAL PRIMARY KEY,
                           Descr VARCHAR(32),
                           Niveau VARCHAR(32)
) COMMENT='Table liée vers Fonctions_Tech';

-- 5. Table de liaison Fonctions_Tech
CREATE TABLE Fonctions_Tech (
                                Fk_Fonction BIGINT UNSIGNED NOT NULL,
                                Fk_Tech BIGINT UNSIGNED NOT NULL,
                                PRIMARY KEY (Fk_Fonction, Fk_Tech),
    -- Liaisons :
                                CONSTRAINT fk_ft_f FOREIGN KEY (Fk_Fonction) REFERENCES Fonctions(Pk_Fonction),
                                CONSTRAINT fk_ft_t FOREIGN KEY (Fk_Tech) REFERENCES Tech(Pk_Tech)
) COMMENT='Table de liaison vers Fonctions et TechEntity';

-- 6. Table de liaison Tech_Jobs
CREATE TABLE Tech_Jobs (
                           Fk_Tech BIGINT UNSIGNED NOT NULL,
                           Fk_Job BIGINT UNSIGNED NOT NULL,
                           PRIMARY KEY (Fk_Tech, Fk_Job),
    -- Liaisons :
                           CONSTRAINT fk_tj_t FOREIGN KEY (Fk_Tech) REFERENCES Tech(Pk_Tech),
                           CONSTRAINT fk_tj_j FOREIGN KEY (Fk_Job) REFERENCES Job(Pk_Job)
) COMMENT='Table de liaison vers TechEntity et Job';

-- 7. Table UserEntity
CREATE TABLE User (
                      Login VARCHAR(32) PRIMARY KEY,
                      Pswd VARBINARY(256),
                      Statut VARCHAR(32),
                      Actif BOOL
) COMMENT='Table indépendante';

-- Utilisateur admin de base
INSERT INTO User VALUES ('admin', '', 'admin', true);

SELECT * FROM tech as t
INNER JOIN tech_jobs ft ON t.Pk_Tech = ft.fk_tech
WHERE ft.Fk_Job = 1
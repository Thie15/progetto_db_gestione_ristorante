DROP DATABASE IF EXISTS `gestioneRistorante`;

CREATE DATABASE `gestioneRistorante`;

USE `gestioneRistorante`;

CREATE TABLE Personale(
    IDPersonale INT(3) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT PRIMARY KEY,
    Nome VARCHAR(30) NOT NULL,
    Cognome VARCHAR(30) NOT NULL,
    Turno ENUM("Pranzo", "Cena") NOT NULL,
    Stipendio FLOAT(6, 2) UNSIGNED NOT NULL,
    Immagine VARCHAR(8) NOT NULL,
    Indirizzo_Comune VARCHAR(30) NOT NULL, 
    Indirizzo_Via VARCHAR(30) NOT NULL,
    Indirizzo_Civico VARCHAR(6) NOT NULL,
    Indirizzo_CAP INT(5) UNSIGNED ZEROFILL NOT NULL,
    INDEX(IDPersonale)
);

CREATE TABLE Timbrature(
    IDPersonale INT(3) UNSIGNED ZEROFILL NOT NULL,
    DataTimbratura DATE NOT NULL,
    Ora TIME NOT NULL,
    Tipologia ENUM("Entrata", "Uscita") NOT NULL,
    PRIMARY KEY(IDPersonale, DataTimbratura, Ora),
    INDEX(IDPersonale),
    CONSTRAINT fk_Personale_Timbrature
        FOREIGN KEY (IDPersonale) REFERENCES Personale(IDPersonale)
            ON DELETE RESTRICT
            ON UPDATE CASCADE
);

CREATE TABLE Camerieri(
    IDPersonale INT(3) UNSIGNED ZEROFILL NOT NULL PRIMARY KEY,
    Zona VARCHAR(20) NOT NULL,
    INDEX(IDPersonale),
    CONSTRAINT fk_Personale_Camerieri
        FOREIGN KEY (IDPersonale) REFERENCES Personale(IDPersonale)
            ON DELETE RESTRICT
            ON UPDATE RESTRICT
);

CREATE TABLE Cuochi(
    IDPersonale INT(3) UNSIGNED ZEROFILL NOT NULL PRIMARY KEY,
    Livello ENUM("Executive Chef", "Sous Chef", "Capopartita", "Aiuto Cuoco") NOT NULL,
    INDEX(IDPersonale),
    CONSTRAINT fk_Personale_Cuochi
        FOREIGN KEY (IDPersonale) REFERENCES Personale(IDPersonale)
            ON DELETE RESTRICT
            ON UPDATE RESTRICT
);

CREATE TABLE Lingue(
    Nome VARCHAR(15) NOT NULL PRIMARY KEY,
    INDEX(Nome)
);

CREATE TABLE Certificazioni(
    Tipologia VARCHAR(40) NOT NULL PRIMARY KEY,
    INDEX(Tipologia)
);

CREATE TABLE Prenotazioni(
    IDPrenotazione INT(5) UNSIGNED ZEROFILL AUTO_INCREMENT NOT NULL PRIMARY KEY,
    Ora TIME NOT NULL,
    DataPrenotazione DATE NOT NULL,
    NumeroPersone INT(2) NOT NULL,
    MetodoPagamento ENUM("Contanti", "Bonifico", "Carta", "PayPal", "Satispay") NOT NULL,
    INDEX(IDPrenotazione),
    CHECK (NumeroPersone > 0)
);

CREATE TABLE Ordini(
    IDOrdine INT(6) UNSIGNED ZEROFILL AUTO_INCREMENT NOT NULL PRIMARY KEY,
    Note VARCHAR(255) NULL,
    IDPrenotazione INT(5) UNSIGNED ZEROFILL NOT NULL,
    IDPersonale INT(3) UNSIGNED ZEROFILL NOT NULL,
    INDEX(IDPrenotazione),
    CONSTRAINT fk_Ordini_Prenotazioni
        FOREIGN KEY (IDPrenotazione) REFERENCES Prenotazioni(IDPrenotazione)
            ON DELETE RESTRICT
            ON UPDATE RESTRICT,
    CONSTRAINT fk_Camerieri_Ordini
        FOREIGN KEY (IDPersonale) REFERENCES Personale(IDPersonale)
            ON DELETE RESTRICT
            ON UPDATE RESTRICT
);

CREATE TABLE Tavoli(
    IDTavolo INT(2) UNSIGNED ZEROFILL AUTO_INCREMENT NOT NULL PRIMARY KEY,
    Posti INT(2) UNSIGNED NOT NULL,
    Ubicazione VARCHAR(20) NOT NULL,
    INDEX(IDTavolo),
    CHECK (Posti > 0)
);

CREATE TABLE Piatti(
    IDPiatto INT(2) UNSIGNED ZEROFILL AUTO_INCREMENT NOT NULL PRIMARY KEY,
    Nome VARCHAR(40) NOT NULL,
    Prezzo FLOAT(4,2) NOT NULL,
    Immagine VARCHAR(7) NOT NULL,
    INDEX(IDPiatto),
    CHECK (Prezzo > 0)
);

CREATE TABLE Menu(
    IDMenu INT(2) UNSIGNED ZEROFILL AUTO_INCREMENT NOT NULL PRIMARY KEY,
    Nome VARCHAR(20) NOT NULL,
    INDEX(IDMenu)
);

CREATE TABLE Ingredienti(
    IDIngrediente INT(3) UNSIGNED ZEROFILL AUTO_INCREMENT NOT NULL PRIMARY KEY,
    Nome VARCHAR(20) NOT NULL,
    Quantita INT(3) NOT NULL,
    UnitaMisura ENUM("g", "kg", "pz", "l") NOT NULL,
    INDEX(IDIngrediente),
    CHECK (Quantita >= 0)
);

CREATE TABLE Specifiche(
    IDSpecifica INT(2) UNSIGNED ZEROFILL AUTO_INCREMENT NOT NULL PRIMARY KEY,
    Nome VARCHAR(20) NOT NULL,
    Immagine VARCHAR(7) NOT NULL,
    INDEX(IDSpecifica)
);

CREATE TABLE Fornitori(
    IDFornitore INT(2) UNSIGNED ZEROFILL AUTO_INCREMENT NOT NULL PRIMARY KEY,
    PIVA VARCHAR(11) NOT NULL,
    Nome VARCHAR(50) NOT NULL,
    Indirizzo_Comune VARCHAR(30) NOT NULL, 
    Indirizzo_Via VARCHAR(30) NOT NULL,
    Indirizzo_Civico VARCHAR(6) NOT NULL,
    Indirizzo_CAP INT(5) UNSIGNED ZEROFILL NOT NULL,
    INDEX(IDFornitore),
    CHECK (CHAR_LENGTH(PIVA) = 11)
);

CREATE TABLE OrdiniFornitori(
    IDOrdineFornitore INT(6) UNSIGNED ZEROFILL AUTO_INCREMENT NOT NULL PRIMARY KEY,
    DataOrdine DATE NOT NULL,
    DataConsegna DATE NULL,
    IDFornitore INT(2) UNSIGNED ZEROFILL NOT NULL,
    INDEX(IDOrdineFornitore),
    CONSTRAINT fk_OrdiniFornitori_Fornitore
        FOREIGN KEY(IDFornitore) REFERENCES Fornitori(IDFornitore)
            ON DELETE RESTRICT
            ON UPDATE CASCADE,
    CHECK (DataConsegna IS NULL OR DataConsegna >= DataOrdine)
);

CREATE TABLE Account(
    IDAccount INT(4) AUTO_INCREMENT PRIMARY KEY,
    Username VARCHAR(50) NOT NULL UNIQUE,
    Email VARCHAR(100) NOT NULL UNIQUE,
    Password CHAR(64) NOT NULL,
    Salt CHAR(64) NOT NULL,
    IDPersonale INT(3) UNSIGNED ZEROFILL NULL,
    IDFornitore INT(2) UNSIGNED ZEROFILL NULL,

    CONSTRAINT fk_Account_Personale
        FOREIGN KEY(IDPersonale) REFERENCES Personale(IDPersonale)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_Account_Fornitori
        FOREIGN KEY(IDFornitore) REFERENCES Fornitori(IDFornitore)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

/*Tabelle ausiliarie*/

CREATE TABLE aux_Camerieri_Lingue(
    IDPersonale INT(3) UNSIGNED ZEROFILL NOT NULL,
    Nome VARCHAR(15) NOT NULL,
    PRIMARY KEY(IDPersonale, Nome),
    INDEX(IDPersonale),
    INDEX(Nome),
    CONSTRAINT fk_Camerieri_auxLingue
        FOREIGN KEY(IDPersonale) REFERENCES Camerieri(IDPersonale)
            ON DELETE RESTRICT
            ON UPDATE CASCADE,
    CONSTRAINT fk_Nome_auxCamerieri
        FOREIGN KEY(Nome) REFERENCES Lingue(Nome)
            ON DELETE RESTRICT
            ON UPDATE CASCADE 
);

CREATE TABLE aux_Cuochi_Certificazioni(
    IDPersonale INT(3) UNSIGNED ZEROFILL NOT NULL,
    Tipologia VARCHAR(40) NOT NULL,
    PRIMARY KEY(IDPersonale, Tipologia),
    INDEX(IDPersonale),
    INDEX(Tipologia),
    CONSTRAINT fk_Cuochi_auxCertificazioni
        FOREIGN KEY(IDPersonale) REFERENCES Cuochi(IDPersonale)
            ON DELETE RESTRICT
            ON UPDATE CASCADE,
    CONSTRAINT fk_Certificazioni_auxCuochi
        FOREIGN KEY(Tipologia) REFERENCES Certificazioni(Tipologia)
            ON DELETE RESTRICT
            ON UPDATE CASCADE
);

CREATE TABLE aux_Ordini_Cuochi_Piatti(
    IDOrdine INT(6) UNSIGNED ZEROFILL NOT NULL,
    IDPersonale INT(3) UNSIGNED ZEROFILL NOT NULL,
    IDPiatto INT(2) UNSIGNED ZEROFILL NOT NULL,
    PRIMARY KEY(IDOrdine, IDPersonale, IDPiatto),
    INDEX(IDOrdine),
    INDEX(IDPersonale),
    INDEX(IDPiatto),
    CONSTRAINT fk_Ordini_auxCuochiPiatti
        FOREIGN KEY(IDOrdine) REFERENCES Ordini(IDOrdine)
            ON DELETE RESTRICT
            ON UPDATE CASCADE,
    CONSTRAINT fk_Cuochi_auxOrdiniPiatti
        FOREIGN KEY(IDPersonale) REFERENCES Cuochi(IDPersonale)
            ON DELETE RESTRICT
            ON UPDATE CASCADE,
    CONSTRAINT fk_Piatti_auxOrdiniCuochi
        FOREIGN KEY(IDPiatto) REFERENCES Piatti(IDPiatto)
            ON DELETE RESTRICT
            ON UPDATE CASCADE
);

CREATE TABLE aux_Prenotazioni_Tavoli(
    IDPrenotazione INT(5) UNSIGNED ZEROFILL NOT NULL,
    IDTavolo INT(2) UNSIGNED ZEROFILL NOT NULL,
    PRIMARY KEY(IDPrenotazione, IDTavolo),
    INDEX(IDPrenotazione),
    INDEX(IDTavolo),
    CONSTRAINT fk_Prenotazioni_auxTavoli
        FOREIGN KEY (IDPrenotazione) REFERENCES Prenotazioni(IDPrenotazione)
            ON DELETE RESTRICT
            ON UPDATE CASCADE,
    CONSTRAINT fk_Tavoli_auxPrenotazioni
        FOREIGN KEY(IDTavolo) REFERENCES Tavoli(IDTavolo)
            ON DELETE RESTRICT
            ON UPDATE CASCADE
);

CREATE TABLE aux_Piatti_Ingredienti(
    IDPiatto INT(2) UNSIGNED ZEROFILL NOT NULL,
    IDIngrediente INT(3) UNSIGNED ZEROFILL NOT NULL,
    PRIMARY KEY(IDPiatto, IDIngrediente),
    INDEX(IDPiatto),
    INDEX(IDIngrediente),
    CONSTRAINT fk_Piatti_auxIngredienti
        FOREIGN KEY(IDPiatto) REFERENCES Piatti(IDPiatto)
            ON DELETE RESTRICT
            ON UPDATE CASCADE,
    CONSTRAINT fk_Ingredienti_auxPiatti
        FOREIGN KEY(IDIngrediente) REFERENCES Ingredienti(IDIngrediente)
            ON DELETE RESTRICT
            ON UPDATE CASCADE
);

CREATE TABLE aux_Ingredienti_Specifiche(
    IDIngrediente INT(3) UNSIGNED ZEROFILL NOT NULL,
    IDSpecifica INT(2) UNSIGNED ZEROFILL NOT NULL,
    PRIMARY KEY(IDIngrediente, IDSpecifica),
    INDEX(IDIngrediente),
    INDEX(IDSpecifica),
    CONSTRAINT fk_Ingredienti_auxSpecifiche
        FOREIGN KEY(IDIngrediente) REFERENCES Ingredienti(IDIngrediente)
            ON DELETE RESTRICT
            ON UPDATE CASCADE,
    CONSTRAINT fk_Specifiche_auxIngredienti
        FOREIGN KEY(IDSpecifica) REFERENCES Specifiche(IDSpecifica)
            ON DELETE RESTRICT
            ON UPDATE CASCADE
);

CREATE TABLE aux_Ingredienti_OrdiniFornitori(
    IDIngrediente INT(3) UNSIGNED ZEROFILL NOT NULL,
    IDOrdineFornitore INT(6) UNSIGNED ZEROFILL NOT NULL,
    Quantita INT(3) NOT NULL,
    UnitaMisura ENUM("g", "kg", "pz", "l") NOT NULL,
    PRIMARY KEY(IDIngrediente, IDOrdineFornitore),
    INDEX(IDIngrediente),
    INDEX(IDOrdineFornitore),
    CONSTRAINT fk_Ingredienti_auxOrdiniFornitori
        FOREIGN KEY(IDIngrediente) REFERENCES Ingredienti(IDIngrediente)
            ON DELETE RESTRICT
            ON UPDATE CASCADE,
    CONSTRAINT fk_OrdiniFornitori_auxIngredienti
        FOREIGN KEY(IDOrdineFornitore) REFERENCES OrdiniFornitori(IDOrdineFornitore)
           ON DELETE RESTRICT
            ON UPDATE CASCADE,
    CHECK (Quantita > 0)
);

/*popolamento db*/

INSERT INTO Personale
(Nome, Cognome, Turno, Stipendio, Immagine, Indirizzo_Comune, Indirizzo_Via, Indirizzo_Civico, Indirizzo_CAP)
VALUES
('Marco','Rossi','Pranzo',1500.00,'001.webp','Aosta','Via Roma','10',11100),
('Luca','Bianchi','Cena',1400.00,'002.webp','Aosta','Via Torino','22',11100),
('Anna','Verdi','Cena',1800.00,'003.webp','Aosta','Via Milano','5',11100),
('Giulia','Neri','Pranzo',1350.00,'004.webp','Aosta','Via Dante','8',11100),
('Paolo','Ferrari','Cena',2000.00,'005.webp','Aosta','Via Garibaldi','15',11100);

INSERT INTO Camerieri (IDPersonale, Zona) VALUES
(001, 'Interno'),
(002, 'Esterno'),
(004, 'Interno');

INSERT INTO Cuochi (IDPersonale, Livello) VALUES
(003, 'Sous Chef'),
(005, 'Executive Chef');

INSERT INTO Lingue (Nome) VALUES
('Italiano'),
('Inglese'),
('Francese'),
('Tedesco'),
('Spagnolo');

INSERT INTO Certificazioni (Tipologia) VALUES
('Sicurezza Alimentare'),
('Cucina Vegana'),
('Cucina Senza Glutine'),
('Cucina Internazionale');

INSERT INTO aux_Camerieri_Lingue VALUES
(001, 'Italiano'),
(001, 'Inglese'),
(002, 'Francese'),
(004, 'Italiano'),
(004, 'Spagnolo');

INSERT INTO aux_Cuochi_Certificazioni VALUES
(003, 'Cucina Vegana'),
(003, 'Cucina Senza Glutine'),
(005, 'Sicurezza Alimentare'),
(005, 'Cucina Internazionale');

INSERT INTO Timbrature VALUES
(001, '2025-06-10', '11:30:00', 'Entrata'),
(001, '2025-06-10', '15:30:00', 'Uscita'),
(003, '2025-06-10', '18:00:00', 'Entrata'),
(003, '2025-06-10', '23:00:00', 'Uscita');

INSERT INTO Tavoli (Posti, Ubicazione) VALUES
(2, 'Interno'),
(4, 'Interno'),
(6, 'Esterno'),
(4, 'Esterno'),
(8, 'Interno');

INSERT INTO Prenotazioni (Ora, DataPrenotazione, NumeroPersone, MetodoPagamento) VALUES
('12:30:00', '2025-06-10', 2, 'Carta'),
('20:00:00', '2025-06-10', 4, 'Contanti'),
('21:00:00', '2025-06-11', 6, 'PayPal'),
('19:30:00', '2025-06-11', 4, 'Satispay'),
('13:00:00', '2025-06-12', 8, 'Bonifico');

INSERT INTO aux_Prenotazioni_Tavoli VALUES
(00001, 01),
(00002, 02),
(00003, 03),
(00004, 04),
(00005, 05);

INSERT INTO Piatti (Nome, Prezzo, Immagine) VALUES
('Spaghetti alla Carbonara',12.50,'01.webp'),
('Risotto ai Funghi',11.00,'02.webp'),
('Bistecca alla Griglia',18.00,'03.webp'),
('Tiramisù',6.00,'04.webp'),
('Insalata Mista',5.50,'05.webp');

INSERT INTO Ingredienti (Nome, Quantita, UnitaMisura) VALUES
('Pasta', 5000, 'g'),
('Uova', 200, 'pz'),
('Funghi', 3000, 'g'),
('Carne Bovina', 10, 'kg'),
('Insalata', 2000, 'g');

INSERT INTO Specifiche (Nome, Immagine) VALUES
('Glutine','01.webp'),
('Uova','02.webp'),
('Vegetariano','03.webp'),
('Vegano','04.webp'),
('Lattosio','05.webp');

INSERT INTO aux_Piatti_Ingredienti VALUES
(01, 001),
(01, 002),
(02, 003),
(03, 004),
(05, 005);

INSERT INTO aux_Ingredienti_Specifiche VALUES
(001, 001),
(002, 002),
(003, 003),
(005, 004);

INSERT INTO Ordini (Note, IDPrenotazione, IDPersonale) VALUES
('Senza sale', 00001, 004),
(NULL, 00002, 001),
('Cottura media', 00003, 002),
(NULL, 00004, 002),
('Allergia lattosio', 00005, 001);

INSERT INTO aux_Ordini_Cuochi_Piatti VALUES
(000001, 003, 01),
(000001, 003, 02),
(000002, 005, 03),
(000003, 005, 04),
(000004, 003, 05);

INSERT INTO Fornitori (PIVA, Nome, Indirizzo_Comune, Indirizzo_Via, Indirizzo_Civico, Indirizzo_CAP) VALUES
('01234567890', 'FreshFood SRL', 'Torino', 'Via Po', '10', 10100),
('09876543211', 'BioMarket SPA', 'Milano', 'Via Verdi', '22', 20100);

INSERT INTO OrdiniFornitori (DataOrdine, DataConsegna, IDFornitore) VALUES
('2025-06-01', '2025-06-03', 01),
('2025-06-05', '2025-06-07', 02);

INSERT INTO aux_Ingredienti_OrdiniFornitori VALUES
(001, 000001, 2000, 'g'),
(002, 000001, 50, 'pz'),
(003, 000002, 1000, 'g'),
(004, 000002, 5, 'kg');

INSERT INTO Account
(Username, Email, Password, Salt, IDPersonale, IDFornitore)
VALUES

('m.rossi', 'm.rossi@smartristo.com',
'4b47756bf5a9436aea07753f83f15ec11f415d4da125f59767e78bc1cb07b2e0',
'3ede9492fb286ae185ed62e627d31eb903f58ea89eac48adc61ec584333fdb79',
001, NULL),

('l.bianchi', 'l.bianchi@smartristo.com',
'b8e90878c77f8d18f2e971f2f2f67f3edda0408dd49d92aea85921dea8b75afe',
'ea5fb9119d10e10a7238ef45eb8ab215c8208aa91672eca4a6dee2633937b8d7',
002, NULL),

('a.verdi', 'a.verdi@smartristo.com',
'a26ae75de64cb676ecd5a64777900825c3f707ebcabdf39a7cf9ab00be442647',
'164aad229edb75f4ea3879a41476938bfdbe855e09b188af7bbe8dc65512f6e5',
003, NULL),

('g.neri', 'g.neri@smartristo.com',
'5a73a2214e69590b1a0a6df9d45081413fa96ea74c29960cc67875ad50515fec',
'342c0ca376e588cf8bf37319639e8fccc53d1675c86c0bcfe8bffdce49777186',
004, NULL),

('p.ferrari', 'p.ferrari@smartristo.com',
'74ad757fda5624b457b4e4defbad9e970ecdd4df89e11977d3d399b1f83a6286',
'c190bd9baa4d4ea26926ea9ca8edf69652abd294dae31cf7fbae4edc9999f187',
005, NULL),

('freshfood', 'freshfood@smartristo.com',
'98c96c88f969de2b784b5a68b2cd8adae33c6fc9365a60e6fcee86630f122322',
'2b4e05a523a5d7273b89831bc86010f7892cb9b81665e9b447f79888e494fc77',
NULL, 01),

('biomarket', 'biomarket@smartristo.com',
'08e7b3668aea18ade42284f70af74852cedb45f06cbc6bda9fd78bd537ca02f6',
'a56636f2d6c020b6db4f588d98c0f5bb8b8557cf42c8edc9a318fe4f9661a0d0',
NULL, 02);
DROP DATABASE IF EXISTS `gestioneRistorante`;

CREATE DATABASE `gestioneRistorante`;

USE `gestioneRistorante`;

CREATE TABLE Personale(
    IDPersonale INT(3) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT PRIMARY KEY,
    Nome VARCHAR(30) NOT NULL,
    Cognome VARCHAR(30) NOT NULL,
    Turno ENUM("Pranzo", "Cena") NOT NULL,
    Stipendio FLOAT(6, 2) UNSIGNED NOT NULL, 
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
    PRIMARY KEY(IDPersonale, DataTimbratura),
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
    MetodoPagamento ENUM("Contanti", "Bonifico", "Carta") NOT NULL,
    INDEX(IDPrenotazione)
);

CREATE TABLE Ordini(
    IDOrdine INT(6) UNSIGNED ZEROFILL AUTO_INCREMENT NOT NULL PRIMARY KEY,
    Note VARCHAR(255) NULL,
    IDPrenotazione INT(5) UNSIGNED ZEROFILL NOT NULL,
    INDEX(IDPrenotazione),
    CONSTRAINT fk_Ordini_Prenotazioni
        FOREIGN KEY (IDPrenotazione) REFERENCES Prenotazioni(IDPrenotazione)
            ON DELETE RESTRICT
            ON UPDATE RESTRICT
);

CREATE TABLE Tavoli(
    IDTavolo INT(2) UNSIGNED ZEROFILL AUTO_INCREMENT NOT NULL PRIMARY KEY,
    Posti INT(2) UNSIGNED NOT NULL,
    Ubicazione VARCHAR(20) NOT NULL,
    INDEX(IDTavolo)
);

CREATE TABLE Piatti(
    IDPiatto INT(2) UNSIGNED ZEROFILL AUTO_INCREMENT NOT NULL PRIMARY KEY,
    Nome VARCHAR(40) NOT NULL,
    Prezzo FLOAT(4,2) NOT NULL,
    INDEX(IDPiatto)
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
    INDEX(IDIngrediente)
);

CREATE TABLE Specifiche(
    IDSpecifica INT(2) UNSIGNED ZEROFILL AUTO_INCREMENT NOT NULL PRIMARY KEY,
    Nome VARCHAR(20) NOT NULL,
    Immagine BLOB NOT NULL,
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
    INDEX(IDFornitore)
);

CREATE TABLE OrdiniFornitori(
    IDOrdineFornitore INT(6) UNSIGNED ZEROFILL AUTO_INCREMENT NOT NULL PRIMARY KEY,
    DataOrdine DATE NOT NULL,
    DataConsegna DATE NULL,
    IDFornitore INT(2) UNSIGNED ZEROFILL NOT NULL,
    INDEX(IDOrdine),
    CONSTRAINT fk_OrdiniFornitori_Fornitore
        FOREIGN KEY(IDFornitore) REFERENCES Fornitori(IDFornitore)
            ON DELETE RESTRICT
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
    PRIMARY KEY(IDIngrediente, IDSpecifica)
    INDEX(IDIngrediente),
    INDEX(IDSpecifica),
    CONSTRAINT fk_Ingredienti_auxSpecifiche
        FOREIGN KEY(IDIngrediente) REFERENCES Ingredienti(IDIngrediente)
            ON DELETE RESTRICT
            ON UPDATE CASCADE,
    CONSTRAINT fk_Specifiche_auxIngredienti
        FOREIGN KEY(IDSpecifica) REFERENCES Specifiche(IDPrenotazione)
            ON DELETE RESTRICT
            ON UPDATE CASCADE
);

CREATE TABLE aux_Ingredienti_OrdiniFornitori(
    IDIngrediente INT(3) UNSIGNED ZEROFILL NOT NULL,
    IDOrdineFornitore INT(6) UNSIGNED ZEROFILL NOT NULL,
    PRIMARY KEY(IDIngrediente, IDOrdineFornitore)
    INDEX(IDIngrediente),
    INDEX(IDOrdineFornitore)
    CONSTRAINT fk_Ingredienti_auxOrdiniFornitori
        FOREIGN KEY(IDIngrediente) REFERENCES Ingredienti(IDIngrediente)
            ON DELETE RESTRICT
            ON UPDATE CASCADE,
    CONSTRAINT fk_OrdiniFornitori_auxIngredienti
        FOREIGN KEY(IDOrdineFornitore) REFERENCES OrdiniFornitori(IDOrdineFornitore)
           ON DELETE RESTRICT
            ON UPDATE CASCADE 
);
/*
 * ---------------------------------------------------------------------------
 *
 * iBiblio : base de données bibliographiques
 * Copyright (C) 2005 Benjamin Gaillard & Nicolas Riegel
 *
 * ---------------------------------------------------------------------------
 *
 * Ce programme est un logiciel libre ; vous pouvez le redistribuer et/ou le
 * modifier conformément aux dispositions de la Licence Publique Générale GNU,
 * telle que publiée par la Free Software Foundation ; version 2 de la
 * licence, ou encore (à votre convenance) toute version ultérieure.
 *
 * Ce programme est distribué dans l'espoir qu'il sera utile, mais SANS AUCUNE
 * GARANTIE ; sans même la garantie implicite de COMMERCIALISATION ou
 * D'ADAPTATION À UN OBJET PARTICULIER. Pour plus de détail, voir la Licence
 * Publique Générale GNU.
 *
 * Vous devez avoir reçu un exemplaire de la Licence Publique Générale GNU en
 * même temps que ce programme ; si ce n'est pas le cas, écrivez à la Free
 * Software Foundation Inc., 675 Mass Ave, Cambridge, MA 02139, États-Unis.
 *
 * ---------------------------------------------------------------------------
 */


CREATE TABLE Revue
(
idRevue SERIAL PRIMARY KEY,
nomRevue VARCHAR(255)
);

CREATE TABLE Article
(
idArticle SERIAL PRIMARY KEY,
titreArticle VARCHAR(255),
idRevue INTEGER,
noVolume INTEGER,
noSerie INTEGER,
pageDebut INTEGER,
pageFin INTEGER,
date DATE,
CONSTRAINT idRev FOREIGN KEY(idRevue) REFERENCES Revue(idRevue) ON DELETE CASCADE
);

CREATE TABLE Editeur
(
idEditeur SERIAL PRIMARY KEY,
nomEditeur VARCHAR(255)
);

CREATE TABLE Ouvrage
(
idOuvrage SERIAL PRIMARY KEY,
titreOuvrage VARCHAR(255),
idEditeur INTEGER,
ville VARCHAR(255),
annee INTEGER,
CONSTRAINT idEdi FOREIGN KEY(idEditeur) REFERENCES Editeur(idEditeur) ON DELETE CASCADE
);

CREATE TABLE Auteur
(
idAuteur SERIAL PRIMARY KEY,
nomAuteur VARCHAR(255),
initialesPrenoms VARCHAR(255)
);

CREATE TABLE AuteurArticle
(
idArticle INTEGER,
idAuteur INTEGER,
CONSTRAINT idArtAut PRIMARY KEY(idArticle,idAuteur),
CONSTRAINT idArti FOREIGN KEY(idArticle) REFERENCES Article(idArticle) ON DELETE CASCADE,
CONSTRAINT idAut FOREIGN KEY(idAuteur) REFERENCES Auteur(idAuteur) ON DELETE CASCADE
);

CREATE TABLE AuteurOuvrage
(
idOuvrage INTEGER,
idAuteur INTEGER,
CONSTRAINT idOuvAut PRIMARY KEY(idOuvrage,idAuteur),
CONSTRAINT idOuv FOREIGN KEY(idOuvrage) REFERENCES Ouvrage(idOuvrage) ON DELETE CASCADE,
CONSTRAINT idAut FOREIGN KEY(idAuteur) REFERENCES Auteur(idAuteur) ON DELETE CASCADE
);

CREATE TABLE Thesaurus
(
idMotCle SERIAL PRIMARY KEY,
nomMotCle VARCHAR(255)
);

CREATE TABLE DescriptionArticle
(
idArticle INTEGER,
idMotCle INTEGER,
CONSTRAINT idArtMot PRIMARY KEY(idArticle,idMotCle),
CONSTRAINT idArt FOREIGN KEY(idArticle) REFERENCES Article(idArticle) ON DELETE CASCADE,
CONSTRAINT idMot FOREIGN KEY(idMotCle) REFERENCES Thesaurus(idMotCLe) ON DELETE CASCADE
);

CREATE TABLE Utilisateur
(
idUtil SERIAL PRIMARY KEY,
nom VARCHAR(255) UNIQUE,
pwd VARCHAR(255),
niveau INTEGER
);

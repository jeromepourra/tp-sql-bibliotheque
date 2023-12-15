## TP SQL Bibliothèque

Toutes les requêtes SQL se trouvent dans ./database/Database.php

Jérôme Pourra

## Création de la table account

Gestionnaire : admin | password123
Abonne : test | password123

```sql

--
-- Structure de la table `account`
--

DROP TABLE IF EXISTS `account`;
CREATE TABLE IF NOT EXISTS `account` (
  `id` int NOT NULL AUTO_INCREMENT,
  `identifier` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  `password` varchar(255) COLLATE utf8mb3_bin NOT NULL,
  `id_abonne` int NOT NULL,
  `role` varchar(255) COLLATE utf8mb3_bin NOT NULL DEFAULT 'abonne',
  PRIMARY KEY (`id`),
  KEY `id_abonne` (`id_abonne`)
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

--
-- Déchargement des données de la table `account`
--

INSERT INTO `account` (`id`, `identifier`, `password`, `id_abonne`, `role`) VALUES
(37, 'admin', '$2y$10$S4HCI6RSZUPibDcoHsDjVeoOsNGn0uqASwfcLBDPbPCMSauH.papC', 3034, 'gestionnaire'),
(38, 'test', '$2y$10$mmTdJnDl/rLkJ0OHwo0GieXTL2Ouo3TkPVOMDxylo09hWYjszh6F2', 3035, 'abonne');

```
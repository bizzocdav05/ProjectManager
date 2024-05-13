-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Creato il: Mag 13, 2024 alle 21:11
-- Versione del server: 8.0.32
-- Versione PHP: 8.0.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `my_torg`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `Attivita`
--

CREATE TABLE `Attivita` (
  `ID` int NOT NULL,
  `bacheca` int NOT NULL,
  `data_creazione` date DEFAULT NULL,
  `data_ultima_modifica` date DEFAULT NULL,
  `titolo` varchar(255) NOT NULL,
  `codice` varchar(16) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dump dei dati per la tabella `Attivita`
--

INSERT INTO `Attivita` (`ID`, `bacheca`, `data_creazione`, `data_ultima_modifica`, `titolo`, `codice`) VALUES
(42, 20, '2024-04-11', '2024-04-11', 'prova', 'YVmAK5g3EscWUYUD'),
(43, 20, '2024-04-11', '2024-04-11', 'prova', 'Y3HINakbhXS7hsUa'),
(44, 20, '2024-04-11', '2024-04-11', 'prova', 'bFWiV9JqfZVWGySU'),
(45, 21, '2024-05-09', '2024-05-09', 'TO DO', 'NATPEPsusXly7FwY'),
(46, 21, '2024-05-09', '2024-05-09', 'Finished', 'RNqJ1qlJK3PjTj4Q'),
(47, 21, '2024-05-09', '2024-05-09', 'To Fix', '4ZH35I3pIMfoJzJD'),
(48, 22, '2024-05-09', '2024-05-09', 'To Do', 'T6p0skunF3iy2EfJ'),
(49, 24, '2024-05-10', '2024-05-10', 'prova', 'MtQrzpQX5da0jWdX');

-- --------------------------------------------------------

--
-- Struttura della tabella `Bacheca`
--

CREATE TABLE `Bacheca` (
  `ID` int NOT NULL,
  `console` int DEFAULT NULL,
  `nome` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `codice` varchar(16) NOT NULL,
  `preferita` tinyint NOT NULL DEFAULT '0',
  `ultimo_accesso` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dump dei dati per la tabella `Bacheca`
--

INSERT INTO `Bacheca` (`ID`, `console`, `nome`, `codice`, `preferita`, `ultimo_accesso`) VALUES
(20, 2, 'prova', 'cT8rR4pAUo2rFTsT', 0, '2024-05-10 08:06:11'),
(21, 2, 'prova_2', 'sPXtZgYdMulBcGU7', 0, '2024-05-10 08:01:52'),
(22, 2, 'prova_3', 'Fwr0HKRcUdInassM', 0, '2024-05-10 08:10:42'),
(28, 2, 'prova3', '8ZzPUuVNCTBjqXBA', 0, '2024-05-10 08:01:46');

-- --------------------------------------------------------

--
-- Struttura della tabella `Bacheca_assoc`
--

CREATE TABLE `Bacheca_assoc` (
  `ID` int NOT NULL,
  `other` int NOT NULL,
  `bacheca` int NOT NULL,
  `codice` varchar(16) NOT NULL,
  `privilegi` int NOT NULL DEFAULT '1',
  `codice_bacheca` varchar(16) NOT NULL,
  `preferita` tinyint NOT NULL DEFAULT '0',
  `ultimo_accesso` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dump dei dati per la tabella `Bacheca_assoc`
--

INSERT INTO `Bacheca_assoc` (`ID`, `other`, `bacheca`, `codice`, `privilegi`, `codice_bacheca`, `preferita`, `ultimo_accesso`) VALUES
(3, 21, 20, 'pQQ3I7rgkLnPOgTH', 1, 'cT8rR4pAUo2rFTsT', 1, '2024-05-06 13:47:19');

-- --------------------------------------------------------

--
-- Struttura della tabella `Chat`
--

CREATE TABLE `Chat` (
  `ID` int NOT NULL,
  `testo` text,
  `orario` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `utente` int DEFAULT NULL,
  `bacheca` int DEFAULT NULL,
  `codice` varchar(16) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dump dei dati per la tabella `Chat`
--

INSERT INTO `Chat` (`ID`, `testo`, `orario`, `utente`, `bacheca`, `codice`) VALUES
(2, 'ciao', '2024-05-03 10:25:15', 22, 0, 'ePNPjZnIkCKBlboq'),
(3, 'ciao', '2024-05-03 10:43:59', 22, 0, 'UaLa0k4ywXrfDvf5'),
(4, 'ciao', '2024-05-03 10:44:39', 22, 0, 'UoLXuYFuhbI9r40D'),
(5, 'ciao', '2024-05-03 10:45:04', 22, 0, '83ZsL0DEZXaeNYXm'),
(6, 'ciao', '2024-05-03 10:47:12', 22, 0, '8OEkChxzCTfDSdHc'),
(7, 'ciao', '2024-05-03 10:47:31', 22, 0, '5oqO897R3BRBSboH'),
(8, 'ciao', '2024-05-03 10:48:05', 22, 0, 'giQf2fzr70pXFZwH'),
(9, 'ciao', '2024-05-03 10:54:03', 22, 0, 'EzM01bE448Rc6aXt'),
(10, 'ciao', '2024-05-03 10:58:30', 22, 0, 'XT9vcEQeM8Ru0FMn'),
(11, 'ciao', '2024-05-03 10:58:52', 22, 0, '2ctU6wXrb0vqdu3x'),
(12, 'ciao', '2024-05-03 10:59:54', 22, 0, '4e5L9UsGDsfGbCJq'),
(13, 'ciao', '2024-05-03 11:01:26', 22, 0, 'XeI2q4ONmM3VjWQN'),
(14, 'ciao', '2024-05-03 11:02:00', 22, 0, 'BQVKOr7jYvqPhj6S'),
(15, 'ciao', '2024-05-03 11:02:17', 22, 0, 'EkYA5L72Rcp6XWnP'),
(16, 'ciao', '2024-05-03 11:02:21', 22, 0, 'j9vV7hZUOZLNibsG'),
(17, 'ciao', '2024-05-03 11:29:55', 22, 0, 'RlNwsyLSgTzFdal8'),
(18, 'ciao', '2024-05-05 14:55:44', 22, 20, '0D8TjZ2BejHUbyUf'),
(19, 'ciao', '2024-05-05 15:14:16', 22, 20, '6cJm9cmI4197Gc3i'),
(20, 'ciao', '2024-05-05 15:15:20', 22, 20, 'KxHsSRaoDlhXNfpt'),
(21, 'bella', '2024-05-05 15:15:29', 22, 20, '7xAnpUXJ98uT7frs'),
(22, 'come va', '2024-05-05 15:15:55', 22, 20, 'Z6aQRC3xoqTrMCli'),
(23, 'io', '2024-05-05 15:16:00', 22, 20, 'tABxyXanZNr1ieFO'),
(24, 'tu', '2024-05-05 15:18:16', 22, 20, 'BTUUkgLuTNHXFxaq'),
(25, 'noi', '2024-05-05 15:18:22', 22, 20, 'AoiJRIY3OvG7Fv79'),
(26, 'voi', '2024-05-05 15:20:49', 22, 20, 'CVj6FVmAUKPIqxfN'),
(27, 'loro', '2024-05-05 15:26:26', 22, 20, 'KnfTlVb92PdR6Ykk'),
(28, 'egli', '2024-05-05 15:26:39', 22, 20, 'VXwfwPqsvzxq9JK7'),
(29, 'sui', '2024-05-05 15:26:45', 22, 20, 'CCI0nkb1shtxAGTR'),
(30, 'ciao', '2024-05-05 16:35:05', 22, 20, 'RkLvSSyS3mJP4IPZ'),
(31, 'uella', '2024-05-05 16:35:17', 21, 20, 'woe0tnSAIcDZ0ugj'),
(32, 'aaaaaa', '2024-05-09 13:29:00', 22, 20, 'iXxq0R20zB41W50z'),
(33, 'ciao', '2024-05-10 08:03:40', 22, 21, 'Oj2yX2Ok74jz3H7D');

-- --------------------------------------------------------

--
-- Struttura della tabella `Checkbox`
--

CREATE TABLE `Checkbox` (
  `ID` int NOT NULL,
  `testo` varchar(255) DEFAULT NULL,
  `is_check` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `lista` int DEFAULT NULL,
  `codice` varchar(16) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dump dei dati per la tabella `Checkbox`
--

INSERT INTO `Checkbox` (`ID`, `testo`, `is_check`, `lista`, `codice`) VALUES
(9, 'cosa', 'false', 57, 'KH9DXhDyvoG6q9KD'),
(2, 'prova2', 'false', 44, 'daEPAlhXM1lrc37g');

-- --------------------------------------------------------

--
-- Struttura della tabella `Codici`
--

CREATE TABLE `Codici` (
  `ID` int NOT NULL,
  `codice` varchar(255) NOT NULL,
  `bacheca` int NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dump dei dati per la tabella `Codici`
--

INSERT INTO `Codici` (`ID`, `codice`, `bacheca`) VALUES
(99, 'cT8rR4pAUo2rFTsT', 20),
(322, '8ZzPUuVNCTBjqXBA', -1),
(318, 'a3dHkeSGMtOm1N6x', 20),
(104, 'Y3HINakbhXS7hsUa', 20),
(108, 'y1mLpRJbTKoBb0wO', 20),
(107, 'bFWiV9JqfZVWGySU', 20),
(109, 'tfVO8ZcTLd9M47Ez', 20),
(110, 'ogIshQThoAZ9axVz', 20),
(111, 'b1VBPDFw2HSCPnvZ', 20),
(112, 'aRihzIOBsavKGo0x', 20),
(113, 'zgXxdN6SnBFQEu5T', 20),
(114, 'yWfnLHp7l0iok2L5', 20),
(115, 'KK7qf6sCww3LzXEQ', 20),
(116, 'qm3G6n1F99bwzLZY', 20),
(117, 'PfoP56zvcpJMfORU', 20),
(118, 'hIUqRaIWslMB3R5K', 20),
(119, '517b6ITexFfyZCsh', 20),
(120, 'fS6HGOVEtgxUfpM3', 20),
(121, 'Fm47fMDJnotO1Yyn', 20),
(122, 'jQPtUdhozRQebkXE', 20),
(123, 'M6xd8kgNJyF7ZD32', 20),
(124, 'Vwc7gyvVbV8wRecJ', 20),
(125, 'BNDN85lc8edkQKI0', 20),
(126, 'ClTmjzvmr8yPjGQa', 20),
(127, 'LXifFJSFGeAnnye4', 20),
(128, 'eAOng6jH7YRTg4bM', 20),
(187, 'pQQ3I7rgkLnPOgTH', 20),
(185, 'Fwr0HKRcUdInassM', 22),
(179, 'SFkgZ1OmspTzdw6d', 20),
(178, 'w9sg9ihSe1POhmeC', 20),
(177, 'EYPrcSpazem7ONQi', 20),
(176, 'oCWBRBnG8FK9MZth', 20),
(175, 'Q1qYDouYPtVCA9Aa', 20),
(174, '4uVZRxoNvbZXvs2I', 20),
(173, 'uNScZxPwpzHFo6xB', 20),
(170, 'd525JXGMdmWJPJHE', 20),
(166, '4PvOXJMN3fUtXGqT', 20),
(297, 'Lei8MsBT9CUAMODM', 20),
(145, 'JMDfT5sMCc6SOHkT', 20),
(146, 'jurs0BAI5D4ILDn4', 20),
(147, 'm47khIQubDJBegPy', 20),
(148, '1bHtgG5p30f5Za4c', 20),
(151, 'sPXtZgYdMulBcGU7', 21),
(150, 'daEPAlhXM1lrc37g', 20),
(153, 'CPiQ1nzYKttr364F', 20),
(154, 'bu1wFNpB1SnVQFao', 20),
(155, 'Q28adVoNlPrdBJJE', 20),
(156, 'P7Dv8p8gOHUUx7BT', 20),
(157, 'eR2ZuS6gx0bRP1Mt', 20),
(306, 'Sfdnkt5qcaiXIgv9', 20),
(168, 'voEfxnWZHhBGlWys', 20),
(320, 'QGvLnvSOxqgymhCT', 20),
(203, 'ePNPjZnIkCKBlboq', 0),
(204, 'UaLa0k4ywXrfDvf5', 0),
(205, 'UoLXuYFuhbI9r40D', 0),
(206, '83ZsL0DEZXaeNYXm', 0),
(207, '8OEkChxzCTfDSdHc', 0),
(208, '5oqO897R3BRBSboH', 0),
(209, 'giQf2fzr70pXFZwH', 0),
(210, 'EzM01bE448Rc6aXt', 0),
(211, 'XT9vcEQeM8Ru0FMn', 0),
(212, '2ctU6wXrb0vqdu3x', 0),
(213, '4e5L9UsGDsfGbCJq', 0),
(214, 'XeI2q4ONmM3VjWQN', 0),
(215, 'BQVKOr7jYvqPhj6S', 0),
(216, 'EkYA5L72Rcp6XWnP', 0),
(217, 'j9vV7hZUOZLNibsG', 0),
(218, 'RlNwsyLSgTzFdal8', 0),
(219, '0D8TjZ2BejHUbyUf', 20),
(220, '6cJm9cmI4197Gc3i', 20),
(221, 'KxHsSRaoDlhXNfpt', 20),
(222, '7xAnpUXJ98uT7frs', 20),
(223, 'Z6aQRC3xoqTrMCli', 20),
(224, 'tABxyXanZNr1ieFO', 20),
(225, 'BTUUkgLuTNHXFxaq', 20),
(226, 'AoiJRIY3OvG7Fv79', 20),
(227, 'CVj6FVmAUKPIqxfN', 20),
(228, 'KnfTlVb92PdR6Ykk', 20),
(229, 'VXwfwPqsvzxq9JK7', 20),
(230, 'CCI0nkb1shtxAGTR', 20),
(231, 'RkLvSSyS3mJP4IPZ', 20),
(232, 'woe0tnSAIcDZ0ugj', 20),
(268, 'iXxq0R20zB41W50z', 20),
(269, 'NATPEPsusXly7FwY', 21),
(278, 'ikLIgEaqLvTIplgb', 21),
(271, 'RNqJ1qlJK3PjTj4Q', 21),
(281, 'jESG9PNNxWJGj083', 21),
(273, '4ZH35I3pIMfoJzJD', 21),
(282, 'Bgzv8Uk9DIuX0yaw', 21),
(284, 'T6p0skunF3iy2EfJ', 22),
(283, 'v0eySslclldpMX5I', 21),
(285, 'TUAxZTXDeDTnDxnR', 22),
(328, 'LqOjcMkmxmWWjGZa', 22),
(288, 'MBUQV7UIK5iIKdIJ', 20),
(308, 'jVfusQv0PznVYreC', 20),
(307, '8ayYcoUNAXgXyIsM', 20),
(309, 'rM8SUCjSzY7inlgd', 20),
(311, 'o4ZXVtu71GVxBCc2', 23),
(312, 'ui7wlgaN37EcSena', -1),
(313, 'klcD7b9FUYZJABGu', -1),
(314, '7pVwULrQ1nYQgYai', -1),
(315, '7ZR4cTEy5b3cjJo9', -1),
(316, 'MtQrzpQX5da0jWdX', 24),
(321, 'qb4GcNRDM1sQB4Wm', 20),
(323, 'I79P6J4XqDrvYr2q', 21),
(324, 'Oj2yX2Ok74jz3H7D', 21),
(325, 'U0SxJyD3dOrN8EOg', 21),
(326, 'KH9DXhDyvoG6q9KD', 21),
(327, 'sP7EuFUB66FhnJ2B', 21);

-- --------------------------------------------------------

--
-- Struttura della tabella `Colore`
--

CREATE TABLE `Colore` (
  `ID` int NOT NULL,
  `red` int DEFAULT NULL,
  `blue` int DEFAULT NULL,
  `green` int DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dump dei dati per la tabella `Colore`
--

INSERT INTO `Colore` (`ID`, `red`, `blue`, `green`) VALUES
(1, 0, 0, 0),
(3, 4, 255, 51),
(4, 255, 255, 64),
(5, 122, 0, 74),
(6, 0, 255, 253),
(7, 231, 13, 13);

-- --------------------------------------------------------

--
-- Struttura della tabella `Commento`
--

CREATE TABLE `Commento` (
  `ID` int NOT NULL,
  `testo` varchar(500) DEFAULT NULL,
  `data_creazione` date DEFAULT NULL,
  `user` int DEFAULT NULL,
  `lista` int DEFAULT NULL,
  `codice` varchar(16) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dump dei dati per la tabella `Commento`
--

INSERT INTO `Commento` (`ID`, `testo`, `data_creazione`, `user`, `lista`, `codice`) VALUES
(11, 'ciao', '2024-05-10', 22, 44, '8ayYcoUNAXgXyIsM'),
(8, 'ciao', '2024-05-10', 22, 44, 'Lei8MsBT9CUAMODM'),
(12, 'nuovo', '2024-05-10', 22, 44, 'jVfusQv0PznVYreC'),
(13, 'nuovo', '2024-05-10', 22, 44, 'rM8SUCjSzY7inlgd'),
(15, 'sono un altro fratello', '2024-05-10', 22, 62, 'QGvLnvSOxqgymhCT'),
(16, 'ciao', '2024-05-10', 22, 57, 'U0SxJyD3dOrN8EOg');

-- --------------------------------------------------------

--
-- Struttura della tabella `Console`
--

CREATE TABLE `Console` (
  `ID` int NOT NULL,
  `utente` int DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dump dei dati per la tabella `Console`
--

INSERT INTO `Console` (`ID`, `utente`) VALUES
(1, 21),
(2, 22),
(8, 28),
(9, 29),
(10, 30);

-- --------------------------------------------------------

--
-- Struttura della tabella `Etichetta`
--

CREATE TABLE `Etichetta` (
  `ID` int NOT NULL,
  `testo` varchar(255) DEFAULT NULL,
  `colore` int DEFAULT NULL,
  `lista` int DEFAULT NULL,
  `codice` varchar(16) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dump dei dati per la tabella `Etichetta`
--

INSERT INTO `Etichetta` (`ID`, `testo`, `colore`, `lista`, `codice`) VALUES
(30, 'importante', 7, 57, 'sP7EuFUB66FhnJ2B'),
(22, 'prova', 1, 44, 'MBUQV7UIK5iIKdIJ'),
(19, 'prova', 5, 44, 'd525JXGMdmWJPJHE'),
(29, 'nero', 1, 62, 'qb4GcNRDM1sQB4Wm');

-- --------------------------------------------------------

--
-- Struttura della tabella `Immagine`
--

CREATE TABLE `Immagine` (
  `id` int NOT NULL,
  `nome` varchar(255) NOT NULL,
  `tipo` varchar(255) NOT NULL,
  `dati` longblob NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dump dei dati per la tabella `Immagine`
--

INSERT INTO `Immagine` (`id`, `nome`, `tipo`, `dati`) VALUES
(17, 'immagine profilo 21', 'image/png', 0xffd8ffe000104a46494600010101006000600000fffe003b43524541544f523a2067642d6a7065672076312e3020287573696e6720494a47204a50454720763632292c207175616c697479203d2037350affdb004300080606070605080707070909080a0c140d0c0b0b0c1912130f141d1a1f1e1d1a1c1c20242e2720222c231c1c2837292c30313434341f27393d38323c2e333432ffdb0043010909090c0b0c180d0d1832211c213232323232323232323232323232323232323232323232323232323232323232323232323232323232323232323232323232ffc00011080064006403012200021101031101ffc4001f0000010501010101010100000000000000000102030405060708090a0bffc400b5100002010303020403050504040000017d01020300041105122131410613516107227114328191a1082342b1c11552d1f02433627282090a161718191a25262728292a3435363738393a434445464748494a535455565758595a636465666768696a737475767778797a838485868788898a92939495969798999aa2a3a4a5a6a7a8a9aab2b3b4b5b6b7b8b9bac2c3c4c5c6c7c8c9cad2d3d4d5d6d7d8d9dae1e2e3e4e5e6e7e8e9eaf1f2f3f4f5f6f7f8f9faffc4001f0100030101010101010101010000000000000102030405060708090a0bffc400b51100020102040403040705040400010277000102031104052131061241510761711322328108144291a1b1c109233352f0156272d10a162434e125f11718191a262728292a35363738393a434445464748494a535455565758595a636465666768696a737475767778797a82838485868788898a92939495969798999aa2a3a4a5a6a7a8a9aab2b3b4b5b6b7b8b9bac2c3c4c5c6c7c8c9cad2d3d4d5d6d7d8d9dae2e3e4e5e6e7e8e9eaf2f3f4f5f6f7f8f9faffda000c03010002110311003f00f7b6918485163dd800e738f5ff000a82dd5a256d8b230271f3cb9c638c0cf4e9538ff5effeeaff00334fa00a53b0bbd3c99239122750f9490ab01d7a8e6a5f35e111466263b8ec04be4f009e4fe14b65ff001e16ff00f5c97f9513ff00aeb5ff00aea7ff00406a0089e3dd7d1cc52512842028948523dc743d6a613b995a3f24e55431f987439ff0a9aa04ff008ff9bfeb927f37a008ed97caf30468edf37cc1a42707db349249f68b12ef13ac4c81c957c301d7b73572a0b2ff008f0b7ffae4bfca8010caf6f0a0313100aa025f24e480324fd6a391775e43232482400ed51210a7d723a1ebdea5bbff0052bff5d63ffd0c54f401089dcccd1f92772a863f30ef9ff0a86d6236e6411accdce089252c01ebc67a75ed52a7fc7fcbff005c93f9b54f40088dbd738c724628a6c5f70ffbcdfccd1401149756f05d32cb3c51b145203b80719347f68597fcfddbff00dfd1fe3528ff005eff00eeaff334a91ac6a5573824b7273c9393fce8029596a1642c6dc7daedff00d52ffcb41e9f5a27d42cbceb6ff4bb7ff5a7fe5a0fee37bd4d6a8b269b02364a98941c1c7614e9ff00d75aff00d753ff00a0350027f68597fcfddbff00dfd1fe35026a165f6e97fd2edffd527fcb41eadef571a246952520ef40429c9efd78fc2a34ff008ff9bfeb927f36a004fed0b2ff009fbb7ffbfa3fc6a0b2d42c858dbffa5dbffaa5ff009683d3eb5752358f76dcfccc58f39e6a0b645974c851f255a1507071da8021bbd42cbc95ff004bb7ff005b1ffcb41fdf1ef53ff68597fcfddbff00dfd1fe345c80b6e8a3a092303fefb1533468d22390772676f27bd00524d42cbedd29fb5dbffab4ff009683d5bdea7fed0b2ff9fbb7ff00bfa3fc6953fe3fe5ff00ae49fcdea4489232e573973b8e493cff004a0048195e2dca432966208390464d14b17dc3fef37f33450003fd7bff00babfccd3eab496e935cb3334a08451f24aca3a9ec0d363b051bbcc9263963b713c8303b0fbd4012597fc785bff00d725fe544ffebad7feba9ffd01aaa41621b4b87c9966590c4bb4b4f210381db753e7b28bceb5f9ee3fd69ff9787fee37bd005fa813fe3fe6ff00ae49fcdaa3360a6452259c20cee1e7c9cfa7f154696517dba5f9ee3fd527fcbc49eadef4017ea0b2ff008f0b7ffae4bfcaa28ec002fe64b3302d94db3c830be87e6e4f5a8adac55b4d87cb9265730aed2679300e3d375005abbff52bff005d63ff00d0c54f59f736318b74dcf396f323c913c9fdf1fed548f600cd194966118cef0679327d31f35004a9ff001ff2ff00d724fe6f53d504b28bedd2fcf71fea93fe5e1fd5bdea48ec5417df24c416f971712703df9fad005887ee1ff79bf99a292050b16d19c0661c9c9ea7b9a280147faf7ff757f99a7d675edf45677789750b3b50c830b71804f27a7cc2aac5aedacbbbfe273a6a90c5406c73ee3f79d280352cbfe3c2dffeb92ff2a27ff5d6bff5d4ff00e80d552037b16990b89227c449f2a40493c0ff006e9f3a5ef9d6dfbf83fd69ff00960dfdc6ff006e802fd409ff001ff37fd724fe6d5131be59923f32221813b85b9c2e3d7e7ac5d6757d4f47be5f2ac65bf32c633f66b566d982dd7e6ef9fd294a4a2aecba74e5524a11dd9d354165ff001e16ff00f5c97f95724fe2fd6576e3c3da836403c593f1edf7aba2b737b1e970c82489f10a908b012c78e9f7fad4c67196c5d5c3d4a36e75bf9afd0b777fea57febac7ff00a18a9eb3ee56f4c084cd00cc91f0603fdf1fedd4adf6e59513cc88eecfcc20385c7afcf5662489ff001ff2ff00d724fe6f53d5044bdfb74bfbfb7ff549ff002c1bd5bfdba7c66fa479177c49b1b00b5b9c371d47cfd2802cc3f70ffbcdfccd14906e117cc416dcd920601393450079dfc49ff909597fd713fceb8b80e664e08f9c75fad7a3f8d342bed6751b76b4452228b0c4e7b93e83dab9a87c19aa970ea2260af83b493820f23a5007a9d97fc785bffd725fe544ff00ebad7feba9ff00d01aaadb5fa45a740d24532a88972cc981d052dcdeaac96ccd04e0090e494ff65a80342a04ff008ff9bfeb927f36a87fb520ce364bff007c542ba945f6c95bcb9706341f73ddbfc68034ea0b2ff8f0b7ff00ae4bfcaa1fed480f44978ff62a1b5d4e18eca00c928db1ae7e4f6a00b977fea57febac7ffa18a9eb32e75289a2502397fd621fb9e8c2a6fed4833f725ffbe280264ff8ff0097feb927f37a9eb31752885ecade5cd831a0fb9eedfe3530d56024e125e3afc94016a2fb87fde6fe668a4b76df0870080c4919f73450028ff5effeeaff00334aa8880ec50b9393818c9f5a41febdff00dd5fe669f4015ad5124d3604750ca625cab0c83c0a66a5feaa2ffae9ff00b29a96cbfe3c2dff00eb92ff002a8b52ff005517fd74ff00d94d0050c0ce7bd307faf7ff00757f99a92a35ff005eff00eeaff334863c003a532100dbc60f4d83f953e9907fa88ffdd1fca80097ee0ff797f98a7e0120e3914c97ee0ff797f98a9280231febdffdd5fe669f81f9d307faf7ff00757f99a7d006b5affc7b27d28a2d7fe3d93e945310f68a373978d18f4c919a6fd9e1ff009e31ff00df228a2800fb3c1ff3c63ffbe451f67841ff00531ffdf228a28017c98bfe79a7fdf228f262ff009e69ff007c8a28a003c98bfe79a7fdf3479317fcf34ffbe45145001e4c5ff3cd3fef91479317fcf34ffbe45145001e4c5ff3cd3fef91479317fcf34ffbe45145003c000600c0145145007fffd9),
(18, 'immagine profilo 22', 'image/png', 0xffd8ffe000104a46494600010101006000600000fffe003b43524541544f523a2067642d6a7065672076312e3020287573696e6720494a47204a50454720763632292c207175616c697479203d2037350affdb004300080606070605080707070909080a0c140d0c0b0b0c1912130f141d1a1f1e1d1a1c1c20242e2720222c231c1c2837292c30313434341f27393d38323c2e333432ffdb0043010909090c0b0c180d0d1832211c213232323232323232323232323232323232323232323232323232323232323232323232323232323232323232323232323232ffc00011080064006403012200021101031101ffc4001f0000010501010101010100000000000000000102030405060708090a0bffc400b5100002010303020403050504040000017d01020300041105122131410613516107227114328191a1082342b1c11552d1f02433627282090a161718191a25262728292a3435363738393a434445464748494a535455565758595a636465666768696a737475767778797a838485868788898a92939495969798999aa2a3a4a5a6a7a8a9aab2b3b4b5b6b7b8b9bac2c3c4c5c6c7c8c9cad2d3d4d5d6d7d8d9dae1e2e3e4e5e6e7e8e9eaf1f2f3f4f5f6f7f8f9faffc4001f0100030101010101010101010000000000000102030405060708090a0bffc400b51100020102040403040705040400010277000102031104052131061241510761711322328108144291a1b1c109233352f0156272d10a162434e125f11718191a262728292a35363738393a434445464748494a535455565758595a636465666768696a737475767778797a82838485868788898a92939495969798999aa2a3a4a5a6a7a8a9aab2b3b4b5b6b7b8b9bac2c3c4c5c6c7c8c9cad2d3d4d5d6d7d8d9dae2e3e4e5e6e7e8e9eaf2f3f4f5f6f7f8f9faffda000c03010002110311003f00f5f0000000001d00a5a28af20ea0a28a2802a6a1a741a9c0b0ced32aab6e06195a360704755c1e84d5297c35613c7124cf7922c4cccbbaea4246e00119ce7181d3eb5b154aff00525b07895ad2f27126ef9ade13205c63ae3a673c7d0d34df4119f0f86b4d68d1a19ef70bc2b2de48318e38e7d38cfa01572c747874f9da68ee6f652536159ee9e45fae18919e3ad25aeae93dd2db0b1bf889240692d9950633d5ba0e9fcbd6b4a9b6fa86814514548c28a28a00e4f5b5c6ab2e17a81d07b51536b1ff0021293e83f9515ec52fe1af43925f133a6a28a2bc73ac28a28a0028a46608a598e001926b26e6e2c35711db47a84f149bf729b7628c4e0f19c74e7a7b5006aa6fdbf3905bd8714eae66daff0047d26fa54935dbc9a55cc6d1dc3b3a839ff77a8c633f5ae9a9b5604145145200a28a280399d63fe42527d07f2a28d63fe42527d07f2a2bd8a5fc35e8724be2674d4514578e75850a19db6a2ee3e808feb4527439048fa1c5546d7f784efd074b6773242e9e438dca4678eff8d739a778267b1be5b8679a50010cace4820fb16c56f4fe79b77104acb291f292c78358cb1788bcd6cde8f2f1f28c8c83ee71fd2894e9ae8ff01a52ee8c8bdf86d7577ab4f7a2e644124c6409ce064e718dd8aed92ce68d76a5b381cf191fe35897f16bed313657a889c7dee474fcfafbd5dd3d6fd216fb74e5df3c10dfe155ed2127669fe02e56b5ba21bbd7b4cb1d562d32eaf6de1be948090492aabb127030a4e4e4fa66b4a900c671df93ef4b532e5fb20afd428a28a919cceb1ff21293e83f95146b1ff21293e83f9515ec52fe1af43925f133a6a28a2bc73ac28a28f41c927a00324d00155350b49aee14482f65b47570de6441493c1e08208c73fa55cd927fcf19bfefdb7f85473c3712425621344e48f9fc92d81f422ad425d857452b0b0bcb499dee3559ef1187092468a14fa82a01fc2b42abdbdade46499e59e639e3f7054631ec2acec93fe78cdff7edbfc28719760ba128a0820e19594fa3291fce8a96adb8c28a28a40733ac7fc84a4fa0fe5451ac7fc84a4fa0fe5457b14bf86bd0e497c4ce9a8a28af1ceb0a553b5d5b6ab63f858641a4a29a6d3ba06ae5897566b681a46890220e809acd1e34b732b44213bd464af703f3ab2caaca5580208c107bd66dfbdad82c6dfd9725c6e247fa3db872bf5ab75ab37eebfc04a10ea8b577e31b6b27db3201ef9e0f19ab765e2017d1978621818ea7d6b1edef6defeec46fa4dd233027cdb8b5dabc7624d69c714712ed8d1101e70a3147b6ac9eaff00e485b62c4f7125c32965550a0f02a2a28a9949c9dd8256d828a28a919cceb1ff21293e83f95146b1ff21293e83f9515ec52fe1af43925f133a6a28a2bc73ac29d1446699230719eff008536a482410ce9230240ce71f4aba76e65714b6d0b5fd9a7fe7a1ff3f853934d4c7cf23e7fd923fc29cda9dbaa966de0019248e950ff006f69dff3f09ff7d0ff001aeefdcf97e063ef937f66c5ff003d24fcc7f851fd9b17fcf493f31fe1513eb7631b159250ac3b3100ff003a7c7ab5acaa5a362e07195c1feb47ee7cbf00f7c82eed96dca6c663bb39dd8aad562eee56e19022b00a0e49fc2abd71d6e5e7f74d617b6a252d1456451cceb1ff0021293e83f95146b1ff0021293e83f9515ec52fe1af43925f133a6a28a2bc73ac28a28a008e6852785e27ced61838acd1e1eb2595a50d36f6183f3f5fd28a2a2518bdd149b5b0ebbd02caf5f74be68f657c0e98ab765630d8c6522dc41eec7268a28518a95ec0dbb1668a28ab2428a28a00e6758ff90949f41fca8a28af6297f0d7a1c92f899fffd9);

-- --------------------------------------------------------

--
-- Struttura della tabella `Lista`
--

CREATE TABLE `Lista` (
  `ID` int NOT NULL,
  `attivita` int NOT NULL,
  `nome` varchar(255) DEFAULT NULL,
  `descrizione` varchar(500) DEFAULT NULL,
  `data_creazione` date DEFAULT NULL,
  `data_ultima_modifica` date DEFAULT NULL,
  `codice` varchar(16) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dump dei dati per la tabella `Lista`
--

INSERT INTO `Lista` (`ID`, `attivita`, `nome`, `descrizione`, `data_creazione`, `data_ultima_modifica`, `codice`) VALUES
(62, 42, 'ciao', 'wella', '2024-05-10', '2024-05-10', 'a3dHkeSGMtOm1N6x'),
(59, 48, 'Task 1', '', '2024-05-09', '2024-05-09', 'TUAxZTXDeDTnDxnR'),
(57, 45, 'Task 2', '', '2024-05-09', '2024-05-09', 'jESG9PNNxWJGj083'),
(58, 47, 'Task 3', '', '2024-05-09', '2024-05-09', 'Bgzv8Uk9DIuX0yaw');

-- --------------------------------------------------------

--
-- Struttura della tabella `Scadenza`
--

CREATE TABLE `Scadenza` (
  `ID` int NOT NULL,
  `data` date DEFAULT NULL,
  `lista` int DEFAULT NULL,
  `codice` varchar(16) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dump dei dati per la tabella `Scadenza`
--

INSERT INTO `Scadenza` (`ID`, `data`, `lista`, `codice`) VALUES
(13, '2024-05-10', 44, 'Sfdnkt5qcaiXIgv9'),
(6, '2024-05-31', 53, 'ikLIgEaqLvTIplgb'),
(7, '2024-05-29', 56, 'v0eySslclldpMX5I'),
(16, '2024-05-26', 59, 'LqOjcMkmxmWWjGZa'),
(15, '2024-05-11', 57, 'I79P6J4XqDrvYr2q');

-- --------------------------------------------------------

--
-- Struttura della tabella `Utenti`
--

CREATE TABLE `Utenti` (
  `ID` int NOT NULL,
  `nome` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `cognome` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `mail` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `data_nascita` date DEFAULT NULL,
  `console` int DEFAULT NULL,
  `img_profilo` int DEFAULT NULL,
  `codice` varchar(256) DEFAULT NULL,
  `active` tinyint NOT NULL DEFAULT '1',
  `tema` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'giallo'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dump dei dati per la tabella `Utenti`
--

INSERT INTO `Utenti` (`ID`, `nome`, `cognome`, `mail`, `password`, `data_nascita`, `console`, `img_profilo`, `codice`, `active`, `tema`) VALUES
(21, 'd', 'd', 'd@ga.com', 'a', '2024-03-25', 1, 17, '6f4b6612125fb3a0daecd2799dfd6c9c299424fd920f9b308110a2c1fbd8f443', 1, 'e0ab23'),
(22, 'Davide', 'Bizzocchi', 'bizzocdav05@zanelli.edu.it', 'c', '2024-03-25', 2, 18, '785f3ec7eb32f30b90cd0fcf3657d388b5ff4297f2f9716ff66e9b69c05ddd09', 1, 'giallo'),
(28, 'propva', 'prova', 'bizzocdav05@zanelli.edu.it', 'ciao', '2024-05-06', 8, NULL, '59e19706d51d39f66711c2653cd7eb1291c94d9b55eb14bda74ce4dc636d015a', 1, 'e0ab23'),
(29, 'Luca', 'Bigi', 'bigiluc05@zanelli.edu.it', 'b', '2024-05-03', 9, NULL, '35135aaa6cc23891b40cb3f378c53a17a1127210ce60e125ccf03efcfdaec458', 0, 'giallo'),
(30, 'fa', 'fa', 'bizzocdav05@zanelli.edu.it', 'bizzo', '2024-05-12', 10, NULL, '624b60c58c9d8bfb6ff1886c2fd605d2adeb6ea4da576068201b6c6958ce93f4', 1, 'giallo');

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `Attivita`
--
ALTER TABLE `Attivita`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `bacheca` (`bacheca`);

--
-- Indici per le tabelle `Bacheca`
--
ALTER TABLE `Bacheca`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `console` (`console`);

--
-- Indici per le tabelle `Bacheca_assoc`
--
ALTER TABLE `Bacheca_assoc`
  ADD PRIMARY KEY (`ID`);

--
-- Indici per le tabelle `Chat`
--
ALTER TABLE `Chat`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `utente` (`utente`),
  ADD KEY `bacheca` (`bacheca`);

--
-- Indici per le tabelle `Checkbox`
--
ALTER TABLE `Checkbox`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `attivita` (`lista`);

--
-- Indici per le tabelle `Codici`
--
ALTER TABLE `Codici`
  ADD PRIMARY KEY (`ID`);

--
-- Indici per le tabelle `Colore`
--
ALTER TABLE `Colore`
  ADD PRIMARY KEY (`ID`);

--
-- Indici per le tabelle `Commento`
--
ALTER TABLE `Commento`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `user` (`user`),
  ADD KEY `attivita` (`lista`);

--
-- Indici per le tabelle `Console`
--
ALTER TABLE `Console`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `utente` (`utente`);

--
-- Indici per le tabelle `Etichetta`
--
ALTER TABLE `Etichetta`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `colore` (`colore`),
  ADD KEY `attivita` (`lista`);

--
-- Indici per le tabelle `Immagine`
--
ALTER TABLE `Immagine`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `Lista`
--
ALTER TABLE `Lista`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `attivita` (`attivita`);

--
-- Indici per le tabelle `Scadenza`
--
ALTER TABLE `Scadenza`
  ADD PRIMARY KEY (`ID`);

--
-- Indici per le tabelle `Utenti`
--
ALTER TABLE `Utenti`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `console` (`console`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `Attivita`
--
ALTER TABLE `Attivita`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT per la tabella `Bacheca`
--
ALTER TABLE `Bacheca`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT per la tabella `Bacheca_assoc`
--
ALTER TABLE `Bacheca_assoc`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT per la tabella `Chat`
--
ALTER TABLE `Chat`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT per la tabella `Checkbox`
--
ALTER TABLE `Checkbox`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT per la tabella `Codici`
--
ALTER TABLE `Codici`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=329;

--
-- AUTO_INCREMENT per la tabella `Colore`
--
ALTER TABLE `Colore`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT per la tabella `Commento`
--
ALTER TABLE `Commento`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT per la tabella `Console`
--
ALTER TABLE `Console`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT per la tabella `Etichetta`
--
ALTER TABLE `Etichetta`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT per la tabella `Immagine`
--
ALTER TABLE `Immagine`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT per la tabella `Lista`
--
ALTER TABLE `Lista`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT per la tabella `Scadenza`
--
ALTER TABLE `Scadenza`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT per la tabella `Utenti`
--
ALTER TABLE `Utenti`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

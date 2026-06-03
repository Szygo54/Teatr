-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Cze 02, 2026 at 06:07 PM
-- Wersja serwera: 10.4.32-MariaDB
-- Wersja PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `teatr_db`
--

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `miejsca`
--

CREATE TABLE `miejsca` (
  `id` int(11) NOT NULL,
  `rzad` int(11) NOT NULL,
  `numer` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `miejsca`
--

INSERT INTO `miejsca` (`id`, `rzad`, `numer`) VALUES
(1, 1, 1),
(2, 1, 2),
(3, 1, 3),
(4, 1, 4),
(5, 1, 5),
(6, 1, 6),
(7, 1, 7),
(8, 1, 8),
(9, 1, 9),
(10, 1, 10),
(11, 1, 11),
(12, 1, 12),
(13, 2, 1),
(14, 2, 2),
(15, 2, 3),
(16, 2, 4),
(17, 2, 5),
(18, 2, 6),
(19, 2, 7),
(20, 2, 8),
(21, 2, 9),
(22, 2, 10),
(23, 2, 11),
(24, 2, 12),
(25, 3, 1),
(26, 3, 2),
(27, 3, 3),
(28, 3, 4),
(29, 3, 5),
(30, 3, 6),
(31, 3, 7),
(32, 3, 8),
(33, 3, 9),
(34, 3, 10),
(35, 3, 11),
(36, 3, 12),
(37, 4, 1),
(38, 4, 2),
(39, 4, 3),
(40, 4, 4),
(41, 4, 5),
(42, 4, 6),
(43, 4, 7),
(44, 4, 8),
(45, 4, 9),
(46, 4, 10),
(47, 4, 11),
(48, 4, 12),
(49, 5, 1),
(50, 5, 2),
(51, 5, 3),
(52, 5, 4),
(53, 5, 5),
(54, 5, 6),
(55, 5, 7),
(56, 5, 8),
(57, 5, 9),
(58, 5, 10),
(59, 5, 11),
(60, 5, 12),
(61, 6, 1),
(62, 6, 2),
(63, 6, 3),
(64, 6, 4),
(65, 6, 5),
(66, 6, 6),
(67, 6, 7),
(68, 6, 8),
(69, 6, 9),
(70, 6, 10),
(71, 6, 11),
(72, 6, 12),
(73, 7, 1),
(74, 7, 2),
(75, 7, 3),
(76, 7, 4),
(77, 7, 5),
(78, 7, 6),
(79, 7, 7),
(80, 7, 8),
(81, 7, 9),
(82, 7, 10),
(83, 7, 11),
(84, 7, 12),
(85, 8, 1),
(86, 8, 2),
(87, 8, 3),
(88, 8, 4),
(89, 8, 5),
(90, 8, 6),
(91, 8, 7),
(92, 8, 8),
(93, 8, 9),
(94, 8, 10),
(95, 8, 11),
(96, 8, 12);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `rezerwacje`
--

CREATE TABLE `rezerwacje` (
  `id` int(11) NOT NULL,
  `uzytkownik_id` int(11) NOT NULL,
  `spektakl_id` int(11) NOT NULL,
  `miejsce_id` int(11) NOT NULL,
  `data_zakupu` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rezerwacje`
--

INSERT INTO `rezerwacje` (`id`, `uzytkownik_id`, `spektakl_id`, `miejsce_id`, `data_zakupu`) VALUES
(1, 1, 2, 3, '2026-06-02 15:46:21'),
(2, 3, 1, 1, '2026-06-02 15:59:44'),
(3, 3, 1, 2, '2026-06-02 15:59:44'),
(4, 3, 2, 4, '2026-06-02 16:03:35'),
(5, 3, 2, 5, '2026-06-02 16:03:35');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `spektakle`
--

CREATE TABLE `spektakle` (
  `id` int(11) NOT NULL,
  `tytul` varchar(150) NOT NULL,
  `opis` text DEFAULT NULL,
  `data_wystawienia` datetime NOT NULL,
  `cena` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `spektakle`
--

INSERT INTO `spektakle` (`id`, `tytul`, `opis`, `data_wystawienia`, `cena`) VALUES
(1, 'Wariat i zakonnica', 'Szalona i pełna dowcipu inscenizacja dramatu Witkacego na scenie Teatru Jura.', '2026-07-15 19:00:00', 85.00),
(2, 'Wesele', 'Klasyczny dramat Stanisława Wyspiańskiego w nowoczesnej odsłonie.', '2026-07-20 18:30:00', 110.00),
(3, 'Mistrz i Małgorzata', 'Niezwykła opowieść o miłości i wolności w wykonaniu zespołu Teatru Jura.', '2026-07-25 19:00:00', 95.00);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `uzytkownicy`
--

CREATE TABLE `uzytkownicy` (
  `id` int(11) NOT NULL,
  `imie` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `haslo` varchar(255) NOT NULL,
  `rola` enum('klient','admin') DEFAULT 'klient'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `uzytkownicy`
--

INSERT INTO `uzytkownicy` (`id`, `imie`, `email`, `haslo`, `rola`) VALUES
(1, 'Jan Kowalski', 'jan@wp.pl', '$2y$10$mC7wS8wV9XpQyE2bZ3uO1eFfGgHhIiJjKkLlMmNnOoPpQqRrSsTtU', 'klient'),
(2, 'Anna Administrator', 'admin@teatr.pl', '$2y$10$mC7wS8wV9XpQyE2bZ3uO1eFfGgHhIiJjKkLlMmNnOoPpQqRrSsTtU', 'admin'),
(3, 'Jola', 'wataha_joli@mail.pl', '$2y$10$Xx3gwBPU9ZAXk96oZO.cpe7eU1eYxF5pgJpg9kdRO12V.AcHIu2Ue', 'klient');

--
-- Indeksy dla zrzutów tabel
--

--
-- Indeksy dla tabeli `miejsca`
--
ALTER TABLE `miejsca`
  ADD PRIMARY KEY (`id`);

--
-- Indeksy dla tabeli `rezerwacje`
--
ALTER TABLE `rezerwacje`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unikaj_dubla` (`spektakl_id`,`miejsce_id`),
  ADD KEY `uzytkownik_id` (`uzytkownik_id`),
  ADD KEY `miejsce_id` (`miejsce_id`);

--
-- Indeksy dla tabeli `spektakle`
--
ALTER TABLE `spektakle`
  ADD PRIMARY KEY (`id`);

--
-- Indeksy dla tabeli `uzytkownicy`
--
ALTER TABLE `uzytkownicy`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `miejsca`
--
ALTER TABLE `miejsca`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=97;

--
-- AUTO_INCREMENT for table `rezerwacje`
--
ALTER TABLE `rezerwacje`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `spektakle`
--
ALTER TABLE `spektakle`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `uzytkownicy`
--
ALTER TABLE `uzytkownicy`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `rezerwacje`
--
ALTER TABLE `rezerwacje`
  ADD CONSTRAINT `rezerwacje_ibfk_1` FOREIGN KEY (`uzytkownik_id`) REFERENCES `uzytkownicy` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rezerwacje_ibfk_2` FOREIGN KEY (`spektakl_id`) REFERENCES `spektakle` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rezerwacje_ibfk_3` FOREIGN KEY (`miejsce_id`) REFERENCES `miejsca` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

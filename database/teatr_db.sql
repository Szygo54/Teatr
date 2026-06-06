-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Cze 06, 2026 at 11:51 PM
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
-- Struktura tabeli dla tabeli `aktorzy`
--

CREATE TABLE `aktorzy` (
  `id` int(11) NOT NULL,
  `imie_nazwisko` varchar(255) NOT NULL,
  `zdjecie` varchar(255) NOT NULL,
  `specjalizacja` varchar(100) DEFAULT 'Aktor'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `aktorzy`
--

INSERT INTO `aktorzy` (`id`, `imie_nazwisko`, `zdjecie`, `specjalizacja`) VALUES
(1, 'Jan Kowalski', 'aktorzy/aktor1.jpg', 'Aktor dramatyczny'),
(2, 'Piotr Zieliński', 'aktorzy/aktor2.jpg', 'Aktor komediowy'),
(3, 'Tomasz Rumosz-Piaskowiec', 'aktorzy/aktor3.jpg', 'Aktor pierwszoplanowy'),
(4, 'Michał Wójcik', 'aktorzy/aktor4.jpg', 'Aktor charakterystyczny'),
(5, 'Maciej Lewandowski', 'aktorzy/aktor5.jpg', 'Aktor'),
(6, 'Jolanta Wilk', 'aktorzy/aktor6.jpg', 'Aktorka dramatyczna'),
(7, 'Zofia Szymańska', 'aktorzy/aktor7.jpg', 'Aktorka komediowa'),
(8, 'Helena Szewczyk', 'aktorzy/aktor8.jpg', 'Aktorka charakterystyczna'),
(9, 'Katarzyna Wiśniewska', 'aktorzy/aktor9.jpg', 'Aktorka gościnna'),
(10, 'Magdalena Dąbrowska', 'aktorzy/aktor10.jpg', 'Aktorka drugoplanowa');

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
-- Struktura tabeli dla tabeli `obsada`
--

CREATE TABLE `obsada` (
  `id` int(11) NOT NULL,
  `spektakl_id` int(11) NOT NULL,
  `aktor_id` int(11) NOT NULL,
  `grana_postac` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `rezerwacje`
--

CREATE TABLE `rezerwacje` (
  `id` int(11) NOT NULL,
  `uzytkownik_id` int(11) NOT NULL,
  `termin_id` int(11) NOT NULL,
  `miejsce_id` int(11) NOT NULL,
  `data_zakupu` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rezerwacje`
--

INSERT INTO `rezerwacje` (`id`, `uzytkownik_id`, `termin_id`, `miejsce_id`, `data_zakupu`) VALUES
(7, 5, 124, 15, '2026-06-06 23:04:00'),
(8, 5, 124, 16, '2026-06-06 23:04:00'),
(9, 5, 124, 17, '2026-06-06 23:04:00'),
(10, 5, 124, 19, '2026-06-06 23:04:00'),
(11, 5, 124, 20, '2026-06-06 23:04:00'),
(12, 5, 124, 21, '2026-06-06 23:04:00'),
(13, 5, 124, 27, '2026-06-06 23:04:00'),
(14, 5, 124, 31, '2026-06-06 23:04:00'),
(15, 5, 124, 33, '2026-06-06 23:04:00'),
(16, 5, 124, 39, '2026-06-06 23:04:00'),
(17, 5, 124, 40, '2026-06-06 23:04:00'),
(18, 5, 124, 41, '2026-06-06 23:04:00'),
(19, 5, 124, 43, '2026-06-06 23:04:00'),
(20, 5, 124, 44, '2026-06-06 23:04:00'),
(21, 5, 124, 45, '2026-06-06 23:04:00'),
(22, 5, 124, 51, '2026-06-06 23:04:00'),
(23, 5, 124, 53, '2026-06-06 23:04:00'),
(24, 5, 124, 57, '2026-06-06 23:04:00'),
(25, 5, 124, 63, '2026-06-06 23:04:00'),
(26, 5, 124, 64, '2026-06-06 23:04:00'),
(27, 5, 124, 65, '2026-06-06 23:04:00'),
(28, 5, 124, 67, '2026-06-06 23:04:00'),
(29, 5, 124, 68, '2026-06-06 23:04:00'),
(30, 5, 124, 69, '2026-06-06 23:04:00'),
(31, 5, 108, 30, '2026-06-06 23:06:36'),
(32, 5, 108, 54, '2026-06-06 23:06:36'),
(35, 5, 116, 5, '2026-06-06 23:09:14'),
(36, 5, 108, 9, '2026-06-06 23:13:58'),
(37, 5, 112, 9, '2026-06-06 23:16:16'),
(38, 5, 108, 82, '2026-06-06 23:18:39'),
(39, 5, 112, 82, '2026-06-06 23:20:53'),
(40, 5, 108, 56, '2026-06-06 23:23:22'),
(43, 5, 108, 65, '2026-06-06 23:23:34'),
(44, 5, 109, 70, '2026-06-06 23:23:52'),
(45, 5, 109, 71, '2026-06-06 23:23:52');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `spektakle`
--

CREATE TABLE `spektakle` (
  `id` int(11) NOT NULL,
  `tytul` varchar(255) NOT NULL,
  `opis` text DEFAULT NULL,
  `cena` decimal(10,2) NOT NULL,
  `plakat` varchar(255) DEFAULT 'zdjecia/domyslny_plakat.jpg'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `spektakle`
--

INSERT INTO `spektakle` (`id`, `tytul`, `opis`, `cena`, `plakat`) VALUES
(1, 'Wariat i zakonnica', 'Co się stanie, gdy chłodna dyscyplina religijna zderzy się z nieokiełznanym ogniem szaleństwa? \"Wariat i zakonnica\" to wybuchowa mieszanka lęku, pasji i psychoanalizy w krzywym zwierciadle. Walpurg - artysta uwięziony w ciele chorego – kontra siostra Barbara, która zamiast leczyć, zaczyna gubić się w świecie własnych emocji. Zapraszamy do spektaklu, w którym granica między zdrowiem a obłędem jest tak cienka, że niemal niewidoczna.', 85.00, 'zdjecia/wariat.jpg'),
(2, 'Wesele', 'Inteligencja kontra wieś. Mity kontra codzienność. Duchy kontra ludzie. \"Wesele\" to najbardziej bezlitosne lustro, w jakim kiedykolwiek przejrzało się nasze społeczeństwo. Na scenie Teatru Jura zderzamy dwa światy, które mówią tym samym językiem, ale kompletnie się nie rozumieją. Czy pod maską weselnych gości kryje się jeszcze jakaś prawda o nas samych, czy zostaliśmy już tylko zbiorem naszych własnych uprzedzeń?', 110.00, 'zdjecia/wesele.jpg'),
(3, 'Mistrz i Małgorzata', 'W Moskwie lat 30. pojawia się tajemniczy nieznajomy, który wywraca porządek miasta do góry nogami. Diabeł w przebraniu profesora, czarny kot chodzący na dwóch łapach i miłość, która jest w stanie pokonać nawet śmierć. \"Mistrz i Małgorzata\" to widowisko, w którym rzeczywistość miesza się z magią, a granica między dobrem a złem zaciera się bezpowrotnie. Czy jesteś gotowy na spotkanie z Wolandem?', 95.00, 'zdjecia/mistrz.jpg'),
(4, 'Folwark zwierzęcy', '\"Wszystkie zwierzęta są równe, ale niektóre są równiejsze od innych\". Orwell stworzył brutalną mapę drogową tego, jak marzenie o wolności zamienia się w koszmar dyktatury. \"Folwark zwierzęcy\" w Teatrze Jura to nie tylko opowieść o zwierzętach - to lustro, w którym przegląda się każda władza, która zapomniała, komu ma służyć. Spektakl o tym, jak łatwo oddać wolność za obietnicę bezpieczeństwa i jak szybko rewolucja zjada własne dzieci.', 80.00, 'zdjecia/folwark.png'),
(5, 'O kotach', 'Mają w sobie coś, czego nam brakuje: absolutny spokój i pogardę dla konwenansów. Bukowski o kotach pisał tak, jak żył - bez upiększania, za to z ogromnym szacunkiem do tych wolnych stworzeń. \'O kotach\' to teatralny wieczór z dymem papierosa w tle, pełen cynicznego humoru i gorzkiej refleksji nad tym, co w życiu naprawdę istotne. Poznaj świat widziany oczami kota i zgorzkniałego geniusza.', 45.00, 'zdjecia/kot.jpg'),
(6, 'Makbet', 'Jeden czyn, który zmienia wszystko. W mrocznych zakamarkach Szkocji ambicja bierze górę nad lojalnością, a przeznaczenie splata się z szaleństwem. Czy Makbet zdoła udźwignąć ciężar korony zdobytej krwią? Zanurz się w studium upadku człowieka, dla którego każda kolejna decyzja prowadzi prosto w otchłań.', 90.00, 'zdjecia/makbet.jpg'),
(7, 'Mistrz i Małgorzata', 'W Moskwie lat 30. pojawia się tajemniczy nieznajomy, który wywraca porządek miasta do góry nogami. Diabeł w przebraniu profesora, czarny kot chodzący na dwóch łapach i miłość, która jest w stanie pokonać nawet śmierć. \"Mistrz i Małgorzata\" to widowisko, w którym rzeczywistość miesza się z magią, a granica między dobrem a złem zaciera się bezpowrotnie. Czy jesteś gotowy na spotkanie z Wolandem?', 95.00, 'zdjecia/mistrz.jpg'),
(8, 'Pijacy', 'Wino, karty i kłótnie o nic. Witamy w domu, gdzie szklanka nigdy nie stoi pusta, a każda rozmowa kończy się awanturą. \"Pijacy\" to błyskotliwa satyra na obyczaje, która nawet po 250 latach nic nie straciła na aktualności. Zobacz, jak bohaterowie Bohomolca zamieniają racjonalne dyskusje w absurdalny spektakl pomyłek i upadków. Spektakl, który bawi do łez, ale zostawia widza z ważnym pytaniem: czy naprawdę warto?', 70.00, 'zdjecia/pijacy.jpg'),
(9, 'Romeo i Julia', 'Dwa rody, jedna nienawiść i miłość, która nie miała prawa się zdarzyć. Historia Romea i Julii to opowieść o buncie młodości przeciwko skostniałym zasadom świata dorosłych. Czy szczere uczucie jest w stanie zatrzymać spiralę przemocy? Spektakl o namiętności tak silnej, że gotowa jest przekroczyć granice życia i śmierci.', 85.00, 'zdjecia/romeo.png'),
(10, 'Wariat i zakonnica', 'Co się stanie, gdy chłodna dyscyplina religijna zderzy się z nieokiełznanym ogniem szaleństwa? \"Wariat i zakonnica\" to wybuchowa mieszanka lęku, pasji i psychoanalizy w krzywym zwierciadle. Walpurg - artysta uwięziony w ciele chorego – kontra siostra Barbara, która zamiast leczyć, zaczyna gubić się w świecie własnych emocji. Zapraszamy do spektaklu, w którym granica między zdrowiem a obłędem jest tak cienka, że niemal niewidoczna.', 85.00, 'zdjecia/wariat.jpg'),
(11, 'Wesele', 'Inteligencja kontra wieś. Mity kontra codzienność. Duchy kontra ludzie. \"Wesele\" to najbardziej bezlitosne lustro, w jakim kiedykolwiek przejrzało się nasze społeczeństwo. Na scenie Teatru Jura zderzamy dwa światy, które mówią tym samym językiem, ale kompletnie się nie rozumieją. Czy pod maską weselnych gości kryje się jeszcze jakaś prawda o nas samych, czy zostaliśmy już tylko zbiorem naszych własnych uprzedzeń?', 110.00, 'zdjecia/wesele.jpg'),
(12, 'Zbrodnia i kara', 'Gdzie kończy się granica między jednostką wybitną a zwykłym zbrodniarzem? Rodion Raskolnikow, przekonany o własnej wyjątkowości, podejmuje próbę, która na zawsze zmienia jego życie. Zanurz się w otchłań ludzkiego sumienia, gdzie każdy krok ku wolności staje się coraz cięższym łańcuchem winy. Czy w świecie pełnym cierpienia istnieje odkupienie?', 95.00, 'zdjecia/zbrodnia.jpg');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `terminy`
--

CREATE TABLE `terminy` (
  `id` int(11) NOT NULL,
  `spektakl_id` int(11) NOT NULL,
  `data_wystawienia` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `terminy`
--

INSERT INTO `terminy` (`id`, `spektakl_id`, `data_wystawienia`) VALUES
(108, 1, '2026-07-03 19:00:00'),
(109, 1, '2026-07-07 19:00:00'),
(110, 1, '2026-07-10 19:00:00'),
(111, 1, '2026-07-14 19:00:00'),
(112, 2, '2026-07-04 16:00:00'),
(113, 2, '2026-07-08 16:00:00'),
(114, 2, '2026-07-11 16:00:00'),
(115, 2, '2026-07-15 16:00:00'),
(116, 3, '2026-07-05 19:00:00'),
(117, 3, '2026-07-09 19:00:00'),
(118, 3, '2026-07-12 19:00:00'),
(119, 3, '2026-07-16 19:00:00'),
(120, 4, '2026-08-01 19:00:00'),
(121, 4, '2026-08-05 19:00:00'),
(122, 4, '2026-08-08 19:00:00'),
(123, 4, '2026-08-12 19:00:00'),
(124, 5, '2026-08-02 18:00:00'),
(125, 5, '2026-08-06 18:00:00'),
(126, 5, '2026-08-09 18:00:00'),
(127, 5, '2026-08-13 18:00:00'),
(128, 6, '2026-08-03 19:00:00'),
(129, 6, '2026-08-07 19:00:00'),
(130, 6, '2026-08-10 19:00:00'),
(131, 6, '2026-08-14 19:00:00'),
(132, 7, '2026-09-02 19:00:00'),
(133, 7, '2026-09-06 19:00:00'),
(134, 7, '2026-09-09 19:00:00'),
(135, 7, '2026-09-13 19:00:00'),
(136, 8, '2026-09-03 19:00:00'),
(137, 8, '2026-09-07 19:00:00'),
(138, 8, '2026-09-10 19:00:00'),
(139, 8, '2026-09-14 19:00:00'),
(140, 9, '2026-09-04 19:00:00'),
(141, 9, '2026-09-08 19:00:00'),
(142, 9, '2026-09-11 19:00:00'),
(143, 9, '2026-09-15 19:00:00');

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
(3, 'Jola Wilk', 'wataha_joli@mail.pl', '$2y$10$Xx3gwBPU9ZAXk96oZO.cpe7eU1eYxF5pgJpg9kdRO12V.AcHIu2Ue', 'admin'),
(4, 'Jan Kowalski', 'jankowalski@mail.pl', '$2y$10$Xg1lKSE99KakDz7Z9Cjj/esPsAe3VqMRyQ9ASSKzK63P5wtXKmpbS', 'klient'),
(5, 'Mikuś', 'mnikiel@gmail.com', '$2y$10$TNo4uOr/2WixK2.u9SeJ7u6KjN1mKrdRbUbyNNQT/YH98ELI5iQX2', 'admin');

--
-- Indeksy dla zrzutów tabel
--

--
-- Indeksy dla tabeli `aktorzy`
--
ALTER TABLE `aktorzy`
  ADD PRIMARY KEY (`id`);

--
-- Indeksy dla tabeli `miejsca`
--
ALTER TABLE `miejsca`
  ADD PRIMARY KEY (`id`);

--
-- Indeksy dla tabeli `obsada`
--
ALTER TABLE `obsada`
  ADD PRIMARY KEY (`id`),
  ADD KEY `spektakl_id` (`spektakl_id`),
  ADD KEY `aktor_id` (`aktor_id`);

--
-- Indeksy dla tabeli `rezerwacje`
--
ALTER TABLE `rezerwacje`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unikalne_miejsce_w_terminie` (`termin_id`,`miejsce_id`),
  ADD KEY `uzytkownik_id` (`uzytkownik_id`),
  ADD KEY `miejsce_id` (`miejsce_id`);

--
-- Indeksy dla tabeli `spektakle`
--
ALTER TABLE `spektakle`
  ADD PRIMARY KEY (`id`);

--
-- Indeksy dla tabeli `terminy`
--
ALTER TABLE `terminy`
  ADD PRIMARY KEY (`id`),
  ADD KEY `spektakl_id` (`spektakl_id`);

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
-- AUTO_INCREMENT for table `aktorzy`
--
ALTER TABLE `aktorzy`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `miejsca`
--
ALTER TABLE `miejsca`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=97;

--
-- AUTO_INCREMENT for table `obsada`
--
ALTER TABLE `obsada`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rezerwacje`
--
ALTER TABLE `rezerwacje`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `spektakle`
--
ALTER TABLE `spektakle`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `terminy`
--
ALTER TABLE `terminy`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=144;

--
-- AUTO_INCREMENT for table `uzytkownicy`
--
ALTER TABLE `uzytkownicy`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `obsada`
--
ALTER TABLE `obsada`
  ADD CONSTRAINT `obsada_ibfk_1` FOREIGN KEY (`spektakl_id`) REFERENCES `spektakle` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `obsada_ibfk_2` FOREIGN KEY (`aktor_id`) REFERENCES `aktorzy` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `rezerwacje`
--
ALTER TABLE `rezerwacje`
  ADD CONSTRAINT `rezerwacje_ibfk_1` FOREIGN KEY (`uzytkownik_id`) REFERENCES `uzytkownicy` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rezerwacje_ibfk_2` FOREIGN KEY (`termin_id`) REFERENCES `terminy` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rezerwacje_ibfk_3` FOREIGN KEY (`miejsce_id`) REFERENCES `miejsca` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `terminy`
--
ALTER TABLE `terminy`
  ADD CONSTRAINT `terminy_ibfk_1` FOREIGN KEY (`spektakl_id`) REFERENCES `spektakle` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

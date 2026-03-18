-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 17, 2026 at 12:58 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `tournament_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `matches`
--

CREATE TABLE `matches` (
  `id` int(11) NOT NULL,
  `tournament_id` int(11) NOT NULL,
  `team1_id` int(11) NOT NULL,
  `team2_id` int(11) NOT NULL,
  `match_date` date NOT NULL,
  `status` enum('scheduled','completed') DEFAULT 'scheduled',
  `winner_team_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ;

--
-- Dumping data for table `matches`
--

INSERT INTO `matches` (`id`, `tournament_id`, `team1_id`, `team2_id`, `match_date`, `status`, `winner_team_id`, `created_at`) VALUES
(2, 9, 6, 7, '2026-03-12', 'completed', 6, '2026-03-12 10:53:23'),
(4, 10, 10, 11, '2026-04-06', 'completed', NULL, '2026-03-12 10:54:44'),
(5, 9, 8, 7, '2026-03-13', 'scheduled', NULL, '2026-03-12 10:59:50'),
(6, 9, 6, 5, '2026-03-13', 'scheduled', NULL, '2026-03-12 11:19:39');

-- --------------------------------------------------------

--
-- Table structure for table `match_scores`
--

CREATE TABLE `match_scores` (
  `id` int(11) NOT NULL,
  `match_id` int(11) NOT NULL,
  `team1_score` int(11) NOT NULL,
  `team2_score` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ;

--
-- Dumping data for table `match_scores`
--

INSERT INTO `match_scores` (`id`, `match_id`, `team1_score`, `team2_score`, `created_at`) VALUES
(1, 4, 2, 2, '2026-03-16 05:01:27'),
(2, 2, 7, 0, '2026-03-16 05:01:53');

-- --------------------------------------------------------

--
-- Table structure for table `players`
--

CREATE TABLE `players` (
  `id` int(11) NOT NULL,
  `team_id` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `players`
--

INSERT INTO `players` (`id`, `team_id`, `user_id`, `name`, `created_at`) VALUES
(1, NULL, 5, 'john2', '2026-03-11 05:09:12'),
(3, 6, 7, 'Jane Smith', '2026-03-11 06:06:06'),
(4, NULL, 8, 'Mike Johnson', '2026-03-11 06:06:06'),
(5, 5, 9, 'Alice Brown', '2026-03-11 06:06:06'),
(6, NULL, 10, 'Bob Wilson', '2026-03-11 06:06:06'),
(12, 6, 16, 'Charlie Davis', '2026-03-11 06:11:38'),
(13, 7, 17, 'Diana Prince', '2026-03-11 06:11:38'),
(14, 8, 18, 'Ethan Hunt', '2026-03-11 06:11:38'),
(15, 10, 19, 'Fiona Gallagher', '2026-03-11 06:11:38'),
(16, 11, 20, 'George Miller', '2026-03-11 06:11:38'),
(17, 5, 21, 'Hannah Abbott', '2026-03-11 06:11:38'),
(18, 6, 22, 'Ian Wright', '2026-03-11 06:11:38'),
(19, 7, 23, 'Jack Sparrow', '2026-03-11 06:11:38'),
(20, 8, 24, 'Kelly Kapoor', '2026-03-11 06:11:38'),
(21, NULL, 25, 'Liam Neeson', '2026-03-11 06:11:38'),
(22, 10, 26, 'Mia Wallace', '2026-03-11 06:11:38'),
(23, 11, 27, 'Noah Centineo', '2026-03-11 06:11:38'),
(24, 5, 28, 'Olivia Pope', '2026-03-11 06:11:38'),
(25, 6, 29, 'Peter Parker', '2026-03-11 06:11:38'),
(26, 7, 30, 'Quentin Tarantino', '2026-03-11 06:11:38'),
(27, 8, 31, 'Riley Reid', '2026-03-11 06:11:38'),
(28, NULL, 32, 'Sam Winchester', '2026-03-11 06:11:38'),
(29, 10, 33, 'Tara Knowles', '2026-03-11 06:11:38'),
(30, 11, 34, 'Ursula Corbero', '2026-03-11 06:11:38'),
(31, 5, 35, 'Victor Stone', '2026-03-11 06:11:38'),
(32, 6, 36, 'Wanda Maximoff', '2026-03-11 06:11:38'),
(33, 7, 37, 'Xavier Renegade', '2026-03-11 06:11:38'),
(34, 8, 38, 'Yara Greyjoy', '2026-03-11 06:11:38'),
(36, 10, 40, 'Arthur Morgan', '2026-03-11 06:11:38'),
(37, 11, 41, 'Bill Williamson', '2026-03-11 06:11:38'),
(38, 5, 42, 'Charles Smith', '2026-03-11 06:11:38'),
(39, 6, 43, 'Dutch van der Linde', '2026-03-11 06:11:38'),
(40, 7, 44, 'Edith Downes', '2026-03-11 06:11:38'),
(41, 8, 45, 'Flaco Hernandez', '2026-03-11 06:11:38'),
(42, NULL, 46, 'Gavin Reed', '2026-03-11 06:11:38'),
(43, 10, 47, 'Hosea Matthews', '2026-03-11 06:11:38'),
(44, 11, 48, 'Isaac Nether', '2026-03-11 06:11:38'),
(45, 5, 49, 'Josiah Trelawny', '2026-03-11 06:11:38'),
(46, 6, 50, 'Karen Jones', '2026-03-11 06:11:38');

-- --------------------------------------------------------

--
-- Table structure for table `teams`
--

CREATE TABLE `teams` (
  `id` int(11) NOT NULL,
  `tournament_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `teams`
--

INSERT INTO `teams` (`id`, `tournament_id`, `name`, `created_at`) VALUES
(4, 2, 'vishwakarma', '2026-03-10 08:32:17'),
(5, 9, 'Red Dragons', '2026-03-11 06:06:06'),
(6, 9, 'Blue Eagles', '2026-03-11 06:06:06'),
(7, 9, 'Golden Lions', '2026-03-11 06:06:06'),
(8, 9, 'Silver Sharks', '2026-03-11 06:06:06'),
(10, 10, 'Unity Stars', '2026-03-11 06:06:06'),
(11, 10, 'Wildfire United', '2026-03-11 06:06:06');

-- --------------------------------------------------------

--
-- Table structure for table `tournaments`
--

CREATE TABLE `tournaments` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `type` enum('league','knockout') NOT NULL,
  `status` enum('upcoming','ongoing','completed') DEFAULT 'upcoming',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ;

--
-- Dumping data for table `tournaments`
--

INSERT INTO `tournaments` (`id`, `name`, `start_date`, `end_date`, `type`, `status`, `created_at`) VALUES
(2, 'sdifngoisngsogj', '2026-03-12', '2026-03-19', 'league', 'ongoing', '2026-03-05 12:46:28'),
(3, 'temp', '2026-03-20', '2026-03-31', 'league', 'ongoing', '2026-03-05 13:20:40'),
(8, 'Champions League 2026', '2026-06-01', '2026-07-15', 'knockout', 'upcoming', '2026-03-11 06:06:06'),
(9, 'Winter Premier League', '2026-01-10', '2026-03-20', 'league', 'completed', '2026-03-11 06:06:06'),
(10, 'City Cup', '2026-04-05', '2026-05-10', 'knockout', 'ongoing', '2026-03-11 06:06:06'),
(11, 'Champions League 2026', '2026-06-01', '2026-07-15', 'knockout', 'upcoming', '2026-03-11 06:11:38'),
(12, 'Winter Premier League', '2026-01-10', '2026-03-20', 'league', 'completed', '2026-03-11 06:11:38'),
(13, 'City Cup', '2026-04-05', '2026-05-10', 'knockout', 'ongoing', '2026-03-11 06:11:38');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','player') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'temp', 'temp@gmail.com', '$2y$10$pvDbThvs2MDorWLzmrQep.5b/HbJq/subJpDDE9fd82ujOp1T5ma2', 'admin', '2026-03-05 07:15:32'),
(2, 'player', 'player@gmail.com', '$2y$10$CAep2bvp7.NEd8GQHh2LuOGRIAClSM.mp1l9kEJbSrJIeqUII5S5q', 'player', '2026-03-10 05:24:31'),
(3, 'john', 'john@gmail.com', '$2y$10$HuJVJ0689EHAf8T.cBQpsO1IYGDrRlinYpx5ELHvS4kuU/mBQhxRS', 'admin', '2026-03-11 04:57:32'),
(5, 'john2', 'john2@gmail.com', '$2y$10$oNi37OjguriBHs4.pd2bm.FghNDp3K/rvUwYCqj2aqXPug2RIIM1i', 'player', '2026-03-11 05:09:12'),
(7, 'Jane Smith', 'jane.smith@example.com', '$2y$10$T130anvYDF.MLpII/AxPJu9hL.2Bi72bEoVZ52/38KWDYr2XV8LPi', 'player', '2026-03-11 06:06:06'),
(8, 'Mike Johnson', 'mike.j@example.com', '$2y$10$T130anvYDF.MLpII/AxPJu9hL.2Bi72bEoVZ52/38KWDYr2XV8LPi', 'player', '2026-03-11 06:06:06'),
(9, 'Alice Brown', 'alice.b@example.com', '$2y$10$T130anvYDF.MLpII/AxPJu9hL.2Bi72bEoVZ52/38KWDYr2XV8LPi', 'player', '2026-03-11 06:06:06'),
(10, 'Bob Wilson', 'bob.w@example.com', '$2y$10$T130anvYDF.MLpII/AxPJu9hL.2Bi72bEoVZ52/38KWDYr2XV8LPi', 'player', '2026-03-11 06:06:06'),
(16, 'Charlie Davis', 'charlie.d@example.com', '$2y$10$mNkaippP1eacmkRUKT/ZNu4je7QpBh4wVA/aOU7MKDNwi0rqiHRc6', 'player', '2026-03-11 06:11:38'),
(17, 'Diana Prince', 'diana.p@example.com', '$2y$10$mNkaippP1eacmkRUKT/ZNu4je7QpBh4wVA/aOU7MKDNwi0rqiHRc6', 'player', '2026-03-11 06:11:38'),
(18, 'Ethan Hunt', 'ethan.h@example.com', '$2y$10$mNkaippP1eacmkRUKT/ZNu4je7QpBh4wVA/aOU7MKDNwi0rqiHRc6', 'player', '2026-03-11 06:11:38'),
(19, 'Fiona Gallagher', 'fiona.g@example.com', '$2y$10$mNkaippP1eacmkRUKT/ZNu4je7QpBh4wVA/aOU7MKDNwi0rqiHRc6', 'player', '2026-03-11 06:11:38'),
(20, 'George Miller', 'george.m@example.com', '$2y$10$mNkaippP1eacmkRUKT/ZNu4je7QpBh4wVA/aOU7MKDNwi0rqiHRc6', 'player', '2026-03-11 06:11:38'),
(21, 'Hannah Abbott', 'hannah.a@example.com', '$2y$10$mNkaippP1eacmkRUKT/ZNu4je7QpBh4wVA/aOU7MKDNwi0rqiHRc6', 'player', '2026-03-11 06:11:38'),
(22, 'Ian Wright', 'ian.w@example.com', '$2y$10$mNkaippP1eacmkRUKT/ZNu4je7QpBh4wVA/aOU7MKDNwi0rqiHRc6', 'player', '2026-03-11 06:11:38'),
(23, 'Jack Sparrow', 'jack.s@example.com', '$2y$10$mNkaippP1eacmkRUKT/ZNu4je7QpBh4wVA/aOU7MKDNwi0rqiHRc6', 'player', '2026-03-11 06:11:38'),
(24, 'Kelly Kapoor', 'kelly.k@example.com', '$2y$10$mNkaippP1eacmkRUKT/ZNu4je7QpBh4wVA/aOU7MKDNwi0rqiHRc6', 'player', '2026-03-11 06:11:38'),
(25, 'Liam Neeson', 'liam.n@example.com', '$2y$10$mNkaippP1eacmkRUKT/ZNu4je7QpBh4wVA/aOU7MKDNwi0rqiHRc6', 'player', '2026-03-11 06:11:38'),
(26, 'Mia Wallace', 'mia.w@example.com', '$2y$10$mNkaippP1eacmkRUKT/ZNu4je7QpBh4wVA/aOU7MKDNwi0rqiHRc6', 'player', '2026-03-11 06:11:38'),
(27, 'Noah Centineo', 'noah.c@example.com', '$2y$10$mNkaippP1eacmkRUKT/ZNu4je7QpBh4wVA/aOU7MKDNwi0rqiHRc6', 'player', '2026-03-11 06:11:38'),
(28, 'Olivia Pope', 'olivia.p@example.com', '$2y$10$mNkaippP1eacmkRUKT/ZNu4je7QpBh4wVA/aOU7MKDNwi0rqiHRc6', 'player', '2026-03-11 06:11:38'),
(29, 'Peter Parker', 'peter.p@example.com', '$2y$10$mNkaippP1eacmkRUKT/ZNu4je7QpBh4wVA/aOU7MKDNwi0rqiHRc6', 'player', '2026-03-11 06:11:38'),
(30, 'Quentin Tarantino', 'quentin.t@example.com', '$2y$10$mNkaippP1eacmkRUKT/ZNu4je7QpBh4wVA/aOU7MKDNwi0rqiHRc6', 'player', '2026-03-11 06:11:38'),
(31, 'Riley Reid', 'riley.r@example.com', '$2y$10$mNkaippP1eacmkRUKT/ZNu4je7QpBh4wVA/aOU7MKDNwi0rqiHRc6', 'player', '2026-03-11 06:11:38'),
(32, 'Sam Winchester', 'sam.w@example.com', '$2y$10$mNkaippP1eacmkRUKT/ZNu4je7QpBh4wVA/aOU7MKDNwi0rqiHRc6', 'player', '2026-03-11 06:11:38'),
(33, 'Tara Knowles', 'tara.k@example.com', '$2y$10$mNkaippP1eacmkRUKT/ZNu4je7QpBh4wVA/aOU7MKDNwi0rqiHRc6', 'player', '2026-03-11 06:11:38'),
(34, 'Ursula Corbero', 'ursula.c@example.com', '$2y$10$mNkaippP1eacmkRUKT/ZNu4je7QpBh4wVA/aOU7MKDNwi0rqiHRc6', 'player', '2026-03-11 06:11:38'),
(35, 'Victor Stone', 'victor.s@example.com', '$2y$10$mNkaippP1eacmkRUKT/ZNu4je7QpBh4wVA/aOU7MKDNwi0rqiHRc6', 'player', '2026-03-11 06:11:38'),
(36, 'Wanda Maximoff', 'wanda.m@example.com', '$2y$10$mNkaippP1eacmkRUKT/ZNu4je7QpBh4wVA/aOU7MKDNwi0rqiHRc6', 'player', '2026-03-11 06:11:38'),
(37, 'Xavier Renegade', 'xavier.r@example.com', '$2y$10$mNkaippP1eacmkRUKT/ZNu4je7QpBh4wVA/aOU7MKDNwi0rqiHRc6', 'player', '2026-03-11 06:11:38'),
(38, 'Yara Greyjoy', 'yara.g@example.com', '$2y$10$mNkaippP1eacmkRUKT/ZNu4je7QpBh4wVA/aOU7MKDNwi0rqiHRc6', 'player', '2026-03-11 06:11:38'),
(40, 'Arthur Morgan', 'arthur.m@example.com', '$2y$10$mNkaippP1eacmkRUKT/ZNu4je7QpBh4wVA/aOU7MKDNwi0rqiHRc6', 'player', '2026-03-11 06:11:38'),
(41, 'Bill Williamson', 'bill.w@example.com', '$2y$10$mNkaippP1eacmkRUKT/ZNu4je7QpBh4wVA/aOU7MKDNwi0rqiHRc6', 'player', '2026-03-11 06:11:38'),
(42, 'Charles Smith', 'charles.s@example.com', '$2y$10$mNkaippP1eacmkRUKT/ZNu4je7QpBh4wVA/aOU7MKDNwi0rqiHRc6', 'player', '2026-03-11 06:11:38'),
(43, 'Dutch van der Linde', 'dutch.v@example.com', '$2y$10$mNkaippP1eacmkRUKT/ZNu4je7QpBh4wVA/aOU7MKDNwi0rqiHRc6', 'player', '2026-03-11 06:11:38'),
(44, 'Edith Downes', 'edith.d@example.com', '$2y$10$mNkaippP1eacmkRUKT/ZNu4je7QpBh4wVA/aOU7MKDNwi0rqiHRc6', 'player', '2026-03-11 06:11:38'),
(45, 'Flaco Hernandez', 'flaco.h@example.com', '$2y$10$mNkaippP1eacmkRUKT/ZNu4je7QpBh4wVA/aOU7MKDNwi0rqiHRc6', 'player', '2026-03-11 06:11:38'),
(46, 'Gavin Reed', 'gavin.r@example.com', '$2y$10$mNkaippP1eacmkRUKT/ZNu4je7QpBh4wVA/aOU7MKDNwi0rqiHRc6', 'player', '2026-03-11 06:11:38'),
(47, 'Hosea Matthews', 'hosea.m@example.com', '$2y$10$mNkaippP1eacmkRUKT/ZNu4je7QpBh4wVA/aOU7MKDNwi0rqiHRc6', 'player', '2026-03-11 06:11:38'),
(48, 'Isaac Nether', 'isaac.n@example.com', '$2y$10$mNkaippP1eacmkRUKT/ZNu4je7QpBh4wVA/aOU7MKDNwi0rqiHRc6', 'player', '2026-03-11 06:11:38'),
(49, 'Josiah Trelawny', 'josiah.t@example.com', '$2y$10$mNkaippP1eacmkRUKT/ZNu4je7QpBh4wVA/aOU7MKDNwi0rqiHRc6', 'player', '2026-03-11 06:11:38'),
(50, 'Karen Jones', 'karen.j@example.com', '$2y$10$mNkaippP1eacmkRUKT/ZNu4je7QpBh4wVA/aOU7MKDNwi0rqiHRc6', 'player', '2026-03-11 06:11:38');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `matches`
--
ALTER TABLE `matches`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tournament_id` (`tournament_id`,`team1_id`,`team2_id`),
  ADD KEY `fk_match_team1` (`team1_id`),
  ADD KEY `fk_match_team2` (`team2_id`),
  ADD KEY `fk_match_winner` (`winner_team_id`);

--
-- Indexes for table `match_scores`
--
ALTER TABLE `match_scores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `match_id` (`match_id`);

--
-- Indexes for table `players`
--
ALTER TABLE `players`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `fk_player_team` (`team_id`);

--
-- Indexes for table `teams`
--
ALTER TABLE `teams`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tournament_id` (`tournament_id`,`name`);

--
-- Indexes for table `tournaments`
--
ALTER TABLE `tournaments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `matches`
--
ALTER TABLE `matches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `match_scores`
--
ALTER TABLE `match_scores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `players`
--
ALTER TABLE `players`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `teams`
--
ALTER TABLE `teams`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `tournaments`
--
ALTER TABLE `tournaments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `matches`
--
ALTER TABLE `matches`
  ADD CONSTRAINT `fk_match_team1` FOREIGN KEY (`team1_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_match_team2` FOREIGN KEY (`team2_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_match_tournament` FOREIGN KEY (`tournament_id`) REFERENCES `tournaments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_match_winner` FOREIGN KEY (`winner_team_id`) REFERENCES `teams` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `match_scores`
--
ALTER TABLE `match_scores`
  ADD CONSTRAINT `fk_score_match` FOREIGN KEY (`match_id`) REFERENCES `matches` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `players`
--
ALTER TABLE `players`
  ADD CONSTRAINT `fk_player_team` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_player_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `teams`
--
ALTER TABLE `teams`
  ADD CONSTRAINT `fk_team_tournament` FOREIGN KEY (`tournament_id`) REFERENCES `tournaments` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

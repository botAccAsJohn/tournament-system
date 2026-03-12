<?php
set_exception_handler(function ($e) {
    echo "Unexpected error occurred. " . $e->getMessage();
});

include_once("dbConnection.php");
// the nge the name of the db in dbConnection.php
$conn = dbConnection();

// there we create the all the tables required to run the applications.
$sql = "CREATE DATABASE tournament_system
        CHARACTER SET utf8mb4
        COLLATE utf8mb4_unicode_ci;
        USE tournament_system;
        
        CREATE TABLE users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(150) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            role ENUM('admin','player') NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
        
        CREATE TABLE tournaments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(150) NOT NULL,
            start_date DATE NOT NULL,
            end_date DATE NOT NULL,
            type ENUM('league','knockout') NOT NULL,
            status ENUM('upcoming','ongoing','completed') DEFAULT 'upcoming',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CHECK (end_date >= start_date)
        );
        
        CREATE TABLE teams (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tournament_id INT NOT NULL,
            name VARCHAR(150) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

            CONSTRAINT fk_team_tournament
            FOREIGN KEY (tournament_id)
            REFERENCES tournaments(id)
            ON DELETE CASCADE,
            UNIQUE (tournament_id, name)
        );
        
        CREATE TABLE players (
            id INT AUTO_INCREMENT PRIMARY KEY,
            team_id INT  NULL,
            user_id INT UNIQUE NOT NULL,
            name VARCHAR(100) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

            CONSTRAINT fk_player_team
            FOREIGN KEY (team_id)
            REFERENCES teams(id)
            ON DELETE SET NULL,
            CONSTRAINT fk_player_user
            FOREIGN KEY (user_id)
            REFERENCES users(id)
            ON DELETE CASCADE,
        );
        
        CREATE TABLE matches (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tournament_id INT NOT NULL,
            team1_id INT NOT NULL,
            team2_id INT NOT NULL,
            match_date DATE NOT NULL,
            status ENUM('scheduled','completed') DEFAULT 'scheduled',
            winner_team_id INT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

            CONSTRAINT fk_match_tournament
            FOREIGN KEY (tournament_id)
            REFERENCES tournaments(id)
            ON DELETE CASCADE,
            CONSTRAINT fk_match_team1
            FOREIGN KEY (team1_id)
            REFERENCES teams(id)
            ON DELETE CASCADE,
            CONSTRAINT fk_match_team2
            FOREIGN KEY (team2_id)
            REFERENCES teams(id)
            ON DELETE CASCADE,
            CONSTRAINT fk_match_winner
            FOREIGN KEY (winner_team_id)
            REFERENCES teams(id)
            ON DELETE SET NULL,

            CHECK (team1_id <> team2_id),
            UNIQUE (tournament_id, team1_id, team2_id),
            UNIQUE (tournament_id, team2_id, team1_id)
        );

        CREATE TABLE match_scores (
            id INT AUTO_INCREMENT PRIMARY KEY,
            match_id INT NOT NULL UNIQUE,
            team1_score INT NOT NULL,
            team2_score INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

            CONSTRAINT fk_score_match
            FOREIGN KEY (match_id)
            REFERENCES matches(id)
            ON DELETE CASCADE,
            CHECK (team1_score >= 0),
            CHECK (team2_score >= 0)
        );";

$conn->exec($sql);
// after the run of this query change the name of the db in dbConnection.php

echo "Database created successfully";

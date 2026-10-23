-- schema.sql
-- Run this once against your MySQL server to create the database and users table.

CREATE DATABASE IF NOT EXISTS authentication CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE authentication;

CREATE TABLE IF NOT EXISTS users (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username   VARCHAR(50)  NOT NULL UNIQUE,
    email      VARCHAR(255) NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Note: extended profile fields (name, age, bio, interests) live in MongoDB,
-- in a "profiles" collection, one document per user, linked by user_id.

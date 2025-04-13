CREATE DATABASE IF NOT EXISTS webproject
CHARACTER SET utf8mb4
COLLATE utf8mb4_general_ci;

USE webproject;

CREATE TABLE users (
    id INT(11) NOT NULL AUTO_INCREMENT,
    username VARCHAR(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
    email VARCHAR(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
    password VARCHAR(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
);

CREATE TABLE reset_tokens (
    id INT(11) NOT NULL AUTO_INCREMENT,
    uid INT(11) NOT NULL,
    token VARCHAR(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
    expiry_time DATETIME NOT NULL,
    PRIMARY KEY (id)
);

ALTER TABLE reset_tokens
ADD CONSTRAINT fk_reset_user
FOREIGN KEY (uid) REFERENCES users(id)
ON DELETE CASCADE;
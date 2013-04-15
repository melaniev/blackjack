
/* Create the database */

CREATE DATABASE `swe_blackjack_db` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;

/* Create the Tables */

/* User Table */
CREATE TABLE swe_blackjack_db.users(
	userID  INT AUTO_INCREMENT UNIQUE,
	username VARCHAR(150) NOT NULL,
	blackj_pass VARCHAR(150),
	PRIMARY KEY (userID));
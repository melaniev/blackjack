
/* Create the database */

CREATE DATABASE `swe_blackjack_db` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;

/* Create the Tables */

/* User Table */
CREATE TABLE swe_blackjack_db.users(
	userID  INT AUTO_INCREMENT UNIQUE,
	sessID VARCHAR(250),
	username VARCHAR(150) NOT NULL,
	blackj_pass VARCHAR(150),
	PRIMARY KEY (userID));

/* Game Table */
CREATE TABLE swe_blackjack_db.games(
	gameID  INT AUTO_INCREMENT UNIQUE,
	gameState BOOLEAN NOT NULL default 0,
	playerCount INT,
	PRIMARY KEY (gameID));

/* Moves Table */
CREATE TABLE swe_blackjack_db.moves(
	moveID	INT AUTO_INCREMENT UNIQUE,
	gameID  INT,
	userID  INT,
	playtype VARCHAR(20),
	card VARCHAR(20),
	newTotal  INT,
	PRIMARY KEY (moveID),
	FOREIGN KEY (userID) REFERENCES users(userID),
	FOREIGN KEY (gameID) REFERENCES games(gameID));

/* Players Table */
CREATE TABLE swe_blackjack_db.gameplayers(
	gameID  INT,
	userID  INT,
	PRIMARY KEY (gameID, userID),
	FOREIGN KEY (userID) REFERENCES users(userID),
	FOREIGN KEY (gameID) REFERENCES games(gameID));

/* Deck */
CREATE TABLE swe_blackjack_db.hand(
	gameID  INT,
	userID  INT,
	card VARCHAR(20),
	FOREIGN KEY (userID) REFERENCES users(userID),
	FOREIGN KEY (gameID) REFERENCES games(gameID));

/* Dealer */
CREATE TABLE swe_blackjack_db.dealer(
	gameID  INT,
	card VARCHAR(20),
	FOREIGN KEY (gameID) REFERENCES games(gameID));

/* Dealer Moves*/
CREATE TABLE swe_blackjack_db.dealerMoves(
	moveID	INT AUTO_INCREMENT UNIQUE,
	gameID  INT,
	playtype VARCHAR(20),
	card VARCHAR(20),
	newTotal  INT,
	FOREIGN KEY (gameID) REFERENCES games(gameID));

/* Records */
CREATE TABLE swe_blackjack_db.records(
	userID  INT,
	wins INT,
	losses INT,
	draws INT,
	FOREIGN KEY (userID) REFERENCES users(userID));

/* Turn Table */
CREATE TABLE swe_blackjack_db.turns(
	handID	INT AUTO_INCREMENT UNIQUE,
	gameID  INT,
	userID  INT,
	turnOver INT,
	FOREIGN KEY (userID) REFERENCES users(userID),
	FOREIGN KEY (gameID) REFERENCES games(gameID));


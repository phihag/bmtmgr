DROP TABLE IF EXISTS db_version;
CREATE TABLE db_version (version INT);
INSERT INTO db_version (version) VALUES (9);

DROP TABLE IF EXISTS user;
CREATE TABLE user (
		id TEXT(50) PRIMARY KEY,
		email TEXT UNIQUE,
		name TEXT,
		permissions_json TEXT
	);
CREATE INDEX IF NOT EXISTS user_email_index ON user(email);
INSERT INTO user
	(id, email, name, permissions_json) VALUES
	('admin', 'turniere@aufschlagwechsel.de', 'aufschlagwechsel.de', '["admin"]');

DROP TABLE IF EXISTS login_email_token;
CREATE TABLE login_email_token (
		token TEXT PRIMARY_KEY,
		user_id TEXT,
		request_time BIGINT,
		expiry_time BIGINT,
		metadata_json TEXT,
		FOREIGN KEY(user_id) REFERENCES user(id)
	);
DROP TABLE IF EXISTS login_cookie_token;
CREATE TABLE login_cookie_token (
		token TEXT PRIMARY_KEY,
		user_id TEXT,
		request_time BIGINT,
		expiry_time BIGINT,
		metadata_json TEXT,
		FOREIGN KEY(user_id) REFERENCES user(id)
);

DROP TABLE IF EXISTS season;
CREATE TABLE season (
	id INT PRIMARY KEY,
	name TEXT,
	visible INTEGER(1)
);

DROP TABLE IF EXISTS player;
CREATE TABLE player (
	id INT PRIMARY KEY,
	season_id INT,
	user_id INT,
	textid TEXT,
	name TEXT,
	FOREIGN KEY(season_id) REFERENCES season(id),
	FOREIGN KEY(user_id) REFERENCES user(id)
);
CREATE INDEX IF NOT EXISTS player_textid_index ON player(textid);

DROP TABLE IF EXISTS tournament;
CREATE TABLE tournament (
	id INT PRIMARY KEY,
	season_id INT,
	name TEXT,
	FOREIGN KEY(season_id) REFERENCES season(id)
);


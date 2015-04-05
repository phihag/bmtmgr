DROP TABLE IF EXISTS db_version;
CREATE TABLE db_version (version INTEGER);
INSERT INTO db_version (version) VALUES (35);

DROP TABLE IF EXISTS user;
CREATE TABLE user (
		id TEXT(50) PRIMARY KEY NOT NULL,
		email TEXT,
		name TEXT UNIQUE NOT NULL,
		permissions_json TEXT NOT NULL
	);
CREATE INDEX IF NOT EXISTS user_email_index ON user(email);
INSERT INTO user
	(id, email, name, permissions_json) VALUES
	('admin', 'turniere@aufschlagwechsel.de', 'aufschlagwechsel.de', '["admin"]');
INSERT INTO user
	(id, email, name, permissions_json) VALUES
	('hobby', 'bmtmgr_hobby@aufschlagwechsel.de', 'Hobbyspieler/innen', '[]');

DROP TABLE IF EXISTS login_email_token;
CREATE TABLE login_email_token (
		token TEXT PRIMARY KEY,
		user_id TEXT,
		request_time BIGINT,
		expiry_time BIGINT,
		metadata_json TEXT,
		FOREIGN KEY(user_id) REFERENCES user(id)
	);
DROP TABLE IF EXISTS login_cookie_token;
CREATE TABLE login_cookie_token (
		token TEXT PRIMARY KEY,
		user_id TEXT,
		request_time BIGINT,
		expiry_time BIGINT,
		metadata_json TEXT,
		FOREIGN KEY(user_id) REFERENCES user(id)
);

DROP TABLE IF EXISTS season;
CREATE TABLE season (
	id INTEGER PRIMARY KEY,
	name TEXT UNIQUE NOT NULL,
	visible INTEGER(1) NOT NULL
);
CREATE INDEX IF NOT EXISTS season_name_index ON season(name);

DROP TABLE IF EXISTS player;
CREATE TABLE player (
	id INTEGER PRIMARY KEY,
	season_id INTEGER NOT NULL,
	club_id INTEGER NOT NULL,
	textid TEXT NOT NULL,
	name TEXT NOT NULL,
	gender TEXT(1) NOT NULL,
	birth_year INTEGER,
	nationality TEXT,
	FOREIGN KEY(season_id) REFERENCES season(id),
	FOREIGN KEY(club_id) REFERENCES user(id),
	UNIQUE (season_id, textid)
);
CREATE INDEX IF NOT EXISTS player_textid_index ON player(textid);

DROP TABLE IF EXISTS tournament;
CREATE TABLE tournament (
	id INTEGER PRIMARY KEY,
	season_id INTEGER,
	name TEXT NOT NULL,
	description TEXT,
	FOREIGN KEY(season_id) REFERENCES season(id)
);

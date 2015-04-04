DROP TABLE IF EXISTS db_version;
CREATE TABLE db_version (version INTEGER);
INSERT INTO db_version (version) VALUES (22);

DROP TABLE IF EXISTS user;
CREATE TABLE user (
		id TEXT(50) PRIMARY KEY,
		email TEXT UNIQUE NOT NULL,
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
CREATE INDEX IF NOT EXISTS season_name_index ON player(textid);

DROP TABLE IF EXISTS player;
CREATE TABLE player (
	id INTEGER PRIMARY KEY,
	season_id INTEGER,
	user_id INTEGER,
	textid TEXT,
	name TEXT,
	FOREIGN KEY(season_id) REFERENCES season(id),
	FOREIGN KEY(user_id) REFERENCES user(id)
);
CREATE INDEX IF NOT EXISTS player_textid_index ON player(textid);

DROP TABLE IF EXISTS tournament;
CREATE TABLE tournament (
	id INTEGER PRIMARY KEY,
	season_id INTEGER,
	name TEXT,
	FOREIGN KEY(season_id) REFERENCES season(id)
);


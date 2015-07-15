DROP TABLE IF EXISTS db_version;
CREATE TABLE db_version (version INTEGER);
INSERT INTO db_version (version) VALUES (51);

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
	('hobby', 'bmtmgr_hobby@aufschlagwechsel.de', 'Hobby', '[]');

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
	visible INTEGER(1) NOT NULL,
	baseurl TEXT
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
	email TEXT,
	phone TEXT,
	league TEXT,
	winrate REAL,
	FOREIGN KEY(season_id) REFERENCES season(id),
	FOREIGN KEY(club_id) REFERENCES user(id),
	UNIQUE (season_id, textid)
);
CREATE INDEX IF NOT EXISTS player_textid_index ON player(textid);

DROP TABLE IF EXISTS tournament;
CREATE TABLE tournament (
	id INTEGER PRIMARY KEY,
	season_id INTEGER NOT NULL,
	name TEXT UNIQUE NOT NULL,
	description TEXT,
	start_time BIGINT,
	end_time BIGINT,
	visible INTEGER(1),
	FOREIGN KEY(season_id) REFERENCES season(id)
);
CREATE INDEX IF NOT EXISTS tournament_name_index ON tournament(name);

DROP TABLE IF EXISTS discipline;
CREATE TABLE discipline (
	id INTEGER PRIMARY KEY,
	tournament_id INTEGER NOT NULL,
	name TEXT NOT NULL,
	dtype TEXT(2) NOT NULL,
	ages TEXT,
	leagues TEXT,
	capacity INTEGER,
	note TEXT,
	FOREIGN KEY(tournament_id) REFERENCES tournament(id),
	UNIQUE (tournament_id, name)
);

DROP TABLE IF EXISTS entry;
CREATE TABLE entry (
	id INTEGER PRIMARY KEY,
	discipline_id INTEGER NOT NULL,
	player_id INTEGER NOT NULL,
	player_club_id INTEGER NOT NULL,
	partner_id INTEGER,
	partner_club_id INTEGER,
	email TEXT,
	created_time BIGINT,
	updated_time BIGINT,
	seeding INTEGER,
	position BIGINT,
	FOREIGN KEY(discipline_id) REFERENCES discipline(id),
	FOREIGN KEY(player_id) REFERENCES player(id),
	FOREIGN KEY(player_club_id) REFERENCES user(id),
	FOREIGN KEY(partner_id) REFERENCES player(id),
	FOREIGN KEY(partner_club_id) REFERENCES user(id),
	UNIQUE (discipline_id, player_id)
);
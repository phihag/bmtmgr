DROP TABLE IF EXISTS db_version;
CREATE TABLE db_version (version INT);
INSERT INTO db_version (version) VALUES (7);

DROP TABLE IF EXISTS USER;
CREATE TABLE user (
		id TEXT(50) PRIMARY KEY,
		email TEXT UNIQUE,
		name TEXT,
		permissions_json TEXT
	);
CREATE INDEX IF NOT EXISTS email_index ON user(email);
INSERT INTO user (id, email, name) VALUES ('admin', 'turniere@aufschlagwechsel.de', 'aufschlagwechsel.de');

DROP TABLE IF EXISTS login_email_token;
CREATE TABLE login_email_token (
		token TEXT PRIMARY_KEY,
		user_id TEXT,
		request_time BIGINT,
		expiry_time BIGINT,
		metadata_json TEXT,
		FOREIGN KEY(user_id) REFERENCES user(id)
	);
DROP TABLE IF EXISTS login_user_token;
CREATE TABLE login_user_token (
		token TEXT PRIMARY_KEY,
		user_id TEXT,
		request_time BIGINT,
		expiry_time BIGINT,
		metadata_json TEXT,
		FOREIGN KEY(user_id) REFERENCES user(id)
	)


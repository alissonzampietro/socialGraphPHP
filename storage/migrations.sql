CREATE TABLE users (
	id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
	firstname TEXT NOT NULL,
	surname TEXT NOT NULL,
	age INTEGER DEFAULT null,
	gender TEXT
);

CREATE TABLE cities  (
	id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
	name TEXT NOT NULL,
	created_at DATETIME
);

CREATE TABLE connections (
	id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
	user_id INTEGER NOT NULL,
	user_connection_id INTEGER NOT NULL,
	connection_date DATETIME DEFAULT CURRENT_DATE,
	active INTEGER DEFAULT 1
);

CREATE TABLE users_cities (
	id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
	user_id INTEGER NOT NULL,
	city_id INTEGER NOT NULL,
	percentual INTEGER NOT NULL
);


INSERT INTO sqlite_master (type, name, tbl_name, rootpage, sql) VALUES ('table', 'messages', 'messages', 2, 'CREATE TABLE messages (
                    id INTEGER PRIMARY KEY,
                    title TEXT,
                    message TEXT,
                    time INTEGER)');
INSERT INTO sqlite_master (type, name, tbl_name, rootpage, sql) VALUES ('table', 'sqlite_sequence', 'sqlite_sequence', 4, 'CREATE TABLE sqlite_sequence(name,seq)');
INSERT INTO sqlite_master (type, name, tbl_name, rootpage, sql) VALUES ('table', 'settings', 'settings', 3, 'CREATE TABLE settings
(
  id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
  alias VARCHAR NOT NULL,
  value VARCHAR NOT NULL,
  created_time DATETIME NOT NULL
)');
INSERT INTO sqlite_master (type, name, tbl_name, rootpage, sql) VALUES ('table', 'users', 'users', 6, 'CREATE TABLE users
(
  id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
  email VARCHAR NOT NULL,
  password VARCHAR NOT NULL
)');
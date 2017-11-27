INSERT INTO main.sqlite_master (type, name, tbl_name, rootpage, sql) VALUES ('table', 'users', 'users', 2, 'CREATE TABLE users
(
    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    email VARCHAR NOT NULL,
    password VARCHAR NOT NULL
)');
INSERT INTO main.sqlite_master (type, name, tbl_name, rootpage, sql) VALUES ('table', 'sqlite_sequence', 'sqlite_sequence', 3, 'CREATE TABLE sqlite_sequence(name,seq)');

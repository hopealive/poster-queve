DROP TABLE IF EXISTS messages;
CREATE TABLE IF NOT EXISTS settings
(
  id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
  alias VARCHAR NOT NULL,
  value VARCHAR NOT NULL,
  created_time DATETIME NOT NULL
);

CREATE TABLE IF NOT EXISTS users
(
  id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
  email VARCHAR NOT NULL,
  password VARCHAR NOT NULL
);

CREATE TABLE IF NOT EXISTS slider
(
  id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
  position INTEGER NOT NULL,
  src VARCHAR NOT NULL
);

-- ###MYSQL
CREATE TABLE  settings (
id INT NOT NULL AUTO_INCREMENT ,
alias VARCHAR( 255 ) NOT NULL ,
value VARCHAR( 255 ) NOT NULL ,
created_time DATETIME NOT NULL ,
PRIMARY KEY ( id )
) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE  users (
id INT NOT NULL AUTO_INCREMENT ,
email VARCHAR( 255 ) NOT NULL ,
password VARCHAR( 32 ) NOT NULL ,
PRIMARY KEY (  id )
) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE  slider (
id INT NOT NULL AUTO_INCREMENT ,
position INT( 11 ) NOT NULL ,
src VARCHAR( 255 ) NOT NULL ,
PRIMARY KEY ( id )
) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_general_ci;
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

INSERT INTO slider (position, src) VALUES
(1, '1.jpg'),
(1, '2.jpg'),
(1, '3.jpg'),
(1, '4.jpg'),
(1, '5.jpg'),
(1, '6.jpg'),
(1, '7.jpg'),
(1, '8.jpg'),
(1, '9.jpg'),
(1, '10.jpg'),
(1, '11.jpg'),
(1, '12.jpg');


CREATE TABLE orders (
  id int(11) NOT NULL AUTO_INCREMENT,
  origin_id int(11) NOT NULL,
  view_id int(11) NOT NULL,
  status varchar(32) NOT NULL,
  origin_status int(11) NOT NULL,
  last_date datetime NOT NULL,
  last_update_date datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
PRIMARY KEY ( id )
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE orders ADD UNIQUE( `origin_id`);

CREATE TABLE order_history (
  id int(11) NOT NULL AUTO_INCREMENT,
  origin_id int(11) NOT NULL,
  view_id int(11) NOT NULL,
  status varchar(32) NOT NULL,
  origin_status int(11) NOT NULL,
  last_date datetime NOT NULL,
  last_update_date datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
PRIMARY KEY ( id )
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;

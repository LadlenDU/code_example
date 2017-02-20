CREATE DATABASE IF NOT EXISTS `db_name`
  CHARACTER SET utf8;

USE `db_name`;

DROP TABLE IF EXISTS `user`;
DROP TABLE IF EXISTS `country`;
DROP TABLE IF EXISTS `language`;

CREATE TABLE `language`
(
  `code` VARCHAR(40) NOT NULL,
  `name` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`code`),
  KEY (`name`)
)
  ENGINE = InnoDB
  CHARSET = utf8
  COMMENT 'Список доступных языков';

CREATE TABLE `country`
(
  `code`          VARCHAR(2)  NOT NULL,
  `language_code` VARCHAR(40) NOT NULL
  COMMENT 'Язык названия страны',
  `name`          VARCHAR(50) NOT NULL
  COMMENT 'Название страны',
  PRIMARY KEY (`code`, `language_code`),
  FOREIGN KEY (`language_code`) REFERENCES `language` (`code`)
    ON UPDATE CASCADE,
  KEY (`name`)
)
  ENGINE = InnoDB
  CHARSET = utf8
  COMMENT 'Список всех стран';

CREATE TABLE `user`
(
  `id`            INT(11) UNSIGNED        NOT NULL                      AUTO_INCREMENT,
  `login`         VARCHAR(50)             NOT NULL,
  `password_hash` VARCHAR(255)            NOT NULL,
  `first_name`    VARCHAR(100)            NULL,
  `last_name`     VARCHAR(100)            NULL,
  `email`         VARCHAR(150)            NULL,
  `phone_mobile`  VARCHAR(30),
  `birthday`      DATE                    NULL,
  `gender`        ENUM ('MALE', 'FEMALE') NULL                          DEFAULT NULL,
  `country_code`  VARCHAR(2)              NULL,
  `address`       VARCHAR(255)            NULL,
  `session`       TEXT,
  `image`         MEDIUMBLOB              NULL,
  `image_thumb`   MEDIUMBLOB              NULL,
  `created`       TIMESTAMP               NOT NULL                      DEFAULT CURRENT_TIMESTAMP,
  `modified`      TIMESTAMP               NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted`       ENUM ('NO', 'YES')      NOT NULL                      DEFAULT 'NO',
  PRIMARY KEY (`id`),
  UNIQUE (`login`),
  CHECK (`login` > ''),
  FOREIGN KEY (`country_code`) REFERENCES `country` (`code`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
)
  ENGINE = InnoDB
  CHARSET = utf8;


INSERT INTO `language`
SET `code` = 'en_US', `name` = 'English';
INSERT INTO `language`
SET `code` = 'ru_RU', `name` = 'Русский';
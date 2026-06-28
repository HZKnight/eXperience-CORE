-- ------------------------------------------------------
-- Installation instructions for eXperience-CORE 
-- ------------------------------------------------------
-- !. create dB
-- 2. create user and grant privileges
-- 3. substitute "$" in the script with the prefix of your choice 
-- 4. run the script
-- ------------------------------------------------------


-- -----------------------------------------------------
-- Table `logger`
-- -----------------------------------------------------
CREATE TABLE `%_logger` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(250) NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `logger_level`
-- -----------------------------------------------------
CREATE TABLE `$_logger_level` (
  `id` INT(11) NOT NULL,
  `description` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`)
)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Data for table `logger_level`
-- -----------------------------------------------------
INSERT INTO `$_logger_level` (id,description) VALUES
	 (400,'DEBUG'),
	 (401,'INFO'),
	 (402,'NOTICE'),
	 (403,'WARNING'),
	 (404,'ERROR'),
	 (405,'CRITICAL'),
	 (406,'ALERT'),
	 (407,'EMERGENCY');


-- -----------------------------------------------------
-- Table `logger_rows`
-- -----------------------------------------------------
CREATE TABLE `$_logger_rows` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `logger_id` INT(11) NOT NULL,
  `level` INT(11) NOT NULL,
  `message` TEXT NOT NULL,
  `context` TEXT NULL DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `hz_logger_rows_logger_id_fk` (`logger_id` ASC) VISIBLE,
  INDEX `fk_hz_logger_rows_1_idx` (`level` ASC) VISIBLE,
  CONSTRAINT `hz_logger_rows_logger_id_fk`
    FOREIGN KEY (`logger_id`)
    REFERENCES `hzsystem`.`hz_logger` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `hz_logger_rows_level_id_fk`
    FOREIGN KEY (`level`)
    REFERENCES `hzsystem`.`hz_logger_level` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
)
ENGINE = InnoDB;


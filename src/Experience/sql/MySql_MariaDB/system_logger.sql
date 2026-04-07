-- -----------------------------------------------------
-- Table `eXperience`.`hz_system_logs`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `$_system_logs` (
  `idh_post` INT NOT NULL AUTO_INCREMENT,
  `nick` VARCHAR(45) NOT NULL,
  `mail` VARCHAR(100) NULL,
  `nazione` VARCHAR(45) NULL,
  `messaggio` TEXT NOT NULL,
  `data` VARCHAR(45) NULL,
  PRIMARY KEY (`idh_post`))
ENGINE = InnoDB;
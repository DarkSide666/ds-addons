-- -----------------------------------------------------
-- Table `scheduler_task`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `scheduler_task` ;
CREATE  TABLE IF NOT EXISTS `scheduler_task` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `code` VARCHAR(255) NULL ,
  `cron_expr` VARCHAR(32) NULL ,
  `if_missed` ENUM('reschedule','run') NULL ,
  `class` VARCHAR(255) NULL ,
  `action` VARCHAR(255) NULL ,
  `enabled` TINYINT(1) NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;

-- -----------------------------------------------------
-- Table `scheduler_job`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `scheduler_job` ;
CREATE  TABLE IF NOT EXISTS `scheduler_job` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `scheduler_task_id` INT NOT NULL ,
  `created_dts` DATETIME NULL ,
  `scheduled_dts` DATETIME NULL ,
  `executed_dts` DATETIME NULL ,
  `finished_dts` DATETIME NULL ,
  `status` ENUM('pending','missed','running','success','error') NULL ,
  `messages` LONGTEXT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_scheduler_job_scheduler_task1_idx` (`scheduler_task_id` ASC) ,
  CONSTRAINT `fk_scheduler_job_scheduler_task1`
    FOREIGN KEY (`scheduler_task_id` )
    REFERENCES `scheduler_task` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

-- -----------------------------------------------------
-- Table `scheduler_config`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `scheduler_config` ;
CREATE  TABLE IF NOT EXISTS `scheduler_config` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(128) NULL ,
  `value` VARCHAR(255) NULL ,
  `description` TEXT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;

-- -----------------------------------------------------
-- Data for table `scheduler_task`
-- -----------------------------------------------------
-- START TRANSACTION;
-- INSERT INTO `scheduler_task` (`id`, `code`, `cron_expr`, `if_missed`, `class`, `action`, `enabled`) VALUES (1, 'Check providers', '@hourly', 'run', 'Controller_SchedulerJobs', 'check_providers', 1);
-- INSERT INTO `scheduler_task` (`id`, `code`, `cron_expr`, `if_missed`, `class`, `action`, `enabled`) VALUES (2, 'Check proxies', '5/15 * * * *', 'reschedule', 'Controller_SchedulerJobs', 'check_proxies', 1);
-- COMMIT;

-- -----------------------------------------------------
-- Data for table `scheduler_config`
-- -----------------------------------------------------
-- START TRANSACTION;
-- INSERT INTO `scheduler_config` (`id`, `name`, `value`, `description`) VALUES (1, 'max_execution_time', '120', 'If a running job has not finished after this time it will be marked as failed. The task itself will not be killed if it actually might be still running.');
-- COMMIT;

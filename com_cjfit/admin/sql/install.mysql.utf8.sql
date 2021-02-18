CREATE TABLE IF NOT EXISTS `#__cjfit_users` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`handle` VARCHAR(150) NOT NULL DEFAULT '0',
	`fitbit_owner_id` VARCHAR(16) NOT NULL,
	`token` MEDIUMTEXT NOT NULL,
	`height` INT(6) NOT NULL DEFAULT '0',
	`weight` INT(6) NOT NULL DEFAULT '0',
	`average_daily_steps` INT(10) NOT NULL DEFAULT '0',
	`stride_length_running` FLOAT NOT NULL DEFAULT '0',
	`stride_length_walking` FLOAT NOT NULL DEFAULT '0',
	`attribs` MEDIUMTEXT NOT NULL,
	`goals_date` DATE NULL DEFAULT NULL,
	`daily_goals` MEDIUMTEXT NULL,
	`last_fetched` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
	`lifetime_stats` MEDIUMTEXT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__cjfit_daily_activity` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`user_id` INT(11) NOT NULL,
	`activity_date` DATE NOT NULL,
	`activity_type` INT(4) NOT NULL,
	`activity_value` FLOAT NULL DEFAULT '0',
	PRIMARY KEY (`id`),
	UNIQUE INDEX `idx_cjfit_daily_activity_uniq` (`user_id`, `activity_date`, `activity_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__cjfit_leaderboard` (
	`user_id` INT(11) NOT NULL,
	`activity_date` DATE NOT NULL,
	`activity_type` INT(4) NOT NULL,
	`activity_value` FLOAT NOT NULL,
	PRIMARY KEY (`user_id`, `activity_date`, `activity_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__cjfit_notifications` (
	`owner_id` VARCHAR(16) NOT NULL,
	`owner_type` VARCHAR(16) NOT NULL,
	`subscription_id` INT(10) UNSIGNED NOT NULL,
	`update_date` VARCHAR(16) NOT NULL,
	`collection_type` VARCHAR(16) NOT NULL,
	`state` TINYINT(4) NOT NULL DEFAULT '0',
	UNIQUE INDEX `idx_uniq_cjfit_notifications` (`owner_id`, `update_date`, `collection_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__cjfit_email_templates` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`title` VARCHAR(255) NOT NULL,
	`description` MEDIUMTEXT NOT NULL,
	`published` TINYINT(4) NOT NULL DEFAULT '0',
	`email_type` VARCHAR(45) NOT NULL,
	`ordering` INT(11) UNSIGNED NOT NULL DEFAULT '0',
	`created` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
	`created_by` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`checked_out` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
	`access` INT(10) UNSIGNED NOT NULL DEFAULT '1',
	`language` CHAR(7) NOT NULL DEFAULT '*',
	`publish_up` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
	`publish_down` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__cjfit_challenges` (
	`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`title` VARCHAR(255) NOT NULL,
	`description` MEDIUMTEXT NOT NULL,
	`rules` MEDIUMTEXT NOT NULL,
	`points` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`created_by` INT(11) NOT NULL DEFAULT '0',
	`created` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
	`published` TINYINT(4) NOT NULL DEFAULT '0',
	`access` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`attribs` VARCHAR(5120) NOT NULL,
	`checked_out` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
	`publish_up` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
	`publish_down` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
	`language` CHAR(7) NOT NULL COMMENT 'The language code for the challenge',
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__cjfit_goals_achieved` (
	`user_id` INT(11) NULL DEFAULT NULL,
	`goal_date` DATE NULL DEFAULT NULL,
	`goal_type` INT(4) NULL DEFAULT NULL,
	`goal_value` FLOAT NULL DEFAULT NULL,
	`created` DATETIME NULL DEFAULT '0000:00:00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

INSERT INTO `#__cjfit_email_templates` (`id`,`title`,`description`,`published`,`email_type`,`ordering`,`created`,`created_by`,`checked_out`,`checked_out_time`,`access`,`language`,`publish_up`,`publish_down`) VALUES 
 (1,'Reached steps goal for {ACTIVITY_DATE}','<div style=\"background-color: #e0e0e0; padding: 10px 20px;\"><div style=\"background-color: #f9f9f9; border-radius: 10px; padding: 5px 10px;\"><p>Hello {AUTHOR_NAME},</p><p>Congratulations. You reached the daily steps goal of <strong>{ACTIVITY_GOAL}</strong> for the date <strong>{ACTIVITY_DATE}.</strong></p><p><a class=\"btn-primary\" style=\"font-family: \'Helvetica Neue\', \'Helvetica\', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 2; color: #fff; text-decoration: none; font-weight: bold; text-align: center; cursor: pointer; display: inline-block; border-radius: 25px; background-color: #348eda; margin: 0 10px 0 0; padding: 0; border-color: #348eda; border-style: solid; border-width: 5px 10px;\" href=\"{DASHBOARD_URL}\">  View Your Dashboard  </a></p><div> </div><div>Have fun interacting with friends and other members on our site.</div><div>The {SITENAME} Team<br /><br /></div></div><p>You are receiving this automatic email message because you have a subscription <span style=\"color: #666677; font-size: x-small; background-color: #e0e0e0;\">at {SITENAME}.</span></p></div>',1,'com_cjfit.steps_goal',0,'0000-00-00 00:00:00',0,0,'0000-00-00 00:00:00',1,'*','2017-11-01 00:00:00','0000-00-00 00:00:00'),
 (2,'Reached distance goal for {ACTIVITY_DATE}','<div style=\"background-color: #e0e0e0; padding: 10px 20px;\"><div style=\"background-color: #f9f9f9; border-radius: 10px; padding: 5px 10px;\"><p>Hello {AUTHOR_NAME},</p><p>Congratulations. You reached the daily distance goal of <strong>{ACTIVITY_GOAL}</strong> for the date <strong>{ACTIVITY_DATE}.</strong></p><p><a class=\"btn-primary\" style=\"font-family: \'Helvetica Neue\', \'Helvetica\', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 2; color: #fff; text-decoration: none; font-weight: bold; text-align: center; cursor: pointer; display: inline-block; border-radius: 25px; background-color: #348eda; margin: 0 10px 0 0; padding: 0; border-color: #348eda; border-style: solid; border-width: 5px 10px;\" href=\"{DASHBOARD_URL}\">  View Your Dashboard  </a></p><div> </div><div>Have fun interacting with friends and other members on our site.</div><div>The {SITENAME} Team<br /><br /></div></div><p>You are receiving this automatic email message because you have a subscription <span style=\"color: #666677; font-size: x-small; background-color: #e0e0e0;\">at {SITENAME}.</span></p></div>',1,'com_cjfit.distance_goal',0,'0000-00-00 00:00:00',0,0,'0000-00-00 00:00:00',1,'*','2017-11-01 00:00:00','0000-00-00 00:00:00'),
 (3,'Reached calories goal for {ACTIVITY_DATE}','<div style=\"background-color: #e0e0e0; padding: 10px 20px;\"><div style=\"background-color: #f9f9f9; border-radius: 10px; padding: 5px 10px;\"><p>Hello {AUTHOR_NAME},</p><p>Congratulations. You reached the daily calories goal of <strong>{ACTIVITY_GOAL}</strong> for the date <strong>{ACTIVITY_DATE}.</strong></p><p><a class=\"btn-primary\" style=\"font-family: \'Helvetica Neue\', \'Helvetica\', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 2; color: #fff; text-decoration: none; font-weight: bold; text-align: center; cursor: pointer; display: inline-block; border-radius: 25px; background-color: #348eda; margin: 0 10px 0 0; padding: 0; border-color: #348eda; border-style: solid; border-width: 5px 10px;\" href=\"{DASHBOARD_URL}\">  View Your Dashboard  </a></p><div> </div><div>Have fun interacting with friends and other members on our site.</div><div>The {SITENAME} Team<br /><br /></div></div><p>You are receiving this automatic email message because you have a subscription <span style=\"color: #666677; font-size: x-small; background-color: #e0e0e0;\">at {SITENAME}.</span></p></div>',1,'com_cjfit.calories_goal',0,'0000-00-00 00:00:00',0,0,'0000-00-00 00:00:00',1,'*','2017-11-01 00:00:00','0000-00-00 00:00:00'),
 (4,'Reached active minutes goal for {ACTIVITY_DATE}','<div style=\"background-color: #e0e0e0; padding: 10px 20px;\"><div style=\"background-color: #f9f9f9; border-radius: 10px; padding: 5px 10px;\"><p>Hello {AUTHOR_NAME},</p><p>Congratulations. You reached the active minutes steps goal of <strong>{ACTIVITY_GOAL}</strong> for the date <strong>{ACTIVITY_DATE}.</strong></p><p><a class=\"btn-primary\" style=\"font-family: \'Helvetica Neue\', \'Helvetica\', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 2; color: #fff; text-decoration: none; font-weight: bold; text-align: center; cursor: pointer; display: inline-block; border-radius: 25px; background-color: #348eda; margin: 0 10px 0 0; padding: 0; border-color: #348eda; border-style: solid; border-width: 5px 10px;\" href=\"{DASHBOARD_URL}\">  View Your Dashboard  </a></p><div> </div><div>Have fun interacting with friends and other members on our site.</div><div>The {SITENAME} Team<br /><br /></div></div><p>You are receiving this automatic email message because you have a subscription <span style=\"color: #666677; font-size: x-small; background-color: #e0e0e0;\">at {SITENAME}.</span></p></div>',1,'com_cjfit.active_minutes_goal',0,'0000-00-00 00:00:00',0,0,'0000-00-00 00:00:00',1,'*','2017-11-01 00:00:00','0000-00-00 00:00:00'),
 (5,'You won the challenge {CHALLENGE_TITLE}','<div style=\"background-color: #e0e0e0; padding: 10px 20px;\"><div style=\"background-color: #f9f9f9; border-radius: 10px; padding: 5px 10px;\"><p>Hello {AUTHOR_NAME},</p><p>Congratulations. You won the challenge <strong>{CHALLENGE_TITLE}</strong> for the date <strong>{ACTIVITY_DATE}.</strong></p><p>{CHALLENGE_DESCRIPTION}</p><p><a class=\"btn-primary\" style=\"font-family: \'Helvetica Neue\', \'Helvetica\', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 2; color: #fff; text-decoration: none; font-weight: bold; text-align: center; cursor: pointer; display: inline-block; border-radius: 25px; background-color: #348eda; margin: 0 10px 0 0; padding: 0; border-color: #348eda; border-style: solid; border-width: 5px 10px;\" href=\"{DASHBOARD_URL}\">  View Your Dashboard  </a></p><div> </div><div>Have fun interacting with friends and other members on our site.</div><div>The {SITENAME} Team<br /><br /></div></div><p>You are receiving this automatic email message because you have a subscription <span style=\"color: #666677; font-size: x-small; background-color: #e0e0e0;\">at {SITENAME}.</span></p></div>',1,'com_cjfit.challenge',0,'0000-00-00 00:00:00',0,0,'0000-00-00 00:00:00',1,'*','2017-11-01 00:00:00','0000-00-00 00:00:00')
ON DUPLICATE KEY UPDATE created = VALUES (created);
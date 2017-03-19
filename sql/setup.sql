CREATE TABLE `todo_list` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `task_name` varchar(255) DEFAULT NULL,
  `status` enum('NEW','IN_PROGRESS','DONE') DEFAULT 'NEW',
  `team_id` varchar(50) DEFAULT NULL,
  `team_name` varchar(100) DEFAULT NULL,
  `channel_id` varchar(50) DEFAULT NULL,
  `channel_name` varchar(100) DEFAULT NULL,
  `user_id` varchar(50) DEFAULT NULL,
  `user_name` varchar(100) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `IDX_task` (`team_id`,`channel_id`,`status`,`task_name`)
) ENGINE=InnoDB;


CREATE TABLE `user_assigned_todo` (
  `user_assigned` varchar(60) NOT NULL,
  `todo_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_assigned`,`todo_id`)
) ENGINE=InnoDB;
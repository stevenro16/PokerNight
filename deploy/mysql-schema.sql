-- ============================================================
-- Poker Night — MySQL Production Schema
-- Updated: 2026-06-06
-- Run against an empty MySQL 8+ database.
-- Tables are ordered to satisfy foreign key constraints.
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- --------------------------------------------------------
-- cache  (Laravel cache driver)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `cache` (
  `key`        VARCHAR(255) NOT NULL,
  `value`      MEDIUMTEXT   NOT NULL,
  `expiration` INT          NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key`        VARCHAR(255) NOT NULL,
  `owner`      VARCHAR(255) NOT NULL,
  `expiration` INT          NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- jobs / queue  (Laravel queue driver)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `jobs` (
  `id`           BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `queue`        VARCHAR(255)     NOT NULL,
  `payload`      LONGTEXT         NOT NULL,
  `attempts`     TINYINT UNSIGNED NOT NULL,
  `reserved_at`  INT UNSIGNED     NULL,
  `available_at` INT UNSIGNED     NOT NULL,
  `created_at`   INT UNSIGNED     NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `job_batches` (
  `id`             VARCHAR(255) NOT NULL,
  `name`           VARCHAR(255) NOT NULL,
  `total_jobs`     INT          NOT NULL,
  `pending_jobs`   INT          NOT NULL,
  `failed_jobs`    INT          NOT NULL,
  `failed_job_ids` LONGTEXT     NOT NULL,
  `options`        MEDIUMTEXT   NULL,
  `cancelled_at`   INT          NULL,
  `created_at`     INT          NOT NULL,
  `finished_at`    INT          NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid`       VARCHAR(255)    NOT NULL,
  `connection` TEXT            NOT NULL,
  `queue`      TEXT            NOT NULL,
  `payload`    LONGTEXT        NOT NULL,
  `exception`  LONGTEXT        NOT NULL,
  `failed_at`  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- sessions  (Laravel session driver)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sessions` (
  `id`            VARCHAR(255)    NOT NULL,
  `user_id`       BIGINT UNSIGNED NULL,
  `ip_address`    VARCHAR(45)     NULL,
  `user_agent`    TEXT            NULL,
  `payload`       LONGTEXT        NOT NULL,
  `last_activity` INT             NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- users
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id`             CHAR(32)     NOT NULL,
  `username`       VARCHAR(255) NOT NULL,
  `email`          VARCHAR(255) NOT NULL,
  `password`       VARCHAR(255) NOT NULL,
  `role`           VARCHAR(255) NOT NULL DEFAULT 'USER',
  `isActive`       TINYINT(1)   NOT NULL DEFAULT 1,
  `avatar_url`     VARCHAR(255) NULL,
  `remember_token` VARCHAR(100) NULL,
  `createdAt`      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt`      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_username_unique` (`username`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- poker_groups
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `poker_groups` (
  `id`          CHAR(32)     NOT NULL,
  `name`        VARCHAR(255) NOT NULL,
  `description` TEXT         NULL,
  `owner_id`    CHAR(32)     NOT NULL,
  `invite_code` VARCHAR(10)  NOT NULL,
  `isActive`    TINYINT(1)   NOT NULL DEFAULT 1,
  `createdAt`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `poker_groups_invite_code_unique` (`invite_code`),
  KEY `poker_groups_owner_id_index` (`owner_id`),
  CONSTRAINT `fk_poker_groups_owner_id` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- group_members
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `group_members` (
  `id`        CHAR(32)     NOT NULL,
  `group_id`  CHAR(32)     NOT NULL,
  `user_id`   CHAR(32)     NOT NULL,
  `role`      VARCHAR(255) NOT NULL DEFAULT 'MEMBER',
  `joined_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `group_members_group_id_user_id_unique` (`group_id`, `user_id`),
  KEY `group_members_group_id_index` (`group_id`),
  KEY `group_members_user_id_index` (`user_id`),
  CONSTRAINT `fk_group_members_group_id` FOREIGN KEY (`group_id`) REFERENCES `poker_groups` (`id`),
  CONSTRAINT `fk_group_members_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- group_players  (per-group roster; user_id nullable for players without accounts)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `group_players` (
  `id`           CHAR(32)     NOT NULL,
  `group_id`     CHAR(32)     NOT NULL,
  `user_id`      CHAR(32)     NULL,
  `name`         VARCHAR(100) NOT NULL,
  `nickname`     VARCHAR(50)  NULL,
  `photo_path`   VARCHAR(255) NULL,
  `role`         VARCHAR(255) NOT NULL DEFAULT 'CORE',
  `email`        VARCHAR(255) NULL,
  `invite_token` VARCHAR(40)  NULL,
  `createdAt`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `group_players_invite_token_unique` (`invite_token`),
  KEY `group_players_group_id_index` (`group_id`),
  KEY `group_players_user_id_index` (`user_id`),
  CONSTRAINT `fk_group_players_group_id` FOREIGN KEY (`group_id`) REFERENCES `poker_groups` (`id`),
  CONSTRAINT `fk_group_players_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- poker_nights
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `poker_nights` (
  `id`           CHAR(32)       NOT NULL,
  `group_id`     CHAR(32)       NOT NULL,
  `created_by`   CHAR(32)       NOT NULL,
  `title`        VARCHAR(255)   NOT NULL,
  `notes`        TEXT           NULL,
  `scheduled_at` DATETIME       NOT NULL,
  `played_at`    DATETIME       NULL,
  `status`       VARCHAR(255)   NOT NULL DEFAULT 'SCHEDULED',
  `buy_in`       DECIMAL(8, 2)  NULL,
  `createdAt`    TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt`    TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `poker_nights_group_id_index` (`group_id`),
  KEY `poker_nights_created_by_index` (`created_by`),
  CONSTRAINT `fk_poker_nights_group_id` FOREIGN KEY (`group_id`) REFERENCES `poker_groups` (`id`),
  CONSTRAINT `fk_poker_nights_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- game_attendees  (user_id and group_player_id are both nullable)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `game_attendees` (
  `id`              CHAR(32)         NOT NULL,
  `poker_night_id`  CHAR(32)         NOT NULL,
  `user_id`         CHAR(32)         NULL,
  `group_player_id` CHAR(32)         NULL,
  `placement`       TINYINT UNSIGNED NULL,
  `rsvp`            VARCHAR(20)      NULL,
  `createdAt`       TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `game_attendees_poker_night_id_index` (`poker_night_id`),
  KEY `game_attendees_user_id_index` (`user_id`),
  KEY `game_attendees_group_player_id_index` (`group_player_id`),
  CONSTRAINT `fk_game_attendees_poker_night_id` FOREIGN KEY (`poker_night_id`) REFERENCES `poker_nights` (`id`),
  CONSTRAINT `fk_game_attendees_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_game_attendees_group_player_id` FOREIGN KEY (`group_player_id`) REFERENCES `group_players` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- game_images
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `game_images` (
  `id`             CHAR(32)          NOT NULL,
  `poker_night_id` CHAR(32)          NOT NULL,
  `uploaded_by`    CHAR(32)          NOT NULL,
  `file_path`      VARCHAR(255)      NOT NULL,
  `caption`        VARCHAR(255)      NULL,
  `is_cover`       TINYINT(1)        NOT NULL DEFAULT 0,
  `sort_order`     SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `createdAt`      TIMESTAMP         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `game_images_poker_night_id_index` (`poker_night_id`),
  KEY `game_images_uploaded_by_index` (`uploaded_by`),
  CONSTRAINT `fk_game_images_poker_night_id` FOREIGN KEY (`poker_night_id`) REFERENCES `poker_nights` (`id`),
  CONSTRAINT `fk_game_images_uploaded_by` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- night_comments
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `night_comments` (
  `id`             CHAR(32)  NOT NULL,
  `poker_night_id` CHAR(32)  NOT NULL,
  `user_id`        CHAR(32)  NOT NULL,
  `message`        TEXT      NOT NULL,
  `createdAt`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `night_comments_poker_night_id_index` (`poker_night_id`),
  KEY `night_comments_user_id_index` (`user_id`),
  CONSTRAINT `fk_night_comments_poker_night_id` FOREIGN KEY (`poker_night_id`) REFERENCES `poker_nights` (`id`),
  CONSTRAINT `fk_night_comments_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

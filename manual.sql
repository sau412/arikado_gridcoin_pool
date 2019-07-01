SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE TABLE `attach_projects` (
  `uid` int(11) NOT NULL,
  `project_uid` int(11) NOT NULL,
  `host_uid` int(11) NOT NULL,
  `status` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `resource_share` int(11) NOT NULL DEFAULT '100',
  `options` text COLLATE utf8_unicode_ci,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `billing_periods` (
  `uid` int(11) NOT NULL,
  `comment` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `start_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `stop_date` datetime DEFAULT NULL,
  `reward` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `blocks` (
  `number` int(11) NOT NULL,
  `hash` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `mint` double NOT NULL,
  `cpid` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `interest` double NOT NULL,
  `rewards_sent` tinyint(1) NOT NULL DEFAULT '0',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `cache` (
  `hash` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `content` text COLLATE utf8_unicode_ci NOT NULL,
  `valid_until` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `currency` (
  `uid` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `full_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `payout_limit` double NOT NULL,
  `tx_fee` double NOT NULL,
  `project_fee` double NOT NULL,
  `url_wallet` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `url_tx` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `url_api` text COLLATE utf8_unicode_ci NOT NULL,
  `rate_per_grc` double NOT NULL DEFAULT '1'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `email` (
  `uid` int(11) NOT NULL,
  `to` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `subject` text COLLATE utf8_unicode_ci NOT NULL,
  `message` text COLLATE utf8_unicode_ci NOT NULL,
  `is_sent` int(11) NOT NULL DEFAULT '0',
  `is_success` int(11) NOT NULL DEFAULT '0',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `faucet` (
  `uid` int(11) NOT NULL,
  `user_uid` int(11) NOT NULL,
  `grc_amount` double NOT NULL,
  `date` date NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `faucet_payouts` (
  `uid` int(11) NOT NULL,
  `grc_address` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `amount` double NOT NULL,
  `wallet_send_uid` bigint(20) DEFAULT NULL,
  `txid` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `global_stats` (
  `uid` int(11) NOT NULL,
  `project_uid` int(11) NOT NULL,
  `cpu` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `cores` int(11) NOT NULL,
  `gpu_type` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `gpu` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `expavg_credit` double NOT NULL,
  `p_fpops` bigint(20) NOT NULL,
  `p_iops` bigint(20) NOT NULL,
  `m_nbytes` bigint(20) NOT NULL,
  `os_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `os_version` varchar(100) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `hosts` (
  `uid` int(11) NOT NULL,
  `username_uid` int(11) NOT NULL,
  `internal_host_cpid` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `external_host_cpid` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `domain_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `p_model` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_query` text COLLATE utf8_unicode_ci
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `host_projects` (
  `uid` int(11) NOT NULL,
  `host_uid` int(11) NOT NULL,
  `project_uid` int(11) NOT NULL,
  `host_id` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `log` (
  `uid` int(11) NOT NULL,
  `message` longtext COLLATE utf8_unicode_ci NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `messages` (
  `uid` int(11) NOT NULL,
  `username_uid` int(11) DEFAULT NULL,
  `reply_to` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `is_read` int(11) NOT NULL,
  `message` text COLLATE utf8_unicode_ci NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `payouts` (
  `uid` int(11) NOT NULL,
  `billing_uid` int(11) NOT NULL,
  `user_uid` bigint(20) DEFAULT NULL,
  `grc_amount` double DEFAULT NULL,
  `currency` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'GRC',
  `rate` double NOT NULL DEFAULT '1',
  `payout_address` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `amount` double NOT NULL,
  `wallet_send_uid` bigint(20) DEFAULT NULL,
  `txid` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `projects` (
  `uid` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `superblock_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `project_url` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `url_signature` text COLLATE utf8_unicode_ci NOT NULL,
  `status` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `weak_auth` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `update_weak_auth` tinyint(1) NOT NULL DEFAULT '1',
  `cpid` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `team` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `expavg_credit` double DEFAULT NULL,
  `team_expavg_credit` double DEFAULT NULL,
  `present_in_superblock` tinyint(4) NOT NULL DEFAULT '0',
  `superblock_expavg_credit` double NOT NULL,
  `last_query` longtext COLLATE utf8_unicode_ci NOT NULL,
  `comment` text COLLATE utf8_unicode_ci NOT NULL,
  `gpu_present` int(11) NOT NULL,
  `file` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `project_hosts_last` (
  `uid` int(11) NOT NULL,
  `project_uid` int(11) NOT NULL,
  `host_uid` int(11) NOT NULL,
  `host_id` int(11) NOT NULL,
  `host_cpid` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `domain_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `p_model` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `expavg_credit` double NOT NULL,
  `total_credit` double NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `project_host_stats` (
  `uid` int(11) NOT NULL,
  `project_uid` int(11) NOT NULL,
  `host_uid` int(11) NOT NULL,
  `host_id` int(11) NOT NULL,
  `expavg_credit` double NOT NULL,
  `total_credit` double NOT NULL DEFAULT '0',
  `interval` bigint(20) DEFAULT NULL,
  `magnitude_unit` float DEFAULT NULL,
  `grc_amount` double DEFAULT NULL,
  `exchange_rate` double DEFAULT NULL,
  `currency` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `currency_amount` double DEFAULT NULL,
  `is_payed_out` tinyint(4) DEFAULT NULL,
  `timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `project_stats` (
  `uid` int(11) NOT NULL,
  `project_uid` int(11) NOT NULL,
  `expavg_credit` double NOT NULL,
  `team_expavg_credit` double NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `tasks` (
  `uid` int(11) NOT NULL,
  `project_uid` int(11) NOT NULL,
  `result_name` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `result_id` bigint(20) NOT NULL,
  `workunit_id` bigint(20) NOT NULL,
  `host_id` int(11) NOT NULL,
  `sent` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `deadline` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `status` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `elapsed_time` double NOT NULL,
  `cpu_time` double NOT NULL,
  `score` double NOT NULL,
  `app` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `task_stats` (
  `uid` int(11) NOT NULL,
  `project_uid` int(11) NOT NULL,
  `host_id` int(11) NOT NULL,
  `app` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `status` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `count` int(11) NOT NULL,
  `date` date NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `users` (
  `uid` int(11) NOT NULL,
  `username` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `send_error_reports` int(11) NOT NULL DEFAULT '0',
  `salt` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `passwd_hash` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `balance` double NOT NULL DEFAULT '0',
  `currency` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'GRC',
  `payout_address` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `status` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `token` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `user_auth_cookies` (
  `uid` int(11) NOT NULL,
  `username_uid` int(11) NOT NULL,
  `cookie_token` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `captcha` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `expire_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `variables` (
  `uid` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `value` text COLLATE utf8_unicode_ci NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `xml` (
  `uid` int(11) NOT NULL,
  `type` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `message` text COLLATE utf8_unicode_ci NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `attach_projects`
  ADD PRIMARY KEY (`uid`),
  ADD UNIQUE KEY `project_id` (`project_uid`,`host_uid`);

ALTER TABLE `billing_periods`
  ADD PRIMARY KEY (`uid`),
  ADD KEY `stop_date` (`stop_date`);

ALTER TABLE `blocks`
  ADD PRIMARY KEY (`number`),
  ADD KEY `cpid` (`cpid`);

ALTER TABLE `cache`
  ADD PRIMARY KEY (`hash`);

ALTER TABLE `currency`
  ADD PRIMARY KEY (`uid`),
  ADD UNIQUE KEY `name` (`name`);

ALTER TABLE `email`
  ADD PRIMARY KEY (`uid`);

ALTER TABLE `faucet`
  ADD PRIMARY KEY (`uid`);

ALTER TABLE `faucet_payouts`
  ADD PRIMARY KEY (`uid`);

ALTER TABLE `global_stats`
  ADD PRIMARY KEY (`uid`),
  ADD KEY `cpu` (`cpu`),
  ADD KEY `gpu` (`gpu`),
  ADD KEY `project_uid` (`project_uid`),
  ADD KEY `gpu_type` (`gpu_type`),
  ADD KEY `cores` (`cores`),
  ADD KEY `expavg_credit` (`expavg_credit`);

ALTER TABLE `hosts`
  ADD PRIMARY KEY (`uid`),
  ADD UNIQUE KEY `username` (`username_uid`,`internal_host_cpid`) USING BTREE;

ALTER TABLE `host_projects`
  ADD PRIMARY KEY (`uid`),
  ADD UNIQUE KEY `host_uid` (`host_uid`,`project_uid`,`host_id`);

ALTER TABLE `log`
  ADD PRIMARY KEY (`uid`);

ALTER TABLE `messages`
  ADD PRIMARY KEY (`uid`);

ALTER TABLE `payouts`
  ADD PRIMARY KEY (`uid`),
  ADD KEY `billing_uid` (`billing_uid`),
  ADD KEY `user_uid` (`user_uid`),
  ADD KEY `currency` (`currency`),
  ADD KEY `payout_address` (`payout_address`),
  ADD KEY `wallet_send_uid` (`wallet_send_uid`),
  ADD KEY `txid` (`txid`);

ALTER TABLE `projects`
  ADD PRIMARY KEY (`uid`),
  ADD UNIQUE KEY `name` (`name`);

ALTER TABLE `project_hosts_last`
  ADD PRIMARY KEY (`uid`),
  ADD UNIQUE KEY `project_uid` (`project_uid`,`host_uid`,`host_id`) USING BTREE;

ALTER TABLE `project_host_stats`
  ADD PRIMARY KEY (`uid`),
  ADD KEY `timestamp` (`timestamp`),
  ADD KEY `project_uid` (`project_uid`,`host_uid`,`host_id`),
  ADD KEY `is_payed_out` (`is_payed_out`),
  ADD KEY `interval` (`interval`);

ALTER TABLE `project_stats`
  ADD PRIMARY KEY (`uid`),
  ADD KEY `project_uid` (`project_uid`),
  ADD KEY `timestamp` (`timestamp`);

ALTER TABLE `tasks`
  ADD PRIMARY KEY (`uid`),
  ADD UNIQUE KEY `project_uid` (`project_uid`,`result_id`) USING BTREE,
  ADD KEY `host_id` (`host_id`),
  ADD KEY `status` (`status`),
  ADD KEY `app` (`app`);

ALTER TABLE `task_stats`
  ADD PRIMARY KEY (`uid`),
  ADD UNIQUE KEY `user_uid` (`project_uid`,`status`,`date`,`app`) USING BTREE;

ALTER TABLE `users`
  ADD PRIMARY KEY (`uid`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `token` (`token`);

ALTER TABLE `user_auth_cookies`
  ADD PRIMARY KEY (`uid`),
  ADD UNIQUE KEY `cookie_token` (`cookie_token`);

ALTER TABLE `variables`
  ADD PRIMARY KEY (`uid`),
  ADD UNIQUE KEY `name` (`name`) USING BTREE;

ALTER TABLE `xml`
  ADD PRIMARY KEY (`uid`);


ALTER TABLE `attach_projects`
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `billing_periods`
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `currency`
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `email`
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `faucet`
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `faucet_payouts`
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `global_stats`
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `hosts`
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `host_projects`
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `log`
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `messages`
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `payouts`
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `projects`
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `project_hosts_last`
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `project_host_stats`
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `project_stats`
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `tasks`
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `task_stats`
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `users`
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `user_auth_cookies`
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `variables`
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `xml`
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT;

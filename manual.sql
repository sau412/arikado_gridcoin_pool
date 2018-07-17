SSET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE TABLE `boincmgr_attach_projects` (
  `uid` int(11) NOT NULL,
  `project_uid` int(11) NOT NULL,
  `host_uid` int(11) NOT NULL,
  `status` varchar(100) NOT NULL,
  `resource_share` int(11) NOT NULL DEFAULT '100',
  `options` text,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `boincmgr_billing_periods` (
  `uid` int(11) NOT NULL,
  `start_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `stop_date` datetime DEFAULT NULL,
  `reward` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `boincmgr_blocks` (
  `number` int(11) NOT NULL,
  `hash` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `mint` double NOT NULL,
  `cpid` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `interest` double NOT NULL,
  `rewards_sent` tinyint(1) NOT NULL DEFAULT '0',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `boincmgr_cache` (
  `hash` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `content` text COLLATE utf8_unicode_ci NOT NULL,
  `valid_until` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `boincmgr_currency` (
  `uid` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `full_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `payout_limit` double NOT NULL,
  `tx_fee` double NOT NULL,
  `project_fee` double NOT NULL,
  `url_wallet` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `url_tx` varchar(100) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `boincmgr_hosts` (
  `uid` int(11) NOT NULL,
  `username_uid` int(11) NOT NULL,
  `internal_host_cpid` varchar(100) NOT NULL,
  `external_host_cpid` varchar(100) NOT NULL,
  `domain_name` varchar(100) NOT NULL,
  `p_model` varchar(100) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_query` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `boincmgr_host_projects` (
  `uid` int(11) NOT NULL,
  `host_uid` int(11) NOT NULL,
  `project_uid` int(11) NOT NULL,
  `host_id` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `boincmgr_log` (
  `uid` int(11) NOT NULL,
  `message` longtext NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `boincmgr_messages` (
  `uid` int(11) NOT NULL,
  `username_uid` int(11) NOT NULL,
  `is_read` int(11) NOT NULL,
  `type` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `message` text COLLATE utf8_unicode_ci NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `boincmgr_payouts` (
  `uid` int(11) NOT NULL,
  `billing_uid` int(11) NOT NULL,
  `grc_amount` double DEFAULT NULL,
  `currency` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'GRC',
  `rate` double NOT NULL DEFAULT '1',
  `payout_address` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `amount` double NOT NULL,
  `txid` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `boincmgr_projects` (
  `uid` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `project_url` varchar(100) NOT NULL,
  `url_signature` text NOT NULL,
  `status` varchar(100) NOT NULL,
  `weak_auth` varchar(100) NOT NULL,
  `update_weak_auth` tinyint(1) NOT NULL DEFAULT '1',
  `cpid` varchar(100) NOT NULL,
  `team` varchar(100) NOT NULL,
  `expavg_credit` double DEFAULT NULL,
  `team_expavg_credit` double DEFAULT NULL,
  `last_query` longtext NOT NULL,
  `comment` text NOT NULL,
  `gpu_present` int(11) NOT NULL,
  `file` varchar(100) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `boincmgr_project_hosts_last` (
  `uid` int(11) NOT NULL,
  `project_uid` int(11) NOT NULL,
  `host_uid` int(11) NOT NULL,
  `host_id` int(11) NOT NULL,
  `host_cpid` varchar(100) NOT NULL,
  `domain_name` varchar(100) NOT NULL,
  `p_model` varchar(100) NOT NULL,
  `expavg_credit` double NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `boincmgr_project_host_stats` (
  `uid` int(11) NOT NULL,
  `project_uid` int(11) NOT NULL,
  `host_uid` int(11) NOT NULL,
  `host_id` int(11) NOT NULL,
  `expavg_credit` double NOT NULL,
  `timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `boincmgr_project_stats` (
  `uid` int(11) NOT NULL,
  `project_uid` int(11) NOT NULL,
  `expavg_credit` double NOT NULL,
  `team_expavg_credit` double NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `boincmgr_tasks` (
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

CREATE TABLE `boincmgr_users` (
  `uid` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `salt` varchar(100) DEFAULT NULL,
  `passwd_hash` varchar(100) NOT NULL,
  `currency` varchar(100) NOT NULL DEFAULT 'GRC',
  `payout_address` varchar(100) NOT NULL,
  `status` varchar(100) DEFAULT NULL,
  `token` varchar(100) NOT NULL DEFAULT '',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `boincmgr_user_auth_cookies` (
  `uid` int(11) NOT NULL,
  `username_uid` int(11) NOT NULL,
  `cookie_token` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `expire_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `boincmgr_variables` (
  `uid` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `value` text COLLATE utf8_unicode_ci NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `boincmgr_xml` (
  `uid` int(11) NOT NULL,
  `type` varchar(100) NOT NULL,
  `message` blob NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


ALTER TABLE `boincmgr_attach_projects`
  ADD PRIMARY KEY (`uid`),
  ADD UNIQUE KEY `project_id` (`project_uid`,`host_uid`);

ALTER TABLE `boincmgr_billing_periods`
  ADD PRIMARY KEY (`uid`);

ALTER TABLE `boincmgr_blocks`
  ADD PRIMARY KEY (`number`),
  ADD KEY `cpid` (`cpid`);

ALTER TABLE `boincmgr_cache`
  ADD PRIMARY KEY (`hash`);

ALTER TABLE `boincmgr_currency`
  ADD PRIMARY KEY (`uid`),
  ADD UNIQUE KEY `name` (`name`);

ALTER TABLE `boincmgr_hosts`
  ADD PRIMARY KEY (`uid`),
  ADD UNIQUE KEY `username` (`username_uid`,`internal_host_cpid`) USING BTREE;

ALTER TABLE `boincmgr_host_projects`
  ADD PRIMARY KEY (`uid`),
  ADD UNIQUE KEY `host_uid` (`host_uid`,`project_uid`,`host_id`);

ALTER TABLE `boincmgr_log`
  ADD PRIMARY KEY (`uid`);

ALTER TABLE `boincmgr_messages`
  ADD PRIMARY KEY (`uid`);

ALTER TABLE `boincmgr_payouts`
  ADD PRIMARY KEY (`uid`);

ALTER TABLE `boincmgr_projects`
  ADD PRIMARY KEY (`uid`),
  ADD UNIQUE KEY `name` (`name`);

ALTER TABLE `boincmgr_project_hosts_last`
  ADD PRIMARY KEY (`uid`),
  ADD UNIQUE KEY `project_uid` (`project_uid`,`host_uid`,`host_id`) USING BTREE;

ALTER TABLE `boincmgr_project_host_stats`
  ADD PRIMARY KEY (`uid`),
  ADD KEY `timestamp` (`timestamp`),
  ADD KEY `project_uid` (`project_uid`,`host_uid`,`host_id`);

ALTER TABLE `boincmgr_project_stats`
  ADD PRIMARY KEY (`uid`),
  ADD KEY `project_uid` (`project_uid`),
  ADD KEY `timestamp` (`timestamp`);

ALTER TABLE `boincmgr_tasks`
  ADD PRIMARY KEY (`uid`),
  ADD UNIQUE KEY `project_uid` (`project_uid`,`result_id`) USING BTREE,
  ADD KEY `host_id` (`host_id`),
  ADD KEY `status` (`status`),
  ADD KEY `app` (`app`);

ALTER TABLE `boincmgr_users`
  ADD PRIMARY KEY (`uid`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `token` (`token`);

ALTER TABLE `boincmgr_user_auth_cookies`
  ADD PRIMARY KEY (`uid`),
  ADD UNIQUE KEY `cookie_token` (`cookie_token`);

ALTER TABLE `boincmgr_variables`
  ADD PRIMARY KEY (`uid`),
  ADD UNIQUE KEY `name` (`name`) USING BTREE;

ALTER TABLE `boincmgr_xml`
  ADD PRIMARY KEY (`uid`);


ALTER TABLE `boincmgr_attach_projects`
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `boincmgr_billing_periods`
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `boincmgr_currency`
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `boincmgr_hosts`
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `boincmgr_host_projects`
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `boincmgr_log`
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `boincmgr_messages`
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `boincmgr_payouts`
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `boincmgr_projects`
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `boincmgr_project_hosts_last`
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `boincmgr_project_host_stats`
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `boincmgr_project_stats`
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `boincmgr_tasks`
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `boincmgr_users`
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `boincmgr_user_auth_cookies`
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `boincmgr_variables`
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `boincmgr_xml`
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT;

-- Projects data

INSERT INTO `boincmgr_projects` (`uid`, `name`, `project_url`, `url_signature`, `status`, `weak_auth`, `update_weak_auth`, `cpid`, `expavg_credit`, `team_expavg_credit`, `timestamp`) VALUES
(1, 'NumberFields@home', 'http://numberfields.asu.edu/NumberFields/', '6131de139c1169826d2edc0cd35ddae35c33b0817d2e60de70222621e10937f6\n7d8e874f8f9c6b2a5540d9e3ec103b7e0077a9592154e3a90e1dee8334d81170\n310cda10336067b8f9caf8a6e4905c1ccd15245f32baa9c90097782ab0b6b95e\n886759663a5fb6235ed96f993e0ed8f29236ed5b6366476cc452dd3cb242ff45\n.', 'auto', '', 1, '', 6783.69, 10919400, '2018-05-30 14:05:11'),
(2, 'Asteroids@home', 'http://asteroidsathome.net/boinc/', 'd785cb24664372177976cc1a31f2e3697b589c120f4ca129ef31ceece1d3ef4c\nf31fdb592e8c2c17a6c5406fc15fff9847784c4c08fc49d659d26b1146bdc87d\n56a155c89d96e4faa8e50ad963f9a94858a1a83ddbda4ace2595f6a9c3ff3989\nac0f654e4aa4f486360e5cc5c6b2d3ac3dfe60422c55906fe166cac92456644b\n.\n', 'auto', '', 1, '', 25.0311, 19786800, '2018-05-30 14:05:16'),
(3, 'Citizen Science Grid', 'https://csgrid.org/csg/', '7550adabaae8e14f605ed673d1634b3f5d678930ddec2985a8e20f24eed2ed5a\nc917c12cee5c08524cd8ac4052f5f3d5cf9d58d354a8a1a33d02e5c14ce273ac\n56ba8408f2421ca17964b60cdce7af65a4b7383b66f20db7e5fe7afe93359351\n477dd3300ef39316a85a3306840596abaa14cb27d0e89b7a0b4167ea7af27ae7\n.\n', 'auto', '', 1, '', 1698.6, 36684100, '2018-05-30 14:05:21'),
(4, 'Cosmology@Home', 'http://www.cosmologyathome.org/', '911e222e9d27be252b4b17a3d118a47fbff6496d887c913856886bc04f916e4c\n2cc07333f2f9d6c4713e6b5586f2428c2c169e8d12456b05f15ea05294e3aaa5\nec15f9e868f2244085161d527ce9fabeafc25adce5c66af43fab5e116d117f1a\na51646fc455fe15f469ec4be8856f0c21c9d759ec1bd16eb14becf863ba92285\n.', 'auto', '', 1, '', 14.935, 1654270, '2018-05-30 14:05:26'),
(6, 'LHC@home', 'https://lhcathome.cern.ch/lhcathome/', '9ea479aead2123f9c5d145ae002adb8dbdca336287db1f7ede14947909affed8\n29c5cdb2c42deee5465d3d6301a3f914849c0e9b44f0df3af075bd6a6708c945\n3236be917e3c5357d0d4d80769d27652d11a681a41e68d8721f4253119267253\nb19c7c23c9a906777b7b1b0e11fdfc496751ad70d6738ff077bc440442a2a7aa\n.', 'auto', '', 1, '', 133.487, 1866730, '2018-05-30 14:05:30'),
(7, 'NFS@Home', 'http://escatter11.fullerton.edu/nfs/', '69f33f483edd90183dc346a4047dc2bd2b03f4dc6b3d8bf6159ad40bc62a9be3\na90e0e8811029c55689950fffdd92976c7d60478af43799265cf880a6c4d60df\nd2973169408a235d22a8f3143e9a01574d73a6ec30039126144abd2a3e21420f\ne144cf2d53987c278b27021a37efa4843c07c42a65acc2b1b9c37477c43137ff\n.', 'auto', '', 1, '', 17.8033, 9204120, '2018-05-30 14:05:38'),
(8, 'latinsquares', 'https://boinc.multi-pool.info/latinsquares/', '61a33aff45618f44b4e95e255cfd22706afc2b4bfaa784eee8ed415247a90c42\n7f8a45ad7060bd08d03ef89154065e2aedcfca0c26349318eafa4861a9b92fe5\n456140bc974bbff9beee3bc9f5e5c487df7419fe75de6901fbac5cd877c45e67\n6dc9db0c7ea84cbf62cf12ed8070988f30100b35cfa3006847dd1238c5011b20\n.', 'auto', '', 1, '', 408.033, 3050480, '2018-05-30 13:05:58'),
(9, 'Rosetta@home', 'http://boinc.bakerlab.org/rosetta/', 'c2e4954d9a03f9f28a3211b2dd3e58640301bdd3765b4a8bfe323f8b7fb2cd5d\n4a4c7869a5097b7a926f549e45e46a9d860ec1b5f16c996e26447354dde7faf5\n79a24e5bcea79b20ea437047e94698e137f52fe4dd05818ecf1a5e9af0d88f5d\ne4fc0d9bac10b38c506dd7540ddee1e013b8a8d95052e005486337239a3a2a3d\n.', 'auto', '', 1, '', 262.317, 4498420, '2018-05-30 14:05:42'),
(10, 'SRBase', 'http://srbase.my-firewall.org/sr5/', 'e22d322a5c90590e113f8ec1e9ce8805692748031bb9565395280c45147ab724\nffc86990281b8d5513ef31caee888fdc659e0b9e40ee5d1a6b7d7d2190f5a6ea\n1524f9b95d741dd0e9d3771faa8c2ed76bfbeb3e78e16d0ca3db7d8742180ef6\nfdd4d80de0e25f220b1b7b738ecdf6cdb43862786eec8ee0490b62db3bc3f6c9\n.', 'auto', '', 1, '', 211.117, 4749410, '2018-05-30 14:05:45'),
(11, 'pogs', 'http://pogs.theskynet.org/pogs/', '42fccea49b40d26bb832eb8c4f647eed4f7ef0c686e562727da9d37fae3d3721\n13cb18555ba64ab0a6452cd43829c7afa3b0fc4ccec7d85cf9d6f1b372ba6787\n7923e415dd1f224562124801ed670730b13765fb04556fce0dcafb17d37662b5\n2dd8e8a9aab4e8de7b0f9c6c43911d0eb8b7d60cf70fc923b8b924c6831641f2\n.', 'auto', '', 1, '', 0, 1328300, '2018-05-16 08:00:21'),
(12, 'TN-Grid Platform', 'http://gene.disi.unitn.it/test/', '5d8fabfa3241b6e5591340743569d0181336cbe70a5adccd4d9e4af809b83f91\n177efe5e35a166e4b13ec15843eec96628436f0dbd2b33f8dac853fc38c6f98b\n759cd4f53148ad3fc6ac0abee422df886783e07c33d51f14beda94d637205e36\n851f9c590fbd3e9515186f1adcce280570283c71876afd6bbbb0add106293fd2\n.', 'auto', '', 1, '', 2.73272, 1577480, '2018-05-30 14:05:48'),
(13, 'VGTU project@Home', 'http://boinc.vgtu.lt/vtuathome/', '5e7d68573c0379e7a4aea4f78bf33d890f589ac050210b794aa04d7a1d112a03\n86fdc93630b3e470145f6da5eea36cf352d0d4410f10dd3593da7a11a1340a9e\n7b7e7e88838cc5c454d61d60c855599772cc9fda0e50d36fb99aaeea1f8315a1\n3d85ec3ee76e6eba0fb497c72655ec02b6d3f9c7378f53022e5c2d7514adce1f\n.', 'auto', '', 1, '', 171.236, 2004280, '2018-05-30 14:05:51'),
(14, 'Universe@Home', 'https://universeathome.pl/universe/', '327026490f867caaadf593cfbb9a7d9e445b830330257c71d382be2d648c2371\n2746de60f5426ad556f9eb27eb32c130e37588d22559aa30c16818ff706ade17\n6af25947159953ac5526002636458f746ef98ac24c07f97fe1a4e182888dd8de\n227d46f841019895226bf6b05bcd97f33127727d79eb367fbf22cd05c953f893\n.', 'auto', '', 1, '', 1981.11, 14715900, '2018-05-30 14:05:54'),
(15, 'World Community Grid', 'http://www.worldcommunitygrid.org/', '04787cecdc3876c787b9fe5bc1e9e9c8ff41125ef192a4eea95b5a3e1140dc81\n01e57462e4962cd7a623671375ce9aa6dcc848b360214728175cce6b996fa80d\n33ae1ee092e413f377babe35bb7810e426f3c79cc4c5c0d7918c606cb29f2c60\n7f181c664bb0d85c53d33b40a7ff695da8ebbe254a12c0ae0939d6b3e8a29391\n.', 'auto', '', 0, '', 2543.41, 5369910, '2018-05-30 14:05:58'),
(16, 'yafu', 'http://yafu.myfirewall.org/yafu/', '9398939130d4db3209389f11863f18d21f4d1b67396b55ada7202d9c5c61ef66\n1ac2d84d79bfb4ab5ebcd51e848bbd5935521815ca969b2e666a7b4ff9f9ebe2\nbddb836d07e293fd21f80af2e7de72e1508fc8275574604886cbd39a031c2f40\n8a2e22b01461300cf396961a46d61c680b5a1e0dba659f4daadb225ec1e21f70\n.', 'auto', '', 1, '', 150.183, 2289550, '2018-05-30 14:06:02'),
(17, 'yoyo@home', 'http://www.rechenkraft.net/yoyo/', '869dd2898cc9381650a2b1b67660971d8bf6566e1041b485a81bbf5c4efbc73b\n382cae150094d5060457712f495cf5904b5fea73e01814db65958db69540d0d1\n122c5cf27114477a7185c984bf8f85bb56632048697bd2001b13cd33d6b67c15\n9acbf2929a5821e5e45a7fd308087fcc2de9e49c33d7f3c50c70c5448e6862a2\n.', 'auto', '', 1, '', 25.678, 3587230, '2018-05-16 08:00:34'),
(18, 'Amicable Numbers', 'https://sech.me/boinc/Amicable/', 'd4344e86058057e9ff1eb6a24c85ffbbe1e5661c3d1d2881697a1923ce400c12\n66ab4e10fb3f5000312cac1b39946adec3bdac09f5141c2d27816f9d12d03d30\n302bd19d4d3f9d0be55a256b6770c375b68de19d578a8c387602a4829d0b4e4a\n495ac23cb4b329b6c20b17c284648f168f832ca6a82192f019221f76a8d967b6\n.', 'auto', '', 1, '', 5341.18, 144532000, '2018-05-30 14:06:04'),
(19, 'collatz', 'https://boinc.thesonntags.com/collatz/', '41642874c3f53b2a1a62f640cf857b0134d33370b08a731063317fde146497a4\n1ca6f6200589bd1df4cb7c496eb17bc7c8369f7a01dd132d47e1f6c9997cc1ea\n0285ff7c780812c5e81c1d4c7e5bd3099f2b9352caefc40dffffeb43dd887444\n034ff8823d43835c82cf31a9a73c3a25bed6267867f81bdaf9bce36cf2524647\n.', 'auto', '', 1, '', 7586.75, 454417000, '2018-05-30 14:06:07'),
(20, 'Einstein@Home', 'http://einstein.phys.uwm.edu/', '63d1ea0500f16eb3c587ae99890a0c39c5a8de274794d75bea11a6b770f1d945\n47509267bfd40709d5583fe90f0dd637748f824ef4e76b0658afe7e098265adf\n42fafc19e92bfe88bb5c1b837483ca2a435faca0babc999090cc730a4fc58b76\n4e36c3efcbbb030f32b1838f4cebd0217153c77c167c35e80cf5ba278acbd234\n.', 'auto', '', 1, '', 390.771, 78579100, '2018-05-30 14:06:14'),
(21, 'Enigma@Home', 'http://www.enigmaathome.net/', '5df27dccf17cc07fd7e41f8e3518822e5258e35cf3891b0a26bd4aa66294b109\ndcbac24aa33d602a7ebb0a8892c73f8efd99b1df79f4153f19b2f8a54adbddd3\n2d0dd1406e9f0bc7bca4d6bdbc549a59b92d6a1238b386b5d5825effd5205ff8\n0a7e97feb01134cc37e390aa477e469656c2a5555772dca145ff46dc23bcdba2\n.', 'auto', '', 1, '', NULL, NULL, '2018-05-15 07:01:22'),
(22, 'GPUGRID', 'http://www.gpugrid.net/', 'e000ae1db66bdc8b39f83f19ce98e85e917153702e64a1eb12867cb7ef3ea74f\n12c5ac27cb832a787330758379bb5b893818c33623390ad25c581d6e00214efb\n46c637459e14bd7b6ce45c3e5b9b2e370e24776b82e69181743d31377c8fc3c3\n93da31253fd75bc4c5c3beab43763c54f32d7ee19daa012d2d860f25d0f2c558\n.', 'auto', '', 1, '', 0, 128678000, '2018-05-30 14:06:18'),
(23, 'Milkyway@Home', 'http://milkyway.cs.rpi.edu/milkyway/', 'c75686bb98fe42784ac978f0b4463c4d17f1e246e75d985046bed5a4af606908\n4e5b0df2192f5cea6086029ffd516e0cf41d41faedeb703dea0cb223201f251c\n379133c542a3d3a41e7e2161ab4af7c831e15bf6b5198fd8e47d5900c853a265\n5c361aa9b453195c28390f3fb68ea6f2d786cf352c1c51be04815aae54e6bcdb\n.', 'auto', '', 1, '', 13.2651, 60606100, '2018-05-30 14:06:22'),
(24, 'PrimeGrid', 'http://www.primegrid.com/', '696f9a2e892692167af134ad044f09e4785e6125aab36c81a524be23be508b71\n1e0964c2f315366738c7e7bbbdcb05257b21a8daeb4d05d7ab628643c4405c9a\nd91f28f0d8ede7a3ec7ae6654358b96cb08702266ea6d08badd51fd5a63bb19d\n45d21908f00b3043129d9e5903f6fea2537d4f882b4d32dc636bdf0661f0f71e\n.', 'auto', '', 1, '', 0, 0, '2018-05-30 14:06:25'),
(25, 'SETI@home', 'http://setiathome.berkeley.edu/', '5aec1657b7e56583d4c5171c78277f0e1be7bce1dea7a085b9dab5606544fd03\nd5c43ab421d6d3266f494cac80736bb0e70694dd57553be7a2f488e35ba7b5e6\n068ea93a3aecec3c6acb15578385186ab36aeafa76ec6d02484e146567c7eac9\nded7448024211cb17f65cc5ffde35413f61eeeb3a5607291d13f220abe0dd829\n.', 'auto', '', 1, '', 0, 0, '2018-05-30 14:06:34');

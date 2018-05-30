# General
Simple gridcoin pool

# Requirements
1) PHP 5/PHP 7
2) Apache web server, mysql, tested in ubuntu
3) At least 2000 gridcoins for staking (more GRC means faster stakes)
4) Gridcoin Research client
5) Optionally crypt_prog from BOINC for signing urls
6) If you want more security: second computer for storing staking wallet outside of web server

# Manual installation
1) Copy files to web-accessible folder, e.g. /var/www/boinc_pool/
2) Create DB and user for pool
3) Run manual.sql in pool's DB
4) Register at every whitelisted BOINC project with one login and password.
4.1) You name in World Community Grid should be same as your email
4.2) Copy every weak auth key to DB table boincmgr_projects (besause world community grid sends incorrect weak key via XML RPC)
4.3) Yoyo@home has no weak auth key. You can use full access key for private pool 4.4) Check that your cpid are synced
5) Change settings in your settings.php
6) Set cron 1h to update_projects_data.php and send_rewards.php
7) Regiter new user via web, then change his status to "admin" in boincmgs_users
8) Setup gridcoinresearch wallets, send beacon, wait for rewards

# Installation via setup.php (not ready yet)
1) Copy files to web-accessible folder, e.g. /var/www/boinc_pool/
2) Run setup via setup.php (not ready yet)
3) Set cron 1h to update_projects_data.php

# Mining guide
1) Register in pool
2) Sync your BOINC client with pool and your username/password
3) Attach projects and sync one more time
4) After 1 day check that your host appears in BOINC hosts
5) Wait for pool stake, then do billing and receive rewards

# Admin guide
## Project statuses
You can set statuses for projects:
1) Enabled - get data from project, rewards enabled, available to attach for users
2) Stats only - get data from project, no rewarding, unavailable for attach
3) Disabled - don't get data from project, no rewarding, unavailable for attach
## User statuses
1) User - ordinary user
2) Admin - can change user statuses, project statuses, do manual billings
3) Banned - user can not login
4) Donator - all user reward distributed between others (like negative fee), could be used for promotion, if you distribute your coins between users.
## Billing
If you want to distribute some coins (or other coins, e.g. SPARC) between users according to their contribution you can do it with that instrument.
## Log
In log section you can view what happening with users and syncs:
1) User actions - registering, attaching, detaching, deleting, syncing
2) Project syncing
3) Errors - login errors, SQL errors and other
Samples:
1) Projects to sync 21, synced 18, errors: Cosmology@Home (no data from project), latinsquares (get project config error), SETI@home (get project config error)
2) Sync username 'sau412' host 'DESKTOP-A8D9DJF' p_model 'Intel(R) Xeon(R) CPU E5420 @ 2.50GHz [Family 6 Model 23 Stepping 10]'
3) Login username 'Arikado'
4) Query error: SELECT `uid`,`name` FROM `boincmgr_projects` WHERE `status` IN ('enabled') AND `uid` NOT IN ( SELECT bap.`project_uid` FROM `boincmgr_hosts` h LEFT JOIN `boincmgr_attach_projects` bap ON bap.`host_uid`=h.`uid` WHERE `host_uid`='116' AND bap.detach=0 ) ORDER BY `name` ASC
5) Admin check rewards from '2018-05-27 07:16:21' to '2018-05-30 15:48:12' reward '10.0000'

# How rewarding works
In gridcoin you receive rewards for BOINC projects when your coins stake. You need about 2000 gridcoins to stake at least once a 6 months (payout horizont). So, if you haven't much coins, you can use one of pools to receive rewards. Each whitelisted project rewarded equally, as I know. Rewards distributed between members of Gridcoin team in BOINC in accordance with contribution. When reward received (and admin clicks 'send rewards' button), rewards distributed between all projects using pool proportion (see Pool stats page). Each project reward distributed between contributors in accordance with contribution.

# Billing
Billing works in manual mode. After billing rewards send automatically.
1) First billing: when pools wallet stakes, write pool start date, current stake date and rewards, click "send rewards"
2) Other billings: when pools wallet stakes, write current stake date (previous stake date is autom-filled) and rewards, click "send rewards"

# To do
* Pool info editor
* Feedback page for questions, requests and answers from pool administration (it's me for that pool implementation).
* More detail stats, graphs, estimations and gridcoin exchange rate.
* If someone wants install their own pool with my sources, I'll do installer (web page for automated pool setup - create settings.php, create and fill tables with data).
* If someone wants it, I could check how it works on raspberry and do raspberry image (or instrunctions) with that pool, if possible.

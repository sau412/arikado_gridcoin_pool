# General
Simple gridcoin pool with automated payments. Can work on raspberry pi 2 or 3.

# Requirements
1) PHP 5 with openssl lib or PHP 7
2) Apache web server, mysql, tested in ubuntu
3) At least 10000 gridcoins for staking (more GRC means faster stakes)
4) Gridcoin Research client
5) Optionally crypt_prog from BOINC for signing urls
6) If you want more security: second computer for storing staking wallet outside of web server

# Automatic installation
1) Copy files to folder, e.g. /var/boinc_pool/
2) Set web access to /var/boinc_pool/web
3) Run setup via setup.php
4) Follow instructions

# Manual installation
## BOINC part
1) Register at every whitelisted BOINC project with one login and password.
2) You name in World Community Grid should be same as your email
3) Copy every weak auth key to DB table boincmgr_projects (besause world community grid sends incorrect weak key via XML RPC)
4) Yoyo@home has no weak auth key. You can use full access key for private pool

## Apache, PHP, MySQL part
1) Copy files to folder, e.g. /var/boinc_pool/
2) Set web access to /var/boinc_pool/web
3) Create DB and user for pool
4) Run manual.sql in pool's DB
5) Set cron 1h to update_projects_data.php, update_blocks.php and send_rewards.php
6) Change settings in your settings.php
7) Regiter new user via web, then change his status to "admin" in boincmgs_users

## Cron jobs
```
# Updace currency rates
20 * * * * www-data cd /var/www/boinc_pool/tasks && php update_rates_coingecko.php
# Update data from projects (user contribution, RAC, tasks, etc)
5 * * * * www-data cd /var/www/boinc_pool/tasks && php update_projects.php
# Update gridcoin superblock data
4 * * * * www-data cd /var/www/boinc_pool/tasks && php update_superblock_data.php
# Update latest gridcoin blocks
15 * * * * www-data cd /var/www/boinc_pool/tasks && php update_blocks.php
# Cache graphs
25 * * * * www-data cd /var/www/boinc_pool/tasks && php update_graphs.php
# Update task statuses and send emails on errors
0 12 * * * www-data cd /var/www/boinc_pool/tasks && php update_task_stats.php
# Update user reward
*/10 * * * * www-data cd /var/www/boinc_pool/tasks && php update_rewards.php
# Move user rewards to payments
30 10 * * * www-data cd /var/www/boinc_pool/tasks && php do_billing.php
# Send user rewards
25,55 * * * * www-data cd /var/www/boinc_pool/tasks && php send_rewards_grc.php
# Send faucet rewards
*/5 * * * * www-data cd /var/www/boinc_pool/tasks && php faucet_send_rewards.php
```

## Gridcoin research wallet, beacon
1) Run gridcoinresearchd in CLI mode with RPC:
```
rpcuser=username
rpcpassword=password1
rpcallowip=127.0.0.1/255.255.255.255
rpcport=port
```
2) Run BOINC, attach to any whitelisted project, run gridcoinresearch for staking (you could use different PC or server for that), send beacon, wait for rewards
3) Check that your cpid are synced (you coud see that in project control page)

# Mining guide
1) Register in pool
2) Sync your BOINC client with pool and your username/password
3) Attach projects and sync one more time
4) After 1 day check that your host appears in BOINC hosts
5) Wait for pool stake, then do billing and receive rewards

# Common interface guide
## Pool info
You can read about pool here
## Payouts
You can see reward payouts here.
## Pool stats
You can see project BOINC stats here.
## Login
You can login here.
## Register
You can register here.
## Feedback
You can leave feedback here, these messages could be read by administrator.

# User interface guide
## Settings
You can change payout address, password and email here. Password is requred to change something.
## Your hosts
You can see hosts, attached to your account, attach and detach projects, delete hosts, see syncronization status..
## BOINC results
You can see your BOINC stats here.

# Admin interface guide
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
In log section you can view what happening with pool, users, projects, syncs, actions:
1) User actions - registering, attaching, detaching, deleting, syncing
2) Project syncing
3) Errors - login errors, SQL errors and other

Samples:
```
1) Projects to sync 21, synced 18, errors: Cosmology@Home (no data from project), latinsquares (get project config error), SETI@home (get project config error)
2) Sync username 'sau412' host 'DESKTOP-A8D9DJF' p_model 'Intel(R) Xeon(R) CPU E5420 @ 2.50GHz [Family 6 Model 23 Stepping 10]'
3) Login username 'Arikado'
4) Query error: SELECT `uid`,`name` FROM `boincmgr_projects` WHERE `status` IN ('enabled') AND `uid` NOT IN ( SELECT bap.`project_uid` FROM `boincmgr_hosts` h LEFT JOIN `boincmgr_attach_projects` bap ON bap.`host_uid`=h.`uid` WHERE `host_uid`='116' AND bap.detach=0 ) ORDER BY `name` ASC
5) Admin check rewards from '2018-05-27 07:16:21' to '2018-05-30 15:48:12' reward '10.0000'
```

## Pool info editor
You chan change pool info here. Any HTML or scripting allowed.

## Messages
You can read feedback messages here.

## Emails
You can see pool email texts and statuses here.

# Debug interfaces
You can set $debug_mode=TRUE in settings.php, and XML between users and XML between projects will be written to table boincmgr_xml.

# How rewarding works
In gridcoin you receive rewards for BOINC projects when your coins stake. You need about 2000 gridcoins to stake at least once a 6 months (payout horizont). So, if you haven't much coins, you can use one of pools to receive rewards. Each whitelisted project rewarded equally, as I know. Rewards distributed between members of Gridcoin team in BOINC in accordance with contribution. When reward received (and admin clicks 'send rewards' button), rewards distributed between all projects using pool proportion (see Pool stats page). Each project reward distributed between contributors in accordance with contribution.

# Billing
1) Billing works in automatic mode. After billing rewards send automatically.
2) If you want, you can distribute coins with "billing" interface. Fill start and stop dates to calculate contribution, reward amount and click "send rewards".

# What's new
2018-08-09 php 5 checked, automated setup, emails, night mode, two level menu, quick RAC
2018-06-07 Estimated GRC/day, view last sync log (for admin), 'project not whitelisted, no rewards' status for project

# To do
* Option for distribute coins equally between users (for rains or faucets)
* Comments to host, user, project

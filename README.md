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

# User and project control (for admins)
1) Banned users are not receive rewards
2) Stats only and disabled projects are not receiving rewards too
3) If you not interested in any projects within the pool change their state to disabled

# How rewarding works
In gridcoin you receive rewards for BOINC projects when your coins stake. You need about 2000 gridcoins to stake at least once a 6 months (payout horizont). So, if you haven't much coins, you can use one of pools to receive rewards. Each whitelisted project rewarded equally, as I know. Rewards distributed between members of Gridcoin team in BOINC in accordance with contribution. When reward received (and admin clicks 'send rewards' button), rewards distributed between all projects using pool proportion (see Pool stats page). Each project reward distributed between contributors in accordance with contribution.

# Billing
Billing works in manual mode. After billing rewards send automatically.
1) First billing: when pools wallet stakes, write pool start date, current stake date and rewards, click "send rewards"
2) Other billings: when pools wallet stakes, write current stake date (previous stake date is autom-filled) and rewards, click "send rewards"

# To do
* Pool info editor
* You need three synchonizations to attach project to new pc (two to existing) - first to attach host to account, second to send attached projects to host, third to send host id back to pool. I'll do more specific messages than 'not synced properly'.
* Feedback page for questions, requests and answers from pool administration (it's me for that pool implementation).
* Automated payments.
* More detail stats, graphs, estimations and gridcoin exchange rate.
* Opportunity to delete host and user.
* If someone wants install their own pool with my sources, I'll do installer (web page for automated pool setup - create settings.php, create and fill tables with data).
* If someone wants it, I could check how it works on raspberry and do raspberry image (or instrunctions) with that pool, if possible.

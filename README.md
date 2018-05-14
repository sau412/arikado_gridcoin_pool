# General
Simple gridcoin pool

# Warnings
1) Send rewards automatically not implemended yet (testing now)

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
4) Register at every whitelisted BOINC project with one login and password
4.1) You name in World Community Grid should be same as your email
4.2) Copy every weak auth key to DB table boincmgr_projects (besause world community grid sends incorrect weak key via XML RPC)
4.3) Yoyo@home has no weak auth key. You can use full access key for private pool 4.4) Check that your cpid are synced
5) Change settings in your settings.php
6) Set cron 1h to update_projects_data.php
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
2) Greylisted projects are not receive rewards too

# Billing
Billing works in manual mode. Check table boincmgr_payouts for manual rewards
1) First billing: when pools wallet stakes, write pool start date, current stake date and rewards, click "send rewards"
2) Other billings: when pools wallet stakes, write previous stake date, current stake date and rewards, click "send rewards"

# Todo
* BOINC client can send incorrect XML, so we need more robust parser
* Filter unknown projects from clients
* Show error when somebody try to steal host (attach host with same host_id and host cpid)
* Make attach and detach projects functions in boincmgr.php
* Automated payments (admin just click "send rewards" and rewards sent automatically)

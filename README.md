# General
Simple gridcoin pool

# Requirements
1) PHP 5/PHP 7
2) Apache web server
3) At least 2000 gridcoins for staking (more GRC means faster stakes)
4) Gridcoin Research client
5) Optionally crypt_prog from BOINC for signing urls

# Manual installation
1) Copy files to web-accessible folder, e.g. /var/www/boinc_pool/
2) Create DB and user for pool
3) Run manual.sql in pool's DB
4) Register at every whitelisted BOINC project with one login and password
5) Change settings in your settings.php
6) Set cron 1h to update_projects_data.php
7) Regiter new user via web, then change his status to "admin" in boincmgs_users

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

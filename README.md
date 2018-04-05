# General
Simple gridcoin pool

# Requirements
1) PHP 5
2) Apache web server
3) At least 2000 gridcoins
4) Gridcoin Research client
5) crypt_prog from BOINC for signing urls

# Installation
1) Copy files to web-accessible folder, e.g. /var/www/boinc_pool/
2) Run setup via setup.php
3) Set cron 1h to update_projects_data.php

# Mining guide
1) Register in pool
2) Sync your BOINC client with pool and your username/password
3) Attach projects and sync one more time
4) After 1 day check that your host appears in BOINC hosts
5) Wait for pool stake, then do billing and receive rewards

# Billing
Billing works in manual mode. Not implemented yet.

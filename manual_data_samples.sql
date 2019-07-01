INSERT INTO `currency` (`uid`, `name`, `full_name`, `payout_limit`, `tx_fee`, `project_fee`, `url_wallet`, `url_tx`, `url_api`, `rate_per_grc`) VALUES
(1, 'GRC', 'Gridcoin (slow RAC, instant payout, no fees)', 0.001, 0.0001, 0, 'https://www.gridcoinstats.eu/address/', 'https://www.gridcoinstats.eu/tx/', 'https://api.coingecko.com/api/v3/coins/gridcoin-research', 1),
(2, 'DOGE', 'Dogecoin (payout limit 101, fee 1)', 101, 1, 0, 'https://dogechain.info/address/', 'https://dogechain.info/tx/', 'https://api.coingecko.com/api/v3/coins/dogecoin', 1.9393939393939),
(3, 'BTC', 'Bitcoin (payout limit 0.00105, fee 0.00005)', 0.001, 0.00005, 0, 'https://bitinfocharts.com/bitcoin/address/', 'https://bitinfocharts.com/bitcoin/tx/', 'https://api.coingecko.com/api/v3/coins/bitcoin', 0.0000005696),
(5, 'LTC', 'Litecoin (payout limit 0.0011, fee 0.0001)', 0.001, 0.0001, 0, 'https://live.blockcypher.com/ltc/address/', 'https://live.blockcypher.com/ltc/tx/', 'https://api.coingecko.com/api/v3/coins/litecoin', 0.000051127020486712),
(7, 'GRC2', 'Gridcoin (quick RAC, instant payout, no fees)', 0.001, 0.0001, 0, 'https://www.gridcoinstats.eu/address/', 'https://www.gridcoinstats.eu/tx/', 'https://api.coingecko.com/api/v3/coins/gridcoin-research', 1),
(8, 'GBYTE', 'ByteBall (payout limit 1 Mb, fee 0)', 0.0001, 0, 0, 'https://explorer.byteball.org/#', 'https://explorer.byteball.org/#', 'https://api.coingecko.com/api/v3/coins/byteball', 0.00018336455939067),
(9, 'BURST', 'BURST (payout limit 10, fee 0.01)', 10, 0.01, 0, '', 'https://explore.burst.cryptoguru.org/transaction/', 'https://api.coingecko.com/api/v3/coins/burst', 0.7162077203571),
(10, 'BAN', 'Banano (payout limit 10, fee 0)', 10, 0, 0, 'https://creeper.banano.cc/explorer/account/', 'https://creeper.banano.cc/explorer/block/', 'https://api.coingecko.com/api/v3/coins/banano', 4.3348554033486),
(11, 'XMR', 'Monero (payout limit 0.001, fee 0.00001)', 0.001, 0.00001, 0, '', 'https://moneroblocks.info/tx/', 'https://api.coingecko.com/api/v3/coins/monero', 0.000067534962029369),
(12, 'TRX', 'Tron (payout limit 1, fee 0)', 1, 0, 0, 'https://tronscan.org/#/address/', 'https://tronscan.org/#/transaction/', 'https://api.coingecko.com/api/v3/coins/tron', 0.19243243243243),
(13, 'RDD', 'Reddcoin (payout limit 1, fee 0.002)', 1, 0.002, 0, 'https://live.reddcoin.com/address/', 'https://live.reddcoin.com/tx/', 'https://api.coingecko.com/api/v3/coins/reddcoin', 3.1662034463591),
(14, 'NANO', 'Nano (payout limit 0.1, fee 0)', 0.1, 0, 0, 'https://www.nanode.co/account/', 'https://www.nanode.co/block/', 'https://api.coingecko.com/api/v3/coins/nano', 0.0049048480151554),
(15, 'WAVES', 'Waves (payout limit 0.1, fee 0)', 0.1, 0, 0, 'https://wavesexplorer.com/address/', 'https://wavesexplorer.com/tx/', 'https://api.coingecko.com/api/v3/coins/waves', 0.0033120130247703),
(16, 'BBP', 'Biblepay (payout limit 100, fee 0)', 100, 0, 0, 'http://explorer.biblepay.org/address/', 'http://explorer.biblepay.org/tx/', 'https://api.coingecko.com/api/v3/coins/biblepay', 14.24),
(17, 'XRP', 'Ripple (payout limit 0.1, fee 0)', 0.1, 0, 0, 'https://xrpcharts.ripple.com/#/graph/', 'https://xrpcharts.ripple.com/#/graph/', 'https://api.coingecko.com/api/v3/coins/ripple', 0.015353099730458);

INSERT INTO `variables` (`uid`, `name`, `value`, `timestamp`) VALUES
(1, 'pool_info', 'Write your own pool info here', '2019-06-24 14:14:50'),
(2, 'magnitude_unit', '0.175', '2019-06-30 22:04:02'),
(96, 'hot_wallet_balance', '0', '2019-07-01 13:00:02'),
(105, 'project_count', '20', '2019-05-31 21:04:03'),

INSERT INTO `projects` (`uid`, `name`, `superblock_name`, `project_url`, `url_signature`, `status`, `weak_auth`, `update_weak_auth`, `cpid`, `team`, `expavg_credit`, `team_expavg_credit`, `present_in_superblock`, `superblock_expavg_credit`, `last_query`, `comment`, `gpu_present`, `file`, `timestamp`) VALUES
(1, 'NumberFields@home', 'numberfields@home', 'http://numberfields.asu.edu/NumberFields/', '', 'auto enabled', '', 1, '', 'Gridcoin', 374.7666636065081, 1566990.743827976, 1, 1351137, '', '', 0, 'numberfields.xml', '2019-07-01 13:05:05');

INSERT INTO `users` (`uid`, `username`, `email`, `send_error_reports`, `salt`, `passwd_hash`, `balance`, `currency`, `payout_address`, `status`, `token`, `timestamp`) VALUES
(7, 'user', 'mail@example.com', 1, '', '', 0, 'GRC', '', 'user', '', '2019-07-01 13:30:35'),

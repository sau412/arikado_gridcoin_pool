<?php
require_once("../lib/settings.php");
require_once("../lib/db.php");

db_connect();

$data = db_query_to_array("SELECT
                                        MIN(`uid`) as min_uid,
                                        `project_uid`,
                                        `host_uid`,
                                        `host_id`,
                                        AVG(COALESCE(`expavg_credit`,0)) AS avg_expavg_credit,
                                        MAX(COALESCE(`total_credit`,0)) AS max_total_credit,
                                        AVG(COALESCE(`magnitude_unit`,0)) AS avg_magnitude_unit,
                                        SUM(COALESCE(`grc_amount`,0)) AS sum_grc_amount,
                                        AVG(COALESCE(`exchange_rate`,0)) AS avg_exchange_rate,
                                        COALESCE(`currency`,'GRC') AS currency,
                                        SUM(COALESCE(`currency_amount`,0)) AS sum_currency_amount,
                                        DATE(`timestamp`) as day_timestamp
                                FROM `project_host_stats`
                                WHERE YEAR(`timestamp`) = 2018
                                GROUP BY `project_uid`, `host_uid`, `host_id`, COALESCE(`currency`,'GRC'), DATE(`timestamp`)
                                HAVING count(*) > 1");

//var_dump($data);
foreach($data as $row) {
        $min_uid = $row['min_uid'];
        $project_uid = $row['project_uid'];
        $host_uid = $row['host_uid'];
        $host_id = $row['host_id'];
        $avg_expavg_credit = $row['avg_expavg_credit'];
        $max_total_credit = $row['max_total_credit'];
        $avg_magnitude_unit = $row['avg_magnitude_unit'];
        $sum_grc_amount = $row['sum_grc_amount'];
        $avg_exchange_rate = $row['avg_exchange_rate'];
        $currency = $row['currency'];
        $sum_currency_amount = $row['sum_currency_amount'];
        $day_timestamp = $row['day_timestamp'];

        var_dump($row);
        db_query("START TRANSACTION");
        db_query("UPDATE `project_host_stats` SET
                                `expavg_credit` = '$avg_expavg_credit',
                                `total_credit` = '$max_total_credit',
                                `magnitude_unit` = '$avg_magnitude_unit',
                                `grc_amount` = '$sum_grc_amount',
                                `exchange_rate` = '$avg_exchange_rate',
                                `currency` = '$currency',
                                `currency_amount` = '$sum_currency_amount'
                        WHERE `uid` = '$min_uid'");
        db_query("DELETE FROM `project_host_stats`
                        WHERE `uid` <> '$min_uid' AND `project_uid` = '$project_uid' AND `host_uid` = '$host_uid' AND `host_id` = '$host_id' AND
                                COALESCE(`currency`,'GRC') = '$currency' AND DATE(`timestamp`) = '$day_timestamp'");
        db_query("COMMIT");
//die();
}

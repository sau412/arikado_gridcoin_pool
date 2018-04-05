<?php

function db_connect() {
    global $db_host,$db_login,$db_password,$db_base;
    $res=mysql_pconnect($db_host,$db_login,$db_password);
    mysql_select_db($db_base);
}

function db_query($query) {
    $result=mysql_query($query);
    if($result===FALSE) die("Query error");
    return $result;
}

function db_query_to_array($query) {
    $result_array=array();
    $result=db_query($query);
    if(mysql_num_rows($result)) {
        while($row=mysql_fetch_assoc($result)) {
            $result_array[]=$row;
        }
    }
    return $result_array;
}

function db_escape($string) {
    return mysql_real_escape_string($string);
}

function db_query_to_variable($query) {
    $result=db_query($query);
    if(mysql_num_rows($result)) {
        $row=mysql_fetch_array($result);
        $res=$row[0];
    } else {
        $res="";
    }
    return $res;
}

?>

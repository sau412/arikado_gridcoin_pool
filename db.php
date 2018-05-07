<?php
// Various DB functions

// Connect to DB
function db_connect() {
    global $db_host,$db_login,$db_password,$db_base;
    $res=mysql_pconnect($db_host,$db_login,$db_password);
    mysql_select_db($db_base);
}

// Query
function db_query($query) {
    $result=mysql_query($query);
    if($result===FALSE) die("Query error: $query");
    return $result;
}

// Query and return results array
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

// Escape string
function db_escape($string) {
    return mysql_real_escape_string($string);
}

// Query and return value from first row first column
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

// For php7
if(!function_exists("mysql_pconnect")) {
        function mysql_pconnect($host,$login,$password) {
                global $mysqli_res;
                $mysqli_res=mysqli_connect($host,$login,$password);
        }
        function mysql_select_db($db) {
                global $mysqli_res;
                return mysqli_select_db($mysqli_res,$db);
        }
        function mysql_query($query) {
                global $mysqli_res;
                return mysqli_query($mysqli_res,$query);
        }
        function mysql_fetch_assoc($resource) {
                global $mysqli_res;
                return mysqli_fetch_assoc($resource);
        }
        function mysql_fetch_array($resource) {
                global $mysqli_res;
                return mysqli_fetch_array($resource);
        }
        function mysql_num_rows($resource) {
                global $mysqli_res;
                return mysqli_num_rows($resource);
        }
        function mysql_real_escape_string($str) {
                global $mysqli_res;
                return mysqli_real_escape_string($mysqli_res,$str);
        }
}
?>

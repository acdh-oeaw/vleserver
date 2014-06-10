<?php

if (function_exists('xdebug_start_error_collection')) {
    xdebug_start_error_collection();
}
ini_set('display_errors', false);
header("Content-Type: text/html; charset=utf-8");
$method = $_SERVER['REQUEST_METHOD'];
$err = 0;
$username = "dicts_ch";
#   $password = "#phpUS3R#";
$password = 'VGxwZUqHG4vHyeGb';
$db_host = ""; # "" = use socket
$database = "dicts_ch";
$queryType = $_GET['queryType'];
$user = mysql_real_escape_string($_GET['user']);
$pw = mysql_real_escape_string($_GET['pw']);

//echo $method;
$link = mysql_connect($db_host, $username, $password);
if ($link === FALSE) {
    $err = 1;
    echo "Can't connect to DB";
} else {
    mysql_set_charset('utf8', $link);
    mysql_query("SET character_set_results = 'utf8',"
            . " character_set_client = 'utf8',"
            . " character_set_connection = 'utf8',"
            . " character_set_database = 'utf8',"
            . " character_set_server = 'utf8'", $link);
}
if ($method == 'GET') {
    if ($err == 0) {
        if ($queryType == 'profiles') {
            mysql_select_db($database);
            $query2 = "SELECT distinct(`table`) FROM dict_users WHERE userid = \"$user\" and pw=\"$pw\"";
            $result = mysql_query($query2);
            $num = mysql_numrows($result);
            $i = 0;
            $sout = '';
            while ($i < $num) {
                $sh = mysql_result($result, $i, 0);
                $sout = $sout . $sh . "\r\n";
                $i++;
            }
            echo "$sout";
        } else {
            mysql_select_db($database);
            $query2 = "SHOW TABLES";
            $result = mysql_query($query2);
            $num = mysql_numrows($result);
            $i = 0;
            $sout = '';
            while ($i < $num) {
                $sh = mysql_result($result, $i, 0);
                $n1 = strstr($sh, '_lck');
                $n2 = strstr($sh, '_ndx');
                if (($n1 != '_lck') && ($n2 != '_ndx')) {
                    $sout = $sout . $sh . $n . "\r\n";
                }
                $i++;
            }

            echo "$sout";
        }
    }
}

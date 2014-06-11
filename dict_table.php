<?php

if (function_exists('xdebug_start_error_collection')) {
    xdebug_start_error_collection();
}
ini_set('display_errors', false);
header("Content-Type: text/html; charset=utf-8");
$method = $_SERVER['REQUEST_METHOD'];
$err = 0;
$username = "dicts-sru-user";
#   $password = "#phpUS3R#";
$password = 'n2vTwvPGF7tqZQXZ';
$db_host = ""; # "" = use socket
$database = "dicts_ch";
$tablename = mysql_real_escape_string($_GET['tablename']);
$type = $_GET['type'];
$userID = mysql_real_escape_string($_GET['userID']);
if ($userID === '') {
    $userID = mysql_real_escape_string($_GET['tableuser']);
}

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

if ($method == 'DELETE') {
    if ($err == 0) {

        mysql_select_db($database);

        switch ($type) {
            case "deleteUser":
                $query2 = "DELETE FROM `dict_users` where userID = '$userID'";
                $result1 = mysql_query($query2);
                echo "Result: r1($result1)   Deleted $userID";
                break;
            case "deleteRecs": $query2 = "DELETE FROM `" . $tablename . "` where id > 699";
                $result1 = mysql_query($query2);

                $query2 = "DELETE FROM `" . $tablename . "_lck` where id > 699";
                $result2 = mysql_query($query2);

                $query2 = "DELETE FROM `" . $tablename . "_ndx` where id > 699";
                $result3 = mysql_query($query2);
                echo "Result: r1($result1) r2(result2) r3($result3) Deleted from $tablename";
                break;
            case "dropTable":
                $query2 = "DROP TABLE `" . $tablename . "` ";
                $result1 = mysql_query($query2);

                $query2 = "DROP TABLE `" . $tablename . "_lck` ";
                $result2 = mysql_query($query2);

                $query2 = "DROP TABLE `" . $tablename . "_ndx` ";
                $result3 = mysql_query($query2);
                echo "Result: r1($result1) r2(result2) r3($result3)  Deleted $tablename";
                break;
        } //switch
    }
}

if ($method == 'GET') {
    $acttype = $_GET['acttype'];

    switch ($acttype) {
        case 'copyTable':
            mysql_select_db($database);
            $newTablename = mysql_real_escape_string($_GET['newTablename']);
            $query2 = 'DROP TABLE IF EXISTS' . $newTablename;
            $result = mysql_query($query2);
            $query2 = 'DROP TABLE IF EXISTS' . $newTablename . '_ndx';
            $result = mysql_query($query2);
            $query2 = 'DROP TABLE IF EXISTS' . $newTablename . '_lck';
            $result = mysql_query($query2);

            $query2 = 'CREATE TABLE ' . $newTablename . ' LIKE ' . $tablename;
            $result = mysql_query($query2);
            $query2 = 'CREATE TABLE ' . $newTablename . '_ndx LIKE ' . $tablename . '_ndx';
            $result = mysql_query($query2);
            $query2 = 'CREATE TABLE ' . $newTablename . '_lck LIKE ' . $tablename . '_lck';
            $result = mysql_query($query2);

            $query2 = 'INSERT ' . $newTablename . ' SELECT * FROM ' . $tablename;
            $result = mysql_query($query2);
            $query2 = 'INSERT ' . $newTablename . '_ndx SELECT * FROM ' . $tablename . '_ndx';
            $result = mysql_query($query2);
            $query2 = 'INSERT ' . $newTablename . '_lck SELECT * FROM ' . $tablename . '_lck';
            $result = mysql_query($query2);
            echo "$result";
            break;
        case 'unlockall':
            $query2 = 'update ' . $tablename . ' set `locked`=\'\'';
            mysql_select_db($database);
            $result = mysql_query($query2);
            echo "unlocked $result";
            break;
        case 'minmax':
            $query2 = "select min(id) from $tablename";
            mysql_select_db($database);
            $result = mysql_query($query2);
            $min = mysql_result($result, 0, 0);

            $query2 = "select max(id) from $tablename";
            $result = mysql_query($query2);
            $max = mysql_result($result, 0, 0);
            echo $min . ' ' . $max;
            break;
        case 'stats':
            mysql_select_db($database);

            $query2 = "select count(*) from $tablename";
            $result = mysql_query($query2);
            $sout = mysql_result($result, 0, 0) . ' records';

            $query2 = "select count(distinct id) from $tablename" . '_ndx where xpath like "entry-form-lemma-orth%"';
            $result = mysql_query($query2);
            $sout2 = mysql_result($result, 0, 0) . ' lemma records';

            $query2 = "select count(distinct id) from $tablename" . '_ndx where xpath like "entry-form-multiWordUnit-orth%"';
            $result = mysql_query($query2);
            $sout3 = mysql_result($result, 0, 0) . ' mwu records';

            $query2 = "select count(distinct id) from $tablename" . '_ndx where xpath like "cit%"';
            $result = mysql_query($query2);
            $sout4 = mysql_result($result, 0, 0) . ' example records';
            echo "$sout\r\n$sout2\r\n$sout3\r\n$sout4";
            break;
        case 'xpaths':
            $tablename = $tablename . '_ndx';
            $query2 = "select distinct(xpath) from $tablename";
            mysql_select_db($database);
            $result = mysql_query($query2);
            $num = mysql_numrows($result);
            $i = 0;
            $sout = '';
            while ($i < $num) {
                $xpath = mysql_result($result, $i, 0);
                $sout = $sout . "\r\n" . $xpath;
                $i++;
            } //while
            echo "$sout";
            break;
        case "getusertype":
            $query4 = "SELECT `writeown`,`write`,`read` from dict_users where `table`='$tablename' and `userID`='$userID' ";
            mysql_select_db($database);
            $result = mysql_query($query4);
            $num = mysql_numrows($result);
            $i = 0;
            $s1 = mysql_result($result, 0, 0);
            $s2 = mysql_result($result, 0, 1);
            $s3 = mysql_result($result, 0, 2);

            $sout = $s1 . '-' . $s2 . '-' . $s3;
            echo "$sout";
            break;
        case "getusers":
            $query4 = "SELECT `userID` from dict_users where `table`='$tablename'";
            mysql_select_db($database);
            $result = mysql_query($query4);
            $num = mysql_numrows($result);
            $i = 0;
            $sout = '';
            while ($i < $num) {
                $xpath = mysql_result($result, $i, 0);
                $sout = $sout . "\r\n" . $xpath;
                $i++;
            } //while
            echo "$sout";
            break;
    }
}

if ($method == 'PUT') { //NEW
    if ($err == 0) {
        mysql_select_db($database);

        switch ($type) {
            case "createuser":
                $pw = mysql_real_escape_string($_GET['pw']);
                $table = mysql_real_escape_string($_GET['table']);
                $read = mysql_real_escape_string($_GET['read']);
                $write = mysql_real_escape_string($_GET['write']);
                $writeown = mysql_real_escape_string($_GET['writeown']);
                $query3 = "INSERT INTO dict_users(`userID`, `pw`, `table`, `read`, `write`, `writeown`) values('$userID', '$pw', '$table', '$read', '$write', '$writeown') ";
                $result = mysql_query($query3);
                echo "Result: r1($result)\r\n$query3";
                break;
            default: if ($tablename == "dict_users") {
                    $query2 = "CREATE TABLE IF NOT EXISTS `" . $tablename . "` (" .
                            "`id` int(11) NOT NULL auto_increment," .
                            "`userID` char(255) default NULL," .
                            "`pw` char(255) default NULL," .
                            "`table` char(255) default NULL," .
                            "`read` char(1) default NULL," .
                            "`write` char(1) default NULL," .
                            "PRIMARY KEY  (`id`)," .
                            "KEY `userID_ndx` (`userID`)," .
                            "KEY `pw_ndx` (`pw`)," .
                            "KEY `table_ndx` (`table`)" .
                            ") ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=770";
                    $result1 = mysql_query($query2);
                    echo "Result: $result1";
                } else {
                    $query2 = "CREATE TABLE IF NOT EXISTS `" . $tablename . "` (" .
                            "`id` int(11) NOT NULL auto_increment," .
                            "`sid` char(255) default NULL," .
                            "`lemma` char(255) default NULL," .
                            "`status` char(255) default NULL," .
                            "`locked` char(255) default NULL," .
                            "`type` char(255) default NULL," .
                            "`entry` MEDIUMTEXT," .
                            "PRIMARY KEY  (`id`)," .
                            "KEY `sid_ndx` (`sid`)," .
                            "KEY `lemma_ndx` (`lemma`)," .
                            "KEY `locked_ndx` (`locked`)," .
                            "KEY `status_ndx` (`status`)," .
                            "FULLTEXT KEY `entry_ndx` (`entry`)" .
                            ") ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=770";
                    $result1 = mysql_query($query2);

                    $query2 = "INSERT INTO `" . $tablename . "`(`id`, `entry`) values(1, 'teiHeader')";
                    $result5 = mysql_query($query2);
                    $query2 = "INSERT INTO `" . $tablename . "`(`id`, `entry`) values(9, 'profile')";
                    $result5 = mysql_query($query2);
                    $query2 = "INSERT INTO `" . $tablename . "`(`id`, `entry`) values(10, 'xslt')";
                    $result5 = mysql_query($query2);
                    $query2 = "INSERT INTO `" . $tablename . "`(`id`, `entry`) values(20, 'schema')";
                    $result5 = mysql_query($query2);

                    $query2 = "CREATE TABLE IF NOT EXISTS `" . $tablename . "_lck` (" .
                            "`id` int(11) NOT NULL auto_increment," .
                            "`resp` char(255) default NULL," .
                            "`dt` char(255) default NULL," .
                            "PRIMARY KEY  (`id`)," .
                            "KEY `resp_ndx` (`resp`)," .
                            "KEY `dt_ndx` (`resp`)" .
                            ") ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=770";
                    $result2 = mysql_query($query2);

                    $query2 = "CREATE TABLE IF NOT EXISTS `" . $tablename . "_ndx` (" .
                            "`id` int(11)," .
                            "`xpath` char(255) default NULL," .
                            "`txt` text," .
                            "KEY  (`id`)," .
                            "KEY `xpath_ndx` (`xpath`)," .
                            "FULLTEXT KEY `txt_ndx` (`txt`)" .
                            ") ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=770";
                    $result3 = mysql_query($query2);

                    echo "Result: r1($result1) r2($result2) r3($result3) r4($result4) r5($result5)\r\nCreated $tablename";
                } //else
        } // switch
    } //if err == 0
} //if emthod == PUT

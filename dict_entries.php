<?php

if (function_exists('xdebug_start_error_collection')) {
    xdebug_start_error_collection();
}
ini_set('display_errors', false);
header("Content-Type: text/html; charset=utf-8");
$method = $_SERVER['REQUEST_METHOD'];
$err = 0;
$username = "dicts_ch_test";
#   $password = "#phpUS3R#";
$password = 'test';
$db_host = ""; # "" = use socket
$database = "dicts_ch_test";
$sh = '';

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

//****************************************************************
//*** METHOD: GET*************************************************
//****************************************************************
if ($method == 'GET') {

    if ($err == 0) { //is connected
        // We have to tell mysql the client's encoding. 
        // http://stackoverflow.com/questions/2943943/utf-8-mysql-and-charset
        $set_client_encoding = mysql_query("SET NAMES utf8"); //or  mysql_set_charset("utf8")

        $perm_granted = false;
        $query = $_GET['q'];
        $tablename = mysql_real_escape_string($_GET['tablename']);
        $tableuser = mysql_real_escape_string($_GET['tableuser']);
        $pw = mysql_real_escape_string($_GET['pw']);
        $acttype = $_GET['acttype'];

        mysql_select_db($database);
        if ($tableuser == 'admin') {
            $query4 = "SELECT `read`,`write` from dict_users where `userID`='$tableuser' and `pw`='$pw'";
        } else {
            $query4 = "SELECT `read`,`write` from dict_users where `table`='$tablename' and `userID`='$tableuser' and `pw`='$pw'";
        }
        $result = mysql_query($query4);
        $num = mysql_numrows($result);
        if ($num == 1) {
            $rd = mysql_result($result, 0, 0);
            $wr = mysql_result($result, 0, 1);
            if ($rd == 'y') {
                if ($wr == 'y') {
                    $perm_granted = true;
                }
            }
        }

        if ($perm_granted) {
            //*****************************************************
            //** MIN MAX ******************************************
            //*****************************************************
            switch ($acttype) {
                case 'ndx':
                    //*****************************************************
                    //** QUERY IN _NDX ************************************
                    //*****************************************************
                    //echo "aaa";
                    $entrytype = mysql_real_escape_string($_GET['entrytype']);
                    $xpath = mysql_real_escape_string($_GET['xpath']);
                    $restype = $_GET['restype'];
                    if (strlen($xpath) > 0) {
                        $xpath = " and t1.xpath like '%$xpath%' ";
                    }
                    if (strlen($entrytype) > 0) {
                        $entrytype = " and t2.type ='$entrytyp'";
                    }
                    $query = $_GET['q'];
                    $query = str_replace("_y_", "#8#", $query);
                    $query = str_replace("_x_", "#9#", $query);
                    $query = str_replace("*", "%", $query);
                    $query = mysql_real_escape_string($query);

                    $l = strlen(strstr($query, '%'));
                    if ($l === 0) {
                        $operator = '=';
                    } else {
                        $operator = 'LIKE';
                    }

                    $ndxTable = $tablename . '_ndx';

                    if (isset($_GET['key'])) {
                        $key = mysql_real_escape_string($_GET['key']);
                        $tblndx = "(SELECT `id` , `xpath` , CONVERT( AES_DECRYPT( UNHEX( `txt` ) , '$key' ) USING utf8 ) AS 'txt' FROM $ndxTable)";
                    } else {
                        $tblndx = "$ndxTable";
                    }

                    $query2 = "SELECT DISTINCT(t1.id),t2.sid,t2.lemma FROM $tblndx AS t1, $tablename AS t2 WHERE t1.txt $operator '$query' $xpath AND t1.id = t2.id $entrytype ORDER BY t2.lemma ASC";
                    //echo "$query2";
                    $result = mysql_query($query2);
                    //echo "$result";
                    $num = mysql_numrows($result);
                    //echo "$num";
                    $i = 0;
                    $sout = '';
                    if ($restype == 'xml') {
                        while ($i < $num) {
                            $id = mysql_result($result, $i, 0);
                            $query3 = "select entry from $tablename where id=$id";
                            $result3 = mysql_query($query3);
                            $text = mysql_result($result3, 0, 0);
                            $text = str_replace("y2y", ";", $text);
                            $text = str_replace("y1y", "&#x", $text);
                            //
                            $sout = $sout . "\r\n" . $text;
                            //$sout = $sout.'<item><id>'.mysql_result($result,$i, 0)."</id><sid>".mysql_result($result,$i, 1)."</sid><lemma>".mysql_result($result,$i, 2)."</lemma></item>\r\n";

                            $i++;
                        }

                        $style = $_GET['style'];
                        $dom = new domDocument();
                        $dom->load('tei_dicts_pes__003.xsl');
                        $proc = new xsltprocessor;
                        $xsl = $proc->importStylesheet($dom);

                        $xml = new DomDocument();
                        $xml->loadXML('<doc>' . $sout . '</doc>');
                        $sh = $proc->transformToXml($xml);
                        $sh = str_replace("_y_", "&#x", $sh);
                        $sh = str_replace("_x_", ";", $sh);
                        //$sh = str_replace("&amp;", "&", $sh);
                        print str_replace("&amp;", "&", $sh);
                        //echo "1";
                        //echo "<res><query>:$query2</query>\r\n$sout</res>";
                        //echo "$sh";
                    } else {
                        while ($i < $num) {
                            $sout = $sout . mysql_result($result, $i, 0) . " " . mysql_result($result, $i, 1) . " " . mysql_result($result, $i, 2) . "\r\n";
                            $i++;
                        }
                        echo "query: $query2\r\n$sout";
                    }
                    break;
                default:
                    //*****************************************************
                    //** QUERY IN TABLE ***********************************
                    //*****************************************************
                    $sid = mysql_real_escape_string($_GET['sid']);
                    $id = mysql_real_escape_string($_GET['id']);
                    $type = $_GET['type'];
                    if ($type != 'like') {
                        $type = '=';
                    }
                    $lem = mysql_real_escape_string($_GET['lem']);
                    $lem = str_replace("*", "%", $lem);
                    //$lem = 'a%';
                    $entrytype = mysql_real_escape_string($_GET['entrytype']);
                    mysql_select_db($database);

                    $et = "";
                    if (strlen($entrytype) > 0) {
                        $et = " AND type = '$entrytype'";
                    }

                    if (strlen($lem) > 0) {
                        $query2 = "SELECT id,sid,lemma from $tablename WHERE lemma $type '$lem' $et ORDER BY lemma ASC ";
                    }
                    if (strlen($sid) > 0) {
                        $query2 = "SELECT id,sid,lemma from $tablename WHERE sid $type '$sid' $et ORDER BY lemma ASC ";
                    }
                    if (strlen($id) > 0) {
                        $sh1 = strstr($id, '-');
                        if (strlen($sh1) > 0) {
                            $ar = split("-", $id);
                            if (count($ar) == 2) {
                                $id = $ar[0];
                                $id1 = $ar[1];
                                $query2 = "SELECT id,sid,lemma FROM $tablename WHERE id>=$id and id<=$id1 ORDER BY lemma ASC ";
                            }
                        } else {
                            $query2 = "SELECT id,sid,lemma FROM $tablename WHERE id $type $id $et ORDER BY lemma ASC ";
                        }
                    }
                    //echo "$query2";
                    $result = mysql_query($query2);
                    $num = mysql_numrows($result);
                    $i = 0;

                    $sout = '';
                    while ($i < $num) {
                        $sout = $sout . mysql_result($result, $i, 0) . " " . mysql_result($result, $i, 1) . " " . mysql_result($result, $i, 2) . "\r\n";
                        $i++;
                    } //while

                    echo "$sout";
                    break; // query in table (default)
            } //switch - end
        } else {  // if perm_granted
            echo "No valid identification! $query4 $tableuser (user) $pw (pw) $query";
        }
    } //is connected
}

if ($method == 'POST') {
    $tablename = mysql_real_escape_string($_POST['tablename']);
    $entry = mysql_real_escape_string(trim($_POST['entry']));
    $lemfn = mysql_real_escape_string($_POST['lemfn']);
    $sid = mysql_real_escape_string($_POST['sid']);
    $lemma = mysql_real_escape_string($_POST['lem']);
    $tradu = mysql_real_escape_string($_POST['tradu']);
    $ilen = strlen($entry); // note the mysql encoding!
    if ($err == 0) {
        mysql_select_db($database);
        $query2 = "INSERT INTO $tablename(sid, lemma, entry, tradu) values('$sid', '$lemma', '$entry', '$tradu')";
        //echo "$sid $txt $tablename $lemma $method";
        //echo "123";
        $result = mysql_query($query2);
        echo "Result: $result  Received: $ilen";
        //echo "$query2";
    }
}

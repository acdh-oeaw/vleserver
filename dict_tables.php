<?php
   $method = $_SERVER['REQUEST_METHOD'];
   $err = 0;
   $username = "phpuser";
#   $password = "#phpUS3R#";
   $password = 'IWGQdQCYMTojckOcdL5B1A=';
   $db_host =  ""; # "" = use socket
   $database = "dicts_ch";
   $queryType = $_GET['queryType'];
   $user = $_GET['user'];
   $pw = $_GET['pw'];
   
   if ($method == 'GET') {
      $link = mysql_connect($db_host, $username, $password);
      if (!$link) {
         $err = 1;
         echo "Can\'t connect to DB";
      }
      if ($err == 0) { 
         if ($queryType == 'profiles') {
            mysql_select_db($database);
            $query2 = "SELECT distinct(`table`) FROM dict_users WHERE userid = \"$user\" and pw=\"$pw\"";
            $result = mysql_query($query2);
            $num = mysql_numrows($result);
            $i=0;
            $sout = '';
            while ($i < $num) {
               $sh = mysql_result($result,$i, 0);
               $sout = $sout.$sh."\r\n";
               $i++;
            }
            echo "$sout";
         } else {
            mysql_select_db($database);
            $query2 = "SHOW TABLES";
            $result = mysql_query($query2);
            $num = mysql_numrows($result);
            $i=0;
            $sout = '';
            while ($i < $num) {
               $sh = mysql_result($result,$i, 0);
               $n1 = strstr($sh, '_lck');
               $n2 = strstr($sh, '_ndx');
               if (($n1 != '_lck')&&($n2 != '_ndx')) {
                  $sout = $sout.$sh.$n."\r\n";
               }
               $i++;
            }

            echo "$sout";
         }
      }
   
   }
  ?>

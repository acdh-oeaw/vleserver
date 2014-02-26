<?php
   $method = $_SERVER['REQUEST_METHOD'];
   $err = 0;
   $username = "root";
   $password = "alf1layla";
   $database = "dicts_ch";
   $tablename = $_GET['name'];
   
   if ($method == 'GET') {
      $link = mysql_connect('', $username, $password);
      if (!$link) {
         $err = 1;
         echo "Can\'t connect to DB";
      }
      if ($err == 0) { 
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
  ?>

<?php
   $username = "root";
   $password = "alf1layla";
   $database = "dicts_ch";
   $tablename = "frs_017";
   $sh = '';
   
   mysql_connect('', $username, $password);
   mysql_select_db($database);
   $query2 = "select min(id) from $tablename" ;
   $result = mysql_query($query2);
   $min = mysql_result($result, 0, 0);
   echo "min: $min\r\n";
   
   $query2 = "select max(id) from $tablename" ;
   $result = mysql_query($query2);
   $max = mysql_result($result, 0, 0);
   echo "max: $max\r\n";
   
   $i = $min;
   $xn = 0;
   while ($i <= $max) {
      $query2 = "SELECT entry FROM $tablename where id=$i" ;
      $result = mysql_query($query2);
      $num = mysql_numrows($result);
      $sh = mysql_result($result, 0);
      $sh1 = strstr($sh, 'type="multiWordUnit"');
      if (strlen($sh1) > 0) {
         $query2 = "UPDATE $tablename SET type='multiWordUnit' where id=$i" ;
         $result = mysql_query($query2);
         $xn = $xn + 1;
         echo "$xn\r\n";
      }
      
      //if (($i % 1000)==0)  {
      //   echo "$sh\r\n";
      //}
      $i = $i+1;
   }
  ?>

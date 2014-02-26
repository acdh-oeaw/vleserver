<?php
   $method = $_SERVER['REQUEST_METHOD'];
   $err = 0;
   $username = "root";
   $password = "alf1layla";
   $database = "dicts_ch";
   $sh = '';
   
   //echo $method;
         
   
   if ($method == 'DELETE') {
      $link = mysql_connect('', $username, $password);
      echo $tablename;
      if (!$link) {
         $err = 1;
         echo "Can\'t connect to DB";
      }
      if ($err == 0) { 
         mysql_select_db($database);
         $id = $_GET['id'];
         $tablename = $_GET['tablename'];
         $query2 = "DELETE FROM $tablename WHERE id=$id";
         $result = mysql_query($query2);
      }
   }
   
   //**************************************************************
   //** UPDATE RECORD *********************************************
   //**************************************************************
   if ($method == 'PUT') {
      $link = mysql_connect('', $username, $password);
      if (!$link) {
         $err = 1;
         echo "Can\'t connect to DB";
      }
      if ($err == 0) { 
         mysql_select_db($database);
         $id = $_GET['id'];
         $sid = $_GET['sid'];
         $lem = $_GET['lem'];
         $stype = $_GET['type'];
         $tablename = $_GET['tablename'];
         
         if ($stype == 'indices') {
            $tablename = $tablename.'_ndx';
            $fp = fopen('php://input','r');
            $ndxdata = stream_get_contents($fp);
            
            //DELETE OLD INDEX ITEMS
            $query2 = "DELETE FROM $tablename WHERE id = $id" ;
            $result = mysql_query($query2);
                     
            //INSERT NEW INDEX ITEMS
            $items = split("#2#", $ndxdata);
            $cnt = count($items);
            echo "$cnt";
            for ($i = 0; $i < $cnt; $i++) {
               $ar = split("#1#", $items[$i]);
               if (count($ar) == 2) {
                  $query3 = "INSERT INTO $tablename(id, txt, xpath) values($id, '$ar[0]', '$ar[1]')" ;
                  $result = mysql_query($query3);
                  //echo "$query3\r\n";
               } //if
            } //for
         } else {
            
            $tablename = $_GET['tablename'];
            if (strlen($sid) > 0) { $sid = ",sid='$sid'"; }
            if (strlen($lem) > 0) { $lem = ",lemma='$lem'"; }
            $addstring = $sid.$lem;
         
            $fp = fopen('php://input','r');
            $stemp = stream_get_contents($fp);
            $ilen = strlen($stemp);
            $query2 = "UPDATE $tablename SET entry='$stemp'$addstring where id=$id" ;
            $result = mysql_query($query2);
            
            
            echo "result: $result\r\nStrlen (received): $ilen\r\n$stype";
         }
      }
   }
   
   if ($method == 'GET') {
      $link = mysql_connect('', $username, $password);
      if (!$link) {
         $err = 1;
         echo "Can\'t connect to DB";
      }
      if ($err == 0) { 
         mysql_select_db($database);
         $ok = false;
         $tablename = $_GET['tablename'];
         $id = $_GET['id'];
         $sid = $_GET['sid'];
         if (strlen($sid) > 0) {
            $query2 = "SELECT entry FROM $tablename where sid=\"$sid\"" ;
            $ok = true;
         }
         
         if (strlen($id) > 0) {
            $query2 = "SELECT entry FROM $tablename where id=$id" ;
            $ok = true;
         }
         
         if ($ok) {
            $result = mysql_query($query2);
            $num = mysql_numrows($result);
            $sh = mysql_result($result, 0);
            
            if ($num == 0) {
               echo $num;
            } else {
               echo $sh;
            }
            
            
         } else {
            echo "no id, no sid";
         }
         //echo "<res>$res</res>";
         //echo "GET: $query2";
      }
      
   }
   
   //**************************************************************
   //** INSERT NEW RECORD *****************************************
   //**************************************************************
   if ($method == 'POST') { 
      $link = mysql_connect('', $username, $password);
      if (!$link) {
         $err = 1;
         echo "Can\'t connect to DB";
      }
      $tablename = $_POST['tablename'];
      $entry = trim($_POST['entry']);
      $stype = $_POST['type'];
      if ($stype == 'indices') {
         if ($err == 0) { 
            mysql_select_db($database);
            $id = $_POST['id'];
            $tablename = $tablename.'_ndx';
            
            //INSERT NEW INDEX ITEMS
            $items = split("#2#", $entry);
            $cnt = count($items);
            //echo "$cnt";
            for ($i = 0; $i < $cnt; $i++) {
               $ar = split("#1#", $items[$i]);
               if (count($ar) == 2) {
                  //$query3 = "INSERT INTO $tablename(id, xpath, txt) values($id, '$ar[0]', '$ar[1]')" ;
                  $query3 = "INSERT INTO $tablename(id, txt, xpath) values($id, '$ar[0]', '$ar[1]')" ;
                  $result = mysql_query($query3);
                  echo "$query3\r\n";
               }
            }
            //echo "Result: $result";
         }
      } else {
         $sid = $_POST['sid'];
         $lemma = $_POST['lem'];
         $tradu = $_POST['tradu'];
         $ilen = strlen($entry);
         if ($err == 0) { 
            mysql_select_db($database);
            $query2 = "INSERT INTO $tablename(sid, lemma, entry, tradu, type) values('$sid', '$lemma', '$entry', '$tradu', '$stype')" ;
            //echo "$sid $txt $tablename $lemma $method";
            //echo "123";
            $result = mysql_query($query2);
         
            //GET NEW ID
            $query2 = "SELECT id FROM $tablename where sid=\"$sid\"" ;
            $result1 = mysql_query($query2);
            $num = mysql_numrows($result1);
            $newid = mysql_result($result1, 0);
            echo "Result: $result  \r\nReceived: $ilen\r\nnewid: $newid\r\nrows in result: $num";
            //echo "$query2";
         }
      }
   
   }
  ?>

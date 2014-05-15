<?php
   ini_set('display_errors', false); 
   $method = $_SERVER['REQUEST_METHOD'];
   $err = 0;
   $username = "phpuser";
#   $password = "#phpUS3R#";
   $password = 'IWGQdQCYMTojckOcdL5B1A=';
   $database = "dicts_ch";
   $db_host =  ""; # "" = use socket
   $sh = '';
   
   //echo $method;
         
   
   if ($method == 'DELETE') {
      $link = mysql_connect($db_host, $username, $password);
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

         $tablename = "$tablename".'_ndx';
         $query2 = "DELETE FROM $tablename WHERE id=$id";
         $result = mysql_query($query2);
      }
   }
   
   //**************************************************************
   //** UPDATE RECORD *********************************************
   //**************************************************************
   if ($method == 'PUT') {
      $link = mysql_connect($db_host, $username, $password);
      if (!$link) {
         $err = 1;
         echo "Can\'t connect to DB";
      }
      
      if ($err == 0) { 
         mysql_select_db($database);
         $id = $_GET['id'];
         $sid = $_GET['sid'];
         $status = $_GET['status'];
         $lem = $_GET['lem'];
         $slen = $_GET['len'];
         $stype = $_GET['type'];
         $tablename = $_GET['tablename'];
         $lastlockedID = $_GET['lastlockedID'];
         
         switch ($stype) {
             case "unlock": if (strlen($lastlockedID) > 0) {
                              $query2 = "UPDATE $tablename SET locked='' where id=$lastlockedID" ;
                              $result4 = mysql_query($query2);
                              echo "Unlock result: $result4";
                            } 
                            break;
            case "indices": 
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
                            break;
             case "append": $tablename = $_GET['tablename'];

                            $fp = fopen('php://input','r');
                            $stemp = stream_get_contents($fp);
                            $ilen = strlen($stemp);
                            echo "result: $result\r\nStrlen (received): $ilen\r\n$stype\r\n$query2";
                            break;
                   default: $tablename = $_GET['tablename'];
                            $stype = $_GET['type'];
                            if (strlen($sid) > 0) { $sid = ",sid='$sid'"; }
                            if (strlen($lem) > 0) { $lem = ",lemma='$lem'"; }
                            if (strlen($stype) > 0) { $stype = ",type='$stype'"; }
                            if (strlen($status) > 0) { $status = ",status='$status'"; }
                            $addstring = $sid.$lem.$stype.$status;
                            
                            $fp = fopen('php://input','r');
                            $stemp = stream_get_contents($fp);
                            $ilen = strlen($stemp);
                            $query2 = "UPDATE $tablename SET entry='$stemp'$addstring where id=$id" ;
                            
                            $protFile = "../logs/prot_001.txt";
                            $fh = fopen($protFile, 'a');
                            fwrite($fh, $query2."\r\n");
                            fclose($fh);
                            
                            $warn = "";
                            $wherePos = strrpos($query2, 'where');
                            $idLen = strlen($id);
                            if (is_numeric($id)) { $isNumber = 'is number'; } else  { $isNumber = 'is no number'; } 
                            if (is_numeric($id)) {
                              if ($idLen > 0) {
                                 if ($wherePos > 10) {
                                    $result = mysql_query($query2);
                                    $warn = "Saved";
                                 } else { $warn = "Did not save";}
                              } else { $warn = "Did not save";}
                            } else { $warn = "Did not save";}
                            echo "result: $result\r\nStrlen (received): $ilen\r\n(sent): $slen\r\n$stype\r\nwp=$wherePos\r\n$idLen\r\n$isNumber\r\n$warn";
         }  //switch
         
         
      } //if err
   } //if PUT
   
   if ($method == 'GET') {
      $text = '';
      $link = mysql_connect($db_host, $username, $password);
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
         $username = $_GET['user'];
         $lock = $_GET['lock'];
         $huquq = $_GET['huquq'];
         $password = $_GET['pw'];
         $lastlockedID = $_GET['lastlockedID'];
         
         if (strlen($sid) > 0) {
            $query2 = "SELECT entry FROM $tablename where sid=\"$sid\"" ;
            $ok = true;
         }
         

         if (strlen($id) > 0) {
            $query2 = "SELECT entry,locked FROM $tablename where id=$id";
            $ok = true;
            
         }
         
         if ($ok) {
            $result = mysql_query($query2);
            $num = mysql_numrows($result);
            $text = mysql_result($result, 0, 0);
            $locked = mysql_result($result, 0, 1);
            $lockRecord = false;
            $belongsTouser = false;
            if (strlen($locked)>0) {
               if ($locked == $username) {
                  $belongsTouser = true;
               } else {
                  $belongsTouser = false;
               }
            } else {
               if ($lock=='true') {
                  $lockRecord = true;
                  $belongsTouser = true;
               }
            }
            
            $mayLock = true;
            if (strlen($huquq) > 0) { //this can be removed once old versions are not used any more
               if ($huquq == 'n-y-y') { 
                  $mayLock = true; 
               } else {
                  $ss = '<fs type="create"><f name="who"><symbol value="';
                  $slen = strlen($ss);
                  $ipos = strpos($text, $ss);
                  if ($ipos) {
                     $ipos2 = strpos($text, '"', $ipos + $slen + 1);
                     $screator = substr($text, $ipos + strlen($ss), $ipos2 - ($slen + $ipos));
                  }
                  if ($screator == $username) {
                     $mayLock = true;
                  } else {
                     $mayLock = false;
                  }
               }
            }
            
               if (strlen($lastlockedID)>0) {
                  $query2 = "UPDATE $tablename SET locked='' where id=$lastlockedID" ;
                  $result3 = mysql_query($query2);
               }
            if ($mayLock) {
               if ($lockRecord == true) {
                  $query2 = "UPDATE $tablename SET locked='$username' where id=$id" ;
                  $result2 = mysql_query($query2);
               }
            }
            
            if ($num == 0) {
               echo $num;
            } else {
               echo "$lock".' lastlockedID('.$lastlockedID.') -islockedby('.$locked.') -locked('.$result2.') -unlocked('.$result3.")\r\n".$text;
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
      $link = mysql_connect($db_host, $username, $password);
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
         $id = $_POST['id'];
         $lemma = $_POST['lem'];
         $ilen = strlen($entry);
         if ($err == 0) { 
            mysql_select_db($database);
            if (strlen($id) > 0) {
               $query2 = "INSERT INTO $tablename(id, sid, lemma, entry, type) values('$id', '$sid', '$lemma', '$entry', '$stype')" ;
            } else {
               $query2 = "INSERT INTO $tablename(sid, lemma, entry, type) values('$sid', '$lemma', '$entry', '$stype')" ;
            }
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

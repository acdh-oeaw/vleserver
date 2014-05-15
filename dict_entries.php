<?php
header("Content-Type: text/txt; charset=utf-8");
   $method = $_SERVER['REQUEST_METHOD'];
   $err = 0;
   $username = "phpuser";
#   $password = "#phpUS3R#";
   $password = 'IWGQdQCYMTojckOcdL5B1A=';
   $db_host = ""; # "" = use socket
   $database = "dicts_ch";
   $sh = '';
   
   //echo $method;
         
   
   //****************************************************************
   //*** METHOD: GET*************************************************
   //****************************************************************
   if ($method == 'GET') {
      $link = mysql_connect($db_host, $username, $password);
      if (!$link) {
         $err = 1;
         echo "Can\'t connect to DB";
      }
      
      if ($err == 0) { //is connected

	// We have to tell mysql the client's encoding. 
      	// http://stackoverflow.com/questions/2943943/utf-8-mysql-and-charset
      	$set_client_encoding = mysql_query("SET NAMES utf8"); //or  mysql_set_charset("utf8")

         $perm_granted = false;
         $query = $_GET['q'];
         $tablename = $_GET['tablename'];
         $tableuser = $_GET['tableuser'];
         $pw = $_GET['pw'];
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
            switch($acttype) {
               case 'ndx':
                  //*****************************************************
                  //** QUERY IN _NDX ************************************
                  //*****************************************************
                  //echo "aaa";
                  $entrytype = $_GET['entrytype'];
                  $xpath = $_GET['xpath'];
                  $restype = $_GET['restype'];
                  if (strlen($xpath) > 0) { $xpath = " and t1.xpath like '%$xpath%' "; }
                  if (strlen($entrytype) > 0 ) { $entrytype = " and t2.type = \"$entrytype\""; }
                  $query = $_GET['q'];
                  $query = str_replace("_y_", "#8#", $query);
                  $query = str_replace("_x_", "#9#", $query);
                  $query = str_replace("*", "%", $query);
                  
                  $l = strlen(strstr($query, '%'));
                  if ($l = 0) {
                     $operator = '=';
                  } else {
                     $operator = 'like';
                  }
                  
                  $tblndx = $tablename.'_ndx';
                  $query2 = "select distinct(t1.id),t2.sid,t2.lemma from $tblndx as t1,$tablename as t2 where t1.txt $operator '$query' $xpath and t1.id = t2.id $entrytype" ;
                  //echo "$query2";
                  $result = mysql_query($query2);
                  //echo "$result";
                  $num = mysql_numrows($result);
                  //echo "$num";
                  $i = 0;
                  $sout = '';
                  if ($restype=='xml') {
                     while ($i < $num) {
                        $id = mysql_result($result,$i, 0);
                        $query3 = "select entry from $tablename where id=$id";
                        $result3 = mysql_query($query3);
                        $text = mysql_result($result3, 0, 0);
                        $text = str_replace("y2y", ";", $text);
                        $text = str_replace("y1y", "&#x", $text);
                        //
                        $sout = $sout."\r\n".$text;
                        //$sout = $sout.'<item><id>'.mysql_result($result,$i, 0)."</id><sid>".mysql_result($result,$i, 1)."</sid><lemma>".mysql_result($result,$i, 2)."</lemma></item>\r\n";
                        
                        $i++;
                     }
                     
                     $style = $_GET['style'];
                     $dom = new domDocument();
                     $dom->load('tei_dicts_pes__003.xsl');
                     $proc = new xsltprocessor;
                     $xsl = $proc->importStylesheet($dom);
            
                     $xml = new DomDocument();
                     $xml->loadXML('<doc>'.$sout.'</doc>');
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
                        $sout = $sout.mysql_result($result,$i, 0)." ".mysql_result($result,$i, 1)." ".mysql_result($result,$i, 2)."\r\n";
                        $i++;
                     }
                     echo "query: $query2\r\n$sout";
                  }
                  break;
               default:
                  //*****************************************************
                  //** QUERY IN TABLE ***********************************
                  //*****************************************************
                  $sid = $_GET['sid'];
                  $id = $_GET['id'];
                  $type = $_GET['type'];
                     if ($type != 'like') { $type = '='; }
                  $lem = $_GET['lem'];
                     $lem = str_replace("*", "%", $lem);
                     //$lem = 'a%';
                  $entrytype = $_GET['entrytype'];
                  mysql_select_db($database);
            
                  $et = "";
                  if (strlen($entrytype) > 0 ) { $et = " and type = \"$entrytype\""; }
            
                  if (strlen($lem) > 0) {
                     $query2 = "select id,sid,lemma from $tablename where lemma $type \"$lem\" $et " ;
                  }
                  if (strlen($sid) > 0) {
                     $query2 = "select id,sid,lemma from $tablename where sid $type \"$sid\" $et " ;
                  }
                  if (strlen($id) > 0) {
                     $sh1 = strstr($id, '-');
                     if (strlen($sh1) > 0 ) {
                        $ar = split("-", $id);
                        if (count($ar) == 2) {
                           $id = $ar[0];
                           $id1 = $ar[1];
                           $query2 = "SELECT id,sid,lemma FROM $tablename where id>=$id and id<=$id1" ;
                        }
                     } else {
                        $query2 = "select id,sid,lemma from $tablename where id $type $id $et " ;
                     }
                  }
                  //echo "$query2";
                  $result = mysql_query($query2);
                  $num = mysql_numrows($result);
                  $i = 0;
         
                  $sout = '';
                  while ($i < $num) {
                     $sout = $sout.mysql_result($result,$i, 0)." ".mysql_result($result,$i, 1)." ".mysql_result($result,$i, 2)."\r\n";
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
      $link = mysql_connect($db_host, $username, $password);
      if (!$link) {
         $err = 1;
         echo "Can\'t connect to DB";
      }
      $tablename = $_POST['tablename'];
      $entry = trim($_POST['entry']);
      $lemfn = $_POST['lemfn'];
      $sid = $_POST['sid'];
      $lemma = $_POST['lem'];
      $tradu = $_POST['tradu'];
      $ilen = strlen($entry);
      if ($err == 0) { 
         mysql_select_db($database);
         $query2 = "INSERT INTO $tablename(sid, lemma, entry, tradu) values('$sid', '$lemma', '$entry', '$tradu')" ;
         //echo "$sid $txt $tablename $lemma $method";
         //echo "123";
         $result = mysql_query($query2);
         echo "Result: $result  Received: $ilen";
         //echo "$query2";
      }
   
   }
  ?>

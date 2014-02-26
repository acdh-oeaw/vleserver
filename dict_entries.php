<?php
header("Content-Type: text/txt; charset=utf-8");
   $method = $_SERVER['REQUEST_METHOD'];
   $err = 0;
   $username = "root";
   $password = "alf1layla";
   $database = "dicts_ch";
   $sh = '';
   
   //echo $method;
         
   
   if ($method == 'DELETE') {
   }
   
   if ($method == 'PUT') {
   }
   
   if ($method == 'GET') {
      $link = mysql_connect('', $username, $password);
      if (!$link) {
         $err = 1;
         echo "Can\'t connect to DB";
      }
      if ($err == 0) { 
         $tablename = $_GET['tablename'];
         $acttype = $_GET['acttype'];
         //*****************************************************
         //** MIN MAX ******************************************
         //*****************************************************
         if ($acttype == 'minmax') {
            mysql_select_db($database);
            $query2 = "select min(id) from $tablename" ;
            $result = mysql_query($query2);
            $min = mysql_result($result, 0, 0);
            
            $query2 = "select max(id) from $tablename" ;
            $result = mysql_query($query2);
            $max = mysql_result($result, 0, 0);
            echo $min.' '.$max;
         } else {
            //*****************************************************
            //** NDX **********************************************
            //*****************************************************
            if ($acttype == 'ndx') {
               //echo "aaa";
               mysql_select_db($database);
               $entrytype = $_GET['entrytype'];
               $xpath = $_GET['xpath'];
               if (strlen($xpath) > 0) { $xpath = " and t1.xpath like '%$xpath%' "; }
               if (strlen($entrytype) > 0 ) { $entrytype = " and t2.type = \"$entrytype\""; }
               $query = $_GET['q'];
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
               while ($i < $num) {
                  $sout = $sout.mysql_result($result,$i, 0)." ".mysql_result($result,$i, 1)." ".mysql_result($result,$i, 2)."\r\n";
                  $i++;
               }

               
               echo "query:$query2\r\n$sout";
               
            } else {
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
                  $query2 = "select id,sid,lemma from $tablename where id $type $id $et " ;
               }
               //echo "$query2";
               $result = mysql_query($query2);
               $num = mysql_numrows($result);
               $i = 0;
         
               $sout = '';
               while ($i < $num) {
                  $sout = $sout.mysql_result($result,$i, 0)." ".mysql_result($result,$i, 1)." ".mysql_result($result,$i, 2)."\r\n";
                  $i++;
               }

               echo "$sout";
            }
         }
      }
      
   }
   
   if ($method == 'POST') {
      $link = mysql_connect('', $username, $password);
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

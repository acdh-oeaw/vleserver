<?php
   $method = $_SERVER['REQUEST_METHOD'];
   $err = 0;
   $username = "root";
   $password = "alf1layla";
   $database = "dicts_ch";
   $tablename = $_GET['tablename'];
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
         $query2 = "DROP TABLE `".$tablename."` ";
         $result1 = mysql_query($query2);

         $query2 = "DROP TABLE `".$tablename."_lck` ";
         $result2 = mysql_query($query2);

         $query2 = "DROP TABLE `".$tablename."_ndx` ";
         $result2 = mysql_query($query2);
         echo "Result: $result1 result2\r\nDeleted $tablename";
      }
   }
   
   if ($method == 'PUT') {
      $link = mysql_connect('', $username, $password);
      //echo $tablename;
      if (!$link) {
         $err = 1;
         echo "Can\'t connect to DB";
      }
      if ($err == 0) { 
         mysql_select_db($database);
         $query2 = "CREATE TABLE IF NOT EXISTS `".$tablename."` (".
            "`id` int(11) NOT NULL auto_increment,".
            "`sid` char(255) default NULL,".
            "`lemma` char(255) default NULL,".
            "`tradu` char(255) default NULL,".
            "`prop` char(255) default NULL,".
            "`resp` char(255) default NULL,".
            "`status` char(255) default NULL,".
            "`type` char(255) default NULL,".
            "`entry` text,".
            "PRIMARY KEY  (`id`),".
            "KEY `sid_ndx` (`sid`),".
            "KEY `lemma_ndx` (`lemma`),".
            "KEY `tradu_ndx` (`tradu`),".
            "KEY `resp_ndx` (`resp`),".
            "KEY `prop_ndx` (`prop`),".
            "KEY `status_ndx` (`status`),".
            "FULLTEXT KEY `entry_ndx` (`entry`)".
            ") ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=770" ;
         $result1 = mysql_query($query2);

         $query2 = "CREATE TABLE IF NOT EXISTS `".$tablename."_lck` (".
            "`id` int(11) NOT NULL auto_increment,".
            "`resp` char(255) default NULL,".
            "`dt` char(255) default NULL,".
            "PRIMARY KEY  (`id`),".
            "KEY `resp_ndx` (`resp`),".
            "KEY `dt_ndx` (`resp`)".
            ") ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=770" ;
         $result2 = mysql_query($query2);

         $query2 = "CREATE TABLE IF NOT EXISTS `".$tablename."_ndx` (".
            "`id` int(11),".
            "`xpath` char(255) default NULL,".
            "`txt` text,".
            "KEY  (`id`),".
            "KEY `xpath_ndx` (`xpath`),".
            "FULLTEXT KEY `txt_ndx` (`txt`)".
            ") ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=770" ;
         $result2 = mysql_query($query2);
         echo "Result: $result1 $result2\r\nCreated $tablename";
      }
   
   }
  ?>

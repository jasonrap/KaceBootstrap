<?php
//DB connection statements
$dbh=mysql_connect ("someKbox", "R1", "somePassword") or die
('I cannot connect to the database because: ' .
mysql_error());
mysql_select_db ("ORG1");


?>

<?php
#start RT queries
#for coming up with the total for today, average, and max
$currentlyOpen="";
include ('includes/config.php');

$theDate = date("Y-m-d");
$theYear = date("Y");

$query = "select COUNT(NAME) as counted from HD_TICKET Inner Join HD_STATUS on HD_STATUS.ID = HD_TICKET.HD_STATUS_ID
where (HD_STATUS.NAME not like '%Spam%')
AND (HD_STATUS.NAME not like '%Server Status Report%')
AND (HD_STATUS.STATE not like '%Closed%')
order by counted desc";


$result = mysql_query($query);
$num = mysql_num_rows($result);
$i = 0;

while ($i < $num)
{
	$currentlyOpen = mysql_result($result,$i,"counted");
	#$name = mysql_result($result,$i,"NAME");
	$i++;
}

?>

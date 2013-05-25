<?
include ('includes/config.php');

$query="Select
  Avg(s1.cct) As max_cct
From
  (Select
    Count(HD_TICKET.ID) As cct
  From
    HD_STATUS Inner Join
  HD_TICKET On HD_TICKET.HD_STATUS_ID = HD_STATUS.ID
  where (HD_STATUS.NAME = 'Closed')
  and (
       	(HD_STATUS.NAME not like '%spam%')
AND (TIME_CLOSED >= ( CURDATE() - INTERVAL 30 DAY ))
)
  Group By
    Date(TIME_CLOSED)) As s1";


$result = mysql_query($query);
if (!$result) {
    echo 'Could not run query: ' . mysql_error();
    exit;
}
$row = mysql_fetch_row($result);
$theAverage = $row[0];
$theAverage = round($theAverage, 2);


#echo "<h1>$theAverage</h1>Average closed ";

$averageClosed = $row[0];
echo "</td>";






?>

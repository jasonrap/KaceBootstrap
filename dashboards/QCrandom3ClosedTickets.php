<?php
############################################################
# Based on tholmes's idea and query at:
#  http://www.itninja.com/blog/view/simple-quality-analysis-report
# Description:
#   Pulls a random 3 tickets that were closed in the last calendar week
#   for each member of the 'All Ticket Owners' group
############################################################

	$numOfTickets = 3; // how many tickets for each user you want to Q/C
?>
<h2>Random <?php echo $numOfTickets; ?> Q/C Tickets by Ticket Owner</h2>
<table class="table table-striped table-bordered table-head-bordered-bottom table-condensed">
  <thead>
    <tr>
	  <th class="span1">&nbsp;</th>
      <th class="span1">Ticket ID</th>
      <th class="span3">Category</th>
      <th class="span1">Submitter</th>
	  <th class="span5">Title</th>
    </tr>
  </thead>
  <tbody>

<?php
$query1 = "
SELECT u.ID,u.FULL_NAME
 FROM `USER` u
  LEFT JOIN USER_LABEL_JT uljt ON (uljt.USER_ID=u.ID)
  LEFT JOIN LABEL l ON (uljt.LABEL_ID=l.ID)
 WHERE l.NAME = 'All Ticket Owners'
  AND u.HD_DEFAULT_QUEUE_ID >= 0
 ORDER BY u.FULL_NAME
";
$result1 = mysql_query($query1);
$numUsers = mysql_numrows($result1);
$userList = array();
$i = 0;
while( ($row = mysql_fetch_assoc($result1)) )
{
	$userList[++$i] = array( 'id'=>$row['ID'], 'user'=>$row['FULL_NAME'] );

	$query1 = "
SELECT
	HD_TICKET.ID,
	HD_CATEGORY.NAME AS CATEGORY,
	S.FULL_NAME AS SUBMITTER_NAME,
	HD_TICKET.TITLE,
	HD_STATUS.NAME AS STATUS,
	HD_PRIORITY.NAME AS PRIORITY
FROM
	HD_TICKET
	JOIN HD_CATEGORY ON (HD_CATEGORY.ID = HD_TICKET.HD_CATEGORY_ID)
	LEFT JOIN USER S ON (S.ID = HD_TICKET.SUBMITTER_ID)
	LEFT JOIN USER O ON (O.ID = HD_TICKET.OWNER_ID)
	LEFT JOIN HD_PRIORITY ON (HD_PRIORITY.ID = HD_TICKET.HD_PRIORITY_ID) 
	JOIN HD_STATUS ON (HD_STATUS.ID = HD_TICKET.HD_STATUS_ID)
WHERE
	(HD_TICKET.HD_QUEUE_ID = $mainQueueID)
	AND ((O.ID = $row[ID])
	AND (DATE(HD_TICKET.TIME_CLOSED) >= DATE_SUB(CURDATE(), INTERVAL DAYOFWEEK(CURDATE()) - 1 + (7*1) DAY)
	AND DATE(HD_TICKET.TIME_CLOSED) < DATE_ADD(CURDATE(), INTERVAL DAYOFWEEK(CURDATE()) - 1 DAY) )
	AND (HD_STATUS.STATE = 'Closed'))
ORDER BY
	RAND()
LIMIT
	$numOfTickets
";

	$subresult = mysql_query($query1);
	$num = mysql_numrows($subresult);
	if ( $num == 0 )
		continue;

	echo "<tr><th colspan='5'>$row[FULL_NAME]</th></tr>\n";

	$i = 0;
	while ( ($row = mysql_fetch_assoc($subresult)) != NULL )
	{
		$ID = stripslashes($row['ID']);
		$Title = stripslashes($row['TITLE']);
		$Category = stripslashes($row['CATEGORY']);
		$Submitter = stripslashes($row['SUBMITTER_NAME']);
		$Status = stripslashes($row['STATUS']);
		$Priority = stripslashes($row['PRIORITY']);

		$StatusSpan="";
		if ($Status=="Stalled")
			$StatusSpan="<span class='label label-warning'>$Status</span>";

		$PriortySpan="";
		if ( $Priority == "High" )
			$PriortySpan = "<span class='label label-important'><i class='icon-exclamation-sign icon-white'></i>High</span>";
		else if ( $Priority == "Low" )
			$PriortySpan = "<span class='label'>Low</span>";

		echo "<tr>\n<td></td>\n";
		echo "<td><a href='http://$KaceBoxDNS/adminui/ticket.php?ID=$ID' target='_blank'>$ID</a> $StatusSpan $PriortySpan</td>\n";
		echo "<td>$Category</td>\n";
		echo "<td>$Submitter</td>\n";
		echo "<td>$Title</td>\n";
		echo "</tr>\n";
		$i++;
	}
}

echo "</tbody></table>\n";
?>
<h6>*** Ticket Owners with no tickets last week omitted.</h6>

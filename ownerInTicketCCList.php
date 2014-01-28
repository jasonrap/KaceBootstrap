<?php
##
## No edits necessary. Assumes $user is user's full name and passed by being set previously.
##
// Commented out so we don't silently fail, so we can catch easier in GUI-browse QC.
/*if ( !isset($user) ) // prevent errors.
	return;*/
?>
<h2><?php echo $user ?>'s CC List Tickets</h2>
<table class="table table-striped table-bordered table-head-bordered-bottom table-condensed">
  <thead>
    <tr>
      <th class=span1>Ticket ID</th>
      <th class=span3>Title</th>
	  <!--<th class=span2>Department</th>-->
      <th class=span1>Submitter</th>
      <th class=span1>Created</th>
      <th class=span1>Modified</th>
    </tr>
  </thead>
  <tbody>



<?php

$query1 = "
SELECT FULL_NAME AS ccOwner, subq.*
 FROM USER
 LEFT JOIN (
	SELECT
	HD_TICKET.ID as ID,
	HD_TICKET.OWNER_ID as OwnerID,
	HD_TICKET.TITLE as Title,
	HD_STATUS.NAME AS Status,
	HD_TICKET.CUSTOM_FIELD_VALUE0 as Department,
	HD_TICKET.CC_LIST,
	HD_TICKET.CREATED as Created,
	HD_TICKET.MODIFIED as Modified,
	HD_PRIORITY.NAME AS Priority,
	S.FULL_NAME AS Submitter
	FROM HD_TICKET JOIN HD_STATUS ON (HD_STATUS.ID = HD_TICKET.HD_STATUS_ID) 
	JOIN HD_PRIORITY ON (HD_PRIORITY.ID = HD_TICKET.HD_PRIORITY_ID) 
	LEFT JOIN USER S ON (S.ID = HD_TICKET.SUBMITTER_ID) 
	WHERE ((HD_TICKET.HD_QUEUE_ID > 0) AND 
	(HD_STATUS.STATE not like '%Closed%'))
 ) AS subq ON (subq.OwnerID != USER.ID AND subq.CC_LIST LIKE CONCAT('%',USER.EMAIL,'%'))
 WHERE FULL_NAME LIKE '%$user%'
ORDER BY subq.Created;
";


$result1 = mysql_query($query1);
$num = mysql_numrows($result1);
$i = 0;
while ($i < $num)
{
	$ID = mysql_result($result1,$i,"ID");
	$Title = mysql_result($result1,$i,"Title");
	$Status = mysql_result($result1,$i,"Status");        
	$Department = mysql_result($result1,$i,"Department");
	$Created = mysql_result($result1,$i,"Created");
	$Modified = mysql_result($result1,$i,"Modified");
	$Priority = mysql_result($result1,$i,"Priority");
	$Owner = mysql_result($result1,$i,"ccOwner");	
	$Submitter = mysql_result($result1,$i,"Submitter");

	$ID = stripslashes($ID);
	$Title = stripslashes($Title);
	$Status = stripslashes($Status);
	$Department = stripslashes($Department);
	$Created = stripslashes($Created);	
	$Modified = stripslashes($Modified);
	$Priority = stripslashes($Priority);
	$Owner = stripslashes($Owner);
	$Submitter = stripslashes($Submitter);


	$StatusSpan="";
	if ($Status=="Stalled")
	{
		$StatusSpan="<span class='label label-warning'>$Status</span>";
	}

	$PriortySpan="";
	if ($Priority=="High")
	{
		$PriortySpan="<span class='label label-important'><i class='icon-exclamation-sign icon-white'></i>High</span>";
	}

	if ($Priority=="Low")
	{
		$PriortySpan="<span class='label'>Low</span>";
	}


	echo "<tr><td><a href='http://$KaceBoxDNS/adminui/ticket.php?ID=$ID' target='_blank'>$ID</a> $StatusSpan $PriortySpan</td> \n";
	echo "<td>$Title</td>\n";
	//echo "<td>$Department</td> \n";
	echo "<td>$Submitter</td> \n";
	echo "<td>$Created</td> \n";
	echo "<td>$Modified</td> \n";
	echo "</tr> \n";
	$i++;
}
                    
echo "</tbody></table> \n";
?>

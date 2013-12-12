<?php
$user="Unassigned";
?>

<h2><?php echo $user ?> Support Tickets</h2>
<table class="table table-striped table-bordered table-condensed">
  <thead>
    <tr>
      <th class=span2>Ticket ID</th>
	  <th class=span1>Queue</th>
      <th class=span2>Title</th>
      <th class=span2>Custom Field 0</th><!--Original author used this field for Department-->
      <th class=span2>Submitter</th>
      <th class=span1>Created</th>
      <th class=span1>Modified</th>
	</tr>
  </thead>
  <tbody>

  
<?php
$query1 = "
SELECT 
HD_TICKET.ID as ID, 
HD_TICKET.TITLE as Title, 
HD_STATUS.NAME AS Status, 
HD_TICKET.CUSTOM_FIELD_VALUE0 as Cust0, 
HD_TICKET.CREATED as Created, 
HD_TICKET.MODIFIED as Modified, 
HD_PRIORITY.NAME AS Priority, 
HD_QUEUE.NAME As QueueName,
O.FULL_NAME AS Owner, 
S.FULL_NAME AS Submitter  
FROM HD_TICKET  JOIN HD_STATUS ON (HD_STATUS.ID = HD_TICKET.HD_STATUS_ID) 
JOIN HD_PRIORITY ON (HD_PRIORITY.ID = HD_TICKET.HD_PRIORITY_ID) 
LEFT JOIN USER O ON (O.ID = HD_TICKET.OWNER_ID) LEFT JOIN USER S ON (S.ID = HD_TICKET.SUBMITTER_ID) 
LEFT JOIN HD_QUEUE ON (HD_QUEUE.ID=HD_TICKET.HD_QUEUE_ID)
WHERE (HD_TICKET.HD_QUEUE_ID > 0) AND 
((O.FULL_NAME is null) AND 
(HD_STATUS.NAME not like '%Closed%'))  
ORDER BY CREATED desc
";


$result1 = mysql_query($query1);
$num = mysql_numrows($result1);
$i = 0;
while ($i < $num)
{
	$ID = mysql_result($result1,$i,"ID");
	$Title = mysql_result($result1,$i,"Title");
	$Status = mysql_result($result1,$i,"Status");        
	$Cust0 = mysql_result($result1,$i,"Cust0");
	$QueueName = mysql_result($result1,$i,"QueueName");
	$Created = mysql_result($result1,$i,"Created");
	$Modified = mysql_result($result1,$i,"Modified");
	$Priority = mysql_result($result1,$i,"Priority");
	$Owner = mysql_result($result1,$i,"Owner");	
	$Submitter = mysql_result($result1,$i,"Submitter");


	$ID = stripslashes($ID);
	$Title = stripslashes($Title);
	$Status = stripslashes($Status);
	$Cust0 = stripslashes($Cust0);
	$QueueName = stripslashes($QueueName);
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
	echo "<td>$QueueName</td> \n";
	echo "<td>$Title</td>\n";
	echo "<td>$Cust0</td> \n";
	echo "<td>$Submitter</td> \n";
	echo "<td>$Created</td> \n";
	echo "<td>$Modified</td> \n";

	echo "</tr> \n";
	$i++;
}

echo "</tbody></table> \n";
?>

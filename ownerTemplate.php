<?
##########################################
#Key in username to search in SQL for owner
#note this can be their last name or first name
#note this will also show up as a h2 tag to indicate whose queue you are looking at.  
$user="";

##########################################





?>

<h2><? echo $user ?>'s tickets</h2>
<table class="table table-striped table-bordered table-condensed">
  <thead>
    <tr>
      <th class=span2>Ticket ID</th>
      <th class=span2>Title</th>
        <th class=span2>Department</th>
        <th class=span2>Submitter</th>

        
        
        <th class=span1>Created</th>
        <th class=span1>Modified</th>


    </tr>
  </thead>
  <tbody>



<?

 $query1 = "
SELECT 
HD_TICKET.ID as ID, 
HD_TICKET.TITLE as Title, 
HD_STATUS.NAME AS Status, 
HD_TICKET.CUSTOM_FIELD_VALUE0 as Department, 
HD_TICKET.CREATED as Created, 
HD_TICKET.MODIFIED as Modified, 
HD_PRIORITY.NAME AS Priority, 
O.FULL_NAME AS Owner, 
S.FULL_NAME AS Submitter  
FROM HD_TICKET  JOIN HD_STATUS ON (HD_STATUS.ID = HD_TICKET.HD_STATUS_ID) 
JOIN HD_PRIORITY ON (HD_PRIORITY.ID = HD_TICKET.HD_PRIORITY_ID) 
LEFT JOIN USER O ON (O.ID = HD_TICKET.OWNER_ID) LEFT JOIN USER S ON (S.ID = HD_TICKET.SUBMITTER_ID) 
WHERE (HD_TICKET.HD_QUEUE_ID = $mainQueue) AND 
((O.FULL_NAME like '%$user%') AND 
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
        $Department = mysql_result($result1,$i,"Department");
        $Created = mysql_result($result1,$i,"Created");
        $Modified = mysql_result($result1,$i,"Modified");
        $Priority = mysql_result($result1,$i,"Priority");
        $Owner = mysql_result($result1,$i,"Owner");	
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
echo "<td>$Department</td> \n";
echo "<td>$Submitter</td> \n";




echo "<td>$Created</td> \n";
echo "<td>$Modified</td> \n";

echo "</tr> \n";
$i ++;

        }
                      
echo "</tbody></table> \n";

?>


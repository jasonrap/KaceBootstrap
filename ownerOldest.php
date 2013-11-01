
<?
#shows who owns the oldest ticket

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
WHERE ((HD_STATUS.NAME not like '%Closed%') and O.FULL_NAME is not null)
ORDER BY HD_TICKET.ID
Limit 1

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



echo "<img src='includes/img/50px_golden-star.jpg'>Congratulations $Owner, you own the 
oldest ticket (<a 
href='http://$KaceBoxDNS/adminui/ticket.php?ID=$ID' 
target='_blank'>$ID</a>)!";

$i ++;

        }
                  

?>


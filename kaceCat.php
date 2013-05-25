
<?
include ('includes/config.php');

$query="Select
  Count(HD_TICKET.ID) As counted,
  HD_CATEGORY.NAME as name
From
  HD_CATEGORY Inner Join
  HD_TICKET On HD_TICKET.HD_CATEGORY_ID = HD_CATEGORY.ID Inner Join
  HD_STATUS On HD_TICKET.HD_STATUS_ID = HD_STATUS.ID
  where HD_STATUS.NAME != 'Closed'
Group By
  HD_CATEGORY.NAME
order by counted desc   ";


$result = mysql_query($query);
if (!$result) {
    echo 'Could not run query: ' . mysql_error();
    exit;
}
$result = mysql_query($query);
$num = mysql_num_rows($result);
$i = 0;

while ($i < $num)
{

$counted = mysql_result($result,$i,"counted");
$name = mysql_result($result,$i,"NAME");

echo "$name = $counted <br>";

$i++;

}

?>
shows breakdown by category






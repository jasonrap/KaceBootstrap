<?php
############################################################
# Lists software that was installed or removed within the last 7 days.
############################################################

	$daysAgo = 7;

/* Catch if we have a drill-down instead of just a basic display */
if ( isset($_GET['sw']) )
{ // drill-down!
	$sw = urldecode($_GET['sw']);
	$displaySW = htmlspecialchars($sw);
	$sqlSW = mysql_real_escape_string($sw);
?>
<h2>'<?php echo $displaySW; ?>' Detailed Installed/Removed Within Last <?php echo $daysAgo; ?> days</h2>
<table class="table table-striped table-bordered table-head-bordered-bottom table-condensed" style='width:50em'>
  <thead>
    <tr>
      <th class=span1>Computer</th>
	  <th class=span1>Type</th>
<!--      <th class=span3>Software Name</th>-->
      <th class=span1>Version</th>
	  <th class=span1>Time</th>
    </tr>
  </thead>
  <tbody>
<?php

$query1 = "
SELECT
	ASSET.MAPPED_ID as MACHINE_ID,
	AH.NAME as COMPUTER,
	CHANGE_TYPE,
	VALUE1 AS SOFTWARE_NAME,
	VALUE2 AS VERSION,
	AH.TIME as TIME
FROM
	ASSET_HISTORY AH
	LEFT JOIN ASSET ON ASSET.ID=AH.ASSET_ID
WHERE
	FIELD_NAME = 'SOFTWARE'
	AND VALUE1 = '$sqlSW'
AND TIME > DATE_SUB(NOW(),INTERVAL $daysAgo DAY)
ORDER BY
	AH.NAME, CHANGE_TYPE
";

$result1 = mysql_query($query1);
$num = mysql_numrows($result1);

while ( ($row = mysql_fetch_assoc($result1)) != NULL )
{
	$ID = stripslashes($row['MACHINE_ID']);
	$computerName = stripslashes($row['COMPUTER']);
	$changeType = stripslashes($row['CHANGE_TYPE']);
	$softwareName = stripslashes($row['SOFTWARE_NAME']);
	$version = stripslashes($row['VERSION']);
	$timeOfChange = stripslashes($row['TIME']);

	echo "<tr>\n";
	if ( $ID )
		echo "<td><a href=\"http://$KaceBoxDNS/adminui/machine.php?ID=$ID\">$computerName</a></td>\n";
	else
		echo "<td>$computerName</td>\n";
	print( "<td>".displaySoftwareChangeType($changeType)."</td>\n" );
//	echo "<td>$softwareName</td>\n";
	echo "<td>$version</td>\n";
	echo "<td>$timeOfChange</td>\n";
	echo "</tr>\n";
}
echo "</tbody></table>\n";
return;
} // end if ( isset($_GET['sw']) )
else
{
/* Default Page Load */
?>
<h2>Software Installed/Removed Within Last <?php echo $daysAgo; ?> days</h2>
<table class="table table-striped table-bordered table-head-bordered-bottom table-condensed" style='width:50em'>
  <thead>
    <tr>
      <th class=span1>Count</th>
	  <th class=span1>Type</th>
      <th class=span3>Software Name</th>
      <th class=span1>Version</th>
    </tr>
  </thead>
  <tbody>

<?php
$query1 = "
SELECT
	Count(*) as CountofSoftware,
	CHANGE_TYPE,
	VALUE1 AS SOFTWARE_NAME,
	VALUE2 AS VERSION
FROM
	ASSET_HISTORY AH
WHERE
	FIELD_NAME = 'SOFTWARE'
	AND VALUE1 NOT LIKE '%Update for%'
	AND VALUE1 NOT LIKE 'Hotfix for%'
	AND VALUE1 NOT LIKE 'Security Update%'
	AND VALUE1 NOT LIKE 'Microsoft Office%'
	AND VALUE1 NOT LIKE 'Microsoft .NET%'
AND TIME > DATE_SUB(NOW(),INTERVAL $daysAgo DAY)
GROUP BY
	CHANGE_TYPE, VALUE1
ORDER BY
	Count(*) DESC, VALUE1, CHANGE_TYPE
";


$result1 = mysql_query($query1);
$num = mysql_numrows($result1);

while ( ($row = mysql_fetch_assoc($result1)) != NULL )
{
	$count = stripslashes($row['CountofSoftware']);
	$changeType = stripslashes($row['CHANGE_TYPE']);
	$softwareName = stripslashes($row['SOFTWARE_NAME']);
	$version = stripslashes($row['VERSION']);

	echo "<tr onclick=\"loadPage('index.php?r=$r&sw=".urlencode($softwareName)."');\" style=\"cursor:pointer; cursor:hand;\">\n";
	echo "<td>$count</td>\n";
	print( "<td>".displaySoftwareChangeType($changeType)."</td>\n" );
	echo "<td>$softwareName</td>\n";
	echo "<td>$version</td>\n";
	echo "</tr>\n";
}
echo "</tbody></table>\n";
echo "<h6>*** A software update will trigger both an uninstall and an install of the software.</h6>";
} // end else

function displaySoftwareChangeType($changeType)
{
	switch($changeType)
	{
		case 'Detected':
			return "Installed";
		case 'No longer detected':
			return "Uninstalled";
		default:
			return "$changeType";
	}
	return "";
}
?>


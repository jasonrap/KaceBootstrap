<?php
############################################################
# Lists software that was installed or removed within the last 7 days.
############################################################

	$daysAgo = 7;
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
GROUP BY CHANGE_TYPE, VALUE1
ORDER BY Count(*) DESC, VALUE1, CHANGE_TYPE
";


$result1 = mysql_query($query1);
$num = mysql_numrows($result1);

while ( ($row = mysql_fetch_assoc($result1)) != NULL )
{
	$count = stripslashes($row['CountofSoftware']);
	$changeType = stripslashes($row['CHANGE_TYPE']);
	$softwareName = stripslashes($row['SOFTWARE_NAME']);
	$version = stripslashes($row['VERSION']);

	echo "<tr>\n";
	echo "<td>$count</td>\n";
	switch($changeType)
	{
		case 'Detected':
			echo "<td>Installed</td>\n";
			break;
		case 'No longer detected':
			echo "<td>Uninstalled</td>\n";
			break;
		default:
			echo "<td>$changeType</td>\n";
			break;
	}
	echo "<td>$softwareName</td>\n";
	echo "<td>$version</td>\n";
	echo "</tr>\n";
}
echo "</tbody></table>\n";
?>

<h6>*** A software update will trigger both an uninstall and an install of the software.</h6>

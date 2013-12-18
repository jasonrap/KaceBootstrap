<?php
############################################################
# Based on jverbosk's idea and query at:
#  http://www.itninja.com/blog/view/k1000-report-patching-vendor-severity-with-machine-count-and-completion-rates
# Description:
#   Displays percentage and count of patch compliance by vendor-supplied severity
############################################################

	$ratingOrder = array( 0=>
		'SuperCritical',
		'Critical',
		'Important',
		'Moderate',
		'Low',
		'None',
		'Unspecified'
	);
	
	$unknownRating = count($ratingOrder);
?>
<h2>Patch Compliance</h2>
<table class="table table-striped table-bordered table-head-bordered-bottom table-condensed" style='width:50em'>
  <thead>
    <tr>
      <th class=span1>Vendor Rating</th>
      <th class=span1>Compliance</th>
      <th class=span1>Applicable</th>
      <th class=span1>Not Patched</th>
      <th class=span1>Patched</th>
      <th class=span1>Error</th>
    </tr>
  </thead>
  <tbody>

<?php
$query1 = "
SELECT
	(IF(V.ATTRVALUE <> '', V.ATTRVALUE, 'Not Available')) AS VENDOR_RATING,
	ROUND((SUM(MS.STATUS='PATCHED')/COUNT(MS.MACHINE_ID)) * 100, 2) AS COMPLIANCE,
	COUNT(MS.MACHINE_ID) AS APPLICABLE,
	SUM(MS.STATUS='NOTPATCHED') AS NOTPATCHED,
	SUM(MS.STATUS='PATCHED') AS PATCHED,
	SUM((MS.DEPLOY_ATTEMPT_COUNT >= 3 and MS.STATUS != 'PATCHED')
		OR MS.STATUS = 'FAIL' or MS.DEPLOY_STATUS = 'FAIL') AS ERROR
FROM
	KBSYS.PATCHLINK_PATCH P
	LEFT JOIN KBSYS.PATCHLINK_VENDORATTRIBUTE V ON V.PATCHUID = P.UID
	LEFT JOIN PATCHLINK_MACHINE_STATUS MS ON MS.PATCHUID = P.UID
	JOIN PATCHLINK_PATCH_STATUS PS ON PS.PATCHUID = P.UID
WHERE
	V.ATTR = 'MaximumSeverityRating'
	AND V.ATTRVALUE not rlike '8211|recommended'
	AND PS.STATUS = 0 AND PS.IS_SUPERCEDED = 0
GROUP BY
	VENDOR_RATING
";


$result1 = mysql_query($query1);
$num = mysql_numrows($result1);
$i = 0;
$outputRows = array();

while ( ($row = mysql_fetch_assoc($result1)) != NULL )
{
	$vendorRating = stripslashes($row['VENDOR_RATING']);
	$compliance = stripslashes($row['COMPLIANCE']);
	$applicable = stripslashes($row['APPLICABLE']);
	$notPatched = stripslashes($row['NOTPATCHED']);
	$patched = stripslashes($row['PATCHED']);	
	$error = stripslashes($row['ERROR']);
	
	$key = array_search($vendorRating,$ratingOrder);
	if ( $key === false || $key === NULL )
		$key = $unknownRating++; // post-increment so we get a new "unknown" rating for the next one

	$outputRows[$key] = array( 'VENDOR_RATING'=>$vendorRating, 'COMPLIANCE'=>$compliance,
		'APPLICABLE'=>$applicable, 'NOTPATCHED'=>$notPatched, 'PATCHED'=>$patched, 'ERROR'=>$error );

	$i++;
}

ksort($outputRows);
foreach($outputRows as $row)
{
	echo "<tr>\n";
	foreach($row as $key=>$value)
	{
		if( $key == 'COMPLIANCE' )
		{
			// Somewhat-standard cascading color scheme
			if ( $value > 95.0 )
				$color = 'lightgreen';
			else if ( $value > 90 )
				$color = 'green';
			else if ( $value > 80 )
				$color = 'orange'; // omitted yellow due to readability
			else if ( $value > 70 )
				$color = 'darkorange';
			else if ( $value > 60 )
				$color = 'red';
			else
				$color = 'darkred';

			echo "<td><span style='color:$color'>$value%</span></td>\n";
		}
		else
			echo "<td>$value</td>\n";
	}
	echo "</tr>\n";
}
echo "</tbody></table>\n";
?>

<h6>*** Note: These are patch opportunities. A single computer can be counted many times, depending on how many outstanding patches it needs.</h6>

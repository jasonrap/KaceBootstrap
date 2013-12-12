<?php
//return;

##################
## Configurables
#######
$graphTitle = "Tickets Opened and Closed (Last 12 Months)";
$maxMonths = 12;
$xAxis="";

require_once('includes/config.php');

//****************************************
// Get ticket owner ID and set up variables
//****************************************
$query1 = "
SELECT u.ID
 FROM `USER` u
 WHERE u.FULL_NAME='$user'
";

$result1 = mysql_query($query1);
$numUsers = mysql_numrows($result1);
$userID = 0;
if ( $numUsers < 1 )
{
	print( "User not found.<br/>\n" );
	return;
}
else
{
	$row = mysql_fetch_assoc($result1);
	if ( is_numeric($row['ID']) )
		$userID = $row['ID'];
}
$seriesOpen = array(); // do two 2D arrays, just to avoid the 3D array nastiness.
$seriesOpened = array();
$seriesClosed = array();
$month_xref = array();
for( $j = 0; $j < $maxMonths; $j++ )
	$month_xref[$j] = "";

for( $j = 0; $j < $maxMonths; $j++ )
{
	$seriesOpen[$j] = 0;
	$seriesOpened[$j] = 0;
	$seriesClosed[$j] = 0;

	$now = time();
	$firstOfMonth = mktime(0, 0, 0, date("m", $now)-$j, 1, date("Y", $now));
	$dt = getdate($firstOfMonth);
	$month_xref[$maxMonths-1-$j] = "$dt[mon]-$dt[year]";
}
$username = htmlspecialchars($user);


//***************************************
// Open Tickets (currently open at that time)
//***************************************
for( $j = $maxMonths-1; $j >= 0; $j-- )
{
	$query1 = "
SELECT hdt.OWNER_ID as owner,
	count(hdt.ID) as total_open,
	HD_STATUS.NAME AS status,
	MONTH(DATE_SUB(DATE_ADD(last_day(NOW()), INTERVAL 1 DAY), INTERVAL ($j+1) MONTH)) as month,
	YEAR(DATE_SUB(DATE_ADD(last_day(NOW()), INTERVAL 1 DAY), INTERVAL ($j+1) MONTH)) as year
FROM HD_TICKET hdt
	JOIN HD_STATUS ON (HD_STATUS.ID = hdt.HD_STATUS_ID)
	LEFT JOIN USER u ON u.ID=hdt.OWNER_ID
WHERE (hdt.HD_QUEUE_ID > 0)
AND hdt.OWNER_ID='$userID'
AND (
	(HD_STATUS.NAME not like '%Spam%')
	AND (HD_STATUS.NAME not like '%Server Status Report%')
)
AND (
	(hdt.CREATED<DATE_SUB(DATE_ADD(last_day(NOW()), INTERVAL 1 DAY), INTERVAL $j MONTH))
	AND	(
		(HD_STATUS.STATE not like '%closed%')
		OR (hdt.TIME_CLOSED>=DATE_SUB(DATE_ADD(last_day(NOW()), INTERVAL 1 DAY), INTERVAL $j MONTH))
	)
)
GROUP BY hdt.OWNER_ID
";

	$result1 = mysql_query($query1);
	while( ($row = mysql_fetch_assoc($result1)) )
	{
		$total_open = $row["total_open"];
		$month = $row["month"];
		$year = $row["year"];

		$xref_count = array_search("$month-$year",$month_xref);
		if ( $xref_count !== false )
			$seriesOpen[$xref_count] = $total_open;
	}
}

//***************************************
// Opened Tickets (were created during time period)
//***************************************
$query1 = "
SELECT hdt.OWNER_ID as owner, u.USER_NAME,
	count(hdt.ID) as total_opened,
	HD_STATUS.NAME AS status,
	MONTH(hdt.CREATED) as month,
	YEAR(hdt.CREATED) as year
FROM HD_TICKET hdt
	JOIN HD_STATUS ON (HD_STATUS.ID = hdt.HD_STATUS_ID)
	LEFT JOIN USER u ON u.ID=hdt.OWNER_ID
WHERE (hdt.HD_QUEUE_ID > 0)
AND hdt.OWNER_ID='$userID'
AND (
	(HD_STATUS.NAME not like '%Spam%')
	AND (HD_STATUS.NAME not like '%Server Status Report%')
)
AND (hdt.CREATED >= DATE_SUB(DATE_ADD(last_day(NOW()), INTERVAL 1 DAY), INTERVAL $maxMonths MONTH))
GROUP BY hdt.OWNER_ID, YEAR(hdt.CREATED), MONTH(hdt.CREATED)
ORDER BY YEAR(hdt.CREATED), MONTH(hdt.CREATED), hdt.OWNER_ID
";

$result1 = mysql_query($query1);

$lastyear = 0;
$lastmonth = 0;
$xref_count = -1;
while( ($row = mysql_fetch_assoc($result1)) )
{
	$total_open = $row["total_opened"];
	$month = $row["month"];
	$year = $row["year"];
/*
	// Repeat, so if we've closed one in the current month, but haven't opened one yet, we'll still get it.
	if ( $lastyear != $year || $lastmonth != $month )
	{
		// this block of logic can only skip up to 11 months.
		if ( $lastmonth < $month && $lastyear == $year )
			$xref_count += ($month-$lastmonth);
		else if ( $lastmonth > $month && $lastyear < $year )
			$xref_count += (abs($lastmonth-12)+$month);
		else // shouldn't hit, but jic
			$xref_count++;

		$lastyear = $year;
		$lastmonth = $month;
		$month_xref[$xref_count] = "$month-$year";
	}
*/
	$xref_count = array_search("$month-$year",$month_xref);
	if ( $xref_count !== false )
		$seriesOpened[$xref_count] = $total_open;
}


//***************************************
// Closed Tickets
//***************************************
$query1 = "
SELECT hdt.OWNER_ID as owner,
count(hdt.ID) as total,
HD_STATUS.NAME AS status,
MONTH(hdt.TIME_CLOSED) as month,
YEAR(hdt.TIME_CLOSED) as year
FROM HD_TICKET hdt
JOIN HD_STATUS ON (HD_STATUS.ID = hdt.HD_STATUS_ID)
WHERE (hdt.HD_QUEUE_ID >= 0)
AND hdt.OWNER_ID='$userID'
	AND (
		(HD_STATUS.STATE like '%closed%')
		AND ((HD_STATUS.NAME not like '%Spam%')
			AND (HD_STATUS.NAME not like '%Server Status Report%'))
	)
AND hdt.TIME_CLOSED >= DATE_SUB(DATE_ADD(last_day(NOW()), INTERVAL 1 DAY), INTERVAL $maxMonths MONTH)
GROUP BY hdt.OWNER_ID, YEAR(hdt.TIME_CLOSED), MONTH(hdt.TIME_CLOSED)
ORDER BY hdt.TIME_CLOSED
";

$result1 = mysql_query($query1);

$lastyear = 0;
$lastmonth = 0;
$xref_count = -1; // we pre-increment on first loop iteration
while( ($row = mysql_fetch_assoc($result1)) )
{
	$total_closed = $row["total"];
	$month = $row["month"];
	$year = $row["year"];
/*
	// Repeat, so if we've closed one in the current month, but haven't opened one yet, we'll still get it.
	if ( $lastyear != $year || $lastmonth != $month )
	{
		// this block of logic can only skip up to 11 months.
		if ( $lastmonth < $month && $lastyear == $year )
			$xref_count += ($month-$lastmonth);
		else if ( $lastmonth > $month && $lastyear < $year )
			$xref_count += (abs($lastmonth-12)+$month);
		else // shouldn't hit, but jic
			$xref_count++;

		$lastyear = $year;
		$lastmonth = $month;
		$month_xref[$xref_count] = "$month-$year";
	}
*/
	$xref_count = array_search("$month-$year",$month_xref);
	if ( $xref_count !== false )
		$seriesClosed[$xref_count] = $total_closed;
}


$endCommaKludge = ""; // blank first time, has ]}, other times, so we can toss a ]} to print after loop
$xAxis = "";
$yAxis = array( 0=>"", "", "" );
foreach( $month_xref as $month_ref => $xAxis_value )
{
	$xAxis .= "'$xAxis_value', ";
	$yAxis[0] .= "{$seriesOpen[$month_ref]}, ";
	$yAxis[1] .= "{$seriesOpened[$month_ref]}, ";
	$yAxis[2] .= "{$seriesClosed[$month_ref]}, ";
}
$xAxis = substr($xAxis,0,-2);
$yAxis[0] = substr($yAxis[0],0,-2);
$yAxis[1] = substr($yAxis[1],0,-2);
$yAxis[2] = substr($yAxis[2],0,-2);
?>



<!DOCTYPE HTML>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title>Tickets Open and Closed by Owner (Last 3 Months)</title>

		<script type="text/javascript" src="includes/js/jquery.min.js"></script>
		<script type="text/javascript">
$(function () {
    var chart;
    $(document).ready(function() {
        chart = new Highcharts.Chart({
            chart: {
                renderTo: 'ticketsowner12months',
                type: 'line',
                marginRight: 130,
                marginBottom: 25
		
            },
            title: {
                text: '<?php echo $graphTitle ?>',
                x: -20 //center
            },
            subtitle: {
                text: 'Source: Kace',
                x: -20
            },
            xAxis: {
                categories: [<?php echo $xAxis ?>]
            },
            yAxis: {
                title: {
                    text: 'Tickets'
                },
                plotLines: [{
                    value: 0,
                    width: 1,
                    color: '#808080'
                }]
            },
            tooltip: {
				crosshairs: true,
                formatter: function() {
                        return '<b>'+ this.series.name +'</b><br/>'+
                        this.x +': '+ this.y;
                }
            },
            legend: {
                layout: 'vertical',
                align: 'right',
                verticalAlign: 'top',
                x: -10,
                y: 100,
                borderWidth: 0
            },
            series: [{
				name: 'Open Tickets',
				data: [<?php echo $yAxis[0] ?>]
			}, {
				name: 'Opened Tickets',
				data: [<?php echo $yAxis[1] ?>]
			}, {
				name: 'Closed Tickets',
				data: [<?php echo $yAxis[2] ?>]
			}]
        });
    });
    
});
		</script>
	</head>
	<body>
<script src="includes/js/highcharts.js"></script>
<script src="includes/js/exporting.js"></script>

<div id="ticketsowner12months" style="min-width: 400px; height: 400px; margin: 0 auto"></div>

	</body>
</html>

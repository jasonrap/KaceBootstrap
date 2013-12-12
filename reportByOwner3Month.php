<?php
//return;

## Can modify if you want more than 3 months.
$maxMonths = 3;
$xAxis="";

$queueopen="Opened Tickets";
$yAxis = array( 0=>array(), 1=>array(), 2=>array(), 3=>array() );
$dataString="";

include ('includes/config.php');

//****************************************
// Get list of ticket owner users to chart
//****************************************
$query1 = "
SELECT u.ID,u.USER_NAME
 FROM `USER` u
  LEFT JOIN USER_LABEL_JT uljt ON (uljt.USER_ID=u.ID)
  LEFT JOIN LABEL l ON (uljt.LABEL_ID=l.ID)
 WHERE l.NAME = 'All Ticket Owners'
  AND u.HD_DEFAULT_QUEUE_ID >= 0
 ORDER BY u.USER_NAME
";

$result1 = mysql_query($query1);
$numUsers = mysql_numrows($result1);
$seriesOpen = array(); // do two 2D arrays, just to avoid the 3D array nastiness.
$seriesClosed = array();
$users = array();
$i = 0;
$userid_xref = array();
while( ($row = mysql_fetch_assoc($result1)) )
{
	$users[$i] = array( 'id'=>$row['ID'], 'user'=>$row['USER_NAME'] );
	$seriesOpen[$i] = array( 0=>0, 0, 0 ); // just set up 3 months default anyway
	$seriesClosed[$i] = array( 0=>0, 0, 0 );
	for( $j = 0; $j < $maxMonths; $j++ )
	{
		$seriesOpen[$i][$j] = 0;
		$seriesClosed[$i][$j] = 0;
	}
	$username = explode('.',$row['USER_NAME']);
	if(is_array($username))
		$username = ucfirst($username[0]);
	$username = htmlspecialchars($username);
	$users[$i]['shortname'] = $username;
	$xAxis .= "'$username', ";
	$userid_xref[$row['ID']] = $i;
	$i++;
}
$userCount = $i;
$xAxis=substr($xAxis,0,-2);

$month_xref = array();

#########################################################
## Removed following block to due re-purposing the report from currently-open to created-during.
#########################################################
//***************************************
// Open Tickets (currently open at that time)
//***************************************
/*$query1 = "
SELECT hdt.OWNER_ID as owner,
count(hdt.ID) as total,
HD_STATUS.NAME AS status,
month(hdt.CREATED) as month,
YEAR(hdt.CREATED) as year,
(SELECT count(subhdt.ID)
	FROM HD_TICKET subhdt
	JOIN HD_STATUS subhds ON (subhds.ID=subhdt.HD_STATUS_ID)
	WHERE (subhdt.HD_QUEUE_ID > 0)
		AND subhdt.OWNER_ID=hdt.OWNER_ID
		AND (
			(subhds.NAME NOT LIKE '%spam%')
			AND (subhds.NAME NOT LIKE '%server status report%')
			AND	(
				(subhdt.CREATED<=MAX(hdt.CREATED))
				AND	(
					(subhds.STATE not like '%closed%')
					OR (subhdt.TIME_CLOSED>MAX(hdt.CREATED))
				)
			)
		)
) as total_open
FROM HD_TICKET hdt
JOIN HD_STATUS ON (HD_STATUS.ID = hdt.HD_STATUS_ID)
LEFT JOIN USER u ON u.ID=hdt.OWNER_ID
WHERE (hdt.HD_QUEUE_ID > 0)
AND (
 (HD_STATUS.NAME not like '%Spam%')
AND (HD_STATUS.NAME not like '%Server Status Report%')
)
AND hdt.CREATED >= DATE_SUB(DATE_ADD(last_day(NOW()), INTERVAL 1 DAY), INTERVAL $maxMonths MONTH)
GROUP BY hdt.OWNER_ID, YEAR(hdt.CREATED), MONTH(hdt.CREATED)
ORDER BY hdt.CREATED
";*/

/*
for( $j = $maxMonths; $j > 0; $j-- )
{
$currentMonths = $j;
$query1 = "
SELECT hdt.OWNER_ID as owner,
	count(hdt.ID) as total_open,
	HD_STATUS.NAME AS status,
	MONTH(hdt.CREATED) as month,
	YEAR(hdt.CREATED) as year
FROM HD_TICKET hdt
	JOIN HD_STATUS ON (HD_STATUS.ID = hdt.HD_STATUS_ID)
	LEFT JOIN USER u ON u.ID=hdt.OWNER_ID
WHERE (hdt.HD_QUEUE_ID > 0)
AND (
	(HD_STATUS.NAME not like '%Spam%')
	AND (HD_STATUS.NAME not like '%Server Status Report%')
)
AND (
	(hdt.CREATED<=DATE_SUB(DATE_ADD(last_day(NOW()), INTERVAL 1 DAY), INTERVAL $j MONTH))
	AND	(
		(HD_STATUS.STATE not like '%closed%')
		OR (hdt.TIME_CLOSED>DATE_SUB(DATE_ADD(last_day(NOW()), INTERVAL 1 DAY), INTERVAL $j MONTH))
	)
)
GROUP BY hdt.OWNER_ID
";

$result1 = mysql_query($query1);

$xref_count = $maxMonths - $j;
while( ($row = mysql_fetch_assoc($result1)) )
{
	$total_open = $row["total_open"];
	$month = $row["month"];
	$year = $row["year"];

	$month_xref[$xref_count] = "$month-$year";

	if ( !isset($userid_xref[$row['owner']]) )
		continue; // skip random owners

	$user = $userid_xref[$row['owner']];
	$seriesOpen[$user][$xref_count] = $total_open;
}
}*/
#########################################################
## End Re-purposing block
#########################################################

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

	if ( !isset($userid_xref[$row['owner']]) )
		continue; // skip random owners

	$user = $userid_xref[$row['owner']];
	$seriesOpen[$user][$xref_count] = $total_open;
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

	if ( !isset($userid_xref[$row['owner']]) )
		continue; // skip random owners

	$user = $userid_xref[$row['owner']];
	$seriesClosed[$user][$xref_count] = $total_closed;
}
?>



<!DOCTYPE HTML>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title>Tickets Open and Closed by Owner (Last <?php echo $maxMonths ?> Months)</title>

		<script type="text/javascript" src="includes/js/jquery.min.js"></script>
		<script type="text/javascript">
$(function () {
    var chart;
    $(document).ready(function() {
        chart = new Highcharts.Chart({
            chart: {
                renderTo: 'ticketsopenclosed3months',
                type: 'column',
                marginRight: 130,
                marginBottom: 25
		
            },
            title: {
                text: 'Tickets Open and Closed by Owner (Last 3 Months)',
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
            series: [<?php
	$endCommaKludge = ""; // blank first time, has ]}, other times, so we can toss a ]} to print after loop
	foreach( $month_xref as $month_ref => $xAxis_value )
	{
		print( $endCommaKludge );

		/* Opened Tickets */
		print( "{
				name: '$xAxis_value - Opened',
				data: [" );
		for( $i = 0; $i < sizeof($users); $i++ )
		{
			print( $seriesOpen[$i][$month_ref] );
			if ( $i+1 < sizeof($users) )
				print( ", " );
		}
		print( "]
			}," );

		/* Closed Tickets */
		print( "{
				name: '$xAxis_value - Closed',
				data: [" );
		for( $i = 0; $i < $userCount; $i++ )
		{
			print( $seriesClosed[$i][$month_ref] );
			if ( $i+1 < $userCount )
				print( ", " );
		}
		$endCommaKludge = "]
			},";
	}
	print( "]
			}" );?>]
        });
    });
    
});
		</script>
	</head>
	<body>
<script src="includes/js/highcharts.js"></script>
<script src="includes/js/exporting.js"></script>

<div id="ticketsopenclosed3months" style="min-width: 400px; height: 400px; margin: 0 auto"></div>

	</body>
</html>

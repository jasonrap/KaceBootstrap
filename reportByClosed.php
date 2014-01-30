<?php

$xAxis="";
$closedAxis="";
$openedAxis="";
$i = 0;

include_once ('includes/config.php');
include_once("reportByClosedAverage.php");

## Can modify if you want different than 30 days.
$maxDays = 30;

$seriesOpened = array();
$seriesClosed = array();
/* Prep open/closed */
$m = strftime('%m');
$y = strftime('%Y');
$d = strftime('%d');
for ($i = 0; $i < $maxDays; $i++)
{
	$key = date("n/d", mktime(0, 0, 0, $m, $d-$i, $y));
    $seriesOpened[$key] = 0;
	$seriesClosed[$key] = 0;
	$xAxis.="'$key',";
}
$xAxis=substr($xAxis,0,-1);

//***************************************
// Closed Tickets (were closed during time period)
//***************************************
$query="
SELECT
	COUNT(HD_TICKET.ID) as total,
	MONTH(TIME_CLOSED) as month,
	DAY(TIME_CLOSED) as day,
	YEAR(TIME_CLOSED) as year
FROM
	HD_TICKET INNER JOIN
	HD_STATUS ON HD_TICKET.HD_STATUS_ID = HD_STATUS.ID
WHERE
	(HD_TICKET.HD_QUEUE_ID = $mainQueueID)
	AND (HD_STATUS.STATE LIKE '%Closed%')
	AND (
		(HD_STATUS.NAME NOT LIKE '%spam%')
		AND (HD_STATUS.NAME NOT LIKE '%Server Status Report%')
	)
	AND (TIME_CLOSED >= ( CURDATE() - INTERVAL $maxDays DAY ))
GROUP BY
	DATE(TIME_CLOSED)
";


$result = mysql_query($query);
if (!$result) {
    echo 'Could not run query: ' . mysql_error();
    exit;
}

while( ($row = mysql_fetch_assoc($result)) )
{
	$total = $row['total'];
	$month = $row['month'];
	$day = $row['day'];
	$key = sprintf("%d/%02d",$month,$day);
	if ( isset($seriesClosed[$key]) ) // SQL time wraps to +1 days
		$seriesClosed[$key] = $total;
}

foreach($seriesClosed as $value)
{
	$closedAxis .= "$value,";
}
$closedAxis = substr($closedAxis,0,-1);


$theAverageString="";
for($j = 0; $j < $maxDays; $j++)
{
	$theAverageString.="$theAverage,";
}
$theAverageString=substr($theAverageString,0,-1);


//***************************************
// Opened Tickets (were created during time period)
//***************************************
$query1 = "
SELECT
	COUNT(HD_TICKET.ID) as total,
	MONTH(CREATED) as month,
	DAY(CREATED) as day,
	YEAR(CREATED) as year
FROM
	HD_TICKET INNER JOIN
	HD_STATUS ON HD_TICKET.HD_STATUS_ID = HD_STATUS.ID
WHERE
	(HD_TICKET.HD_QUEUE_ID = $mainQueueID)
	AND (HD_STATUS.STATE LIKE '%Closed%')
	AND (
		(HD_STATUS.NAME NOT LIKE '%spam%')
		AND (HD_STATUS.NAME NOT LIKE '%Server Status Report%')
	)
	AND (CREATED >= ( CURDATE() - INTERVAL $maxDays DAY ))
GROUP BY
	DATE(CREATED)
";

$result = mysql_query($query1);
if (!$result) {
    echo 'Could not run query: ' . mysql_error();
    return;
}

while( ($row = mysql_fetch_assoc($result)) )
{
	$total = $row['total'];
	$month = $row['month'];
	$day = $row['day'];
	$key = sprintf("%d/%02d",$month,$day);
	if ( isset($seriesOpened[$key]) ) // SQL time wraps to +1 days
		$seriesOpened[$key] = $total;
}

foreach($seriesOpened as $value)
{
	$openedAxis .= "$value,";
}
$openedAxis = substr($openedAxis,0,-1);
?>



<!DOCTYPE HTML>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title>Tickets Closed All Queues</title>

		<script type="text/javascript" src="includes/js/jquery.min.js"></script>
		<script type="text/javascript">
$(function () {
    var chart;
    $(document).ready(function() {
        chart = new Highcharts.Chart({
            chart: {
                renderTo: 'conReportByClosed',
                type: 'line',
                marginRight: 130,
                marginBottom: 25
            },
            title: {
                text: 'Tickets Closed All Queues, last <?php echo $maxDays; ?> days',
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
                    text: 'Tickets Closed'
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
						this.x +': '+ this.y + (this.series.name=='Tickets Opened'?' Opened':' Closed');
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
                name: 'Tickets Closed',
                data: [<?php echo $closedAxis ?>],
				lineWidth: 5
			}, {
				name: 'Average Closed',
				data: [<?php echo $theAverageString ?>]
			}, {
				name: 'Tickets Opened',
				data: [<?php echo $openedAxis ?>],
				lineWidth: 1
			}]
        });
    });
    
});
		</script>
	</head>
	<body>
<script src="includes/js/highcharts.js"></script>
<script src="includes/js/exporting.js"></script>

<div id="conReportByClosed" style="min-width: 400px; height: 400px; margin: 0 auto"></div>

	</body>
</html>

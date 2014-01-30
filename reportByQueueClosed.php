<?php

// TODO: work out how to best scale these queries so only the query section needs to be added
// (IE have the series [] in the javascript scale as we toss in more xAxis, yAxis and data info into a container array).
$xAxis="";
$yAxis="";
$yAxis1="";
$yAxis2="";
$yAxisOpen="";
$yAxisOpened="";

#########################
# Edit these lines below
##########

# Table/Chart Titles
# Open and Opened queries are explicitly used in the chart. These two vars are just chart titles.
# If you don't want them, set the $deleteQueue to true;
$chartTitle = "Tickets Closed per Queue";

$queueopen="Total Open Tickets";
$queueopened="Tickets Opened";

#mainQueueID and mainQueueName are set within the includes/config.php file
$queue0=$mainQueueName;
$queue0ID=$mainQueueID;

# A single-queue data plot on the chart
$queue1="IT Projects";
$queue1ID=16;
$deleteQueue1 = false;

# This one is a range of queues rolled up into one data plot.
$queue2="Onboarding and Termination";
$queue2IDMin = 2;
$queue2IDMax = 15;
$deleteQueue2 = false;

$yAxis = array( 0=>array(), 1=>array(), 2=>array(), 3=>array(), 4=>array() );
$dataString="";

include_once('includes/config.php');

//***************************************
// Total Open Tickets
//***************************************
$query1 = "
SELECT count(hdt.ID) as total,
HD_STATUS.NAME AS STATUS,
month(hdt.CREATED) as month,
YEAR(hdt.CREATED) as year,
(SELECT count(subhdt.ID) as total_open
	FROM HD_TICKET subhdt
	JOIN HD_STATUS subhds ON (subhds.ID=subhdt.HD_STATUS_ID)
	WHERE (subhdt.HD_QUEUE_ID > 0)
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
WHERE (hdt.HD_QUEUE_ID > 0)
AND (
 (HD_STATUS.NAME not like '%Spam%')
AND (HD_STATUS.NAME not like '%Server Status Report%')
)
AND hdt.CREATED >= DATE_SUB(DATE_ADD(last_day(NOW()), INTERVAL 1 DAY), INTERVAL 1 YEAR)
group by YEAR(hdt.CREATED), MONTH(hdt.CREATED)
ORDER BY hdt.CREATED
";

$result1 = mysql_query($query1);
$num = mysql_numrows($result1);
$i = 0;
while ($i < $num)
{
	$total = mysql_result($result1,$i,"total_open");
	$month = mysql_result($result1,$i,"month");
	$year = mysql_result($result1,$i,"year");

	$total = stripslashes($total);
	$month = stripslashes($month);
	$year = stripslashes($year);

	$xAxis.="'$month-$year', ";
	//$yAxis.="$total, ";
	$yAxis[0]["$month-$year"] = 0;
	$yAxis[1]["$month-$year"] = 0;
	$yAxis[2]["$month-$year"] = 0;
	$yAxis[3]["$month-$year"] = $total;
	$yAxis[4]["$month-$year"] = 0;
	$i++;
}
$xAxis=substr($xAxis,0,-2);
//$yAxis=substr($yAxis,0,-2);
#echo $yAxis;


//***************************************
// Opened Tickets
//***************************************
$query1 = "
SELECT count(hdt.ID) as total_opened,
	HD_STATUS.NAME AS status,
	MONTH(hdt.CREATED) as month,
	YEAR(hdt.CREATED) as year
FROM HD_TICKET hdt
JOIN HD_STATUS ON (HD_STATUS.ID = hdt.HD_STATUS_ID)
WHERE (hdt.HD_QUEUE_ID > 0)
	AND (
		(HD_STATUS.NAME NOT LIKE '%spam%')
		AND (HD_STATUS.NAME NOT LIKE '%server status report%')
	)
AND hdt.CREATED >= DATE_SUB(DATE_ADD(last_day(NOW()), INTERVAL 1 DAY), INTERVAL 1 YEAR)
group by YEAR(hdt.CREATED), MONTH(hdt.CREATED)
ORDER BY hdt.CREATED
";

$result1 = mysql_query($query1);
$num = mysql_numrows($result1);
$i = 0;
while ($i < $num)
{
	$total = mysql_result($result1,$i,"total_opened");
	$month = mysql_result($result1,$i,"month");
	$year = mysql_result($result1,$i,"year");

//	$xAxis.="'$month-$year', ";
	//$yAxisOpened.="$total, ";
	$yAxis[4]["$month-$year"] = $total;
	$i++;
}
//$xAxis=substr($xAxis,0,-2);
//$yAxis=substr($yAxis,0,-2);
#echo $yAxis;


//***************************************
// IT Service Desk
//***************************************
$query1 = "
SELECT count(HD_TICKET.ID) as total,
HD_STATUS.NAME AS STATUS,
month(HD_TICKET.TIME_CLOSED) as month,
YEAR( HD_TICKET.TIME_CLOSED) as year
FROM HD_TICKET
JOIN HD_STATUS ON (HD_STATUS.ID = HD_TICKET.HD_STATUS_ID)
WHERE (HD_TICKET.HD_QUEUE_ID = $queue0ID)
AND ((HD_STATUS.STATE like '%closed%')
AND (HD_STATUS.NAME not like '%Spam%')
AND (HD_STATUS.NAME not like '%Server Status Report%')
)
AND HD_TICKET.TIME_CLOSED >= DATE_SUB(DATE_ADD(last_day(NOW()), INTERVAL 1 DAY), INTERVAL 1 YEAR)
group by YEAR(HD_TICKET.TIME_CLOSED), MONTH(HD_TICKET.TIME_CLOSED)
";


$result1 = mysql_query($query1);
$num = mysql_numrows($result1);
$i = 0;
while ($i < $num)
{
	$total = mysql_result($result1,$i,"total");
	$month = mysql_result($result1,$i,"month");
	$year = mysql_result($result1,$i,"year");

	$total = stripslashes($total);
	$month = stripslashes($month);
	$year = stripslashes($year);

	//$xAxis.="'$month-$year', ";
	//$yAxis.="$total, ";
	$yAxis[0]["$month-$year"] = $total;
	$i++;
}
//$xAxis=substr($xAxis,0,-2);
//$yAxis=substr($yAxis,0,-2);
#echo $yAxis;



//***************************************
// IT Projects
//***************************************
if ( !$deleteQueue1 )
{
$i="0";
$query1 = "
SELECT count(HD_TICKET.ID) as total,
HD_STATUS.NAME AS STATUS,
month(HD_TICKET.TIME_CLOSED) as month,
YEAR( HD_TICKET.TIME_CLOSED)as year
FROM HD_TICKET
JOIN HD_STATUS ON (HD_STATUS.ID = HD_TICKET.HD_STATUS_ID)
WHERE (HD_TICKET.HD_QUEUE_ID = $queue1ID)
AND ((HD_STATUS.STATE like '%closed%')
AND (HD_STATUS.NAME not like '%Spam%')
AND (HD_STATUS.NAME not like '%Server Status Report%')
)
AND HD_TICKET.TIME_CLOSED >= DATE_SUB(DATE_ADD(last_day(NOW()), INTERVAL 1 DAY), INTERVAL 1 YEAR)
group by YEAR(HD_TICKET.TIME_CLOSED), MONTH(HD_TICKET.TIME_CLOSED)
";


$result1 = mysql_query($query1);
$num = mysql_numrows($result1);
$i = 0;
while ($i < $num)
{
	$total = mysql_result($result1,$i,"total");
	$month = mysql_result($result1,$i,"month");
	$year = mysql_result($result1,$i,"year");

	$total = stripslashes($total);
	$month = stripslashes($month);
	$year = stripslashes($year);

	//$yAxis1.="$total, ";
	$yAxis[1]["$month-$year"] = $total;
	$i++;
}
//$yAxis1=substr($yAxis1,0,-2);
}


//***************************************
// Onboarding and Termination Queues
//***************************************
if ( !$deleteQueue2 )
{
$i="0";
$query1 = "
SELECT count(HD_TICKET.ID) as total,
HD_STATUS.NAME AS STATUS,
month(HD_TICKET.TIME_CLOSED) as month,
YEAR( HD_TICKET.TIME_CLOSED)as year
FROM HD_TICKET
JOIN HD_STATUS ON (HD_STATUS.ID = HD_TICKET.HD_STATUS_ID)
WHERE (HD_TICKET.HD_QUEUE_ID >= $queue2IDMin AND HD_TICKET.HD_QUEUE_ID <= $queue2IDMax)
AND ((HD_STATUS.STATE like '%closed%')
AND (HD_STATUS.NAME not like '%Spam%')
AND (HD_STATUS.NAME not like '%Server Status Report%')
)
AND HD_TICKET.TIME_CLOSED >= DATE_SUB(DATE_ADD(last_day(NOW()), INTERVAL 1 DAY), INTERVAL 1 YEAR)
group by YEAR(HD_TICKET.TIME_CLOSED), MONTH(HD_TICKET.TIME_CLOSED)
";

$result1 = mysql_query($query1);
$num = mysql_numrows($result1);
$i = 0;
while ($i < $num)
{
	$total = mysql_result($result1,$i,"total");
	$month = mysql_result($result1,$i,"month");
	$year = mysql_result($result1,$i,"year");

	$total = stripslashes($total);
	$month = stripslashes($month);
	$year = stripslashes($year);

	//$xAxis.="'$month-$year', ";
	//$yAxis2.="$total, ";
	$yAxis[2]["$month-$year"] = $total;
	$i++;
}
//$xAxis=substr($xAxis,0,-2);
//$yAxis2=substr($yAxis2,0,-2);
}


/*****
   Set up yAxis (data values) strings for each plot (ie ="1,2,3,4,5")
******/
$yAxis1 = $yAxis2 = $yAxis3 = $yAxisOpen = "";
foreach( $yAxis[0] as $key => $value )
{
	$yAxis1 .= "{$yAxis[0][$key]}, ";
	if ( !$deleteQueue1 )
		$yAxis2 .= "{$yAxis[1][$key]}, ";
	if ( !$deleteQueue2 )
		$yAxis3 .= "{$yAxis[2][$key]}, ";
	$yAxisOpen .= "{$yAxis[3][$key]}, ";
	$yAxisOpened .= "{$yAxis[4][$key]}, ";
}
$yAxis1=substr($yAxis1,0,-2);
if ( !$deleteQueue1 )
	$yAxis2=substr($yAxis2,0,-2);
if ( !$deleteQueue2 )
	$yAxis3=substr($yAxis3,0,-2);
$yAxisOpen=substr($yAxisOpen,0,-2);
$yAxisOpened=substr($yAxisOpened,0,-2);
//print( "$yAxis1<br/>\n$yAxis2<br/>\n$yAxis3<br/>$yAxisOpened<br/>\n" );
?>



<!DOCTYPE HTML>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title>Tickets Closed per Queue</title>

		<script type="text/javascript" src="includes/js/jquery.min.js"></script>
		<script type="text/javascript">
$(function () {
    var chart;
    $(document).ready(function() {
        chart = new Highcharts.Chart({
            chart: {
                renderTo: 'conReportByQueueClosed',
                type: 'line',
                marginRight: 130,
                marginBottom: 25
		
            },
            title: {
                text: '<?php echo $chartTitle ?>',
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
                        this.x +': '+ this.y + (this.series.name=='<?php echo $queueopen ?>'?' Total Open':
											(this.series.name=='<?php echo $queueopened ?>'?' Opened':' closed'));
                }
            },
            legend: {
                layout: 'vertical',
                align: 'right',
                verticalAlign: 'top',
                x: -10,
                y: 45,
                borderWidth: 0
            },
            series: [{
                name: '<?php echo $queue0 ?>',
                data: [<?php echo $yAxis1 ?>]
            },<?php
				if ( !$deleteQueue1 )
					print( " {
                name: '$queue1',
                data: [$yAxis2]
            }, " );

				if ( !$deleteQueue2 )
					print( "{
                name: '$queue2',
                data: [$yAxis3]
            }, " );
			?>{
                name: '<?php echo $queueopen ?>',
                data: [<?php echo $yAxisOpen ?>]
            }, {
                name: '<?php echo $queueopened ?>',
                data: [<?php echo $yAxisOpened ?>]
            }]
        });
    });
    
});
		</script>
	</head>
	<body>
<script src="includes/js/highcharts.js"></script>
<script src="includes/js/exporting.js"></script>

<div id="conReportByQueueClosed" style="min-width: 400px; height: 400px; margin: 0 auto"></div>

	</body>
</html>

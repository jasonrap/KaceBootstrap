<?php

$xAxis="";
$yAxis="";
$i = 0;
$ii = 0;

include_once ('includes/config.php');
include_once("reportByClosedAverage.php");

$query="
SELECT count(HD_TICKET.ID) as counted,
MONTH(TIME_CLOSED) as month,
DAY(TIME_CLOSED) as day,
YEAR(TIME_CLOSED) as year
from
  HD_STATUS Inner Join
  HD_TICKET On HD_TICKET.HD_STATUS_ID = HD_STATUS.ID
  where (HD_STATUS.STATE LIKE '%Closed%')
  and (
       	(HD_STATUS.NAME not like '%spam%')
		AND (TIME_CLOSED >= ( CURDATE() - INTERVAL 30 DAY ))
	)
group by DATE(TIME_CLOSED)
";


$result = mysql_query($query);
if (!$result) {
    echo 'Could not run query: ' . mysql_error();
    exit;
}
$result = mysql_query($query);
$num = mysql_num_rows($result);


while ($i < $num)
{
	$counted = mysql_result($result,$i,"counted");
	$month = mysql_result($result,$i,"month");
	$day = mysql_result($result,$i,"day");
	$year = mysql_result($result,$i,"year");
	#echo "$counted on $month/$day/$year<br>";

	$i++;

	$xAxis.="'$month/$day', ";
	$yAxis.="$counted, ";
}

#$yAxis=strrev($yAxis);
#$xAxis=strrev($xAxis);
$xAxis=substr($xAxis,0,-2);
$yAxis=substr($yAxis,0,-2);
#echo $yAxis;
#echo "<br>";
#echo $xAsis;
$theAverageString="";

while ($ii < $i)
{
	$theAverageString.="$theAverage, ";
	$ii++;
}
$theAverageString=substr($theAverageString,0,-2);

?>



<!DOCTYPE HTML>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title>Tickets closed all Queues</title>

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
                text: 'Tickets closed all Queues, last 30 days',
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
                        this.x +': '+ this.y +' closed';
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
                data: [<?php echo $yAxis ?>]
		}, {
		name: 'Average Closed',
		data: [<?php echo $theAverageString ?>]

            
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

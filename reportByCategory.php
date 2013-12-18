<?php
include ('includes/config.php');

$data="";

$query1 = "
SELECT count(HD_TICKET.ID) as total, 
HD_CATEGORY.NAME as CatName
FROM HD_TICKET  
JOIN HD_STATUS ON (HD_STATUS.ID = HD_TICKET.HD_STATUS_ID) 
LEFT JOIN HD_CATEGORY ON (HD_TICKET.HD_CATEGORY_ID=HD_CATEGORY.ID)
WHERE (HD_STATUS.NAME not like '%Server Status Report%') 
AND ((HD_STATUS.STATE like '%closed%') 
AND (HD_STATUS.NAME not like '%spam%')
AND (HD_TICKET.HD_QUEUE_ID = $mainQueueID)
AND (HD_TICKET.TIME_CLOSED > utc_timestamp() - interval 30 day)
)
GROUP BY HD_TICKET.HD_CATEGORY_ID
order by total;
";


$result1 = mysql_query($query1);
$num = mysql_numrows($result1);
$i = 0;
while ($i < $num)
{
	$total = mysql_result($result1,$i,"total");
	$CatName = mysql_result($result1,$i,"CatName"); 

	$total = stripslashes($total);
	$CatName = stripslashes($CatName);

	$CatName = str_replace("'", "", $CatName);
	#echo "$CatName <br>"; 
	$data.="['$CatName', $total], ";
	$i++;
}
$data=substr($data,0,-2);
#echo $data;
?>

<!DOCTYPE HTML>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title>Closed Tickets by Category (<?php echo $mainQueueName; ?>)</title>

		<script type="text/javascript" src="includes/js/jquery.min.js"></script>
		<script type="text/javascript">
$(function () {
    var chart2;
    $(document).ready(function() {
        chart2 = new Highcharts.Chart({
            chart: {
                renderTo: 'conReportByCategory',
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false
            },
            title: {
                text: 'Closed Tickets by Category, last 30 days (<?php echo $mainQueueName; ?>)'
        	},    
            subtitle: {  
                text: 'Source: Kace',
                x: -20
            
            },
            tooltip: {
                formatter: function() {
                    return '<b>'+ this.point.name +'</b>: '+ this.percentage.toFixed(1) +' %';
                }
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: true,
			valueDecimals: 1,
                        color: '#000000',
			connectorColor: 'white',

                        formatter: function() {
                            return '<b>'+ this.point.name +'</b>: '+ this.percentage.toFixed(1) +' %';
                        }
                    }
                }
            },
            series: [{
                type: 'pie',
                name: 'Categories',
                data: [
                        <?php echo $data; ?>
                ]
            }]
        });
    });
    
});
		</script>
	</head>
	<body>
<script src="includes/js/highcharts.js"></script>
<script src="includes/js/modules/exporting.js"></script>

<div id="conReportByCategory" style="min-width: 400px; height: 400px; margin: 0 auto"></div>

	</body>
</html>

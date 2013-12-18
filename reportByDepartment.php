<?php
include ('includes/config.php');

$data="";

$query1 = "
SELECT count(HD_TICKET.ID) as total,  
HD_TICKET.CUSTOM_FIELD_VALUE0 as department  
FROM HD_TICKET  
JOIN HD_STATUS ON (HD_STATUS.ID = HD_TICKET.HD_STATUS_ID) 
WHERE (HD_STATUS.NAME not like '%Server Status Report%') 
AND ((HD_STATUS.STATE like '%closed%') 
AND (HD_STATUS.NAME not like '%spam%')
AND (HD_TICKET.HD_QUEUE_ID = $mainQueueID)
AND (HD_TICKET.TIME_CLOSED > utc_timestamp() - interval 30 day)
)
group by department
order by total
";


 $result1 = mysql_query($query1);
 $num = mysql_numrows($result1);
 $i = 0;
 while ($i < $num)
        {
        $total = mysql_result($result1,$i,"total");
        $department = mysql_result($result1,$i,"department"); 

        $total = stripslashes($total);
        $department = stripslashes($department);

$department = str_replace("'", "", $department);
#echo "$department <br>"; 

$data.="['$department', $total], ";

$i++;

}

$data=substr($data,0,-2);
#echo $data;
?>

<!DOCTYPE HTML>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title>Tickets by Department (Support Queue)</title>

		<script type="text/javascript" src="includes/js/jquery.min.js"></script>
		<script type="text/javascript">
$(function () {
    var chart2;
    $(document).ready(function() {
        chart2 = new Highcharts.Chart({
            chart: {
                renderTo: 'conReportByDepartment',
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false
            },
            title: {
                text: 'Tickets by Department, last 30 days  (Support Queue)'
        	},    
	subtitle: {  
                text: 'Source: Kace',
                x: -20
            
            },
            tooltip: {
                formatter: function() {
                    return '<b>'+ this.point.name +'</b>: '+ this.percentage +' %';
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
                            return '<b>'+ this.point.name +'</b>: '+ this.percentage +' %';
                        }
                    }
                }
            },
            series: [{
                type: 'pie',
                name: 'Browser share',
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

<div id="conReportByDepartment" style="min-width: 400px; height: 400px; margin: 0 auto"></div>

	</body>
</html>

<?php
//return;
###################
## This reports on mainQueueID, which is configured in includes/config.php
########

require_once('includes/config.php');
$gridTitle = "$mainQueueName - Open/Closed Tickets by Category";

$category = 0;
$sub = 0;

if ( isset($_GET['user']) )
{
	$user = urldecode($_GET['user']);
	$user = strip_tags($user);
	$user = ucwords($user);
	$user = mysql_escape_string($user);
}

if ( !isset($user) )
	$user = false;
else
	$gridTitle = "$user's Open/Closed Tickets by Category";

if ( isset($_GET['cat']) && is_numeric($_GET['cat']) )
{
	$user = false;
	$category = intval($_GET['cat']);
	$catName = "";
	$query = "SELECT NAME FROM HD_CATEGORY WHERE HD_CATEGORY.ID = '$category' LIMIT 1";
	$result = mysql_query($query);
	while( ($row = mysql_fetch_assoc($result)) )
		$catName = $row['NAME'];

	if ( isset($_GET['sub']) )
	{
			$tableQuery = "
SELECT HD_TICKET.ID as ID, HD_TICKET.TITLE as Title, 
HD_STATUS.NAME AS Status, HD_PRIORITY.NAME AS Priority, 
DATE_FORMAT(HD_TICKET.CREATED,'%Y-%m-%d') as Created,
DATE_FORMAT(HD_TICKET.MODIFIED,'%Y-%m-%d') as Modified,
DATE_FORMAT(HD_TICKET.TIME_CLOSED,'%Y-%m-%d') as Closed,
TIMESTAMPDIFF(SECOND,HD_TICKET.CREATED,HD_TICKET.TIME_CLOSED) as CloseRate,
S.FULL_NAME as Submitter, O.FULL_NAME as Owner, 
HD_TICKET.CUSTOM_FIELD_VALUE0 as Type 
FROM HD_TICKET  
JOIN HD_STATUS ON (HD_STATUS.ID = HD_TICKET.HD_STATUS_ID) 
JOIN HD_PRIORITY ON (HD_PRIORITY.ID = HD_TICKET.HD_PRIORITY_ID) 
LEFT JOIN USER S ON (S.ID = HD_TICKET.SUBMITTER_ID) 
LEFT JOIN USER O ON (O.ID = HD_TICKET.OWNER_ID)
WHERE (HD_TICKET.HD_QUEUE_ID = $mainQueueID)".($user!==false?" AND (O.FULL_NAME like '%$user%')":"");

	switch( $_GET['sub'] )
	{
		case '1': // currently_open
			$tableName = "$catName - Currently Open";
			$tableQuery .= "
	AND (HD_TICKET.HD_CATEGORY_ID = '$category')
	AND (HD_STATUS.STATE not like '%closed%')
	AND (HD_STATUS.NAME not like '%Server Status Report%')
	AND (HD_STATUS.NAME not like '%spam%')
ORDER BY Created DESC
";
			$sub = $_GET['sub'];
			break;

		case '2': // openedLast30
			$tableName = "$catName - Opened in Last 30 Days";
			$tableQuery .= "
	AND (HD_TICKET.HD_CATEGORY_ID = '$category')
	AND (HD_TICKET.CREATED >= DATE_SUB(NOW(), INTERVAL 30 DAY))
	AND (HD_STATUS.NAME not like '%Server Status Report%')
	AND (HD_STATUS.NAME not like '%spam%')
ORDER BY Created DESC
";
			$sub = $_GET['sub'];
			break;

		case '3': // closedLast30
			$tableName = "$catName - Closed in Last 30 Days";
			$tableQuery .= "
	AND (HD_TICKET.HD_CATEGORY_ID = '$category')
	AND (HD_TICKET.TIME_CLOSED >= DATE_SUB(NOW(), INTERVAL 30 DAY))
	AND (HD_STATUS.NAME not like '%Server Status Report%')
	AND (HD_STATUS.NAME not like '%spam%')
ORDER BY Closed DESC
";
			$sub = $_GET['sub'];
			break;

		case '4': // openedLast12
			$tableName = "$catName - Opened in Last 12 Months";
			$tableQuery .= "
	AND (HD_TICKET.HD_CATEGORY_ID = '$category')
	AND (HD_TICKET.CREATED >= DATE_SUB(NOW(), INTERVAL 12 MONTH))
	AND (HD_STATUS.NAME not like '%Server Status Report%')
	AND (HD_STATUS.NAME not like '%spam%')
ORDER BY Created DESC
";
			$sub = $_GET['sub'];
			break;

		case '5': // closedLast12
			$tableName = "$catName - Closed in Last 12 Months";
			$tableQuery .= "
	AND (HD_TICKET.HD_CATEGORY_ID = '$category')
	AND (HD_TICKET.TIME_CLOSED >= DATE_SUB(NOW(), INTERVAL 12 MONTH))
	AND (HD_STATUS.NAME not like '%Server Status Report%')
	AND (HD_STATUS.NAME not like '%spam%')
ORDER BY Created DESC
";
			$sub = $_GET['sub'];
			break;

		default:
			$sub = 0;
			break;
	}
	}

	print12MonthChart($category);
}
else
{
	if ( isset($_GET['r']) )
		print12MonthChart(0);
}

if ( $category > 0 && $sub > 0 )
{
	printTicketTable( $tableName, $tableQuery );
	return;
}


//*********************************************
//* Default Ticket Category List
//*********************************************
$query = "
SELECT count(HD_TICKET.ID) as total,
	HD_TICKET.HD_CATEGORY_ID as CatID,
	HD_CATEGORY.NAME as CatName,
	SUM(IF(HD_STATUS.STATE not like '%closed%',1,0)) as currently_open,
	SUM(IF(HD_TICKET.CREATED >= DATE_SUB(CURDATE(), INTERVAL 30 DAY),1,0)) as openedLast30,
	SUM(IF(HD_TICKET.TIME_CLOSED >= DATE_SUB(CURDATE(), INTERVAL 30 DAY),1,0)) as closedLast30,
	SUM(IF(HD_TICKET.TIME_CLOSED >= DATE_SUB(CURDATE(), INTERVAL 30 DAY),
		TIMESTAMPDIFF(SECOND,HD_TICKET.CREATED,HD_TICKET.TIME_CLOSED),0))
		/ SUM(IF(HD_TICKET.TIME_CLOSED >= DATE_SUB(CURDATE(), INTERVAL 30 DAY),1,0)) as avg30_s,
	SUM(IF(HD_TICKET.CREATED >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH),1,0)) as openedLast12,
	SUM(IF(HD_TICKET.TIME_CLOSED >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH),1,0)) as closedLast12,
	SUM(IF(HD_TICKET.TIME_CLOSED >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH),
		TIMESTAMPDIFF(SECOND,HD_TICKET.CREATED,HD_TICKET.TIME_CLOSED),0))
		/ SUM(IF(HD_TICKET.TIME_CLOSED >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH),1,0)) as avg12m_s
FROM HD_TICKET
	JOIN HD_STATUS ON (HD_STATUS.ID = HD_TICKET.HD_STATUS_ID) 
	LEFT JOIN HD_CATEGORY ON (HD_TICKET.HD_CATEGORY_ID=HD_CATEGORY.ID)
	LEFT JOIN USER O ON (O.ID = HD_TICKET.OWNER_ID)
WHERE (HD_STATUS.NAME not like '%Server Status Report%')
	AND (HD_STATUS.NAME not like '%spam%')
	AND (HD_TICKET.HD_QUEUE_ID = $mainQueueID)
	AND (
		((HD_STATUS.STATE not like '%closed%')
			AND HD_TICKET.CREATED >= DATE_SUB(DATE_ADD(last_day(CURDATE()), INTERVAL 1 DAY), INTERVAL 12 MONTH))
		OR (HD_TICKET.TIME_CLOSED >= DATE_SUB(DATE_ADD(last_day(CURDATE()), INTERVAL 1 DAY), INTERVAL 12 MONTH))
	)
	".($user!==false?" AND (O.FULL_NAME like '%$user%')":"")."
GROUP BY HD_TICKET.HD_CATEGORY_ID
ORDER BY SUM(IF(HD_STATUS.STATE not like '%closed%',1,0)) DESC;
";

$result = mysql_query($query);
$rows = array();
while( ($row = mysql_fetch_assoc($result)) )
{
	if ($user===false && $row['openedLast12']<=5)
		continue; // skip small categories
	
	$rows[] = $row;
}
?>


<h2><?php echo $gridTitle ?></h2>
<table class="table table-striped table-bordered table-head-bordered-bottom table-condensed">
  <thead>
    <tr>
      <th class=span2>Category</th>
      <th class=span1>Currently Open</th>
      <th class=span1>Opened Last<br/>30 Days</th>
      <th class=span1>Closed Last<br/>30 Days</th>
	  <th class=span1>30 Day<br/>Avg Closure Time</th>
      <th class=span1>Opened Last<br/>12 Months</th>
      <th class=span1>Closed Last<br/>12 Months</th>
	  <th class=span1>12 Month<br/>Avg Closure Time</th>
    </tr>
  </thead>
  <tbody>
  <?php
	$count = array();
	$count['currently_open'] = $count['openedLast30'] = $count['closedLast30'] = $count['openedLast12'] = $count['closedLast12'] = 0;
	foreach($rows as $row)
	{ //".$_SERVER['PHP_SELF']."
		print( "		<tr>
		<td>".strip_tags($row['CatName'])."</td>
		<td style=\"cursor:pointer; cursor:hand;\" onclick=\"window.location.href='?r=cat&cat=$row[CatID]&sub=1".($user!==false?"&user=".urlencode($user):"")."'\">$row[currently_open]</td>
		<td style=\"cursor:pointer; cursor:hand;\" onclick=\"window.location.href='?r=cat&cat=$row[CatID]&sub=2".($user!==false?"&user=".urlencode($user):"")."'\">$row[openedLast30]</td>
		<td style=\"cursor:pointer; cursor:hand;\" onclick=\"window.location.href='?r=cat&cat=$row[CatID]&sub=3".($user!==false?"&user=".urlencode($user):"")."'\">$row[closedLast30]</td>
		<td>".($row['closedLast30']>0?runtime(0,$row['avg30_s'],true):"")."</td>
		<td style=\"cursor:pointer; cursor:hand;\" onclick=\"window.location.href='?r=cat&cat=$row[CatID]&sub=4".($user!==false?"&user=".urlencode($user):"")."'\">$row[openedLast12]</td>
		<td style=\"cursor:pointer; cursor:hand;\" onclick=\"window.location.href='?r=cat&cat=$row[CatID]&sub=5".($user!==false?"&user=".urlencode($user):"")."'\">$row[closedLast12]</td>
		<td>".($row['closedLast12']>0?runtime(0,$row['avg12m_s'],true):"")."</td>
		</tr>\n" );

		$count['currently_open'] += $row['currently_open'];
		$count['openedLast30'] += $row['openedLast30'];
		$count['closedLast30'] += $row['closedLast30'];
		$count['openedLast12'] += $row['openedLast12'];
		$count['closedLast12'] += $row['closedLast12'];
	}

	print( "<tr class='totalsrow'><th style='text-align:right'>Totals:</th><th>$count[currently_open]</th>
		<th>$count[openedLast30]</th><th>$count[closedLast30]</th><th></th>
		<th>$count[openedLast12]</th><th>$count[closedLast12]</th><th></th></tr>\n" );
  ?>
  </tbody>
  </table>
  

<?php
function printTicketTable( $tableName, $tableQuery )
{
	global $KaceBoxDNS;
	global $user;

	print("<h2>".($user===false?"":"$user's Tickets - ")."$tableName</h2>
<table class=\"table table-striped table-bordered table-condensed\">
  <thead>
    <tr>
      <th class=span1>Ticket ID</th>
      <th class=span2>Title</th>
      <th class=span1>Submitter</th>
".($user===false?"      <th class=span1>Owner</th>":"").
"      <th class=span1>Created</th>
      <th class=span1>Modified</th>
	  <th class=span1>Closed</th>
	  <th class=span1>Time to Close</th>
    </tr>
  </thead>
  <tbody>
");

	$result = mysql_query($tableQuery);
	if ( $result !== false )
	while( ($row = mysql_fetch_assoc($result)) )
	{
		$ID = htmlspecialchars($row['ID']);
		$Title = htmlspecialchars($row['Title']);
		$Status = htmlspecialchars($row['Status']);
		$Type = htmlspecialchars($row['Type']);
		$Created = htmlspecialchars($row['Created']);	
		$Modified = htmlspecialchars($row['Modified']);
		$Closed = htmlspecialchars($row['Closed']);
		if ( $Closed == "0000-00-00" )
			$Closed = "";
		$CloseRate = htmlspecialchars($row['CloseRate']);
		$Priority = htmlspecialchars($row['Priority']);
		$Owner = htmlspecialchars($row['Owner']);
		$Submitter = htmlspecialchars($row['Submitter']);

		$StatusSpan="";
		if ($Status=="Stalled")
		{
			$StatusSpan="<span class='label label-warning'>$Status</span>";
		}

		$PriortySpan="";
		if ($Priority=="High")
		{
			$PriortySpan="<span class='label label-important'><i class='icon-exclamation-sign icon-white'></i>High</span>";
		}

		if ($Priority=="Low")
		{
			$PriortySpan="<span class='label'>Low</span>";
		}

		print( "		<tr>
		<td><a href='http://$KaceBoxDNS/adminui/ticket.php?ID=$ID' target='_blank'>$ID</a> $StatusSpan $PriortySpan</td>
		<td>$Title</td>
		<td>$Submitter</td>
".($user===false?"      <td>$Owner</td>":"").
"		<td>$Created</td>
		<td>$Modified</td>
		<td>$Closed</td>
		<td>".($Closed==""?"":runtime(0,$CloseRate,true))."</td>
		</tr>\n" );
	}

	print( "</tbody>\n</table>\n" );
}

function UMAX($v1,$v2)
{
	if ( $v1 > $v2 )
		return $v1;
	return $v2;
}
/* Modified version of a function from php.net's time() comments. */
/* Returns human-readable time between timestamps
  * KJ - Added shortformat support */
function runtime($startstamp, $endstamp, $shortformat = TRUE, $num_times = 2)
{
	$times = array(31536000 => 'year',
					2592000 => 'month',
					604800 => 'week',
					86400 => 'day',
					3600 => 'hour',
					60 => 'minute',
					1 => 'second' );
	$secs = UMAX( $endstamp - $startstamp, 0 );
	if( $secs == 0 && $endstamp == $startstamp ) // quick run!
		return ('0' . ($shortformat?'s':' seconds'));
	$count = 0;
	$time = '';

	foreach ($times as $key => $value)
	{
		if ($secs >= $key)
		{
			//time found
			$s = '';
			$time .= floor($secs / $key);

			if ((floor($secs / $key) != 1))
				$s = 's';

			if ( !$shortformat )
				$time .= ' ' . $value . $s;
			else
				$time .= substr($value,0,1);
			$count++;
			$secs = $secs % $key;

			if ($count > $num_times - 1 || $secs == 0)
				break;
			else
				$time .= (!$shortformat?', ': ' ');
		}
	}

	return $time;
}


function print12MonthChart($category)
{
global $mainQueueOwners;
global $mainQueueID;
//return;

## Can modify if you want different than 12 months.
$maxMonths = 12;

$seriesOpened = array();
$seriesClosed = array();
/* Prep open/closed */
$m = strftime('%m');
$y = strftime('%Y');
for ($i = 0; $i < $maxMonths; $i++)
{
	$key = date("Y-m", mktime(0, 0, 0, $m-$i, 1, $y));
    $seriesOpened[$key] = 0;
	$seriesClosed[$key] = 0;
}

//***************************************
// Opened Tickets (were created during time period)
//***************************************
$query1 = "
SELECT count(hdt.ID) as total,
	MONTH(hdt.CREATED) as month,
	YEAR(hdt.CREATED) as year
FROM HD_TICKET hdt
	JOIN HD_STATUS ON (HD_STATUS.ID = hdt.HD_STATUS_ID)
WHERE (hdt.HD_QUEUE_ID = $mainQueueID)
".($category>0?" AND (hdt.HD_CATEGORY_ID = '$category')":"")."
AND (
	(HD_STATUS.NAME not like '%Spam%')
	AND (HD_STATUS.NAME not like '%Server Status Report%')
)
AND (hdt.CREATED >= DATE_SUB(DATE_ADD(last_day(CURDATE()), INTERVAL 1 DAY), INTERVAL $maxMonths MONTH))
GROUP BY YEAR(hdt.CREATED), MONTH(hdt.CREATED)
ORDER BY YEAR(hdt.CREATED), MONTH(hdt.CREATED)
";

$result1 = mysql_query($query1);
while( ($row = mysql_fetch_assoc($result1)) )
{
	$total = $row["total"];
	$month = $row["month"];
	$year = $row["year"];

	$seriesOpened[sprintf("%d-%02d",$year,$month)] = $total;
}


//***************************************
// Closed Tickets
//***************************************
$query1 = "
SELECT count(hdt.ID) as total,
	MONTH(hdt.TIME_CLOSED) as month,
	YEAR(hdt.TIME_CLOSED) as year
FROM HD_TICKET hdt
	JOIN HD_STATUS ON (HD_STATUS.ID = hdt.HD_STATUS_ID)
WHERE (hdt.HD_QUEUE_ID = $mainQueueID)
".($category>0?" AND (hdt.HD_CATEGORY_ID = '$category')":"")."
	AND (
		(HD_STATUS.STATE like '%closed%')
		AND (
			(HD_STATUS.NAME not like '%Spam%')
			AND (HD_STATUS.NAME not like '%Server Status Report%')
		)
	)
AND hdt.TIME_CLOSED >= DATE_SUB(DATE_ADD(last_day(CURDATE()), INTERVAL 1 DAY), INTERVAL $maxMonths MONTH)
GROUP BY YEAR(hdt.TIME_CLOSED), MONTH(hdt.TIME_CLOSED)
ORDER BY hdt.TIME_CLOSED
";

$result1 = mysql_query($query1);
while( ($row = mysql_fetch_assoc($result1)) )
{
	$total = $row["total"];
	$month = $row["month"];
	$year = $row["year"];

	$seriesClosed[sprintf("%d-%02d",$year,$month)] = $total;
}

/* Order the dates */
ksort($seriesOpened);
ksort($seriesClosed);

/* Set up xAxis of dates */
$xAxis="";
foreach($seriesOpened as $key=>$value)
	$xAxis .= "'$key', ";
$xAxis = substr($xAxis,0,-2);

?>
		<title>Tickets Opened/Closed by Month (Last <?php echo $maxMonths ?> Months)</title>

		<script type="text/javascript" src="includes/js/jquery.min.js"></script>
		<script type="text/javascript">
$(function () {
    var chart;
    $(document).ready(function() {
        chart = new Highcharts.Chart({
            chart: {
                renderTo: 'ticketsopenclosedgrid12months',
                type: 'column',
                marginRight: 130,
                marginBottom: 25
		
            },
            title: {
                text: 'Tickets Opened/Closed by Month (Last <?php echo $maxMonths ?> Months)',
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
	/* Opened Tickets */
	print( "{
			name: 'Opened',
			data: [" );
	$yAxis = "";
	foreach($seriesOpened as $value)
		$yAxis .= "$value, ";
	$yAxis = substr($yAxis,0,-2);
	print( $yAxis );
	print( "]
		}," );

	/* Closed Tickets */
	print( "{
			name: 'Closed',
			data: [" );
	$yAxis = "";
	foreach($seriesClosed as $value)
		$yAxis .= "$value, ";
	$yAxis = substr($yAxis,0,-2);
	print( $yAxis );
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

<div id="ticketsopenclosedgrid12months" style="min-width: 500px; height: 300px; margin: 0 auto"></div>
<?php
}
?>

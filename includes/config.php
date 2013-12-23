<?php
##########################################
########CONFIG###########
#Change the following three fields 1)KaceBoxDNS 2)password 3)mainQueueID

#DNS or IP address of Dell KaceBox
#do not enter in any http:// or https://
$KaceBoxDNS="kace";

#MYSQL password on Dell Kace Box (from Settings -> General Settings)
$dbpassword="yourpassword";

#number of main queue (usually the default queue)
#used in ownerTemplate.php, ownerUnassigned.php, reportByCategory.php, and reportbyDepartment.php
$mainQueueID=0; # leave set to 0 if you want your default queue to be your mainQueue.
$mainQueueName="IT Service Desk";
// this is the IDs of the Ticket Owners by Label in the Queue Configuration for the default ticket queue.
# Add the comma-separated label group numbers if you want to override what is set up in your default ticket queue.
$mainQueueOwners="34"; // ours would be: "34,88,89";


##########################
## Reports Dropdown Items
##########################
$DROPDOWN_DASHBOARDS = 1;
$DROPDOWN_REPORTS = 2;
$dropdowns = array(
	$DROPDOWN_DASHBOARDS => "Dashboards",
	$DROPDOWN_REPORTS => "Reports"
);

$dropdownReports = array(
	'serviceDesk' => array(
		'menu'=>$DROPDOWN_DASHBOARDS,
		'displayName'=>"Service Desk",
		'refreshRate'=>10000000,
		'files'=>array(
			'reportKaceCurrentOpen.php',
			#the below file is commented out as you have to do an edit within the file before it will work
			'reportByQueueClosed.php',
			'reportByOwner3Month.php',
			'reportByCategory.php',
			# we don't use the by-department reporting, so commented out.
//			'reportByDepartment.php',
			'reportByClosed.php'
		)
	),
	'openClosedByCategory' => array(
		'menu'=>$DROPDOWN_DASHBOARDS,
		'displayName'=>"Open/Closed by Category",
		'refreshRate'=>60*60,
		'files'=>'reportGridByCategory.php'
	),
	'recentlyClosed' => array(
		'menu'=>$DROPDOWN_REPORTS,
		'displayName'=>"Recently Closed Tickets",
		'refreshRate'=>60*5,
		'files'=>'dashboards/recentlyClosedTickets.php'
	),
	'patchCompliance' => array(
		'menu'=>$DROPDOWN_REPORTS,
		'displayName'=>"Patch Compliance",
		'refreshRate'=>10000000,
		'files'=>'dashboards/patchCompliance.php'
	),
	'qcClosedTickets' => array(
		'menu'=>$DROPDOWN_REPORTS,
		'displayName'=>"Q/C Closed Tickets",
		'refreshRate'=>10000000,
		'files'=>'dashboards/QCrandom3ClosedTickets.php'
	),
	'softwareInstalls' => array(
		'menu'=>$DROPDOWN_REPORTS,
		'displayName'=>"Recent Software Changes",
		'refreshRate'=>10000000,
		'files'=>'dashboards/softwareInstalls.php'
	)
);

########END CONFIG#######
##########################################


########################
######NOTES#############

#===========================================================
#Files that will need to be edited to fit your organization:
#===========================================================
#includes/config.php - REQUIRED. Your KBox's DNS name and your report user's password (from Settings -> General Settings). Also, your main (default) ticket queue ID and name.
#includes/nav.php - REQUIRED. Update the userList array with your users.
#index.php - Pick the reports in the switch() at line 70 that you want to uncomment if you have the need.
#buildTickets.php - if you have a queue for computer builds, otherwise, don't worry.
#ownerUnassigned.php - You may want to rename the Custom Field 0 column (or remove it altogether if you don't use it).
#reportByQueueClosed.php - REQUIRED. Queue IDs and plot titles. You can disable a couple plots too.
#reportForOwner12Months.php - Number of months and chart title (or don't worry if you like the defaults).
#webTickets.php - If you have a web-generated tickets queue, edit the queue ID in here.

###### You will have to edit the above files manually

# Queue-less (or auto-configured) files include; i.e. you will not have to edit these
#ownerOldest.php - shows you who owns the oldest ticket for all queues
#reportByClosed.php - shows you tickets closed over the last 30 days for all queues
#reportByClosedAverage.php - gives an average closed over the last 30 days for all queues
#reportKaceCurrentOpen.php - tells you how many tickets are not closed for all queues
#reportByCategory.php - creates the category pie chart on the dashboard
#reportByOwner3Month.php - Creates the user open/closed bar chart on the dashboard
#reportGridByCategory - Creates the 3month/12month opened/closed table under Dashboards.

######END NOTES#########
########################


if ( !($dbh=mysql_connect("$KaceBoxDNS", "R1", "$dbpassword")) )
	die('I cannot connect to the database because: ' . mysql_error());
mysql_select_db ("ORG1");


#####################################################
#### Auto poll default queue for main queue ID
#####################################################
if ( $mainQueueID == 0 ) // If unconfigured
{
	$query = "
SELECT
 hdq.ID, hdq.NAME
FROM SETTINGS
LEFT JOIN HD_QUEUE hdq
 ON hdq.ID=SETTINGS.VALUE
WHERE
 SETTINGS.NAME='HD_DEFAULT_QUEUE_ID'
LIMIT 1
";
	$results = mysql_query($query);
	$num = @mysql_numrows($results);
	if ( $num > 0 && ($row = mysql_fetch_assoc($results)) != NULL && $row['NAME'] != NULL )
	{
		$mainQueueID = $row['ID'];
		$mainQueueName = $row['NAME'];
	}
}

#####################################################
#### Pull the label groups that are ticket owners of the default ticket queue.
#####################################################
if ( $mainQueueOwners == NULL || strlen($mainQueueOwners) < 1 ) // If not configured
{
	$query = "
SELECT
	QOL.LABEL_ID
FROM
	HD_QUEUE Q
	LEFT JOIN HD_QUEUE_OWNER_LABEL_JT QOL ON QOL.HD_QUEUE_ID=Q.ID
WHERE Q.ID=$mainQueueID
";

	$results = mysql_query($query);
	$num = @mysql_numrows($results);
	if ( $num > 0 )
	{
		$mainQueueOwners = ""; // reset to empty

		while( ($row = mysql_fetch_assoc($results)) != NULL )
		{
			$mainQueueOwners .= "$row[LABEL_ID],";
		}
		$mainQueueOwners = substr($mainQueueOwners,0,-1); // chop off the trailing comma
	}
}
?>

<?php
##########################################
########CONFIG###########
#Change the following three fields 1)KaceBoxDNS 2)password 3)mainQueueID

#DNS or IP address of Dell KaceBox
#do not enter in any http:// or https://
$KaceBoxDNS="kace";

#MYSQL password on dell Kace Box (from Settings -> General Settings)
$dbpassword="yourpassword";

#number of main queue
#used in ownerTemplate.php, ownerUnassigned.php, reportByCategory.php, and reportbyDepartment.php
$mainQueueID=1;
$mainQueueName="IT Service Desk";

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

?>

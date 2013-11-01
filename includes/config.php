<?
##########################################
########CONFIG###########
#Change the following three fields 1)KaceBoxDNS 2)password 3)mainQueue

#DNS or IP address of Dell KaceBox
#do not enter in any http:// or https://
$KaceBoxDNS="";

#MYSQL password on dell Kace Box
$password="";

#number of main queue
#used in ownerTemplate.php, ownerUnassigned.php, and reportbyDepartment.php
$mainQueue="10";
$mainQueueName="Support Requets";


########CONFIG###########
##########################################


########################
######NOTES#############


#hard coded queues can be found within
#webTickets.php - shows a listing of currently open web tickets
#buildTickets.php - shows a listing of currently opened computer build tickets
#reportByQueueClosed.php (three hard coded here)
#you will have to edit the above files manually

#queue-less files include; i.e. you will not have to edit these
#ownerOldest.php - shows you who owns the oldest ticket for all queues
#reportByClosed.php - shows you tickets closed over the last 30 days for all queues
#reportByClosedAverage.php - gives an average closed over the last 30 days for all queues
#reportKaceCurrentOpen.php - tells you how many tickets are not closed for all queues

######NOTES#############
########################





$dbh=mysql_connect ("$KaceBoxDNS", "R1", "$password") or die
('I cannot connect to the database because: ' .
mysql_error());
mysql_select_db ("ORG1");


?>

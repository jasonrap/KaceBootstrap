<?php
include_once("includes/config.php");

######################################
# Used within the navigation bar. Needs to match exactly the user's Full Name field.
// TODO: Have the list be user IDs, or at least query for user IDs,
//   so we can use the IDs in the GET URL, rather than names. It's safer that way.
// Here's the code to do (most of) it:
//****************************************
// Get list of ticket owner users for nav
//****************************************
$query1 = "
SELECT u.ID,u.FULL_NAME
 FROM `USER` u
  LEFT JOIN USER_LABEL_JT uljt ON (uljt.USER_ID=u.ID)
  LEFT JOIN LABEL l ON (uljt.LABEL_ID=l.ID)
 WHERE l.NAME = 'All Ticket Owners'
  AND u.HD_DEFAULT_QUEUE_ID >= 0
 ORDER BY u.FULL_NAME
";
$result1 = mysql_query($query1);
$numUsers = mysql_numrows($result1);
$userList = array();
$i = 0;
while( ($row = mysql_fetch_assoc($result1)) )
{
	$userList[++$i] = array( 'id'=>$row['ID'], 'user'=>$row['FULL_NAME'] );
	$username = explode(' ',$row['FULL_NAME']);
	if(is_array($username))
		$username = ucfirst($username[0]);
	$username = htmlspecialchars($username);
	$userList[$i]['shortname'] = $username;
}

// Check for duplicate first names and make more descriptive if needed
foreach($userList as $key => $row)
{
	$found = 0;
	foreach($userList as $checkKey => $checkRow)
	{
		if ( $row['shortname'] == $checkRow['shortname'] ) // Duplicate first names
		{
			if ( ++$found == 1 ) // convert source name on first contact
			{
				$username = explode(' ',$row['user']);
				if(is_array($username))
					$username = ucfirst($username[0]) . ' ' . substr(ucfirst($username[1]),0,1); // make a first name and last initial
				$username = htmlspecialchars($username);
				$userList[$key]['shortname'] = $username;
			}
			// Modify contact shortname
			$username = explode(' ',$checkRow['user']);
			if(is_array($username))
				$username = ucfirst($username[0]) . ' ' . substr(ucfirst($username[1]),0,1); // make a first name and last initial
			$username = htmlspecialchars($username);
			$userList[$checkKey]['shortname'] = $username;
		}
	}
}

################################################
## Update this user list for your environment.
## Used within the navigation bar.
## Needs to match exactly the user's Full Name field.
################################################
// We could pull the userlist dynamically from Kace (see code above),
//   but this will allow you to include only those you want to see.
$userList = array(
	'Kirk Johnson',
	'Jason Rappaport'
);

######################################
$r="";
$d="";
$u="";
$p="";

if (isset($_GET['u']))
{
	$u = $_GET['u'];
}

if ( isset($_GET['r']) )
	$r = $_GET['r'];

$refreshRate=60;

// Modify the refresh rate for report/dashboard pages
switch ($r)
{
	case "serviceDesk":
	case "patchCompliance":
	case "qcClosedTickets":
		$refreshRate=10000000; // basically, do not refresh
		break;

	case "cat":
		$refreshRate=60*60;
		break;

	case 'recentlyClosed':
		$refreshRate=60*5;
		break;

	default:
		break;
}


echo "<ul class='nav'>\n";

foreach( $userList as $username )
{
	$parts = explode(' ',$username);
	$shortName = (is_array($parts)?$parts[0]:$parts);
	$active = (($username == $u)?"active":"");
	print( "	<li class='nav $active'><a href='index.php?u=$username'>$shortName</a></li>\n" );
}

echo "
	<li class='dropdown'>
		<a class='dropdown-toggle' data-toggle='dropdown' href='#'>
			Dashboards
			<b class='caret'></b>
		</a>
		<ul class='dropdown-menu'>
			<li class='nav '><a href='index.php?r=serviceDesk'><i class='icon-picture'></i> Service Desk</a></li>
			<li class='nav '><a href='index.php?r=cat'><i class='icon-picture'></i> Open/Closed by Category</a></li>
			<li class='nav '><a href='index.php?r=recentlyClosed'><i class='icon-picture'></i> Recently Closed Tickets</a></li>
		</ul>
	</li>
	<li class='dropdown'>
		<a class='dropdown-toggle' data-toggle='dropdown' href='#'>
			Reports
			<b class='caret'></b>
		</a>
		<ul class='dropdown-menu'>
			<li class='nav '><a href='index.php?r=patchCompliance'><i class='icon-picture'></i> Patch Compliance</a></li>
			<li class='nav '><a href='index.php?r=qcClosedTickets'><i class='icon-picture'></i> Q/C Closed Tickets</a></li>
		</ul>
	</li>
</ul>
";
?>

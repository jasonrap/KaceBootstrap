<?php
include_once("includes/config.php");

// TODO: Have the list be user IDs, or at least query for user IDs,
//   so we can use the IDs in the GET URL, rather than names. It's safer that way.
//****************************************
// Get list of ticket owner users for nav
//****************************************
$query1 = "
SELECT u.ID,u.FULL_NAME
 FROM `USER` u
  LEFT JOIN USER_LABEL_JT uljt ON (uljt.USER_ID=u.ID)
  LEFT JOIN LABEL l ON (uljt.LABEL_ID=l.ID)
 WHERE -- l.NAME = 'All Ticket Owners'
	l.ID IN ($mainQueueOwners)
  AND u.HD_DEFAULT_QUEUE_ID >= 0
  GROUP BY u.ID
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
		if ( $key == $checkKey )
			continue; // don't check against itself

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
/*$userList = array(
	'Kirk Johnson',
	'Jason Rappaport'
);*/

######################################
$r="";
$d="";
$u="";
$p="";

if (isset($_GET['u']))
	$u = $_GET['u'];
if ( isset($_GET['r']) )
	$r = $_GET['r'];

$refreshRate=60;

// Lots of validation checking
if ( array_key_exists($r,$dropdownReports) )
{
	if ( isset($dropdownReports["$r"]) && is_array($dropdownReports["$r"])
	&& isset($dropdownReports["$r"]['refreshRate']) )
	{
		$refreshRate = $dropdownReports["$r"]['refreshRate'];
		if ( !is_numeric($refreshRate) )
			$refreshRate = 60; // jic
	}
}
/*
// Modify the refresh rate for report/dashboard pages
switch ($r)
{
	case "serviceDesk":
	case "patchCompliance":
	case "qcClosedTickets":
	case "softwareInstalls":
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
*/

echo "<ul class='nav'>\n";

// Display Ticket Owners across the nav bar.
foreach( $userList as $username )
{
	if ( is_array($username) )
	{
		$shortName = $username['shortname'];
		$username = $username['user'];
	}
	else
	{
		$parts = explode(' ',$username);
		$shortName = (is_array($parts)?$parts[0]:$parts);
	}
	$active = (($username == $u)?"active":"");
	print( "	<li class='nav $active'><a href='index.php?u=$username'>$shortName</a></li>\n" );
}

// Generate the dropdown menus after the Ticket Owners list.
foreach( $dropdowns as $dropdownID => $dropdownName )
{
	echo "
	<li class='dropdown'>
		<a class='dropdown-toggle' data-toggle='dropdown' href='#'>
			$dropdownName
			<b class='caret'></b>
		</a>
		<ul class='dropdown-menu'>
";
	// Populate the dropdown menu.
	foreach( $dropdownReports as $paramID => $item )
	{
		if ( !is_array($item) || !isset($item['menu'])
		|| $item['menu'] != $dropdownID )
			continue;

		echo "<li class='nav '><a href='index.php?r=$paramID'><i class='icon-picture'></i> $item[displayName]</a></li>";
	}

	echo "
		</ul>
	</li>
";
}
/* // The old way of doing it.
echo "
	<li class='dropdown'>
		<a class='dropdown-toggle' data-toggle='dropdown' href='#'>
			Dashboards
			<b class='caret'></b>
		</a>
		<ul class='dropdown-menu'>
			<li class='nav '><a href='index.php?r=serviceDesk'><i class='icon-picture'></i> Service Desk</a></li>
			<li class='nav '><a href='index.php?r=openClosedByCategory'><i class='icon-picture'></i> Open/Closed by Category</a></li>
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
			<li class='nav '><a href='index.php?r=softwareInstalls'><i class='icon-picture'></i> Recent Software Changes</a></li>
		</ul>
	</li>
</ul>
";*/
?>

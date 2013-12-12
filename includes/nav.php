<?php
include_once("includes/config.php");

######################################
# Used within the navigation bar. Needs to match exactly the user's Full Name field.
// TODO: Have the list be user IDs, or at least query for user IDs, so we can use the IDs in the GET URL, rather than names. It's safer that way.
// Here's the code to do (most of) it:
//****************************************
// Get list of ticket owner users for nav
//****************************************
$query1 = "
SELECT u.ID,u.USER_NAME
 FROM `USER` u
  LEFT JOIN USER_LABEL_JT uljt ON (uljt.USER_ID=u.ID)
  LEFT JOIN LABEL l ON (uljt.LABEL_ID=l.ID)
 WHERE l.NAME = 'All Ticket Owners'
  AND u.HD_DEFAULT_QUEUE_ID >= 0
 ORDER BY u.USER_NAME
";
$result1 = mysql_query($query1);
$numUsers = mysql_numrows($result1);
$userList = array();
$i = 0;
while( ($row = mysql_fetch_assoc($result1)) )
{
	$userList[++$i] = array( 'id'=>$row['ID'], 'user'=>$row['USER_NAME'] );
	$username = explode('.',$row['USER_NAME']);
	if(is_array($username))
		$username = ucfirst($username[0]);
	$username = htmlspecialchars($username);
	$users[$i]['shortname'] = $username;
}

################################################
## Update this user list for your environment
##############
// We could pull the userlist dynamically from Kace, but this will allow you to include only those you want to see.
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

$refreshRate=60;

switch ($u)
{
	case "r":
		$r="active";
		$refreshRate=10000000;
		break;

	case "r2":
		$r="active";
		$refreshRate=10000000;
		break;

	case 'cat':
		$grid="active";
		break;

	case "p":
		$refreshRate=10000000;
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
			Dasboards
			<b class='caret'></b>
		  </a>
		<ul class='dropdown-menu'>
			<li class='nav '><a href='index.php?u=r'><i class='icon-picture'></i> Service Desk</a></li>
			<li class='nav '><a href='index.php?u=cat'><i class='icon-picture'></i> Open/Closed by Category</a></li>
		</ul>
	</li>
</ul>
";
?>

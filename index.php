<!DOCTYPE html>
<html lang="en">
  <head>
    
<?php
	include_once("includes/header.php");
?>
    <!-- Le styles -->
    <link href="includes/css/bootstrap.css" rel="stylesheet">

    <style>
      body {
        padding-top: 60px; /* 60px to make the container go all the way to the bottom of the topbar */
      }
    </style>
    <link href="includes/css/bootstrap-responsive.css" rel="stylesheet">

    <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <!-- Le fav and touch icons -->
    <link rel="shortcut icon" href="includes/ico/favicon.ico">
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="includes/ico/apple-touch-icon-144-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="includes/ico/apple-touch-icon-114-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="includes/ico/apple-touch-icon-72-precomposed.png">
    <link rel="apple-touch-icon-precomposed" href="includes/ico/apple-touch-icon-57-precomposed.png">
  </head>

  <body>

    <div class="navbar navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container">
          <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </a>
          <a class="brand" href="index.php">Service Desk</a>
          <div class="nav-collapse">
            <ul class="nav">
<?php
                include_once("includes/nav.php");
?>
            </ul>
          </div><!--/.nav-collapse -->
        </div>
      </div>
    </div>
    <div class="container">

    <meta charset="utf-8" HTTP-EQUIV="refresh" CONTENT="<?php echo $refreshRate ?>">

<?php

if (isset($_GET['u']))
{
	$u = $_GET['u'];
}
else
	$u = NULL;

####################################################
# This switch statement reads in the URL variables to then include the appropriate files.  
# You can customize the order in which things are displayed by simply reorganizing the include files.
# You can also prevent things from being shown by simply commenting them out.                      
#################################################### 
switch ($u) {
//************************

case "r":
	include_once("reportKaceCurrentOpen.php");
	echo "<h2>Service Desk Dashboard</h2> <span class='label label-success'>Currently open = $currentlyOpen</span>";
	#the below file is commented out as you have to do an edit within the file before it will work
	include_once("reportByQueueClosed.php");
	include_once("reportByOwner3Month.php");
	include_once("reportByCategory.php");
	# we don't use the by-department reporting, so commented out.
	//include_once("reportByDepartment.php");
	include_once("reportByClosed.php");
	break;

case 'cat':
	include_once("reportGridByCategory.php");
	break;

case 'recentlyClosed':
	include_once("dashboards/recentlyClosedTickets.php");
	break;

default:
	if ( $u !== NULL )
	{
		$user="DefaultUser";
		if ( isset($_GET['u']) )
		{
			$user = strip_tags($_GET['u']);
			$user = ucwords($user);
			$user = mysql_escape_string($user);
		}
		include_once("reportForOwner12Months.php");
		include_once("ownerTemplate.php");
		include_once("reportGridByCategory.php");
	}
	else
	{
		#if nothing (no user or dashboard) is selected show this:
		include_once("ownerUnassigned.php");
		# the below file is commented out as you have to do an edit within the file before it will work
		# Edit: We don't use a System Builds queue like the original author, and have commented it out.
		//include_once("buildTickets.php");
		include_once("openTickets.php");
		# Edit: We don't use a separate queue for end-user, web-filled tickets (yet) so commented out.
		//include_once("webTickets.php");
	}
	break;
}
?>

    </div> <!-- /container -->

    <!-- Le javascript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="includes/js/jquery.js"></script>
    <script src="includes/js/bootstrap-transition.js"></script>
    <script src="includes/js/bootstrap-alert.js"></script>
    <script src="includes/js/bootstrap-modal.js"></script>
    <script src="includes/js/bootstrap-dropdown.js"></script>
    <script src="includes/js/bootstrap-scrollspy.js"></script>
    <script src="includes/js/bootstrap-tab.js"></script>
    <script src="includes/js/bootstrap-tooltip.js"></script>
    <script src="includes/js/bootstrap-popover.js"></script>
    <script src="includes/js/bootstrap-button.js"></script>
    <script src="includes/js/bootstrap-collapse.js"></script>
    <script src="includes/js/bootstrap-carousel.js"></script>
    <script src="includes/js/bootstrap-typeahead.js"></script>
    <script src="includes/js/numpad.js" type="text/javascript"></script>

  </body>
</html>

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
	$u = $_GET['u'];
else
	$u = NULL;

if ( isset($_GET['r']) )
{
	$r = $_GET['r'];

	####################################################
	# This section reads the URL r (report/dashboard) variable and finds the files from the reports table.
	# You can customize the order in which things are displayed by simply reorganizing the files in the table.
	# You can also prevent things from being shown by simply commenting them out from the table files list.
	####################################################
	if ( isset($dropdownReports[$r]) )
	{
		$report = $dropdownReports[$r];
		if ( is_array($report) && isset($report['files']) )
		{
			$files = $report['files'];
			if ( is_array($files) )
			{
				foreach($files as $file)
					include($file);
			}
			else
				include($files);
		}
	}
	else
	{
		// custom hidden reports. Use this until enough to justify adding a 'hidden' value to the dropdown table.
		switch($r)
		{
			case 'cat':
				include_once("reportGridByCategory.php");
				break;
		}
	}
/*	// Modify in includes/config.php now
	switch($r)
	{
		case 'serviceDesk':
			include("reportKaceCurrentOpen.php");
			#the below file is commented out as you have to do an edit within the file before it will work
			include("reportByQueueClosed.php");
			include("reportByOwner3Month.php");
			include("reportByCategory.php");
			# we don't use the by-department reporting, so commented out.
			//include("reportByDepartment.php");
			include("reportByClosed.php");
			break;

		case 'openClosedByCategory':
			include("reportGridByCategory.php");
			break;

		case 'recentlyClosed':
			include("dashboards/recentlyClosedTickets.php");
			break;

		case 'patchCompliance':
			include("dashboards/patchCompliance.php");
			break;

		case 'qcClosedTickets':
			include("dashboards/QCrandom3ClosedTickets.php");
			break;

		case 'softwareInstalls':
			include("dashboards/softwareInstalls.php");
			break;

		default:
			break;
 	}*/
}
else
{
	$r = NULL;

	####################################################
	# This switch statement reads in the URL u (user) variable to then include the appropriate files.  
	# You can customize the order in which things are displayed by simply reorganizing the include files.
	# You can also prevent things from being shown by simply commenting them out.
	####################################################
	switch ($u)
	{
		default:
			if ( $u !== NULL )
			{
				$user="DefaultUser";
				if ( isset($_GET['u']) ) // override previous $u
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
	<!-- our custom js functions -->
	<script src="includes/js/kacebootstrap.js" type="text/javascript"></script>

  </body>
</html>

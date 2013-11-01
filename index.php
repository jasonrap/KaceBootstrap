<!DOCTYPE html>
<html lang="en">
  <head>
    
<? include_once("includes/header.php");

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
           
              <?

                include_once("includes/nav.php");
                ?>
	


            </ul>
          </div><!--/.nav-collapse -->
        </div>
      </div>
    </div>
    <div class="container">

    <meta charset="utf-8" HTTP-EQUIV="refresh" CONTENT="<? echo $refreshRate ?>">

<?

####################################################
#This switch statement reads in the URL variables to then include the appropriate files.  
#you can customize the order in which things are displayed by simply reorganizing the include files.
#you can also prevent things from being shown by simply commenting them out.                      
####################################################                    
                    
if (isset($_GET['u']))
{
$u = $_GET['u'];
}

switch ($u) {
//************************
case "User1":
#note, depending on the order in which a support rep wants to see their personal view, just change the order of the include files accordingly.  
    include_once("ownerTemplate.php");
    include_once("ownerUnassigned.php");
    #the below file is commented out as you have to do an edit within the file before it will work
    #include_once("buildTickets.php");
break;

case "User2":
    include_once("ownerTemplate.php");
    include_once("ownerUnassigned.php");
    #the below file is commented out as you have to do an edit within the file before it will work
    #include_once("buildTickets.php");
break;




case "r":
#this is the dashboard view.      
    include_once("reportKaceCurrentOpen.php");
    echo "<h2>Service Desk Dashboard</h2> <span class='label label-success'>Currently open = $currentlyOpen</span>";
    #the below file is commented out as you have to do an edit within the file before it will work
    include_once("reportByQueueClosed.php"); 
    include_once("reportByDepartment.php");
    include_once("reportByClosed.php");
break;


default:
#if nothing is selected show this:
    include_once("ownerOldest.php");
    include_once("ownerUnassigned.php");
    #the below files are commented out as you have to do an edit within the files before they will work
    #include_once("buildTickets.php");
    #include_once("webTickets.php");
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

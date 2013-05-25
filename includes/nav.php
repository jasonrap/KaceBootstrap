<?

include_once("config.php");

$user1="";
$user2="";
$d="";
$u="";
$p="";

if (isset($_GET['u']))
{
$u = $_GET['u'];
}

$refreshRate=60;

switch ($u) {
//************************
case "user1":
$user1="active";
break;


case "user2":
$user2="active";
break;





case "r":
$r="active";
$refreshRate=10000000;
break;

case "r2":
$r="active";
$refreshRate=10000000;
break;

case "p":
$refreshRate=10000000;
break;

default:
break;

}



echo "
<ul class='nav'>
              <li class='nav $user1'><a href='index.php?u=user1'>User1</a></li>
              <li class='nav $user2'><a href='index.php?u=user2'>User2</a></li>
	      
<li class='dropdown'>
    <a class='dropdown-toggle ' data-toggle='dropdown' href='#'>
        Dasboards
        <b class='caret'></b>
      </a>
	<ul class='dropdown-menu'>
	


              <li class='nav '><a href='index.php?u=r'><i class='icon-picture'></i> Service Desk</a></li> 
              

    </ul>
  </li>




</ul> 






";

     



<html>
<?php
//////////////////////////////////////////////////////////
//LOAD LIBRARIES
//////////////////////////////////////////////////////////
$HOST=$_SERVER["HTTP_HOST"];
$SCRIPTNAME=$_SERVER["SCRIPT_FILENAME"];
$ROOTDIR=rtrim(shell_exec("dirname $SCRIPTNAME"));
require("$ROOTDIR/web/aristarchus.php");
$body="";

//////////////////////////////////////////////////////////
//INITIALIZATION
//////////////////////////////////////////////////////////
$headers=getHeaders();
$head=getHead();
$mainmenu=getMainMenu();

//////////////////////////////////////////////////////////
//PAGE MENU OPTIONS
//////////////////////////////////////////////////////////
$mainmenu.=<<<M
<span class="botonmenu">
  <a class="inverted" href="aristarco6.php#upload">Upload</a>
</span>
<span class="botonmenu">
  <a class="inverted" href="aristarco6-contacts.php">Contact times</a>
</span>
M;
$title=<<<T
<center><h3>Aristarchus 6: The Transit of Mercury of May 9 2016</h3></center>
T;
$body.=$title;
//////////////////////////////////////////////////////////
//BODY
//////////////////////////////////////////////////////////
$body.=<<<B
<h3>Contact times calculator</h3>
<p>
The web page below allows you to calculate the precise contact times
of the transit for your specific location. The tool was developed by
Xavier M. Jubier.  A link to the original web page is provided below.
</p>
<p>
<a href="http://xjubier.free.fr/en/site_pages/MercuryTransitCalculator.html?Transit=%2720160509%27&Lat=6.2&Lng=-75.4&Elv=1670.0&TZ=%27-0500%27&DST=0&Calc=1"
target="_blank">Open the contact time webpage in another tab</a>.
</p>
B;

$body.=<<<B
<iframe
src="http://xjubier.free.fr/en/site_pages/MercuryTransitCalculator.html?Transit=%2720160509%27&Lat=6.2&Lng=-75.4&Elv=1670.0&TZ=%27-0500%27&DST=0&Calc=1"
width="100%"
height="1200"
frameborder=0
>
</iframe>
B;


//////////////////////////////////////////////////////////
//FOOTER
//////////////////////////////////////////////////////////
$messages=getMessages();
$footer=getFooter();

//////////////////////////////////////////////////////////
//RENDER
//////////////////////////////////////////////////////////
echo renderPage($headers,$head,$mainmenu,$body,$footer,$messages);
?>

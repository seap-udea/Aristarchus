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
  <a class="inverted" href="aristarco6.php">Mercury Transit</a>
</span>
<span class="botonmenu">
  <a class="inverted" href="http://bit.ly/aristarco-saa-6-en" target="_blank">Campaign Site</a>
</span>
M;

//////////////////////////////////////////////////////////
//BODY
//////////////////////////////////////////////////////////

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//WELCOME BOX
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$body.=fadingBox("
<center style='font-size:1.2em;margin-bottom:0.2em;'>
Welcome to the Activity Site of
the <a href='http://bit.ly/campana-aristarcho' target='_blank'>Aristarchus
Campaigns</a>
</center>
In this website you will find on-line tools and activities intended to
make more complete your experience with the Campaigns.  You will also
find here the upload forms required to submit your observations.
");

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//WHAT'S NEW
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$body.="<h2>What's New</h2>";

$body.=newsBox("01/05/2016","Upload page of Mercury transit images",
"

The transit of Mercury of May 9 is coming and we are preparing the
online tools to assist participants in the Aristarchus Campaign to
analyze and submit their images. The <a href=aristarco6.php>submission
page</a> is ready.  Using this page you will be able not only to
submit your images but also to perform basic analysis on them.

"
);

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

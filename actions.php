<?php
//////////////////////////////////////////////////////////
//LOAD LIBRARIES
//////////////////////////////////////////////////////////
$HOST=$_SERVER["HTTP_HOST"];
$SCRIPTNAME=$_SERVER["SCRIPT_FILENAME"];
$ROOTDIR=rtrim(shell_exec("dirname $SCRIPTNAME"));
require("$ROOTDIR/web/aristarchus.php");
$output="";

if(0){}
//////////////////////////////////////////////////////////
//ACTIONS
//////////////////////////////////////////////////////////
else if($action=="test"){
  $output.="DATE:$DATE<br/>";
}
//////////////////////////////////////////////////////////
//DEFAULT
//////////////////////////////////////////////////////////
else{
  $output="Action '$action' not recognized.";
}
echo $output;   
?>

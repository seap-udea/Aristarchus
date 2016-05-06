<?php
//////////////////////////////////////////////////////////
//LOAD LIBRARIES
//////////////////////////////////////////////////////////
$HOST=$_SERVER["HTTP_HOST"];
$SCRIPTNAME=$_SERVER["SCRIPT_FILENAME"];
$ROOTDIR=rtrim(shell_exec("dirname $SCRIPTNAME"));
require("$ROOTDIR/web/aristarchus.php");
$output="";
if(isset($params)){
  $ps=parseParams($params);
}

if(0){}
//////////////////////////////////////////////////////////
//ACTIONS
//////////////////////////////////////////////////////////
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//LOGOUT
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
else if($action=="logout"){
  $urlref="$SITEURL/index.php";
  session_unset();
  header("Refresh:0;url=$urlref");
}
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//CROP
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
else if($action=="crop"){
  //PARAMETERS
  $imgsrc=$ps["imgsrc"];
  $coords=$ps["coords"];
  $typeimg=$ps["typeimg"];

  $dirname=rtrim(shell_exec("dirname $imgsrc"));
  $fname=rtrim(shell_exec("basename $imgsrc"));

  $size=getimagesize($imgsrc);
  $width=$size[0];$height=$size[1];
  $cs=preg_split("/,/",$coords);
  $x=$cs[0]*$width;
  $y=$cs[1]*$height;
  $dx=($cs[2]-$cs[0])*$width;
  $dy=($cs[3]-$cs[1])*$height;
  shell_exec("convert -crop ${dx}x${dy}+$x+$y $imgsrc $imgsrc-crop__$typeimg.png");
  $output="Done.";
}
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//LOCATE
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
else if($action=="locate"){
  //PARAMETERS
  $imgsrc=$ps["imgsrc"];
  $coords=$ps["coords"];
  $cmd="$PYTHONCMD bin/aristarco6-locate.py $imgsrc $coords";
  $output=shell_exec($cmd);
}
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//LOCATE
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
else if($action=="align"){
  //PARAMETERS
  $obsdir=$ps["obsdir"];
  $images=$ps["images"];

  //RUN PYTHON PROCEDURE
  $cmd="$PYTHONCMD bin/aristarco6-align.py $obsdir $images";
  $output=$cmd;
  //$output=shell_exec($cmd);
  //sleep(1);
}
//////////////////////////////////////////////////////////
//DEFAULT
//////////////////////////////////////////////////////////
else{
  $output="Action '$action' not recognized.";
}
echo $output;   
?>

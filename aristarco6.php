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
//HEADER, HEAD AND MAINMENU
$headers=getHeaders();
$head=getHead();
$mainmenu=getMainMenu();
$onload="";

//ON DOCUMENT READY
$onload.="\n<script>\n$(window).load(function(){\n";

//OBSERVATION ID
if(!isset($obsid)){$obsid=generateRandomString(6);}
$obsdir="data/Aristarco6/$obsid";

//SEARCH ALREADY UPLOADED IMAGES
$obsimages=listImages($obsid);

//////////////////////////////////////////////////////////
//PAGE MENU
//////////////////////////////////////////////////////////
$mainmenu.=<<<M
<span class="botonmenu">
  <a class="inverted" href="aristarco6.php?mode=contacts">Contact times</a>
</span>
<span class="botonmenu">
  <a class="inverted" href="aristarco6.php">Submit observations</a>
</span>
<span class="botonmenu">
  <a class="inverted" href="aristarco6-align.php">Images alignment</a>
</span>
M;
$title=<<<T
<center><h3>Aristarchus 6: Transit of Mercury, May 9 2016</h3></center>
T;
$body.=$title;

//AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA
//AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA
//AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA
//////////////////////////////////////////////////////////
//ACTIONS
//////////////////////////////////////////////////////////
//AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA
//AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA
//AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA

if(!isset($action)){}

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//LOAD
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
else if($action=="load")
{
  if($observation=mysqlCmd("select * from Aristarco6 where obsid='$obsid' and code='$code'")){
    foreach(array_keys($ARISTARCO6_FIELDS) as $field) $$field=$observation["$field"];
    statusMsg("Observation '$obsid' loaded");
    $onload.="$('.helpbox').hide();";

    //LOAD IMAGES INFORMATION
    $i=1;
    foreach($obsimages as $img){
      preg_match("/([^\.]+)\.\w+/",$img,$matches);
      $fname=$matches[1];
      include("$obsdir/$fname.php");
      $var="mercury$i";$$var=$mercury;
      $var="sunspot$i";$$var=$sunspot;
      $i++;
    }
  }else{
    errorMsg("Code provided not valid");
  }
}

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//SAVE
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
else if($action=="Next Step" or $action=="Save")
{
  //$body.=print_r($_POST,true);

  //=====================================
  //PREPARE OBSERVATIONS DIRECTORY
  //=====================================
  shell_exec("mkdir -p $obsdir");

  //=====================================
  //REMOVE HELP BOXES
  //=====================================
  $onload.="$('.helpbox').hide();";

  //=====================================
  //REMOVE IMAGES
  //=====================================
  if(isset($nimg)){
    $obsimages=listImages($obsid);
    for($i=1;$i<=$nimg;$i++){
      $var="remove$i";
      if(isset($$var)){
	$val=$$var;
	preg_match("/([^\.]+)\.\w+/",$val,$matches);
	$fname=$matches[1];
	statusMsg("Deleting image $fname...");
	shell_exec("rm $obsdir/$fname*.*");
      }
    }
    $obsimages=listImages($obsid);
    $nimg=count($obsimages);
    if($nimg==0){
      $step=2;
      mysqlCmd("update Aristarco6 set step='$step' where obsid='$obsid'");
    }
  }

  //=====================================
  //CHECK PROVIDED INFORMATION
  //=====================================
  if($step>=1){
    //%%%%%%%%%%%%%%%%%%%%
    //CHECK STEP1 OPTIONS
    //%%%%%%%%%%%%%%%%%%%%
    $noblank=array("sitename",
		   "latitude","longitude","timezone","altitude",
		   "name","email","code");
    foreach($noblank as $var){
      if(isBlank($$var)){
	errorMsg("No $var provided");
	goto endaction;
      }
    }

    //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
    //SAVE CALIBRATION IMAGE
    //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
    $calfile=$_FILES["calimage"];
    if($calfile["size"]>0){
      $fname=$calfile["name"];
      preg_match("/\.(\w+)$/",$fname,$matches);
      $ext=$matches[1];
      $filename="${obsid}-calibration.$ext";
      statusMsg("Saving calibration image $fname as $filename...");
      $tmp=$calfile["tmp_name"];
      shell_exec("cp $tmp '$obsdir/$filename'");
      $calimage=$filename;
    }
  }
  if($step>=2){

    //%%%%%%%%%%%%%%%%%%%%
    //CHECK STEP2 OPTIONS
    //%%%%%%%%%%%%%%%%%%%%
    if(count($obsimages)==0){
      if($_FILES["image"]["size"]==0){
	errorMsg("No image provided");
	goto endaction;
      }
      if(isBlank($time)){
	errorMsg("No time provided");
	goto endaction;
      }
    }

    //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
    //SAVE IMAGE
    //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
    $imgfile=$_FILES["image"];
    if($imgfile["size"]>0){
      $numimgs+=1;
      $tmp=$imgfile["tmp_name"];
      $fname=$imgfile["name"];
      preg_match("/\.(\w+)$/",$fname,$matches);
      $ext=$matches[1];
      $timestr=preg_replace("/[:\s]/","-",$time);
      $suffix="image_$timestr";
      $filename="${obsid}-$suffix.$ext";
      $filephp="${obsid}-$suffix.php";
      statusMsg("Saving image $fname as $filename...");
      shell_exec("cp $tmp '$obsdir/$filename'");
      $fl=fopen("$obsdir/$filephp");
      fwrite($fl,"<?php\n");
      fwrite($fl,"\$time='$time';\n");
      fwrite($fl,"?>\n");
      fclose($fl);
      $obsimages=listImages($obsid);
    }
  }
  if($step>=3){
    //%%%%%%%%%%%%%%%%%%%%
    //CHECK STEP3 OPTIONS
    //%%%%%%%%%%%%%%%%%%%%
    
    //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
    //SAVE IMAGE INFORMATION
    //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
    for($i=1;$i<=$nimg;$i++){
      $var="img$i";$val=$$var;
      $fl=fopen("$obsdir/$val.php","w");
      fwrite($fl,"<?php\n");

      $var="time$i";$val=$$var;
      fwrite($fl,"\$time='$val';\n");

      $var="mercury${i}";$val=$$var;
      fwrite($fl,"\$mercury='$val';\n");

      $var="sunspot${i}";$val=$$var;
      fwrite($fl,"\$sunspot='$val';\n");

      fwrite($fl,"?>\n");
      fclose($fl);
    }

  }

  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  //SAVE IN DATABASE
  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  if(!preg_match("/code$/",$code)){
    $code=md5($code)."code";
  }
  insertSql("Aristarco6",$ARISTARCO6_FIELDS);

  if($action=="Save"){
    statusMsg("Observation '$obsid' saved.");
  }else{
    statusMsg("Next step of observation '$obsid'.");
  }

  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  //NEXT STEP 
  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  if($action=="Next Step"){
    if($step<4){$step+=1;}
  }
 endaction:
}
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//SUBMIT
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
else if($action=="Submit")
{

}

//////////////////////////////////////////////////////////
//BODY
//////////////////////////////////////////////////////////
if(0){}
else if($mode=="contacts"){

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//CONTACTS
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
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
}else{

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//IMAGE SUBMISSION
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&

//==============================
//PREPARE VARIABLES
//==============================

//BLANK IMAGE
$blankimg=<<<IMG
<div style="background-color:lightgray;width:200px;height:200px;padding:5px;">
No image upload yet
</div>
IMG;

//SET STEP
if(!isset($step)){$step=1;}

//CALIBRATION IMAGE
$calimage=$blankimg;
if($out=shell_exec("ls $obsdir/${obsid}-calibration.*")){
  $calimage=rtrim(shell_exec("basename $out"));
$calimage=<<<C
<img class="sample" src=$out/>
C;
}

//IMAGES SAMPLE AND CALIBRATION CODE
$calibrate="";

$samples=<<<I
<h3>Upload images</h3>
<div class='table' style='text-align:center'>
I;

$nimg=0;
foreach($obsimages as $img){
  $nimg++;

  preg_match("/([^\.]+)\.\w+/",$img,$matches);
  $fname=$matches[1];

  include("$obsdir/$fname.php");

  //SAMPLES
$samples.=<<<I
<div style="float:left">
<input type="checkbox" name="remove$nimg" value="$img"> delete<br/>
<img class='sample' src='$obsdir/$img'/><br/>
Image $fname<br/>
</div>
I;

   //GET WIDTH AND HEIGHT OF IMAGE
   $size=getimagesize("$ROOTDIR/$obsdir/$img");
   $width=400.0;
   $height=($width*$size[1])/$size[0];

   //CALIBRATION CANVAS
   $varmerc="mercury$nimg";
   $varspot="sunspot$nimg";
   if(!isset($$varmerc)){$valmerc="Use your mouse";}
   else{$valmerc=$$varmerc;}
   if(!isset($$varspot)){$valspot="Use your mouse";}
   else{$valspot=$$varspot;}

$calibrate.=<<<C
<tr>
  <td>
    <canvas id="image$nimg" value="$obsdir/$img" style="border:solid black 2px" width="$width" height="$height">
    </canvas>
    <div class="caption">$img</div>
    <input type="hidden" name="img$nimg" value="$fname">
  </td>
  <td valign="top">
    <div>
      <b>Time $nimg</b>: $time
      <input type="hidden" name="time$nimg" value="$time">
    </div>
    <div>
      <b>Mercury</b>:
      <div id="image${nimg}_rect" style="font-style:italic;margin-left:20px;">$valmerc</div>
      <input id="image${nimg}_irect" type="hidden" name="mercury$nimg" value="$valmerc">
    </div>
    <div>
      <b>Sunspot</b>:
      <div id="image${nimg}_rect_spot" style="font-style:italic;margin-left:20px;">$valspot</div>
      <input id="image${nimg}_irect_spot" type="hidden" name="sunspot$nimg" value="$valspot">
    </div>
  </td>
</tr>
C;

   //JAVASCRIPT CODE
   $onload.="\nloadCanvas('image$nimg','$valmerc','$valspot');\n";
}
if($nimg==0){$samples.="$blankimg";}
$calibrate.="<input type='hidden' name='nimg' value='$nimg'>";
$samples.="</div>";

//==============================
//TITLE
//==============================
$body.=<<<B
<h3>Submit your observations</h3>
$FORM
<input type="hidden" name="obsid" value="$obsid">
<input type="hidden" name="step" value="$step">

B;

//==============================
//STATUS AND ERRORS
//==============================
if(!isBlank($STATUS)){
$body.=<<<B
<div class="box status">
$STATUS
</div>
B;
}

if(!isBlank($ERRORS)){
$body.=<<<B
<div class="box error">
$ERRORS
</div>
B;
}

//==============================
//SAVE & NEXT STEP BUTTONS
//==============================
$nextbut=<<<BUT
<div class="buttons">
<input class="submit" type="submit" name="action" value="Next Step">
</div>
BUT;
$savebut=<<<BUT
<div class="buttons">
<input class="submit" type="submit" name="action" value="Save" style="margin-bottom:0.5em"><br/>
</div>
BUT;

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//FORMS
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&

if($step>=4){
//==============================
//SUBMISSION BUTTON
//==============================
$body.=<<<B
<div class="step">
<div class="boxtitle" style="text-align:center;margin-top:10px;padding:20px;">
  <input type="submit" name="action" value="Submit">
</div>
</div>
B;
}

if($step>=3){
//==============================
//IMAGE CALLIBRATION
//==============================
$body.=<<<B
<div class="step">
<div class="boxtitle">Step 3. Image calibration</div>
$nextbut$savebut
<p>
Use your mouse to draw a rectangle around Mercury. Once finished
repeat the same procedure to indicate (optionally) the position
of a sunspot visible in all the images.
</p>
<center>
<table>
$calibrate
</table>
</center>
</div>
B;
}

if($step>=2){
//==============================
//IMAGE UPLOAD
//==============================
  if($step>2){$nextbut="";}
$body.=<<<B
<div class="step">
<div class="boxtitle">Step 2. Images upload</div>
$nextbut$savebut
<table class="form">
<!-- -------------------- FIELD -------------------- -->
<tr>
  <td class="field">Image to upload:</td><td class="input"><input type="file" name="image"></td>
</tr>
<tr>
  <td class="help" colspan=2>Upload here your image</td>
</tr>
<!-- -------------------- FIELD -------------------- -->
<tr>
  <td class="field">Local time:</td><td class="input"><input type="text" name="time" placeholder="eg. 10:10:20"></td>
</tr>
<tr>
  <td class="help" colspan=2>Time of the observation</td>
</tr>
<!-- -------------------- FIELD -------------------- -->
</table>
$samples
</div>
B;
}

if($step>=1){
//==============================
//BASIC INFORMATION
//==============================
  if($step>1){$nextbut="";}
$body.=<<<B
<div class="step">
<div class="boxtitle">Step 1. Location information</div>
$nextbut$savebut
<table class="form">
<!-- -------------------- FIELD -------------------- -->
<tr>
  <td class="field">Site name:</td>
  <td class="input">
    <input type="text" name="sitename" value="$sitename" size="30" placeholder="Your site name, eg. New York (US)">
  </td>
</tr>
<tr>
  <td class="help" colspan=2>Describe the site of your observations</td>
</tr>
<!-- -------------------- FIELD -------------------- -->
<tr>
  <td class="field">Latitude:</td><td class="input"><input type="text" name="latitude" value="$latitude" placeholder="eg. 6.245453"></td>
</tr>
<tr>
  <td class="help" colspan=2>Please use 4 to 6 decimal places.</td>
</tr>
<!-- -------------------- FIELD -------------------- -->
<tr>
  <td class="field">Longitude:</td><td class="input"><input type="text" name="longitude" value="$longitude" placeholder="eg. -75.345678"></td>
</tr>
<tr>
  <td class="help" colspan=2>Please use 4 to 6 decimal places. Use "-" for a longitude to the west of Greenwich.</td>
</tr>
<!-- -------------------- FIELD -------------------- -->
<tr>
  <td class="field">Time zone:</td><td class="input">UTC<input type="text" name="timezone" value="$timezone" size="2" placeholder="-5"></td>
</tr>
<tr>
  <td class="help" colspan=2>Your timezone. Examples: EST is UTC-5.</td>
</tr>
<!-- -------------------- FIELD -------------------- -->
<tr>
  <td class="field">Altitude:</td><td class="input"><input type="text" name="altitude" value="$altitude" placeholder="eg. 1520"></td>
</tr>
<tr>
  <td class="help" colspan=2>In meters.</td>
</tr>
<!-- -------------------- FIELD -------------------- -->
<tr>
  <td class="field">Calibration image:</td>
  <td class="input">
    <input type="file" name="calimage">
    $calimage
  </td>
</tr>
<tr>
  <td class="help" colspan=2>This is a picture of the computer screen showing the website.</td>
</tr>
<!-- -------------------- FIELD -------------------- -->
<tr>
  <td class="field">Instrument details:</td>
  <td class="input">
    <textarea name="instrument" rows="5" cols="35">$instrument</textarea>
  </td>
</tr>
<tr>
  <td class="help" colspan=2>Please provide details of your instruments or method of observation.</td>
</tr>
<!-- -------------------- FIELD -------------------- -->
<tr>
  <td class="field">Name:</td>
  <td class="input">
    <input type="text" name="name" value="$name" size="30" placeholder="eg. John Smith">
  </td>
</tr>
<tr>
  <td class="help" colspan=2>Provide your name for contact purposes</td>
</tr>
<!-- -------------------- FIELD -------------------- -->
<tr>
  <td class="field">E-mail:</td>
  <td class="input">
    <input type="text" name="email" value="$email" size="30" placeholder="me@server.mail.com">
  </td>
</tr>
<tr>
  <td class="help" colspan=2>Provide your e-mail</td>
</tr>
<!-- -------------------- FIELD -------------------- -->
<tr>
  <td class="field">Secret code:</td>
  <td class="input">
    <input type="password" name="code" value="$code" size="30" placeholder="secret code for your data">
  </td>
</tr>
<tr>
  <td class="help" colspan=2>Provide a secret code to secure your observations. Only you with your secret code will be able to access and modify this information</td>
</tr>
<!-- -------------------- FIELD -------------------- -->
</table>
</div>
B;
}

}//End Main Body

//////////////////////////////////////////////////////////
//FOOTER
//////////////////////////////////////////////////////////
$onload.="});\n";
$onload.="</script>";
$body.=$onload;
$messages=getMessages();
$footer=getFooter();

//////////////////////////////////////////////////////////
//RENDER
//////////////////////////////////////////////////////////
echo renderPage($headers,$head,$mainmenu,$body,$footer,$messages);
?>

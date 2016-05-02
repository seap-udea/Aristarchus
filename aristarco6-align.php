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
array2Globals($_SESSION);

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
$nimg=count($obsimages);

//////////////////////////////////////////////////////////
//PAGE MENU
//////////////////////////////////////////////////////////
$mainmenu.=<<<M
<span class="botonmenu">
  <a class="inverted" href="aristarco6-align.php?mode=contacts">Contact times</a>
</span>
<span class="botonmenu">
  <a class="inverted" href="aristarco6-align.php">Submit observations</a>
</span>
<span class="botonmenu">
  <a class="inverted" href="aristarco6-align.php?mode=list">List of observations</a>
</span>
M;
$title=<<<T
<div class="pagetitle">
Aristarchus 6: Transit of Mercury, May 9 2016
</div>
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
  if($observation=mysqlCmd("select * from Aristarco6 where obsid='$obsid' and code='$lcode'")){
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
  //$body.="STEP:$step<br/>";

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
  if(isset($nimg) and $nimg>0){
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
    if(count($obsimages)==0){
      if($_FILES["image"]["size"]==0){
	errorMsg("No calibration image provided");
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
      shell_exec("identify -verbose '$obsdir/$filename' > $obsdir/${obsid}-$suffix.exif");

      $fl=fopen("$obsdir/$filephp","w");
      fwrite($fl,"<?php\n");
      fwrite($fl,"\$time='$time';\n");
      fwrite($fl,"?>\n");
      fclose($fl);

      $obsimages=listImages($obsid);
    }

    if($nimg<3 and $action=="Next Step"){
      errorMsg("You must upload at least 3 images");
      goto endaction;
    }
  }
  if($step>=2){
    //%%%%%%%%%%%%%%%%%%%%
    //CHECK STEP2 OPTIONS
    //%%%%%%%%%%%%%%%%%%%%
    
    //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
    //SAVE IMAGE INFORMATION
    //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
    $ncimg=0;
    for($i=1;$i<=$nimg;$i++){
      $var="img$i";$val=$$var;
      $fl=fopen("$obsdir/$val.php","w");
      fwrite($fl,"<?php\n");

      $var="time$i";$val=$$var;
      fwrite($fl,"\$time='$val';\n");

      $var="mercury${i}";$val=$$var;
      if(preg_match("/,/",$val)){$ncimg++;}
      fwrite($fl,"\$mercury='$val';\n");

      $var="sunspot${i}";$val=$$var;
      fwrite($fl,"\$sunspot='$val';\n");

      fwrite($fl,"?>\n");
      fclose($fl);
    }
    if($ncimg<3 and $action=="Next Step"){
      errorMsg("$ncimg images have been calibrated");
      goto endaction;
    }
  }
  if($step>=3){
    //%%%%%%%%%%%%%%%%%%%%
    //CHECK STEP3 OPTIONS
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
      preg_match("/([^\.]+)\.(\w+)$/",$fname,$matches);
      $bname=$matches[1];
      $ext=$matches[2];
      $filename="${obsid}-calibration.$ext";
      statusMsg("Saving calibration image $fname as $filename...");
      $tmp=$calfile["tmp_name"];
      shell_exec("cp $tmp '$obsdir/$filename'");
      shell_exec("identify -verbose '$obsdir/$filename' > $obsdir/${obsid}-calibration.exif");
      $calimage=$filename;
    }

    //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
    //SAVE IN DATABASE
    //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
    if(!preg_match("/code$/",$code)){
      $code=md5($code)."code";
    }
    insertSql("Aristarco6",$ARISTARCO6_FIELDS);

    //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
    //SAVE IN USER DATABASE
    //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
    if(!mysqlCmd("select * from Users where email='$email'")){
      statusMsg("Creating new user");
      insertSql("Users",$USERS_FIELDS);
    }else{
      statusMsg("Updating user information");
      insertSql("Users",$USERS_FIELDS);
    }
    if(!isset($_SESSION["email"])){
      session_start();
      foreach($USERS_FIELDS as $key){
	$_SESSION["$key"]=$$key;
      }
      header("Refresh:0;url=aristarco6-align.php?action=load&obsid=$obsid&code=$code");
    }
  }

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
  //CHANGE STATUS
  $step=5;
  insertSql("Aristarco6",$ARISTARCO6_FIELDS);

  //SEND CONFIRMATION E-MAIL
  $subject="[Aristarchus Campaign 6] Your observations ($obsid) have been submitted";

$message=<<<M
<p>

  Dear $name,

</p>

<p>

  On behalf of the techincal team of
  the <a href="http://bit.ly/aristarco-saa-6-en">Aristarchus Campaign
  6</a> and the organizations that support it (Sociedad Antioqueña de
  Astronomía, Universidad de Antioquia and Astronomers Without
  Borders), we want to thank you for providing observations of the
  Mercury Transit.

</p>

<p>

  Your observations are now identified with the unique
  identifier <b>$obsid</b>.  You will be able to modify these
  observations at any time, at least until our team start a more
  thoroughly analysis.  For that purpose you should use the following
  direct link:

</p>

<center>
<a href="$SITEURL/aristarco6-align.php?action=load&obsid=$obsid&lcode=$code" style="font-size:1.5em">
Link to modify observations $obsid
</a>
</center>

<p>

  Your involvement in this campaign will demonstrate how using readily
  available technological devices, we are able to measure,
  colaboratively, the size of the Universe.
  
</p>

<p> 

  We will be in touch with you to provide you instantaneous updates of
  the analysis process.  You will be also welcome to participate in
  this important process.

</p>

<p>

  With our best wishes,

</p>

<p>

<b>Aristarchus Campaigns Technical Team</b>

</p>

M;
   sendMail($email,$subject,$message,$EHEADERS);
   statusMsg("Confirmation e-mail sent to $email");
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
}
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//CONTACTS
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
else if($mode=="list"){

$body.=<<<B
<h3>List of observations</h3>
<p>This is the list of observation uploaded by $email</p>
B;

   //==============================
   //LIST
   //==============================
   if($dbout=mysqlCmd("select * from Aristarco6 where email='$email'",$qout=1)){

$body.=<<<T
<center>
<table border=1px>
<tr>
  <th>Obs.ID</th>
  <th>Step</th>
  <th>Sitename</th>
  <th>Latitude</th>
  <th>Longitude</th>
</tr>
T;
     
     foreach($dbout as $obs){
       array2Globals($obs);

$body.=<<<T
<tr>
  <td>
    <a href="aristarco6-align.php?action=load&obsid=$obsid&email=$email&lcode=$code">$obsid</a>
  </td>
  <td>
    $step
  </td>
  <td>
    $sitename
  </td>
  <td>
    $latitude
  </td>
  <td>
    $longitude
  </td>
</tr>
T;
     }
   }

$body.=<<<T
</table>
</center>
T;

$body.=<<<B
<h3>Global observations</h3>
<p>This is the map of the observations submitted to date</p>
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
if($step>=5){
$body.=<<<B
<div class="step">
  <div style="text-align:center;margin-top:10px;padding:20px;background:lightblue;font-size:1.5em;">
  Congratulations! your observations have been submitted!
</div>
</div>
B;
}else{
$body.=<<<B
<div class="step">
  <div style="text-align:center;margin-top:10px;padding:20px;background:lightgreen;font-size:1.5em;">
  You seem to be ready to
  <input type="submit" name="action" value="Submit">
  your observations
</div>
</div>
B;
}
}

if($step>=3){
//==============================
//BASIC INFORMATION
//==============================
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
  <td colspan=2 style="text-align:center">
    <a class="nolevel0" href="JavaScript:void(null)" onclick="$('#userinfo').toggle()">
      Update user information
    </a>
  </td>
</tr>
</table>

<table id="userinfo" class="form nolevel1">
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

if($step>=2){
//==============================
//IMAGE CALIBRATION
//==============================
  if($step>2){$nextbut="";}
$body.=<<<B
<div class="step">
<div class="boxtitle">Step 2. Image calibration</div>
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

if($step>=1){
//==============================
//IMAGE UPLOAD
//==============================
  if($step>1){$nextbut="";}
$body.=<<<B
<div class="step">
<div class="boxtitle">Step 1. Images upload</div>
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

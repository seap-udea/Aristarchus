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
$headers.="\n<script>\n$(document).ready(function(){\n";
if(!isset($obsid)){$obsid=generateRandomString(6);}
$obsdir="data/Aristarco6/$obsid";

//////////////////////////////////////////////////////////
//PAGE MENU OPTIONS
//////////////////////////////////////////////////////////
$mainmenu.=<<<M
<span class="botonmenu">
  <a class="inverted" href="aristarco6-contacts.php">Contact times</a>
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

//////////////////////////////////////////////////////////
//CONTENT ACTIONS
//////////////////////////////////////////////////////////
if(!isset($action)){}
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//SAVE
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
else if($action=="Next Step" or $action=="Save")
{
  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  //REMOVE HELP BOXES
  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  $headers.="$('.helpbox').hide();";

  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  //CHECK PROVIDED INFORMATION
  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  $noblank=array("sitename",
		 "latitude","longitude","timezone","altitude",
		 "name","email","code");
  foreach($noblank as $var){
    if(isBlank($$var)){
      errorMsg("No $var provided");
      goto endaction;
    }
  }
  shell_exec("mkdir -p $obsdir");

  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  //SAVE CALIBRATION IMAGE
  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  $calfile=$_FILES["calimage"];
  if($calfile["size"]>0){
    $fname=$calfile["name"];
    preg_match("/\.(\w+)$/",$fname,$matches);
    $ext=$matches[1];
    $filename="${obsid}_calibration.$ext";
    statusMsg("Saving calibration image $fname as $filename...");
    $tmp=$calfile["tmp_name"];
    shell_exec("cp $tmp '$obsdir/$filename'");
    $calimage=$filename;
  }

  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  //SAVE IN DATABASE
  //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  insertSql("Aristarco6",$ARISTARCO6_FIELDS);
  statusMsg("Observation $obsid saved.");

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
//DOM ACTIONS
//////////////////////////////////////////////////////////
$headers.="});\n";
$headers.="</script>";

//////////////////////////////////////////////////////////
//PREPARE
//////////////////////////////////////////////////////////
if(!isset($step)){$step=1;}
if($out=shell_exec("ls $obsdir/${obsid}_calibration.*")){
  $calimage=rtrim(shell_exec("basename $out"));
$calimage_img=<<<C
<img src=$out height=150px style='margin-top:10px'/>
<input type="hidden" name="calimage" value="$calimage">
C;
}

//////////////////////////////////////////////////////////
//BODY
//////////////////////////////////////////////////////////
$body.=<<<B
<h3>Submit your observations</h3>
$FORM
<input type="hidden" name="obsid" value="$obsid">
<input type="hidden" name="step" value="$step">
B;

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//HELP BOX
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
$body.=<<<B
<div class="box helpbox">
Submitting your observations to the Aristarchus campaign is a three
step process.
<ul>
<li>Step 1. Provide information about your location.</li>
<li>Step 2. Upload your images.</li>
<li>Step 3. Help us to callibrate images.</li>
</ul>
</div>
B;

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//STATUS AND ERRORS
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
if(!isBlank($STATUS)){
$body.=<<<B
<div class="box" style="background:lightblue;margin-top:10px;">
$STATUS
</div>
B;
}

if(!isBlank($ERRORS)){
$body.=<<<B
<div class="box" style="background:pink;margin-top:10px;">
$ERRORS
</div>
B;
}

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//LOCATION
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
$buttons=<<<BUT
<div style="position:relative;float:right;top:0px;right0px;text-align:right;">
<input class="submit" type="submit" name="action" value="Save" style="margin-bottom:0.5em"><br/>
<input class="submit" type="submit" name="action" value="Next Step">
</div>
BUT;

$blankimg=<<<IMG
<div style="background-color:lightgray;width:200px;height:200px;padding:5px;">
No image upload yet
</div>
IMG;

if($step>=4){
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//SUBMIT
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
$body.=<<<B
<div class="step">
<div class="boxtitle" style="text-align:center;margin-top:10px;padding:20px;">
  <input type="submit" name="action" value="Submit">
</div>
</div>
B;
}

if($step>=3){
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//IMAGE CALIBRATION
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
$body.=<<<B
<div class="step">
<div class="boxtitle">Step 3. Image calibration</div>
$buttons
<table class="form">
<tr>
</tr>
</table>
$blankimg
</div>
B;
}

if($step>=2){
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//IMAGE UPLOAD
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
$body.=<<<B
<div class="step">
<div class="boxtitle">Step 2. Images upload</div>
$buttons
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
<p>Uploaded images:</p>
$blankimg
</div>
B;
}

if($step>=1){
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//BASIC INFORMATION
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
$body.=<<<B
<div class="step">
<div class="boxtitle">Step 1. Location information</div>
$buttons
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
    $calimage_img
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

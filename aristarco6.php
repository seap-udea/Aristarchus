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

$IMGVARIABLES=array("time",
		    "mercury","sunspot",
		    "posmercury","possunspot",
		    "center","cropcenter",
		    "R","dR","tm","rm","AP");

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

//LEGACY -> REMOVE
if(array_key_exists("sitename",$_SESSION)){
  unset($_SESSION["sitename"]);
}

//////////////////////////////////////////////////////////
//PAGE MENU
//////////////////////////////////////////////////////////
$mainmenu.=<<<M
<span class="botonmenu">
  <a class="inverted" href="aristarco6.php?mode=howto">How to participate?</a>
</span>
<span class="botonmenu">
  <a class="inverted" href="aristarco6.php?mode=contacts">Contact times</a>
</span>
<span class="botonmenu">
  <a class="inverted" href="aristarco6.php?mode=submitx">Submit observations</a>
</span>
<span class="botonmenu">
  <a class="inverted" href="aristarco6.php?mode=list">List of observations</a>
</span>
M;
$title=<<<T
<div class="pagetitle">
Aristarchus Campaign 6: Transit of Mercury, May 9 2016
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
    //statusMsg("Observation '$obsid' loaded");
    $onload.="$('.helpbox').hide();";
    
    //LOAD IMAGES INFORMATION
    $i=1;
    foreach($obsimages as $img){

      $fp=fileProperties($img);
      $fname=$fp["fname"];

      include("$obsdir/$fname.php");

      if(file_exists("$obsdir/$fname-align.php")){
	include("$obsdir/$fname-align.php");
      }

      foreach($IMGVARIABLES as $var){
	$vari="$var$i";
	if(isset($$var)){
	  $$vari=$$var;
	}else{
	  $$vari='0';
	}
      }
      $i++;
    }

  }else{
    errorMsg("Code provided not valid");
  }
}

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//SAVE
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
else if($action=="Analyze")
{
  statusMsg("Attempting alignment of observation '$obsid'...");
}

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//SAVE
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
else if($action=="Save" or $action=="Next Step")
{
  //=====================================
  //PREPARE OBSERVATIONS DIRECTORY
  //=====================================
  shell_exec("mkdir -p $obsdir");

  //=====================================
  //REMOVE IMAGES
  //=====================================
  if($nimg>0){
    for($i=1;$i<=$nimg;$i++){
      $var="remove$i";
      if(isset($$var)){
	$val=$$var;
	$fp=fileProperties($val);
	$fname=$fp["fname"];
	statusMsg("Deleting image $fname...");
	shell_exec("rm $obsdir/$fname*.*");
      }
    }
    $obsimages=listImages($obsid);
    $nimg=count($obsimages);
    if($nimg==0){
      $step=1;
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

    if($nimg==0){
      if($_FILES["image"]["size"][0]==0){
	errorMsg("No image provided");
	goto endaction;
      }
    }

    //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
    //SAVE IMAGE
    //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
    $imgfile=$_FILES["image"];
    if($imgfile["size"][0]>0){
      $nfiles=count($imgfile["size"]);
      for($i=0;$i<$nfiles;$i++){
	$numimgs+=1;
	$tmp=$imgfile["tmp_name"][$i];
	$fname=$imgfile["name"][$i];
	$fp=fileProperties("$fname");
	//$body.=print_r($fp,true);
	$bname=$fp["fname"];
	$bname=preg_replace("/\s/","__",$bname);
	$bname=preg_replace("/\./","_",$bname);
	$ext=$fp["ext"];
	$imgid=generateRandomString(3);
	$suffix="image-$imgid-$bname";
	//$body.="Suffix:".$bname;
	$filename="${obsid}-$suffix.$ext";
	$filephp="${obsid}-$suffix.php";
	statusMsg("Saving image $fname as $filename...");
	shell_exec("cp $tmp '$obsdir/$filename'");
	shell_exec("identify -verbose '$obsdir/$filename' > $obsdir/${obsid}-$suffix.exif");
	if($ext=="CR2"){
	  statusMsg("Storing CR2 image and displaying PNG conversion...");
	  shell_exec("cp $obsdir/$filename $obsdir/${obsid}-$suffix-original.$ext");
	  shell_exec("convert -resize 800x $obsdir/$filename $obsdir/${obsid}-$suffix.png");
	}
	shell_exec("echo > '$obsdir/$filephp'");
      }
      $obsimages=listImages($obsid);
      $nimg=count($obsimages);
    }

    if($nimg<2 and $action=="Next Step"){
      errorMsg("You must upload at least 3 images");
      goto endaction;
    }

    //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
    //SAVE IMAGE METADATA
    //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
    $ncimg=0;
    for($i=1;$i<=$nimg;$i++){

      $var="img$i";$val=$$var;
      if(isBlank($val)) continue;

      $fl=fopen("$obsdir/$val.php","w");
      fwrite($fl,"<?php\n");

      $var="time$i";$val=$$var;
      if(isBlank($val)){
	errorMsg("No time provided for image $i");
	goto endaction;
      }
      fwrite($fl,"\$time='$val';\n");

      $var="mercury${i}";$val=$$var;
      if(!preg_match("/\d/",$val)){
	errorMsg("No Mercury position provided for image $i");
	goto endaction;
      }
      if(preg_match("/,/",$val)){$ncimg++;}
      fwrite($fl,"\$mercury='$val';\n");

      $var="sunspot${i}";$val=$$var;
      fwrite($fl,"\$sunspot='$val';\n");

      $var="posmercury${i}";$val=$$var;
      if(isBlank($val)){
	errorMsg("No Mercury position provided for image $i");
	goto endaction;
      }
      if(preg_match("/,/",$val)){$ncimg++;}
      fwrite($fl,"\$posmercury='$val';\n");

      $var="possunspot${i}";$val=$$var;
      fwrite($fl,"\$possunspot='$val';\n");

      fwrite($fl,"?>\n");
      fclose($fl);
    }
  }

  if($step>=2){
    //%%%%%%%%%%%%%%%%%%%%
    //CHECK STEP2 OPTIONS
    //%%%%%%%%%%%%%%%%%%%%
    $i=1;
    foreach($obsimages as $img){

      $fp=fileProperties($img);
      $fname=$fp["fname"];

      include("$obsdir/$fname.php");

      if(file_exists("$obsdir/$fname-align.php")){
	include("$obsdir/$fname-align.php");
      }

      foreach($IMGVARIABLES as $var){
	$vari="$var$i";
	if(isset($$var)){
	  $$vari=$$var;
	}else{
	  $$vari='0';
	}
      }
      $i++;
    }

    //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
    //UPDATE USER INFORMATION
    //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
    if(!preg_match("/code$/",$code)){
      $code=md5($code)."code";
    }
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
    if(!$QPERMISO){
      errorMsg("You don't have permissions to save this observations. Login first.");
      goto endaction;
    }

    //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
    //SAVE CALIBRATION IMAGE
    //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
    $calfile=$_FILES["calimage"];
    if($calfile["size"]>0){
      $fname=$calfile["name"];
      $fp=fileProperties($fname);
      $bname=$fp["fname"];
      $ext=$fp["ext"];
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
    //INSERT
    insertSql("Aristarco6",$ARISTARCO6_FIELDS);
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
<a href="$SITEURL/aristarco6.php?action=load&obsid=$obsid&lcode=$code" style="font-size:1.5em">
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
   $qconfirm=1;
}

//////////////////////////////////////////////////////////
//BODY
//////////////////////////////////////////////////////////
if(0){}
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//HOWTO
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
else if($mode=="howto"){
$body.=<<<B
<center>
<h3>How to participate in the Aristarchus Campaign?</h3>
B;
 $list=rtrim(shell_exec("ls -m img/aristarco6/Aristarco-6-QuickGuide-english-final/*.jpg"));
 $files=preg_split("/\s*,\s*/",$list);
 $content="";
 $listfiles="";
 $i=1;
 foreach($files as $file){
   if(isBlank($file)){continue;}
   $listfiles.="<p><a name='#slide$i'></a><img src='$file' style='border:solid black 1px;margin:5px;'/></p>\n";
$content.=<<<C
 <a href='JavaScript:void(null)' onclick='location.hash="#slide$i"'>Slide $i</a> |
C;
   $i+=1;
 }
 $content=trim($content,"|");
 $body.="<p><!--$content--></p>$listfiles</center>";
}
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//SUBMIT SCREENSHOTS
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
else if($mode=="submit" and 0){

$list=rtrim(shell_exec("ls -m img/aristarco6/Screenshots/*.png"));
$files=preg_split("/\s*,\s*/",$list);

$screenshots="";

$i=1;
foreach($files as $file){
  if(isBlank($file)){continue;}
  $screenshots.="<img src='$file' style='border:solid black 1px;margin:5px;width:80%;'/>\n";
  $i+=1;
}

$body.=<<<B
<div style="font-size:1.2em;margin-left:20px;margin-right:20px">
<p>
<img src="img/transit.gif" width=50% align="left" style="margin-right:10px"/>
<p>
The day of the Transit of Mercury has arrived.  People around the
world are preparing their equipments to observe this relatively rare
phenomenon.
</p>
<p>
The team of the <a href=http://bit.ly/saa-aristarco-6>Aristarchus
Campaigns</a> is also prepared to receive observations from different
places in the world.  For this purpose, we <b>will release
tomorrow</b>, when the transit begins, a special <b>uploading and
image analysis on-line tool</b>.
</p>
<p>
Using this tool you will not only be able to submit your observations
to the Campaign, but also to get a glimpse of the information that
those observations contain.
</p>
<p>
  Stay tunned! (and of course), clear skies for all!
</p>
</div>
<h3>Submission tool screenshots</h3>
<center>
    $screenshots
</center>
B;

}
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//CONTACTS
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
else if($mode=="contacts"){
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
//LIST
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
else if($mode=="list"){

  
  //==============================
  //USER LIST
  //==============================
  if($QPERMISO){

$body.=<<<B
<h3>List of observations</h3>
B;

$body.=<<<B
<p>This is the list of observation uploaded by $email</p>
B;

   if($dbout=mysqlCmd("select * from Aristarco6 where email='$email'",$qout=1)){

$body.=<<<T
<center>
<table border=1px>
<tr>
  <th>ID</th>
  <th>Submission</th>
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
    <a href="aristarco6.php?action=load&obsid=$obsid&email=$email&lcode=$code">$obsid</a>
  </td>
  <td>
    $datesubmission
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

  }

  //==============================
  //GLOBAL OBSERVATIONS
  //==============================
$body.=<<<B
<h3>Global observations</h3>
<p>This is the map of the observations submitted to date</p>
B;

}else{

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//MAIN FORM
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//SET STEP
if(!isset($step)){$step=1;}

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
$alignbut=<<<BUT
<div class="buttons">
<input class="submit" type="submit" name="action" value="Analyze" style="margin-bottom:0.5em"><br/>
</div>
BUT;

//==============================
//HELP TEXTS
//==============================

$helpsubmission=fadingBox("

<p>
Using this page you will be able to analyze a set of images of the
transit of Mercury and obtain from them amazing information about
Mercury and the Sun.  This is precisely the aim of the Aristarchus
Campaign.
</p>

<p>
The analysis is a 3-step process.  In the <b>first step</b> you will be asked
to upload your images. In order to simplify the analysis you will be
also asked to identify Mercury and one sunspot (if visible) in all the
images.  We will also ask you for the time when each image was taken.
</p>

<p>
The <b>second step</b> is the analysis of the images.  We will study
the uploaded images and will measure the Sun and Mercury.  Combining
the information of all images we will attempt at aligning the images.
As a result you will be able to see the path of Mercury across the
solar disk.  With this information at hand we will provide you
incredible information about the planet and its orbit.
</p>

<p>
In the <b>Third step</b> you will be invited to submit your
observation to the campaign.  For doing so we need just a couple of
additional information about the place and time when the pictures were
taken.
</p>

<p>
We will use this <b>colored boxes</b> to guide you through the
process. You may close them using the 'X' in the upper-right corner.
Use the <input class='submit' type='submit' name='submit'
value='Save'> button to save your changes or upload the images.  Once
you complete the goal of each step press the <input class='submit'
type='submit' name='submit' value='Next Step'> button to continue.
</p>

","font-size:1.2em;");
 
 $helpinfo=fadingBox("

Now that you have uploaded more than 3 photos you seem ready for the
<input class='submit' type='submit' name='action' value='Next Step'>.

","text-align:left;font-size:1.2em;background:lightblue");
 
 $helpupload.=fadingBox("

Click the box below to select the images you want to
upload. Alternatively you may also drag them into the box.  You may
upload individual images of less than 50MB. If you select/drag several
images the total size must not be larger than 50MB.

","text-align:left;font-size:1.2em;background:lightblue");
 
 $helplocate.=fadingBox("

Use your mouse to select a small rectangular region around Mercury.
A <b>dashed blue rectangle</b> will appear when selecting Mercury.
Repeat yo select the region where you see the largest sunspot.  A
<b>dashed red rectangle</b> will be visible in that case.  If Mercury
or the sunspot are succesfully identified a crosshair will mark the
position of the objects. If the position is wrong, repeat the procedure.

","text-align:left;font-size:1.2em;background:lightblue");

 $helpalign.=fadingBox("

You're ready to analyse your image set.  Before proceed we need to set
several important parameters.  More importantly, and in order to keep
track of who's behind this images and analysis, we kindly ask you to
provide an e-mail and a <b>secret code</b>.  With this information you
will be able to acccess and even modify your observations in the
future (see login).

","text-align:left;font-size:1.2em;background:lightblue");

 $helpresults.=fadingBox("

<p>

We have succesfully performed the analysis of your images.  You may
check the results using the link below.  This results are preliminary
and will be double checked by the Aristarchus Campaign technical
team. If you are ready, we invite you to submit your images and
results to the Aristarchus Campaign by pressing <input class='submit'
type='submit' name='action' value='Next Step'>.
</p>
","text-align:left;font-size:1.2em;background:lightblue");

 $helparistarchus.=fadingBox("

<p>
Great! You have decided to submit your images to the Aristarchus
Campaign. We really appreciate your interest!
</p>

<p>
In order to proceed we just need the information below.
</p>

","text-align:left;font-size:1.2em;background:lightblue");


 $helpfinal.=fadingBox("

<p> 

It's time to submit your observations to the Aristarchus Campaign.  In
order to proceed we need additional information about the observing
site and (optionally) the instruments used to get the photos.  A
special requirement of the Campaign is a 'Calibration Image'
(see <a href=?howto target=_blank>How to participate?</a> page).  This
is a picture of the computer screen with the same equipment used to
get the photos, showing the time being.  Below is an example of the
type of image you should upload:<br/>
<center><img src='img/time.png' width='20%'></center>
</p>

",
		       "text-align:left;font-size:1.2em;background:lightblue");



//==============================
//SET BLANK HELP TEXTS
//==============================
if($QPERMISO or
   $nimg>0){
  $helpsubmission="";
}
if($QPERMISO or
   $nimg>0){
  $helpupload="";
}
if($QPERMISO or
   $nimg==0 or
   $nimg>=3){
  $helplocate="";
}
if($QPERMISO or
   $nimg<3 or 
   $step>1){
  $helpinfo="";
}
if($QPERMISO or 
   file_exists("$obsdir/output.log") or 
   $action=="Analyze"){
  $helpalign="";
}
if($step>=3 or
   $action=="load"){
  $helpresults="";
 }
if($QPERMISO or
   $step>=3){
  $helparistarchus="";
}
if($step>=4){
  $helpfinal="";
}

//==============================
//CALIBRATION IMAGE
//==============================
//BLANK IMAGE
$blankimg=<<<IMG
<div style="background-color:lightgray;width:200px;height:200px;padding:10px;">
No image upload yet
</div>
IMG;
$calimage=$blankimg;
if($out=shell_exec("ls $obsdir/${obsid}-calibration.*")){
  $calimage=rtrim(shell_exec("basename $out"));
$calimage=<<<C
<img class="sample" src=$out/>
C;
}

//==============================
//PREPARE IMAGE EDITION TABLE
//==============================

//TABLE CONTENT
$samples="";

//SETLECTION
$seltypea="";

//ACTIVATE ONLY WHEN THERE ARE 1 IMAGE OR MORE
if($nimg>0){

$caption=<<<C
<h3>Uploaded Images</h3>
C;
}//End when are more than one image

$samples.=<<<S
$seltypea
<p></p>
<table style="margin-left:5%;width:90%;" border="0px">
<caption style="font-size:1.2em;margin-bottom:20px;">
  $caption
</caption>
<tr><td colspan=2>
  $helplocate
  $helpinfo
</td></tr>
S;

$nimg=0;
foreach($obsimages as $img){
  $nimg++;

  //GET FILENAME OF IMAGES
  $fp=fileProperties($img);
  $fname=$fp["fname"];

  //GET PROPERTIES OF FILE
  $fsize=round(filesize("$obsdir/$img")/1024,2);

  //INCLUDE PROPERTIES OF IMAGES
  include("$obsdir/$fname.php");

  //GET WIDTH AND HEIGHT OF IMAGE
  $size=getimagesize("$ROOTDIR/$obsdir/$img");
  $width=600.0;
  $height=($width*$size[1])/$size[0];
  $swidth=100;
  $sheight=($swidth*$size[1])/$size[0];

  
  //CALIBRATION CANVAS
  $varmerc="mercury$nimg";
  $varspot="sunspot$nimg";
  $varposmerc="posmercury$nimg";
  $varposspot="possunspot$nimg";
  $varcenter="center$nimg";
  $varR="R$nimg";

  if(!isset($$varmerc)){$valmerc="Use your mouse";}
  else{$valmerc=$$varmerc;}

  if(!isset($$varspot)){$valspot="Use your mouse";}
  else{$valspot=$$varspot;}

  if(!isset($$varposmerc)){$valposmerc="Use your mouse";}
  else{$valposmerc=$$varposmerc;}

  if(!isset($$varposspot)){$valposspot="Use your mouse";}
  else{$valposspot=$$varposspot;}

  if(!isset($$varcenter)){$valcenter="Not yet determined";}
  else{$valcenter=$$varcenter;}

  if(!isset($$varR)){$valR="Not yet determined";}
  else{$valR=$$varR;}

  $vartime="time$nimg";
  $valtime=$$vartime;
  
$samples.=<<<C
<tr>
  <td>
    <canvas id="image$nimg" value="$obsdir/$img" width="$width" height="$height">
    </canvas>
    <div class="figcaption">
      $img<br/>
      <a href="$obsdir/$img" target="_blank">Download</a> ($fsize kB) |
      <a href="$obsdir/$fname.exif" target="_blank">Metadata</a>
      <input type="hidden" name="img$nimg" value="$fname">
    </div>
  </td>
  <td valign="top" style="width:100%;padding-left:10px;">
    <table style="font-size:0.8em">
      <tr><td>
	  <b>Image $nimg</b>
      </td></tr>
      <!-- TIME -->
      <tr><td>
	  <b>Local Time</b>: <input type="text" name="time$nimg" value="$valtime" placeholder="HH:MM:SS">
      </td></tr>
      <!-- SOLAR DISK -->
      <tr><td>
	  <b>Solar center</b>:<br/>
	  <input id="image${nimg}_center" type="text" name="center$nimg" value="$valcenter" readonly>
      </td></tr>
      <tr><td>
          <b>Solar radius (px)</b>:<br/>
	  <input id="image${nimg}_R" type="text" name="R$nimg" value="$valR" readonly>
      </td></tr>
      <!-- MERCURY -->
      <tr><td>
	  <b>Mercury position and size (px)</b>:<br/>
	  <input id="image${nimg}_rect" type="hidden" name="mercury$nimg" value="$valmerc" readonly>
	  <input id="image${nimg}_rect_pos" size=20 type="text" 
		 name="posmercury$nimg" value="$valposmerc" readonly>
      </td></tr>
      <!-- SPOT -->
      <tr><td>
	  <b>Sunspot position and size (px)</b>:<br/>
	  <input id="image${nimg}_rect_spot" type="hidden" name="sunspot$nimg" value="$valspot" readonly>
	  <input id="image${nimg}_rect_spot_pos" size=20 type="text" 
		 name="possunspot$nimg" value="$valposspot" readonly>
      </td></tr>
      <!-- DELETE -->
      <tr><td>
	  <input type="checkbox" name="remove$nimg" value="$img"> Delete image
      </td></tr>
    </table>
  </td>
</tr>
C;

   //JAVASCRIPT CODE
   $onload.="\nloadCanvas('image$nimg');\n";
}
$samples.=<<<T
</table>
T;

//==============================
//TITLE
//==============================
$body.=<<<B
$helpsubmission
$FORM
<input type="hidden" name="obsid" value="$obsid">
<input type="hidden" name="step" value="$step">
B;

//==============================
//STATUS AND ERRORS
//==============================
if(!isBlank($STATUS)){
$body.=<<<B
<div class='box status'>
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

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//FORMS
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&

if($step>=4){
//==============================
//SUBMISSION BUTTON
//==============================
if($step>=5){
  if($action!="load"){
$body.=<<<B
<div>
  <div style="text-align:center;margin-top:10px;padding:20px;background:lightgreen;font-size:1.5em;">
    Congratulations! your observations have been submitted!<br/>
  </div>
</div>
B;
 if(isset($qconfirm)){
$body.=<<<B
<div style="text-align:center;fontsize:0.8em;margin-top:1em;">
  <a href="JavaScript:void(null)" onclick="$('.step').toggle()">View/Edit/Hide information about this observations</a>
</div>
B;
         $onload.="\n$('.step').hide();\n";
     }
  }
}else{
$body.=<<<B
<div class="step">
  <div style="text-align:center;margin-top:10px;padding:20px;background:lightgreen;font-size:1.5em;">
  You seem to be ready to
  <input class="submit" type="submit" name="action" value="Submit">
  your observations
</div>
</div>
B;
}
}

if($step>=3){
//==============================
//STEP 3: BASIC INFORMATION
//==============================

  //SET DATE
  if(!isset($datesubmission)){
    $datesubmission=$DATE;
  }
  $datechange=$DATE;
  if(!$QPERMISO){
    $userchange="Anonymous";
  }else{
    $userchange=$_SESSION["email"];
  }

  if($step>3){$nextbut="";}
$body.=<<<B
<div class="step">
<a name="#step3"></a>
<div class="boxtitle">Step 3. Location information</div>
$nextbut$savebut
<div style="margin-top:3em">$helpfinal</div>
<table class="form">
<!-- -------------------- FIELD -------------------- -->
<tr>
  <td class="field">Submission date:</td>
  <td class="input">
    <input type="text" name="datesubmission" value="$datesubmission" size="30" readonly>
    <input type="hidden" name="datechange" value="$datechange">
    <input type="hidden" name="userchange" value="$userchange">
  </td>
</tr>
<tr>
  <td class="help" colspan=2>Describe the site of your observations</td>
</tr>
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
</table>
</div>
B;
}

if($step>=2){
//==============================
//STEP 2: ANALYSIS RESULTS
//==============================
  if($step>2){$nextbut="";}

$body.=<<<B
<div class="step">
<a name="#step2"></a>
<div class="boxtitle">Step 2. Analyse your images</div>
$nextbut$savebut$alignbut
B;

 if($action=="Analyze"){

$body.=<<<B
<center id="loading">
  <div>
  <span style="font-size:1.5em">Analyzing</span><br/>
    <img src="img/loading.gif"/>
  </div>
</center>
<div class="hidealign" style="margin-top:3em">$helpresults</div>
<div id="results"></div>
B;
    $alignimages=implode(",",$obsimages);
    $cmd="alignImages('$typealignment','$obsdir','$alignimages','loading','results');";
    $onload.="\nalignImages('$typealignment','$obsdir','$alignimages','loading','results');\n";
 }else{

   if(file_exists("$obsdir/output.log")){
      $output=shell_exec("cat $obsdir/output.log");
$body.=<<<B
<div class="hidealign" style="margin-top:3em">$helpresults</div>
$output
B;
   }

 }

 $selection=generateSelection(array("auto"=>"Automatic","spot"=>"Sunspot"),
			      "typealignment",$typealignment);
   
$body.=<<<B
<div style="margin-top:3em">$helpalign</div>

<table class="hidealign form" style="background:lightgray">
<caption style="font-size:1.2em;margin-bottom:1em;">
<b>Analysis parameters</b>
</caption>
<tr>
  <td class="field">
    Type of alignment:
  </td>
  <td class="input">
    $selection
    <input type='hidden' name='nimg' value='$nimg'>
  </td>
</tr>
<tr>
  <td colspan=2 style="text-align:center">
    <a class="nolevel0" href="JavaScript:void(null)" onclick="$('#userinfo').toggle()">
      Update user information
    </a>
  </td>
</tr>
</table>

<table id="userinfo" class="hidealign form nolevel1" style="background:lightgray">
<tr>
  <td class="field">Your name:</td>
  <td class="input">
    <input type="text" name="name" value="$name" size="30" placeholder="eg. John Smith">
  </td>
</tr>
<tr>
  <td class="field">
    Your e-mail:
  </td>
  <td class="input">
    <input type="text" name="email" value="$email" size="30" placeholder="me@server.mail.com">
  </td>
</tr>
<tr>
  <td class="field">
    Secret code:
  </td>
  <td class="input">
    <input type="password" name="code" value="$code" size="30" placeholder="secret code for your data">
  </td>
</tr>
</table>
B;

 $body.="</div>";
}

if($step>=1){
//==============================
//STEP 1:IMAGE UPLOAD
//==============================
  if($step>1){$nextbut="";}

$body.=<<<B
<div class="step">
<a name="#step1"></a>
<div class="boxtitle">Step 1. Upload your images</div>
$nextbut$savebut
<center style="margin-top:3em">
  $helpupload
  <div class="fileUpload">
    <span>
      Click or drag your images here to upload them<br/>
      <img src="img/upload.png" width="100px"/>
    </span>
    <input id="inputfiles" type="file" 
	   name="image[]" multiple="multiple" class="upload" onchange="filesUpload()"/>
  </div>
  <span id="files">
  </span>
</center>
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

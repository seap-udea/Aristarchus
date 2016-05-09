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
//ACTION
//////////////////////////////////////////////////////////
if(0){}
else if($action=="Login"){
  //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
  //CHECK INPUT
  //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
  if(isBlank($email)){
    errorMsg("No email provided");
    goto endaction;
  }
  if(isBlank($code)){
    errorMsg("No code provided");
    goto endaction;
  }else{
    $code=md5($code)."code";
  }

  //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
  //CHECK USER
  //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
  if($dbout=mysqlCmd("select * from Users where email='$email' and code='$code'")){
    statusMsg("Success");
    session_start();
    foreach($USERS_FIELDS as $key){
      $_SESSION["$key"]=$dbout["$key"];
    }
    header("Refresh:2;url=index.php");
  }else{
    errorMsg("User not recognized");
  }

}
else if($action=="Recover"){
  //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
  //CHECK INPUT
  //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
  if(isBlank($email)){
    errorMsg("No email provided");
    goto endaction;
  }
  //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
  //CHECK USER
  //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
  if($dbout=mysqlCmd("select * from Users where email='$email'")){
    $name=$dbout["name"];
    $code=generateRandomString(8);
    $subject="[Aristarchus Campaigns] Password recovery";
$message=<<<M
<p>
Dear $name,
</p>
<p>
A new password has been reset for you in the Aristarchus Campaign Activity Website.
</p>
<p>
Your new password is: <b>$code</b>
</p>
<p><b>Aristarchus Campaigns Technical Team</b></p>
M;
    sendMail($email,$subject,$message,$EHEADERS);
    $code=md5($code)."code";
    mysqlCmd("update Users set code='$code' where email='$email';");
    statusMsg("A new password has been sent to '$email'");
  }else{
    errorMsg("User not recognized");
  }
 }
endaction:
//////////////////////////////////////////////////////////
//BODY
//////////////////////////////////////////////////////////
if(0){}
else if($mode=="login"){
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
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//LOGIN
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$body.=<<<LOGIN
$FORM
<center>
<h3>Login</h3>
<table style="border:solid black 3px;padding:10px;">
  <tr>
    <th>E-mail</th><td><input type="text" name="email" value="$email"></td>
  </tr>
  <tr>
    <th>Secret code</th><td><input type="password" name="code"></td>
  </tr>
  <tr>
    <td colspan=2 style="text-align:center">
      <input type="submit" name="action" value="Login">
      <input type="submit" name="action" value="Recover">
    </td>
  </tr>
</table>
</center>
</form>
LOGIN;
}else{
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//WELCOME BOX
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$body.=fadingBox("
<p>
La página de actividades de la Campaña Aristarco no esta todavía
disponible en Español.  Esperamos que <b>a partir del 9 de Mayo de
2016</b> cuando ocurrirá el tránsito de Mercurio
</p>

","background:yellow;font-size:1.2em");


$body.=fadingBox("
<center style='font-size:1.2em;margin-bottom:0.2em;'>
Welcome to the Activity Site of
the <a href='http://bit.ly/campana-aristarcho' target='_blank'>Aristarchus
Campaigns</a>
</center>
In this website you will find on-line tools and activities intended to
make more complete your experience with the Campaigns.  You will also
find here the upload forms required to submit your observations.
","font-size:1.2em");

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

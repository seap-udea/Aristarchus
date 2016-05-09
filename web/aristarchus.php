<?php
//////////////////////////////////////////////////////////
//EXTERNAL LIBRARIES
//////////////////////////////////////////////////////////
require "lib/PHPMailer/PHPMailerAutoload.php";
session_start();
header("Content-Type: text/html;charset=UTF-8");

//////////////////////////////////////////////////////////
//CONFIGURACION
//////////////////////////////////////////////////////////
$USER="aristarchus";
$PASSWORD="123";
$DATABASE="Aristarchus";
$EMAIL_USERNAME="aristarcosaa@gmail.com";
$EMAIL_PASSWORD="AristarchusSAA2016";

//////////////////////////////////////////////////////////
//GLOBAL VARIABLES
//////////////////////////////////////////////////////////

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//PERMISSIONS
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&

$QPERMISO=0;
$PERMCSS="";
if(isset($_SESSION["email"])){
  $QPERMISO=1;
}
$type="inline";
$perm="$type";
$nperm="none";
if($QPERMISO){
  $perm="none";
  $nperm="$type";
}
$PERMCSS.=".level0{display:$perm;}\n.nolevel0{display:$nperm;}\n";
for($i=1;$i<=4;$i++){
  $perm="none";
  $nperm="$type";
  if($i<=$QPERMISO){$perm="$type";$nperm="none";}
  $PERMCSS.=".level$i{display:$perm;}\n.nolevel$i{display:$nperm;}\n";
}
$PERMCSS.=".level5{display:none;}\n.nolevel5{display:$type;}\n";

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//STATUS MESSAGES
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
$STATUS="";
$ERRORS="";

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//SYSTEM VARIABLES
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
$FILENAME=$_SERVER["SCRIPT_NAME"];
$BASEDIR=rtrim(shell_exec("dirname $FILENAME"));
$SITEURL="http://$HOST$BASEDIR/";
if(isset($_SERVER["HTTP_REFERER"])){
  $REFERER=$_SERVER["HTTP_REFERER"];
}else{
  $REFERER=$SITEURL;
}
$PYTHONCMD="PYTHONPATH=. MPLCONFIGDIR=/tmp python";

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//INPUT VARIABLES
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
foreach(array_keys($_GET) as $field){$$field=$_GET[$field];}
foreach(array_keys($_POST) as $field){$$field=$_POST[$field];}

//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
//EMAIL HEADERS
//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
$EHEADERS="";
$EHEADERS.="From: noreply@udea.edu.co\r\n";
$EHEADERS.="Reply-to: noreply@udea.edu.co\r\n";
$EHEADERS.="MIME-Version: 1.0\r\n";
$EHEADERS.="MIME-Version: 1.0\r\n";
$EHEADERS.="Content-type: text/html\r\n";
$FORM="<form method='post' enctype='multipart/form-data' accept-charset='utf-8'>";

//////////////////////////////////////////////////////////
//ROUTINES
//////////////////////////////////////////////////////////
function isBlank($string)
{
  if(!preg_match("/\w+/",$string)){return 1;}
  return 0;
}

function sqlNoblank($out)
{
  $res=mysqli_fetch_array($out);
  $len=count($res);
  if($len==0){return 0;}
  return $res;
}

function errorMessage($msg)
{
$error=<<<E
  <div style=background:lightgray;padding:10px>
    <i style='color:red'>$msg</i>
    </div><br/>
E;
 return $error;
}

function generateSelection($values,$name,$value,$options="",$readonly=0)
{
  $parts=$values;
  $selection="";
  if($readonly){
    $selection.="<input type='hidden' name='$name' value='$value'>";
    $selection.=$value;
    return $selection;
  }
  $selection.="<select $options name='$name'>";
  foreach(array_keys($parts) as $part){
    $show=$parts[$part];
    $selected="";
    if($part==$value){$selected="selected";}
    $selection.="<option value='$part' $selected>$show";
  }
  $selection.="</select>";
  return $selection;
}

function generateSelectionOptions($values,$name,$value,$options="",$readonly=0)
{
  $parts=$values;
  $selection="";
  if($readonly){
    $selection.="<input type='hidden' name='$name' value='$value'>";
    $selection.=$value;
    return $selection;
  }
  foreach(array_keys($parts) as $part){
    $show=$parts[$part];
    $selected="";
    if($part==$value){$selected="selected";}
    $selection.="<option value='$part' $selected>$show";
  }
  return $selection;
}

function mysqlCmd($sql,$qout=0)
{
  global $DB,$DATE;
  if(!($out=mysqli_query($DB,$sql))){
    die("Error:".mysqli_error($DB));
  }
  if(!($result=sqlNoblank($out))){
    return 0;
  }
  if($qout){
    $result=array($result);
    while($row=mysqli_fetch_array($out)){
      array_push($result,$row);
    }
  }
  return $result;
}

function mysqlCmdDB($db,$sql,$qout=0)
{
  if(!($out=mysqli_query($db,$sql))){
    die("Error:".mysqli_error($db));
  }
  if(!($result=sqlNoblank($out))){
    return 0;
  }
  if($qout){
    $result=array($result);
    while($row=mysqli_fetch_array($out)){
      array_push($result,$row);
    }
  }
  return $result;
}

function generateRandomString($length = 10) {
  $characters = '0123456789abc0defghijkmnpqrstuvwxyz';//ABCDEFGHIJKLMNOPQRSTUVWXYZ';
  $randomString = '';
  for ($i = 0; $i < $length; $i++) {
    $randomString .= $characters[rand(0, strlen($characters) - 1)];
  }
  return $randomString;
}

function upAccents($string)
{
  $string=strtoupper($string);
  $accents=array("á"=>"Á","é"=>"É","í"=>"Í","ó"=>"Ó","ú"=>"Ú");
  foreach(array_keys($accents) as $acc){
    $string=preg_replace("/$acc/",$accents["$acc"],$string);
  }
  return $string;
}

function sendMail($email,$subject,$message,$headers="")
{
  date_default_timezone_set('Etc/UTC');
  $mail = new PHPMailer;
  $mail->isSMTP();
  $mail->SMTPDebug = 0;
  $mail->Debugoutput = 'html';
  $mail->Host = 'smtp.gmail.com';
  $mail->Port = 587;
  $mail->SMTPSecure = 'tls';
  $mail->SMTPAuth = true;
  $mail->Username = $GLOBALS["EMAIL_USERNAME"];
  $mail->Password = $GLOBALS["EMAIL_PASSWORD"];
  $mail->setFrom($mail->Username, 'Aristarchus Campaigns');
  $mail->addReplyTo($mail->Username, 'Aristarchus Campaigns');
  $mail->addAddress($email,"Destinatario");
  $mail->Subject=$subject;
  $mail->CharSet="UTF-8";
  $mail->Body=$message;
  $mail->IsHTML(true);
  if(!($status=$mail->send())) {
    $status="Mailer Error:".$mail->ErrorInfo;
  }else{
    //Senbd copy to administrative user
    if(!preg_match("/\[Copy\]/",$subject)){
      sendMail($GLOBALS["EMAIL_USERNAME"],"[Copy]".$subject,$message,$headers);
    }
  }
  return $status;
}

function array2Globals($list)
{
  foreach(array_keys($list) as $key){
    $GLOBALS["$key"]=$list["$key"];
  }
}

function str2Array($string)
{
  $string=preg_replace("/[{}\"]/","",$string);
  $comps=preg_split("/,/",$string);
  
  $list=array();
  foreach($comps as $comp){
    $parts=preg_split("/:/",$comp);
    $key=$parts[0];
    $value=$parts[1];
    $list["$key"]=$value;
  }
  return $list;
}

function parseParams($params)
{
  $parameters=array();
  $parts=preg_split("/;/",$params);
  foreach($parts as $part){
    $comps=preg_split("/:/",$part);
    $param=$comps[0];
    $value=$comps[1];
    $parameters["$param"]=$value;
  }
  return $parameters;
}

function updateCursos($planid)
{
  $results=mysqlCmd("select * from Cursos where Planes_planid_s like '%$planid;%' order by nombre",$qout=1);
  $cursos=array("--"=>"--");
  foreach($results as $curso){
    $codigo=$curso["codigo"];
    $creditos=$curso["creditos"];
    $nombre=$curso["nombre"];
    $cursos["$codigo:$creditos"]=$nombre;
  }
  $cursos["000000:0"]="No listada";
  return $cursos;
}

function generateReconocimientos()
{
  global $GLOBALS;
  foreach(array_keys($GLOBALS) as $var){
    $$var=$GLOBALS["$var"];
  }

  $numrecon=20;
  $nummaterias=3;
  $numasignaturas=3;
  $chidden="hidden";

  $reconocimientos="";
  $hidden="";
  $recdir=getRecdir($recid);
  $recurl="$SITEURL/".preg_replace("/^\/.+\/data/","data",$recdir);

  for($ir=1;$ir<=$numrecon;$ir++){

    $nqr="qreconocimiento_${ir}";
    $vqr=$$nqr;

    $hidden="class='$chidden'";
$reconocimientos.=<<<RECON

    <table id="ireconocimiento_$ir" border="${TBORDER}px" width="${TWIDTH}px" $hidden>
    <tr><td  width=800px>

	<div class="reconocimiento">Reconocimiento $ir</div>
        <input type="hidden" name="qreconocimiento_${ir}" value="$vqr" class="confirm">

	<table border="${TBORDER}px" width="${TWIDTH}px">

	  <tr><td class="materias">Materia(s) vista(s)</td></tr>

	  <tr class="materias_vistas">

	    <td>
	      <div id="materia_${ir}_0" class="agregar">
		<a href="JavaScript:void(null)" onclick="addCourse(this)">Agregar materia</a>
	      </div>

RECON;

        for($im=1;$im<=$nummaterias;$im++){
	  $nmateria="materia_${ir}_${im}";
	  $vmateria=$$nmateria;
	  $nuniv="univ_${ir}_${im}";
	  $vuniv=$$nuniv;
	  $nnota="nota_${ir}_${im}";
	  $vnota=$$nnota;
	  $nqm="qmateria_${ir}_${im}";
	  $vqm=$$nqm;
	  $nsel="selmateria_${ir}_${im}";
	  $vsel=$$nsel;
	  $nmm="mmateria_${ir}_${im}";
	  $vmm=$$nmm;
	  $nsemestre="semestre_${ir}_${im}";
	  $vsemestre=$$nsemestre;
	  $nprograma="programa_${ir}_${im}";
	  $vprograma=$$nprograma;

	  $nobs="observaciones_${ir}_${im}";
	  $vobs=$$nobs;

	  //SELECT TYPE OF MATERIA INPUT
	  $input="";
$input.=<<<I
  <select id="materia_${ir}_${im}" name="smateria_${ir}_${im}" class="ccursos hidden" onchange="updateMateria(this)">
    $vsel
  </select>
I;
$input.=<<<I
  <input type="text" name="materia_${ir}_${im}" value="$vmateria" class="ccursos_input">
I;

$reconocimientos.=<<<RECON
	      <table id="imateria_${ir}_${im}" class="materia $chidden" border="${TBORDER}px">
		<tr><td class="field">Nombre de materia:</td><td class="input">
		    <input type="hidden" name="qmateria_${ir}_${im}" value="$vqm" class="confirm">
		    $input
		</td></tr>

		<tr class="ccursos_input">
		  <td class="field">Semestre:</td><td class="input">
		    <input type="text" name="semestre_${ir}_${im}" value="$vsemestre">
		  </td>
		</tr>
		
		<tr id="smmateria_${ir}_${im}" class="hidden">
		  <td class="field">Materia manual:</td>
		  <td class="input">
		    <input type="text" id="mmateria_${ir}_${im}" name="mmateria_${ir}_${im}" value="$vmm" class="confirm">
		  </td>
		</tr>
		
		<tr><!-- class="ccursos_input"-->
		  <td class="field">Programa de la asignatura:</td><td class="input">
		    <input type="file" name="programa_${ir}_${im}"><br/>
		    <i class="archivo">Archivo: <a href=$recurl/$vprograma target=_blank>$vprograma</a></i>
		    <input type="hidden" name="programa_${ir}_${im}" value="$vprograma"><br/>
		  </td>
		</tr>

		<tr><td class="field">Universidad:</td><td class="input"><input class="univ" type="text" name="univ_${ir}_${im}" value="$vuniv"></td></tr>
		<tr>
		  <td class="field">
		    Calificación:<br/>
		    <span class="help">Use "." no ","</span>
		  </td>
		  <td class="input"><input type="text" name="nota_${ir}_${im}" value="$vnota" onchange="updateAverage('${ir}')"></td>
		</tr>
		
		<tr>
		  <td class="field">
		    Observaciones:<br/>
		    <span class="help">
		      Información complementaria
		    </span>
		  </td>
		  <td class="input"><input type="text" name="observaciones_${ir}_${im}" value="$vobs"></td>
		</tr>

		<tr><td class="agregar" id="materia_${ir}_${im}" colspan=2>
RECON;

          if($im<$nummaterias){
$reconocimientos.=<<<RECON
		    <a href="JavaScript:void(null)" onclick="addCourse(this)">Agregar otra materia</a> |
RECON;
	  }

$reconocimientos.=<<<RECON
		    <a href="JavaScript:void(null)" onclick="removeCourse(this)">Remover esta materia</a>
		</td></tr>
	      </table>	  
RECON;
	}

$reconocimientos.=<<<RECON
	  <tr class="header level3">
	    <td width=800px class="materias">Reconocida por</td>
	  </tr>

	  <tr class="materias_reconocidas level3">

	    <td width=800px>

	      <div id="asignatura_${ir}_0" class="agregar">
		<a href="JavaScript:void(null)" onclick="addCourse(this)">Agregar asignatura</a>
	      </div>
RECON;

	for($ia=1;$ia<=$numasignaturas;$ia++){
	  $ncreditos="creditos_${ir}_${ia}";
	  $vcreditos=$$ncreditos;
	  $ndef="definitiva_${ir}_${ia}";
	  $vdef=$$ndef;
	  $nsel="selasignatura_${ir}_${ia}";
	  $vsel=$$nsel;

	  $nqa="qasignatura_${ir}_${ia}";
	  $vqa=$$nqa;

	  $nma="masignatura_${ir}_${ia}";
	  $vma=$$nma;

	  $nca="mcodigo_${ir}_${ia}";
	  $vca=$$nca;

$reconocimientos.=<<<RECON
	      <table id="iasignatura_${ir}_${ia}" class="materia $chidden" border="${TBORDER}px" width="${TWIDTH}px">
		<tr>
		  <td class="field">Asignatura:</td>
		  <td class="input">
		    <input type="hidden" name="qasignatura_${ir}_${ia}" value="$vqa" class="confirm">
		    <select id="asignatura_${ir}_${ia}" name="asignatura_${ir}_${ia}" class="cursos" onchange="updateCredits(this,'creditos_${ir}_${ia}')">
		      $vsel
		    </select>
		  </td>
		</tr>
		
		<tr id="smasignatura_${ir}_${ia}" class="hidden">
		  <td class="field">Asignatura manual:</td>
		  <td class="input">
		    <input type="text" id="masignatura_${ir}_${ia}" name="masignatura_${ir}_${ia}" value="$vma" class="confirm">
		  </td>
		</tr>

		<tr id="smcodigo_${ir}_${ia}" class="hidden">
		  <td class="field">Codigo manual:</td>
		  <td class="input">
		    <input type="text" id="mcodigo_${ir}_${ia}" name="mcodigo_${ir}_${ia}" value="$vca" class="confirm">
		  </td>
		</tr>

		<tr><td class="field">Créditos:</td><td class="input">
		    <input type="text" id="creditos_${ir}_${ia}" name="creditos_${ir}_${ia}" value="$vcreditos">
		</td></tr>
		<tr><td class="field">Definitiva:</td><td class="input"><input type="text" name="definitiva_${ir}_${ia}" value="$vdef"></td></tr>
		<tr><td class="agregar" id="asignatura_${ir}_${ia}" colspan=2>

RECON;

 if($ia<$numasignaturas){
$reconocimientos.=<<<RECON
		    <a href="JavaScript:void(null)" onclick="addCourse(this)">Agregar asignatura</a> | 
RECON;
 }

$reconocimientos.=<<<RECON
		    <a href="JavaScript:void(null)" onclick="removeCourse(this)">Remover asignatura</a>
		</td></tr>
	      </table>
RECON;
	}

$reconocimientos.=<<<RECON
	    </td>
	  </tr>
	</table>

	<div class="agregar" style="background:lightgreen;" id="reconocimiento_${ir}">
RECON;

 if($ir<$numrecon){
$reconocimientos.=<<<RECON
	  <a href="JavaScript:void(null)" onclick="addRecon(this)">Agregar reconocimiento</a> | 
RECON;
 }

$reconocimientos.=<<<RECON
	  <a href="JavaScript:void(null)" onclick="removeRecon(this)">Remover reconocimiento</a>
	</div>

    </td></tr>
    </table>
RECON;
  }
  return $reconocimientos;
}

function errorMsg($msg)
{
  global $ERRORS;
  $ERRORS.="".$msg."<br/>";
}

function statusMsg($msg)
{
  global $STATUS;
  $STATUS.="".$msg."<br/>";
}

function getHeaders()
{
  global $PERMCSS;
$header=<<<H
<!-- ------------------------------------------ -->
<!-- HEADER -->
<!-- ------------------------------------------ -->
<meta http-equiv="Content-Type" content="text/html;charset=UTF-8" />
<link rel="stylesheet" href="lib/jquery-ui/jquery-ui.min.css">
<link rel="stylesheet" href="css/aristarchus.css" />
<script src="lib/jquery-ui/jquery.min.js"></script>
<script src="lib/jquery-ui/jquery.min.js"></script>
<script src="lib/jquery-ui/jquery-ui.min.js"></script>
<script src="lib/jquery-ui/moment.min-locales.js"></script>
<script src="js/aristarchus.js"></script>
<style>
$PERMCSS
</style>
H;
 return $header;
}

function getHead()
{
$head=<<<H
<!-- ------------------------------------------------------- -->
<!-- HEAD -->
<!-- ------------------------------------------------------- -->
<a href="index.php"><img class="logo" src="img/Aristarco.png"/></a>
H;
 return $head;
}

function getMainMenu()
{
  global $QPERMISO;

  $userinfo="";
if($QPERMISO){
  $email=$_SESSION["email"];
$userinfo=<<<U
<span class="botonmenu">
  $email (<a class="inverted" href="actions.php?action=logout">logout</a>)
</span>
U;
}else{
$userinfo=<<<U
<span class="botonmenu">
  <a class="inverted" href="index.php?mode=login">Login</a>
</span>
U;
}  

$mainmenu=<<<M
$userinfo
<span class="botonmenu">
  <a class="inverted" href=index.php>Main</a>
</span>
M;
 return $mainmenu;
}

function getFooter()
{
  global $_SERVER;
  $filetime=date(DATE_RFC2822,filemtime($_SERVER["SCRIPT_FILENAME"]));

$footer=<<<M
Latest update: $filetime - 
  Developed by <a class="inverted" href=mailto:jorge.zuluaga@udea.edu.co>Jorge I. Zuluaga</a>, Andrés Gómez, Luis Correa (C) 2016
M;
 return $footer;
}

function getMessages()
{
  global $ERRORS,$STATUS;
  $msg="";

  if(strlen($STATUS)){
$msg.=<<<M
<span class="status">
$STATUS
</span>
M;
  }

  if(strlen($ERRORS)){
$msg.=<<<M
<span class="errors">
$ERRORS
</span>
M;
  }
  return $msg;
}

function renderPage($headers,$head,$mainmenu,$body,$footer,$messages)
{
$page=<<<P
<html>
  <head>
    $headers
  </head>
  <body>
    <div class="layout table">
      <div class="row" style="height:20%">
	<div class="table">
	  <div class="row">
	    <div class="logo cell" style="width:15%">
	      $head
	    </div>
	    <div class="head cell" style="width:55%">
	      <a href="index-es.php"><img src="img/spa.png" height="20px"></a>
	      <a href="index.php"><img src="img/eng.png" height="20px"></a>
	      <br/>
	      <span class="title">Aristarchus Campaigns</span><br/>
	      <span class="subtitle">Collaboratively Measuring the Universe</span><br/>
	      <span class="explanation">Activity site</span>
	    </div>
	    <div class="logos cell">
	      <a href="http://saastronomia.org" target="_blank"><img class="logoinst" src="img/saa.jpg"/></a><br/>
	      <a href="http://www.udea.edu.co" target="_blank"><img class="logoinst" src="img/udea.jpg"/></a>
	      <a href="http://astronomerswithoutborders.org" target="_blank"><img class="logoinst" src="img/awb.jpg"/></a>
	    </div>
	  </div>
	</div>
      </div>
      <div class="row" style="height:2em">
	<div class="menu cell">
           $mainmenu
	</div>
      </div>
      <div class="row" style="height:80%">
	<div class="table">
	  <div class="row">
	    <div class="body cell" style="width:100%">
	      $body
	    </div>
	    <!--
	    <div class="messages cell" style="width:50%">
	      $messages very well
	    </div>
	    -->
	  </div>
	</div>
      </div>
      <div class="row">
	<div class="footer cell">
	  $footer
      </div>
    </div>
  </body>
</html>
P;
   return $page;
}


function readRecon($recfile){
  $fl=fopen($recfile,"r");
  $object=fread($fl,filesize($recfile));
  $data=unserialize($object);
  fclose($fl);
  return $data;
}

function insertSql($table,$mapfields)
{
  global $GLOBALS;
  foreach(array_keys($GLOBALS) as $var){$$var=$GLOBALS["$var"];}
  
  $fields="(";
  $values="(";
  $udpate="";
  $i=0;
  foreach(array_keys($mapfields) as $field){
    $nvalue=$mapfields["$field"];
    if($nvalue==""){$nvalue=$field;}
    if(!isset($$nvalue)) continue;
    $value=$$nvalue;
    $fields.="$field,";
    $values.="'$value',";
    if($i>0){$update.="$field=VALUES($field),";}
    $i++;
  }
  $fields=rtrim($fields,",").")";
  $values=rtrim($values,",").")";
  $update=rtrim($update,",");
  $sql="insert into $table $fields values $values on duplicate key update $update";
  //$GLOBALS["body"].="SQL: $sql<br/>";
  $result=mysqlCmd($sql);
  return $result;
}

function getRecdir($recid)
{
  global $ROOTDIR;
  if($results=mysqlCmd("select * from Reconocimientos where recid='$recid'")){
    $documento=$results["Estudiantes_documento"];
    $planid=$results["Planes_planid"];
    $recdir="$ROOTDIR/data/recon/${documento}_${planid}_${recid}";
  }else{
    $recdir=0;
  }
  return $recdir;
}

function fechaRango($id,$start="",$end=""){
$code=<<<C
<input type="hidden" id="fecharango" name="fecharango">
<script>
    $("#$id").daterangepicker({
        presetRanges: [{
            text: 'Hoy',
	    dateStart: function() { return moment() },
	    dateEnd: function() { return moment() }
	}, {
            text: 'Mañana',
	    dateStart: function() { return moment().add('days', 1) },
	    dateEnd: function() { return moment().add('days', 1) }
	}, {
            text: 'La próxima semana',
            dateStart: function() { return moment().add('weeks', 1).startOf('week') },
            dateEnd: function() { return moment().add('weeks', 1).endOf('week') }
	}],
	datepickerOptions: {
            minDate: 0,
            maxDate: null
        },
	applyOnMenuSelect: false,
	initialText : 'Seleccione el rango de fechas...',
	applyButtonText : 'Escoger',
	clearButtonText : 'Limpiar',
	cancelButtonText : 'Cancelar',
    });
    jQuery(function($){
        $.datepicker.regional['es'] = {
            closeText: 'Cerrar',
            prevText: '&#x3c;Ant',
            nextText: 'Sig&#x3e;',
            currentText: 'Hoy',
            monthNames: ['Enero','Febrero','Marzo','Abril','Mayo','Junio',
                         'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'],
            monthNamesShort: ['Ene','Feb','Mar','Abr','May','Jun',
                              'Jul','Ago','Sep','Oct','Nov','Dic'],
            dayNames: ['Domingo','Lunes','Martes','Mi&eacute;rcoles','Jueves','Viernes','S&aacute;bado'],
            dayNamesShort: ['Dom','Lun','Mar','Mi&eacute;','Juv','Vie','S&aacute;b'],
            dayNamesMin: ['Do','Lu','Ma','Mi','Ju','Vi','S&aacute;'],
            weekHeader: 'Sm',
            dateFormat: 'dd/mm/yy',
            firstDay: 1,
            isRTL: false,
            showMonthAfterYear: false,
            yearSuffix: ''};
        $.datepicker.setDefaults($.datepicker.regional['es']);
    });
C;

  if(!isBlank($start)){
$code.=<<<C
  $("#$id").daterangepicker({
      onOpen: $("#$id").daterangepicker(
          "setRange",
          {start:$.datepicker.parseDate("yy-mm-dd","$start"),
           end:$.datepicker.parseDate("yy-mm-dd","$end")}
      )
  });
C;
  }else{
$code.=<<<C
  var today = moment().toDate();
  var tomorrow = moment().add('days', 1).startOf('day').toDate();
  $("#$id").daterangepicker({
    onOpen: $("#$id").daterangepicker("setRange",{start: today,end: tomorrow})
    });
C;
  }
    
  $code.="</script>";
  return $code;
}

function fadingBox($text,$style,$close=true)
{
  $closeb="";
  $id=generateRandomString(3);
  
  if($close){
$closeb=<<<B
<div style="float:right;top:0px;right:0px;clear:right;position:relative;font-size:0.5em">
<a href="JavaScript:void(null)" onclick="$('#$id').toggle()">
X
</a>
</div>
B;
  }

$box=<<<BOX
<div id="$id" class="box" style="$style">
$closeb
$text
</div>
BOX;
 return $box;
}

function newsBox($date,$title,$text)
{
$text=<<<T
  <div style="position:relative;float:right;font-style:italic">
    $date
  </div>
  <div style="border-bottom:solid black 1px;width:100%;padding-bottom:5px;font-weight:bold">
    $title
  </div>
  <div style="padding-top:10px;">
    $text
  </div>
T;
 $news=fadingBox($text,"background-color:lightblue;margin-bottom:10px;",false);
 return $news;
}

function listImages($obsid)
{
  $obsdir="data/Aristarco6/$obsid";
  $imgs=rtrim(shell_exec("ls -rtm $obsdir/$obsid-image-*.*"));
  $fimages=preg_split("/\s*,\s*/",$imgs);
  $numimgs=0;
  $images=[];
  foreach($fimages as $img){
    if(preg_match("/\.php/",$img) or
       preg_match("/\.exif/",$img) or
       preg_match("/-result\./",$img) or
       isBlank($img)){continue;}
    $numimgs++;
    $imgname=rtrim(shell_exec("basename $img"));
    preg_match("/([^\.]+)\.(\w+)$/",$imgname,$matches);
    $fname=$matches[1];
    $ext=$matches[2];
    array_push($images,"$fname.$ext");
  }
  return $images;
}

function fileProperties($filename)
{
  $dirname=rtrim(shell_exec("dirname '$filename'"));
  $basename=rtrim(shell_exec("basename '$filename'"));
  preg_match("/([^\.]+)\.(\w+)/",$basename,$matches);
  $fname=$matches[1];
  $ext=$matches[2];
  return array("dirname"=>$dirname,
	       "basename"=>$basename,
	       "fname"=>$fname,
	       "ext"=>$ext);
}

function mySystem($cmd,$tmp,&$out,&$err)
{
  $proc=proc_open($cmd,array(0=>array("pipe","r"),
			     1=>array("pipe","w"),
			     2=>array("pipe","w")),$pipes);
  if(is_resource($proc)){
    $out=stream_get_contents($pipes[1]);
    $err=stream_get_contents($pipes[2]);

    $fout=fopen("$tmp/cmd.log","w");
    fwrite($fout,$cmd);
    fclose($fout);

    $fout=fopen("$tmp/output.log","w");
    fwrite($fout,$out);
    fclose($fout);

    $ferr=fopen("$tmp/error.log","w");
    fwrite($ferr,$err);
    fclose($ferr);

    proc_close($proc);
  }
}

//////////////////////////////////////////////////////////
//CONNECT TO DATABASE
//////////////////////////////////////////////////////////
$DB=mysqli_connect("localhost",$USER,$PASSWORD,$DATABASE);
mysqli_set_charset($DB,'utf8');
mysqli_query($DB,"set names 'utf8'");
$result=mysqlCmd("select now();",$qout=0);
$DATE=$result[0];
$DATE_ARRAY=preg_split("/ /",$DATE);

//////////////////////////////////////////////////////////
//TABLE FIELDS
//////////////////////////////////////////////////////////
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//MOVILIDAD
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$results=mysqlCmd("describe Aristarco6;",$qout=1);
$ARISTARCO6_FIELDS=array();
foreach($results as $field){
  $fieldname=$field[0];
  $ARISTARCO6_FIELDS["$fieldname"]=$fieldname;
}

$results=mysqlCmd("describe Users;",$qout=1);
$USERS_FIELDS=array();
foreach($results as $field){
  $fieldname=$field[0];
  $USERS_FIELDS["$fieldname"]=$fieldname;
}

?>

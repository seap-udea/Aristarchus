<form id='aristarco6-form' method='post' enctype='multipart/form-data' accept-charset='utf-8'>
  <input type="file" id="file-select" name="photos[]" multiple/>
  <button type="submit" id="upload-button">Upload</button>
</form>

//==============================
//UPLOADED IMAGES
//==============================
$upimages="";
$upimages.="<p>Uploaded images:</p>";
$imgs=rtrim(shell_exec("ls -m $obsdir/*_image_*.*"));
$fimages=preg_split("/\s*,\s*/",$imgs);
$numimgs=0;
$upimages.="<div class='table' style='text-align:center'>";
$images="";
$times="";
foreach($fimages as $img){
  if(preg_match("/\.php/",$img)){continue;}
  if(isBlank($img)){continue;}
  $numimgs++;
  $images.=rtrim(shell_exec("basename $img")).";";
  include("$obsdir/${obsid}_image_${numimgs}.php");
  $times.="$time;";
$upimages.=<<<I
<div class='cell'>
<img class='sample' src='$img'/><br/>
Image $numimgs<br/>
Time: $time
</div>
I;
}
if(!$numimgs){$body.=$blankimg;}
$upimages.="Times def:$times, Images def:$images<br/>";
$upimages.="</div></div>";

//==============================
//HELP
//==============================
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

//==============================
//SUBMISSION FORM
//==============================

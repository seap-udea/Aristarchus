	renderImg(element+"_sub_"+typeimg,imgsrc+"-crop__"+typeimg+".png");



	renderImg(element+"_sub_merc",imgsrc+"-crop__merc.png");
	renderImg(element+"_sub_spot",imgsrc+"-crop__spot.png");


	  <br/>
	  <canvas id="image${nimg}_sub_merc" width="$swidth" height="$sheight" style="border:solid black 1px">
	  </canvas>


	  <br/>
	  <canvas id="image${nimg}_sub_spot" width="$swidth" height="$sheight" style="border:solid black 1px">
	  </canvas>




	  <br/>
	  <canvas id="image${nimg}_sub_merc" width="$swidth" height="$sheight" style="border:solid black 1px">
	  </canvas>



      $timestr=preg_replace("/[:\s]/","-",$time);
      $suffix="image_$timestr";

      $fl=fopen("$obsdir/$filephp","w");
      fwrite($fl,"<?php\n");
      fwrite($fl,"\$time='$time';\n");
      fwrite($fl,"?>\n");
      fclose($fl);

      if(isBlank($time)){
	errorMsg("No time provided");
	goto endaction;
      }


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

<tr>
  <td class="field">
    E-mail me the results:
  </td>
  <td class="input">
    <input type="text" name="email" value="$email" size="30" placeholder="me@server.mail.com">
  </td>
</tr>


  if($nimg>=3){
    $selection=generateSelection(array("auto"=>"Automatic","spot"=>"Sunspot"),
				 "typealignment",$typealignment);
$seltypea=<<<S
<table class="form" style="background:lightgray">
<tr>
  <td class="field">
    Type of alignment:
  </td>
  <td class="input">
    $selection
    <input type='hidden' name='nimg' value='$nimg'>
  </td>
</tr>
</table>
S;
  }

else if($nimg>=3 and 
	     (isBlank($email) or
	      !preg_match("/@/",$email) or
	      !preg_match("/\./",$email))
	     ){
      errorMsg("Before perform the analysis you must provide a valid e-mail");
      goto endaction;
    }
// Closure
(function() {
    /**
     * Decimal adjustment of a number.
     *
     * @param {String}  type  The type of adjustment.
     * @param {Number}  value The number.
     * @param {Integer} exp   The exponent (the 10 logarithm of the adjustment base).
     * @returns {Number} The adjusted value.
     */
    function decimalAdjust(type, value, exp) {
        // If the exp is undefined or zero...
        if (typeof exp === 'undefined' || +exp === 0) {
            return Math[type](value);
        }
        value = +value;
        exp = +exp;
        // If the value is not a number or the exp is not an integer...
        if (isNaN(value) || !(typeof exp === 'number' && exp % 1 === 0)) {
            return NaN;
        }
        // Shift
        value = value.toString().split('e');
        value = Math[type](+(value[0] + 'e' + (value[1] ? (+value[1] - exp) : -exp)));
        // Shift back
        value = value.toString().split('e');
        return +(value[0] + 'e' + (value[1] ? (+value[1] + exp) : exp));
    }

    // Decimal round
    if (!Math.round10) {
        Math.round10 = function(value, exp) {
            return decimalAdjust('round', value, exp);
        };
    }
    // Decimal floor
    if (!Math.floor10) {
        Math.floor10 = function(value, exp) {
            return decimalAdjust('floor', value, exp);
        };
    }
    // Decimal ceil
    if (!Math.ceil10) {
        Math.ceil10 = function(value, exp) {
            return decimalAdjust('ceil', value, exp);
        };
    }
})();

function defaultSuccess(result){
    alert('Success:\n'+result);
}
function defaultError(xhttp,status,error){
    alert('Error:\n'+getAttrs(error));
}

function getAttrs(object,context){
    var attributes="";
    var endline="<br/>";
    if(context=="alert"){endline="\n";}
    var attribute;
    for(attribute in object){
        attributes+=attribute+":"+object[attribute]+endline;
    }
    return attributes;
}

function ajaxDo(action,params,onsuc,onerr)
{
    if(typeof(params)=='undefined'){params='';}
    if(typeof(onsuc)=='undefined'){onsuc=defaultSuccess;}
    if(typeof(onerr)=='undefined'){onerr=defaultError;}

    var ajax='ajax.php';
    
    jQuery.ajax({
	url:'actions.php?action='+action+'&params='+params,
	success:onsuc,
	error:onerr,
    });
}

function drawInit(elementId) {
    var canvas=document.getElementById(elementId);
    var ctx=canvas.getContext('2d');
    canvas={ctx:ctx,w:canvas.width,h:canvas.height};
    ctx.save();
    return canvas;
}

function getPosition($element)
{
    var off=$element.offset();
    var xoff=off.left;
    var yoff=off.top;
    return {xoff:xoff,yoff:yoff};
}

function subimg(canvas,subcanvas,imgsrc,position)
{
    var img=new Image();
    img.src=imgsrc;
    var iw=$('#'+canvas).width();
    var ih=$('#'+canvas).height();
    var canvas=drawInit(subcanvas);
    var c=canvas.ctx,w=canvas.w,h=canvas.h;
    var posx=0,posy=0,facx=1,facy=1;
    if(position.indexOf(",")!=-1){
	var pos=position.split(",");
	posx=pos[0]*w/(1.0*iw);posy=pos[1]*h/(1.0*ih);
	facx=(1.0*iw)/(pos[2]-pos[0]),facy=(1.0*ih)/(pos[3]-pos[1]);
    }
    alert(w/2+","+h/2+","+iw+","+ih+","+posx+","+posy+","+facx+","+facy);
    img.onload=function(){
	c.translate(posx,posy);
	c.scale(facx,facy);
	c.translate(-posx,-posy);
	c.drawImage(img,0,0,w,h);
    }
}

function renderImg(element,imgsrc)
{
    //Get image size
    var img=new Image();
    img.src=imgsrc;
    
    alert("Rendering "+img.src+" at "+element);

    var width=img.naturalWidth;
    var height=img.naturalHeight;

    var $element=$('#'+element);
    $element.attr("height",100);
    $element.attr("width",(100.0*height)/width);

    //Get Canvas Object
    var canvas=drawInit(element);
    var c=canvas.ctx,w=canvas.w,h=canvas.h;

    img.onload=function(){
	c.drawImage(img,0,0,w,h);
    }
}

function loadCanvas(element)
{
    //Set size of image
    var $element=$('#'+element);

    var $merc=$('#'+element+"_rect");
    var $spot=$('#'+element+"_rect_spot");
    var $posmerc=$('#'+element+"_rect_pos");
    var $posspot=$('#'+element+"_rect_spot_pos");

    var $targ,$pos;

    var domelement=document.getElementById(element);

    //Get Canvas Object
    var canvas=drawInit(element);
    var c=canvas.ctx,w=canvas.w,h=canvas.h;

    function rectangles(){
	c.save();
	c.setLineDash([0]);
	var mercury=$merc.val();
	var sunspot=$spot.val();
	var mercuryxy;
	if(mercury.indexOf(',')!=-1){
	    mxy=mercury.split(",");
	    c.beginPath()
	    c.strokeStyle="blue";
	    c.rect(mxy[0]*w,mxy[1]*h,(mxy[2]-mxy[0])*w,(mxy[3]-mxy[1])*h);
	    c.stroke();
	}
	var sunspotxy;
	if(sunspot.indexOf(',')!=-1){
	    mxy=sunspot.split(",");
	    c.beginPath()
	    c.strokeStyle="red";
	    c.rect(mxy[0]*w,mxy[1]*h,(mxy[2]-mxy[0])*w,(mxy[3]-mxy[1])*h);
	    c.stroke();
	}
	c.restore();
    }

    //Draw image
    var img=new Image();
    var imgsrc=$element.attr('value');
    var iw,ih;
    img.onload=function(){
	c.drawImage(img,0,0,w,h);
	rectangles();
	iw=img.naturalWidth;
	ih=img.naturalHeight;
    }
    img.src=imgsrc;
    ////subimg(element,element+"_sub_merc",imgsrc,$merc.val());

    var xoff,yoff;
    var off=getPosition($element.parent());
    xoff=off.xoff;
    yoff=off.yoff;
    //$info.html(xoff+","+yoff);
    
    //Start drawing
    var xini=0,yini=0;
    var xend=0,yend=0;
    var startrect=0;
    var merc=1;
    var spot=0;
    var typeimg;

    function startDrawing(e){
	if(merc){
	    domelement.style.cursor="crosshair";
	}else{
	    domelement.style.cursor="default";
	}
	var x=e.pageX;
	var y=e.pageY;
	//$info.html(x+","+y);
	xini=parseInt(x-xoff);
	yini=parseInt(y-yoff);
	//domelement.style.cursor="pointer";
	startrect=1;
	c.clearRect(0,0,w,h);
	c.drawImage(img,0,0,w,h);
    }
    function stopDrawing(e){
	var x=e.pageX;
	var y=e.pageY;
	xend=parseInt(x-xoff);
	yend=parseInt(y-yoff);
	//domelement.style.cursor="default";
	
	if(merc){
	    $targ=$merc;typeimg="merc";
	    $pos=$posmerc;
	}
	if(spot){
	    $targ=$spot;typeimg="spot";
	    $pos=$posspot;
	}
	
	$targ.val(Math.round10(xini/(1.0*w),-4)+","+
		  Math.round10(yini/(1.0*h),-4)+","+
		  Math.round10(xend/(1.0*w),-4)+","+
		  Math.round10(yend/(1.0*h),-4));

	merc=merc?0:1;
	spot=merc?0:1;
	if(merc){
	    domelement.style.cursor="crosshair";
	}else{
	    domelement.style.cursor="default";
	}

	startrect=0;
	xini=0;xend=0;
	yini=0;yend=0;

	rectangles();

	var dh=h/10,dw=w/10

	//Extract area
	ajaxDo("locate","imgsrc:"+imgsrc+";coords:"+$targ.val(),
	       function(result){
		   $pos.val(result);
		   pos=result.split(",");
		   x=pos[0]/(1.0*iw)*w;
		   y=pos[1]/(1.0*ih)*h;
		   c.beginPath()
		   c.strokeStyle='blue';
		   c.setLineDash([0])
		   c.moveTo(x,y-dh);
		   c.lineTo(x,y+dh);
		   c.moveTo(x-dw,y);
		   c.lineTo(x+dw,y);
		   c.stroke();
	       },
	       function(error){
		   $('#test').html(error);
	       });
    }
    function mouseMove(e){
	var x=e.pageX;
	var y=e.pageY;
	var color='blue';
	xend=parseInt(x-xoff);
	yend=parseInt(y-yoff);
	if(startrect){
	    c.clearRect(0,0,w,h);
	    c.beginPath();
	    if(spot) color='red'
	    c.strokeStyle=color;
	    c.setLineDash([5]);
	    //$info.html(xini+","+yini+"->"+xend+","+yend);
	    c.rect(xini,yini,xend-xini,yend-yini);
	    c.drawImage(img,0,0,w,h);
	    c.stroke();
	}
    }

    $element.mousedown(startDrawing);
    $element.mouseup(stopDrawing);
    $element.mousemove(mouseMove);
}

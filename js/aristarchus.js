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
	url:'ajax.php?action='+action+'&params='+params,
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

function loadCanvas(element)
{
    //Set size of image
    var $element=$('#'+element);
    var $info=$('#'+element+"_info");
    var domelement=document.getElementById(element);
    
    //Get Canvas Object
    canvas=drawInit(element);
    var c=canvas.ctx,w=canvas.w,h=canvas.h;

    //Draw image
    var img=new Image();
    img.src=$element.attr('value');
    c.clearRect(0,0,w,h);
    c.drawImage(img,0,0,w,h);

    var xoff,yoff;
    window.setTimeout(function(){
	var off=getPosition($element);
	xoff=off.xoff;
	yoff=off.yoff;
	$info.html(xoff+","+yoff);
    },1000);

    //Start drawing
    var xini=0,yini=0;
    var xend=0,yend=0;
    var startrect=0;
    function startDrawing(e){
	var x=e.pageX-window.scrollX;
	var y=e.pageY-window.scrollY;
	$info.html(x+","+y);
	xini=parseInt(x-xoff);
	yini=parseInt(y-yoff);
	domelement.style.cursor="crosshair";
	startrect=1;
	c.clearRect(0,0,w,h);
	c.drawImage(img,0,0,w,h);
    }
    function stopDrawing(e){
	var x=e.pageX-window.scrollX;
	var y=e.pageY-window.scrollY;
	xend=parseInt(x-xoff);
	yend=parseInt(y-yoff);
	domelement.style.cursor="default";
	startrect=0;
	xini=0;xend=0;
	yini=0;yend=0;
    }
    function mouseMove(e){
	var x=e.pageX-window.scrollX;
	var y=e.pageY-window.scrollY;
	xend=parseInt(x-xoff);
	yend=parseInt(y-yoff);
	if(startrect){
	    c.clearRect(0,0,w,h);
	    c.beginPath();
	    c.strokeStyle="black";
	    c.setLineDash([5]);
	    $info.html(xini+","+yini+"->"+xend+","+yend);
	    c.rect(xini,yini,xend-xini,yend-yini);
	    c.drawImage(img,0,0,w,h);
	    c.stroke();
	}
    }
    $element.mousedown(startDrawing);
    $element.mouseup(stopDrawing);
    $element.mousemove(mouseMove);
    
    /*

    //

    var xini,yini;
    var xend,yend;
    function startHandler(e){
	xini=e.pageX;
	yini=e.pageY;
    }
    function endHandler(e){
	xend=e.pageX;
	yend=e.pageY;
    }
    function show(e){
	alert(xini+","+yini+","+xend+","+yend);
    }
    //$element.on("touchstart",startHandler);
    $element.click(startHandler);
    $element.onmousemove=endHandler;
    $element.ondblclick=show;
    */
}

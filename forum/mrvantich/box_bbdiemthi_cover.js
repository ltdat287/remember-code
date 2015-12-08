document.writeln('<link rel="stylesheet" type="text/css" href="http:/sinhviennamdinh.net/mrvantich/style.css" />');
function getRadCheck(value)
{
	document.getElementById('choice').value =value;
}
function redirect()
{
	var search = document.getElementById('bbsearch').value;
	var tab = document.getElementById('tab').value;
	var choice = document.getElementById('choice').value ;
	var utm_campaign = document.getElementById('utm_campaign').value ;
	window.open("http://embed.baamboo.com/tools/Redirection.aspx?search="+ search +"&tab="+ tab +"&choice="+choice+"&utm_campaign"+utm_campaign);
}
function enterRedirect(ev)
{
	var genEvent = (!ev) ? window.event : ev;
	if (genEvent.keyCode == 13 ) {
		checkInput();
		return false;
	}
}
function getTab(value)
{
	document.getElementById('tab').value=value;
	var tab = document.getElementById('tab').value;
	if (tab !=5 && tab != '5'){
		document.getElementById('searchButton').style.backgroundImage = 'url(http://sinhviennamdinh.net/mrvantich/bg_button_search.jpg)';
	}
}

function displayrad(id)
{
	if(id =="1")
	{
		document.getElementById("tcontent2").style.display = 'block';
		document.getElementById("tcontent1").style.display = 'none';
		document.getElementById("tcontent3").style.display = 'none';
		document.getElementById("tcontent4").style.display = 'none';
		document.getElementById("tcontent5").style.display = 'none';
		document.getElementById("tcontent6").style.display = 'none';
		document.getElementById("mp3").className='selected';
		document.getElementById("video").className='noselected';
		document.getElementById("dic").className='noselected';
		document.getElementById("tra").className='noselected';
		document.getElementById("dthi").className='noselected';
		
		clearDefault();

	}else if(id=="2")

	{
		document.getElementById("tcontent2").style.display = 'none';
		document.getElementById("tcontent1").style.display = 'block';
		document.getElementById("tcontent3").style.display = 'none';
		document.getElementById("tcontent4").style.display = 'none';
		document.getElementById("tcontent5").style.display = 'none';
		document.getElementById("tcontent6").style.display = 'none';

		document.getElementById("video").className='selected';
		document.getElementById("mp3").className='noselected';
		document.getElementById("dic").className='noselected';
		document.getElementById("tra").className='noselected';
		document.getElementById("dthi").className='noselected';
		
		clearDefault();
	}else if(id=="3")
	{
		document.getElementById("tcontent2").style.display = 'none';
		document.getElementById("tcontent3").style.display = 'block';
		document.getElementById("tcontent1").style.display = 'none';
		document.getElementById("tcontent4").style.display = 'none';
		document.getElementById("tcontent5").style.display = 'none';
		document.getElementById("tcontent6").style.display = 'none';

		document.getElementById("video").className='noselected';
		document.getElementById("tra").className='noselected';
		document.getElementById("mp3").className='noselected';
		document.getElementById("dic").className='selected';
		document.getElementById("dthi").className='noselected';
		
		clearDefault();
	}else if(id=="4")
	{
		document.getElementById("tcontent2").style.display = 'none';
		document.getElementById("tcontent3").style.display = 'none';
		document.getElementById("tcontent1").style.display = 'none';
		document.getElementById("tcontent4").style.display = 'block';
		document.getElementById("tcontent5").style.display = 'none';
		document.getElementById("tcontent6").style.display = 'none';

		document.getElementById("video").className='noselected';
		document.getElementById("tra").className='selected';
		document.getElementById("mp3").className='noselected';
		document.getElementById("dic").className='noselected';
		document.getElementById("dthi").className='noselected';
		
		clearDefault();
	}else if(id=="5")
	{
		document.getElementById("tcontent2").style.display = 'none';
		document.getElementById("tcontent3").style.display = 'none';
		document.getElementById("tcontent1").style.display = 'none';
		document.getElementById("tcontent4").style.display = 'none';
		document.getElementById("tcontent5").style.display = 'block';
		document.getElementById("tcontent6").style.display = 'none';

		document.getElementById("video").className='noselected';
		document.getElementById("tra").className='noselected';
		document.getElementById("mp3").className='noselected';
		document.getElementById("dic").className='noselected';
		document.getElementById("dthi").className='selected';
		
		setDefault();
	}
	else if(id=="6")
	{
		document.getElementById("tcontent2").style.display = 'none';
		document.getElementById("tcontent3").style.display = 'none';
		document.getElementById("tcontent1").style.display = 'none';
		document.getElementById("tcontent4").style.display = 'none';
		document.getElementById("tcontent5").style.display = 'block';
		document.getElementById("tcontent6").style.display = 'none';

		document.getElementById("video").className='noselected';
		document.getElementById("tra").className='noselected';
		document.getElementById("mp3").className='noselected';
		document.getElementById("dic").className='noselected';
		document.getElementById("dthi").className='noselected';

		clearDefault();		
	}
	else
	{
		document.getElementById("tcontent2").style.display = 'none';
		document.getElementById("tcontent3").style.display = 'none';
		document.getElementById("tcontent1").style.display = 'none';
		document.getElementById("tcontent4").style.display = 'none';
		document.getElementById("tcontent5").style.display = 'block';
		document.getElementById("tcontent6").style.display = 'none';

		document.getElementById("video").className='noselected';
		document.getElementById("tra").className='noselected';
		document.getElementById("mp3").className='noselected';
		document.getElementById("dic").className='noselected';
		document.getElementById("dthi").className='selected';
		setDefault();		
	}
}
function clearDefault(){
	
	document.getElementById('bbsearch').value = '';
	document.getElementById('bbsearch').style.color = "#000000";
	
}
function setDefault(){
	document.getElementById('bbsearch').value = 'Nhập số báo danh hoặc họ tên';
	document.getElementById('bbsearch').style.color = "#919BA5";
	document.getElementById('searchButton').style.backgroundImage = 'url(http://sinhviennamdinh.net/mrvantich/bg_button_search.jpg)';
}
function addLoadEvent(func) {
	var oldonload = window.onload;
	if (typeof window.onload != 'function') {
		window.onload = func;
	} else {
		window.onload = function() {
			if (oldonload) {
				oldonload();
			}
			func();
		}
	}
}
addLoadEvent(function() {
	document.getElementById('bbsearch').onkeypress = enterRedirect;
	document.getElementById("tcontent2").style.display = 'none';
	document.getElementById("tcontent1").style.display = 'none';
	document.getElementById("tcontent3").style.display = 'none';
	document.getElementById("tcontent4").style.display = 'none';
	document.getElementById("tcontent5").style.display = 'block';
	document.getElementById("tcontent6").style.display = 'none';
	document.getElementById("mp3").className='noselected';
	document.getElementById("video").className='noselected';
	document.getElementById("dic").className='noselected';
	document.getElementById("tra").className='noselected';
	document.getElementById("dthi").className='selected';
	setDefault();
	getTab(5);	
});
function checkInput(){	
	var tab = document.getElementById('tab').value;
	if (tab ==5 || tab == '5'){
		
		var u = document.getElementById("u");
		var s = document.getElementById('bbsearch');
		var url;
		var dmain = "http://timdiemthi.com/";
		if (s.value == 'Nhập số báo danh hoặc họ tên')					
			url = dmain + "?ncache=1&dt=" + location.hostname + "&ui=" + u.selectedIndex + "&u=" + u.value + "&s=";
		else 
			url = dmain + "?ncache=1&dt=" + location.hostname +"&ui=" + u.selectedIndex + "&u=" + u.value + "&s=" + s.value;		
		
		
		LetGo(url);
	} else {
		redirect();
	}
}

function LetGo(url)
{
    window.location = url;    
}


var tablewidth ="468px";
var tableheight ="60px";
var searchbox_tablewidth ="443px";
var textbox_tdwidth ="335px";
var textbox_size ="42";
var tablestyle ="border:solid 1px #45BEF1; background-image:url(\'http:/sinhviennamdinh.net/mrvantich/bgsearch.gif\');background-repeat:repeat-x; ";
document.writeln('<center><table cellpadding="0" cellspacing="0" width='+tablewidth+' height='+tableheight+' align="center" style="'+tablestyle+'"  >'+
''+
'<tr>'+
'<td align="center" style="height:25px;font:bold 11px Tahoma;color:#999;">	'+
'<span id="mp3" class="noselected"><a href="http://sinhviennamdinh.net//forumdisplay.php?175-M%C3%A3-ngu%E1%BB%93n-Vbb">VBB</a></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;	'+
'<span id="video" class="noselected"><a href="http://sinhviennamdinh.net/showthread.php?t=714&p=1283#post1283">TSMT</a></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;	'+
'<span id="diemtin" class="noselected"><a href="http://sinhviennamdinh.net/content.php">TRANG CHỦ</a></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;	'+
'<span id="dic" class="noselected" style="display:none;"><a href="javascript:displayrad(\'3\');getRadCheck(\'1\');document.getElementById(\'radD1\').checked=\'true\';getTab(\'3\');">VIETDIC</a></span>'+
'<span id="tra" class="noselected"><a href="http://sinhviennamdinh.net//forumdisplay.php?163-Software-and-Download">CNTT-IT</a></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;	'+
'<span id="dthi" class="selected"><a href="javascript:displayrad(\'5\');getRadCheck(\'1\');getTab(\'5\');">ĐIỂM THI</a></span>&nbsp;<img src="http://sinhviennamdinh.net/mrvantich/new.gif">&nbsp;&nbsp;&nbsp;&nbsp;	'+
'</td>'+
'</tr>'+

'<tr>'+
'<td align="center">'+
'<table cellpadding="0" cellspacing="0" width='+searchbox_tablewidth+'  align="center" >'+
'<tr>'+
'<td style="background-image:url(\'http://sinhviennamdinh.net/mrvantich/bg_textbox_left.jpg\');width:4px;background-repeat:no-repeat;">'+

'</td>'+
'<td style="background-image:url(\'http://sinhviennamdinh.net/mrvantich/bg_textbox.jpg\');width:'+textbox_tdwidth+';background-repeat:repeat-x;height:25px;padding:0 10px 0 0;" align="left">	'+
'<input  id="utm_campaign" name="utm_campaign" type="hidden" value="Mua" >'+
'<input id="bbsearch" name="bbsearch"  type="text" size='+textbox_size+'  style="padding: 0px 0 3px 10px;width:100%;background-image:url(\'http://sinhviennamdinh.net/mrvantich/bg_textbox_in.jpg\');background-repeat:no-repeat;border:none;background-position:right;border-width:0px;" onclick="this.style.backgroundImage=\'none\';javascript: clearDefault();" >'+
'</td>'+
'<td id="searchButton" align="center" style="background-image:url(\'http://sinhviennamdinh.net/mrvantich/bg_button_search.jpg\');width:91px;background-repeat:no-repeat;">'+

'<div style="cursor:pointer;width:88px; height:20px;" onclick="javascript: checkInput();">&nbsp;</div>'+
'</td>'+
'</tr>'+
'</table>'+
'</td>'+
'</tr>'+
'<tr>'+
'<td style="font:12px Verdana; height:48px; ">'+
'<input id="choice" name="choice" type="hidden" value="1">'+
'<input id="tab" name="tab" type="hidden" value="5">'+
'<div id="tcontent1" style="text-align:center;display:none; color:#000;font:12px Tahoma;">'+
'<input name="raVideo" type="radio" value="1" onclick="getRadCheck(this.value)" checked >&nbsp;<span style="vertical-align:middle;">YouTube</span>&nbsp;&nbsp;'+
'<input name="raVideo" id="radV1" type="radio" value="3" onclick="getRadCheck(this.value)" >&nbsp;Phim<img src="http://sinhviennamdinh.net/mrvantich/new.gif" />'+
'<input name="raVideo" type="radio" value="5" onclick="getRadCheck(this.value)">&nbsp;Việt nam&nbsp;&nbsp;'+
'</div>'+
'<div id="tcontent2" style="color:#000;text-align:center;display:none;font:12px Tahoma;" >'+
'<input name="raMP3" id="radM1" type="radio" value="1"  onclick="getRadCheck(this.value)" checked>&nbsp;Việt nam &nbsp;&nbsp;'+
'<input name="raMP3" type="radio" value="3" onclick="getRadCheck(this.value)">&nbsp;Lời bài hát &nbsp;&nbsp;'+      
'</div>'+
'<div id="tcontent3" style="text-align:center;display:none; color:#000;font:12px Tahoma;">'+
'<input name="raDic" id="radD1" type="radio" value="1" onclick="getRadCheck(this.value)" checked>&nbsp;Anh-Việt&nbsp;&nbsp;'+
'<input name="raDic" type="radio" value="3" onclick="getRadCheck(this.value)">&nbsp;Pháp-Việt&nbsp;&nbsp;'+
'<input name="raDic" type="radio" value="9" onclick="getRadCheck(this.value)">&nbsp;Đức-Việt&nbsp;&nbsp;'+
'</div>'+
'<div id="tcontent4" style="text-align:center;display:none; color:#000;font:12px Tahoma;">'+
'<input name="raTra" id="radT1" type="radio" value="en_vn" onclick="getRadCheck(this.value)" checked>&nbsp;Anh-Việt&nbsp;&nbsp;'+
'<input name="raTra" type="radio" value="fr_vn" onclick="getRadCheck(this.value)">&nbsp;Pháp-Việt&nbsp;&nbsp;'+
'<input name="raTra" type="radio" value="vn_vn" onclick="getRadCheck(this.value)">&nbsp;Việt-Việt&nbsp;&nbsp;'+
'</div>'+
'<div id="tcontent5" style="text-align:center; width:100%;color:#000;font:12px Verdana;">'+
	'<table width="100%"><tr><td width="100%" style="text-align:left; padding: 0 0 0 40px;"><span style="text-align:center;font-size:9pt">'+
	'Chọn trường:&nbsp;'+
	'</span>'+
	'<select id="u" style="font-size: 12px; font-family: Tahoma, Verdana,Times New Roman,Arial; 	background-color: #f5f5f5; scrollbar-face-color:#C1BFB6; scrollbar-highlight-color:#C1BFB6; scrollbar-3dlight-color:#EDE9E5; scrollbar-darkshadow-color:#EDE9E5;	scrollbar-shadow-color:#C1BFB6; scrollbar-arrow-color:#FFFFFF; scrollbar-track-color:#EDE9E5; padding: 0; width: 200px;">'+		
	MakeCombo()+	
	'</select>'+
	'</td></tr><tr><td style="text-align:right;color:#000;font:10px Verdana;">'+
	'<img src=http://sinhviennamdinh.net/mrvantich/new.gif>&nbsp;<b>Tính năng mới:</b> Bạn có thể sớm biết mình đã trúng tuyển! <a href="http://timdiemthi.com/More.aspx" target="_blank" >Thử ngay!</a>'+
     
'</td></tr></table>'+
'</div>'+
'<div id="tcontent6" style="text-align:center;display:none; color:#000;font:11px Tahoma;">'+
'<input name="raRao" id="radR1" type="radio" value="1" onclick="getRadCheck(this.value)" checked>&nbsp;Tìm chính xác&nbsp;&nbsp;'+
'<input name="raRao" type="radio" value="0" onclick="getRadCheck(this.value)">&nbsp;Có một trong các từ trên&nbsp;&nbsp;'+
'</div>'+

'</td>'+
' </tr>'+
'<tr>'+
'<td align="center" style="font:bold 10px Tahoma;padding-bottom:3px;">'+
'</td>'+
'</tr>'+
'</table></center>');

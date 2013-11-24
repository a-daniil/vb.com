// JavaScript Document

function getId(id){return document.getElementById(id);}
function crEl(type){return document.createElement(type);}

function ajax(request,func) {
	var req;
	try{req=new XMLHttpRequest();}
	catch(e){
		try{req=new ActiveXObject("Microsoft.XMLHTTP");}
		catch(e){return false;}
	}
	req.open('GET',request,true);
	req.onreadystatechange=function(){
		if(req.readyState==4 && req.status==200){func(req.responseText);}
	}
	req.send(null);
	return true;
}

function imgIndex() {
    
}

var imgCurrent = 0;
var objImgCurrent;

function imgLoad(parent, url){
	getId('ank-photo').src=url;
        imgCurrent = parseInt($(parent).attr('img_num'));
        objImgCurrent = parent;
        
}

function imgNext(parent){
    imgCurrent ++;
    //Don`t refactoring next 2 lines!!!
    if ($('#ank_img_' + imgCurrent).length == 0) imgCurrent = 0;
    imgCurrentId = '#ank_img_' + imgCurrent;
    
    bg = $(imgCurrentId).css('background-image');
    bg_url = bg.replace('url(','').replace(')','');
    src_img_full = $(imgCurrentId).attr('src_img_full');
    $(parent).attr('img_src_from',imgCurrentId)
    if (src_img_full == null) src_img_full = bg_url;
    $(parent).attr('src', src_img_full);
}

function showPhoto(url){
	var div=crEl('div');
	var cls=crEl('p');
	var img=new Image;
	img.src=url;
	div.className='window';	
	cls.className='close';
	cls.innerHTML='Закрыть';
	cls.onclick=function(){document.body.removeChild(this.parentNode);}
	div.appendChild(img);
	div.appendChild(cls);
	document.body.appendChild(div);
	div.style.left=Math.round(document.body.offsetWidth/4+Math.random()*100)+'px';
	div.style.top=window.pageYOffset+div.offsetTop+Math.round(Math.random()*50)+'px';
}

function types(){
	var value = getId('jstype').value;
	var performer = getId('jsperformer');
	
	if ( value == 2 ){
		performer.disabled = true;
	} else {
		performer.disabled = false;
	}
	
}

function createList( list, id, q, name ) {
	var select=getId(id);
	var value=getId('jscity').value;
	select.innerHTML='';
	switch(value){
		case '1': var ln= q + '1'; break;
		case '2': var ln= q + '2'; break;
		default:
			input=document.createElement('input');
			input.setAttribute('name','metro');
			select.appendChild(input);
			return;
	}
	input=document.createElement('select');
	input.setAttribute('name', name);
	var len=list[ln].length;
	for(var i=0;i<len;i++){
		var opt=document.createElement('option');
		opt.value = i + 1;
		opt.innerHTML=list[ln][i];
		input.appendChild(opt);
	}
	input.className = 'span6';
	select.appendChild(input);
}

function createList2( list, id, q, name ) {
	var select=getId(id);
	var value=getId('city').value;
	select.innerHTML='';
	switch(value){
		case '1': var ln= q + '1'; break;
		case '2': var ln= q + '2'; break;
		default:
			input=document.createElement('input');
			input.setAttribute('name','metro');
			select.appendChild(input);
			return;
	}
	//input=document.createElement('select');
	//input.setAttribute('name', name);
	var len=list[ln].length;
	for(var i=0;i<len;i++){
		var opt=document.createElement('option');
		opt.value = i + 1;
		opt.innerHTML=list[ln][i];
		select.appendChild(opt);
	}
	//select.appendChild(input);
}

function createList2Form( list, id, q ) {
	var value=getId('city').value;
	switch(value){
		case '1': var ln= q + '1'; break;
		case '2': var ln= q + '2'; break;
	}
	input=getId(id);
	for(i=input.options.length-1;i>=0;i--)
    {
		input.remove(i);
    }
	var len=list[ln].length;
	for(var i=0;i<len;i++){
		var opt=document.createElement('option');
		opt.value = i + 1;
		opt.innerHTML=list[ln][i];
		input.appendChild(opt);
	}	
}

var passchange=false;

function showPassChange(){
	var cont=getId('passchange')
	if(passchange){
		cont.style.display='none';
		passchange=false;
	}
	else{
		cont.style.display='block';
		passchange=true;
	}
	
}

function captchaRefresh(){
	$.get('/auth/captcha-refresh/id/'+$('#captcha-id').value,function(response){		
		$('#captcha-image').attr( 'src', '/captcha/'+response+'.png');
		$('#captcha-id').val(response);
	});
}

function addAnkets(url) {
	$.get('/index/add-ankets', {}, function(response){
		$('.span6').html(response);
		console.log(response);
	});
}

function addComment(url) {
	$.get('/index/comm-add-form', {url: url}, function(response){
		$('#add-comment').html(response);
		$('#url').val(url);
	});	
}

function ctrlEnter(e,id){
    if (((e.keyCode == 13) || (e.keyCode == 10)) && (e.ctrlKey == true)) document.getElementById(id).click();
}

function sendEnter(e,id){
    if ((e.keyCode == 13) || (e.keyCode == 10)) document.getElementById(id).click();
}

function submitForm(name) {
	document.forms[name].submit();
}
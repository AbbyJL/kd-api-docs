function getPagingGlobal(obj,showjuli,isFirstPage){
	
	
	if(!window['pageLoadedSuccess']){ return false;}
	window['pageLoadedSuccess'] = false;
	keyvalues = $.extend({'p':'1'},keyvalues,obj);

	//console.log(keyvalues);
	//console.log(obj);

	var iPage = keyvalues['p'];
	if(url_obj['p'] !== '' && typeof url_obj['p'] !== 'undefined'){
		iPage = parseInt(url_obj['p']);
		if(iPage === 1 || !!isNaN(iPage)){url_obj['p'] = '';}
	}
	if(typeof isFirstPage == 'undefined'){
		keyvalues['p'] = iPage; 
	}else if(isFirstPage == '2'){
		url_obj['p'] = '';
		keyvalues['p'] = '1'; 
	}else if(isFirstPage == '0'){
		keyvalues['p'] = parseInt(keyvalues['p'])+1; 
	}else{}
	if(keyvalues['p'] == '1'){
		$('#returnFirstPage1').hide();
		$('#returnFirstPage').hide();
	}
	
	
	var url ='?jsoncallback=?';
	//alert(url_obj);
	//alert(urlLoaddata);
	
	//console.log(keyvalues);
	
	$.getJSON(urlLoaddata,keyvalues,function(data){
		
		//console.log(data);
		//console.log(data[0]);
		//alert(data[0].islogin);
		
		if(data[0].islogin == '1'){
			//console.log(1);
			function showNoMore(){
				window['ifNoMore'] = true;
				lis = document.createElement('li');
				lis.innerText = '没有更多了';
				lis.className = 'noMore';
				lis.id = 'noMore';
				$('#pagingList').append(lis);
				if(data[0].PageCount !== '0' && data[0].PageCount !== '1'){
					$('#returnFirstPage1').show();
					$('#returnFirstPage').show();
				}
			}
			window['ifTieziDetailLoadding'] = false;
			window['pageLoadedSuccess'] = true;
			//console.log(2);
			
			$('#pullUp').hide();
			$('#pageLoader').hide();
			
			if(keyvalues['p'] > data[0].PageCount){
				keyvalues['p'] = data[0].PageCount;
				showNoMore();
				return false;
			}
			if(typeof url_obj['p'] !== 'undefined' && url_obj['p'] !== ''){
				if(keyvalues["p"] == data[0].PageCount || data[0].PageCount == '1'){
					showNoMore();
					return false;
				}
			}
			$('#pagingList').append(data[0].MSG).show();
			$('#pageNavigation').html(data[0].PageSplit);
			setTimeout(function(){
				lazyImg('#pagingList',false);
				if($.cookie('myJYsid') !== undefined){
					$('#item'+$.cookie('myJYsid'))[0] && $("html,body").scrollTo( $('#item'+$.cookie('myJYsid')), { duration:500, axis:'y', offset:-150, onAfter:function(){}});
				}
				$.removeCookie('myJYsid',{ path:'/'});
			},100);
			if(keyvalues["p"] == data[0].PageCount || data[0].PageCount == '1'){
				showNoMore();
			}
			if(keyvalues["p"] != '1' && data[0].PageCount !== '0' && data[0].PageCount !== '1'){
				$('#returnFirstPage1').show();
				$('#returnFirstPage').show();
			}
			history.pushState(null, '', '?p='+keyvalues['p']);
		}
	}).error(function(){});
	return false;
}
function setCookieID(sid){
	$.cookie('myJYsid',sid,{path:'/',expires:10});
	return true;
}
function returnFirstpage(o){
	window['ifNoMore'] = false;
	$('#returnFirstPage').hide();
	$('#returnFirstPage1').hide();
	$('#pagingList').empty();
	getPagingGlobal({},true,'2');
	return false;
}
function lazyImg(selector){
	var w_h = $(window).height();
	$(selector).find('img').each(function(){
		if($(this).attr("data-ifshow") === '0' && ($(document).scrollTop()+w_h) > $(this).offset().top){
			$(this).attr({'src':$(this).attr('data-src'),"data-ifshow":'1'})
		}
	});
}
function clickLink(o,name,val,isSubmit){
	$('#'+name).val(val);
	$('.item_'+name+'_'+val).siblings('.cur').removeClass('cur');
	$('.item_'+name+'_'+val).addClass('cur');
	if(!!isSubmit){
		$('#str_'+name+'_node').html($(o).html());
		checkMyForm();
	}
	return false;
}
$.fn.form_filter = function(){
	var list = $(this).find('.select');
	list.each(function(){
		var t=$(this),name=t.attr('data-name'),sel = $('#'+name),sel_list = sel.find('option'),txt='',classes='';
		sel_list.each(function(){
			classes = $(this).prop('selected')?'cur':'';
			txt += '<li class="'+classes+' item_'+t.attr('data-name')+'_'+$(this).attr('value')+'"><a href="#" onClick="return clickLink(this,\''+t.attr('data-name')+'\',\''+$(this).attr('value')+'\',true);">'+$(this).text()+'</a></li>';
		});
		$('#'+t.attr('data-for')).append(txt);
	});
}
$.fn.form_filter2 = function(){
	var list = $(this).find('.select2');
	list.each(function(){
		var t=$(this),name=t.attr('data-name'),sel = $('#'+name),sel_list = sel.find('option'),txt='',classes='';
		sel_list.each(function(){
			classes = $(this).prop('selected')?'cur':'';
			txt += '<span class="'+classes+' item_'+t.attr('data-name')+'_'+$(this).attr('value')+'"><a href="#" onClick="return clickLink(this,\''+t.attr('data-name')+'\',\''+$(this).attr('value')+'\',false);">'+$(this).text()+'</a></span>';
		});
		$('#'+t.attr('data-for')).append(txt);
	});
}
$.fn.orderbyList = function(){
	var t = $(this),list = t.find('li'),links = t.find('a');
	links.click(function(e){
		e.preventDefault();
		window['ifNoMore'] = false;
		$('#pagingList').empty();
		getPagingGlobal({'orderby':$(this).attr('data-orderby'),'p':'1'},true);
		list.removeClass('select');
		$(this).parent().addClass('select');
	});
}


$.fn.serializeJson=function(){
	var serializeObj={};
	var array=this.serializeArray();
	var str=this.serialize();
	$(array).each(function(){
		if(serializeObj[this.name]){
			if($.isArray(serializeObj[this.name])){
				serializeObj[this.name].push(this.value);
			}else{
				serializeObj[this.name]=[serializeObj[this.name],this.value];
			}
		}else{
			serializeObj[this.name]=this.value;    
		}
	});
	return serializeObj;
};



function checkMyForm(){	
	window['ifNoMore'] = false;
	$('#pagingList').empty();
	var param = $("#form_filter").serializeJson();
	
	console.log(param);
	keyvalues = $.extend(keyvalues,param);
	getPagingGlobal({},true);
	$('#fullbg').trigger('click');
	return false;
}


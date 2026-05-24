function submitSearch(){
	filterClick(this,{keywords:($('#s_keyword').val())},'','');
	$('#s_keyword').trigger('blur');
	return false;
}
function filterClick(o,obj,txt_id,name){
	var i_pageidname = 'pageid';
	if(typeof window['pageidname'] !== 'undefined'){
		i_pageidname = window['pageidname'];
	}
	var obj2 = {};
	obj2[i_pageidname] = '1';
	window['keyvalues'] = $.extend(window['keyvalues'],obj,obj2);
	$('#returnFirstPage1').hide();
	mylist.getNewData();
	if(typeof txt_id === 'undefined')return;
	var t_o = $('#'+$(o).attr('id'));
	if($(o).attr('data-isall')==='1'){
		var filter2 = $('#filter2'),tabs = filter2.find('.tab .item').eq(0);
		filter2.find('.inner').eq(0).find('.cur').removeClass('cur');
		tabs.eq(0).removeAttr('data-hasbigid');
	}else{
		if(t_o.attr('data-double')==='1'){
			t_o.parent().parent().parent().parent().find('.cur').removeClass('cur');
		}else{
			t_o.parent().parent().parent().find('.cur').removeClass('cur');
		}
		t_o.parent().addClass('cur');
	}
	$('#'+txt_id).html(name);
	$('#fullbg').trigger('click');
	return false;
}
function filterClick2(o,obj){
	//mylist.styletype = obj.style;
	var style_txt='';
	if(obj.style==='1'){
		style_txt = '双列显示'
	}else if(obj.style==='2'){
		style_txt = '单列显示'	
	}else{
		style_txt = '横版显示'
	}
	changeStyle({style:obj.style},'s_txt_style',style_txt);
	
	
	var i_pageidname = 'pageid';
	if(typeof window['pageidname'] !== 'undefined'){
		i_pageidname = window['pageidname'];
	}
	var obj2 = {};
	obj2[i_pageidname] = '1';
	window['keyvalues'] = $.extend(window['keyvalues'],obj,obj2);
	$('#returnFirstPage1').hide();
	mylist.getNewData();
	$(o).parent().parent().find('.cur').removeClass('cur');
	$(o).parent().addClass('cur');
	var my2 = $('#s_ClassId_'+obj.ClassId);
	if(obj.ClassId==='0' || my2.html() === ''){
		$('#smallCatList').parent().hide();
		$('#inner_container').css('top','81px')
		return false;
	}
	$('#inner_container').css('top','125px')
	$('#smallCatList').html(my2.html()).parent().show();
	$('#smallCatList').find('li').eq(0).addClass('cur');
	return false;
}
function filterClick3(o,obj){
	var i_pageidname = 'pageid';
	if(typeof window['pageidname'] !== 'undefined'){
		i_pageidname = window['pageidname'];
	}
	var obj2 = {};
	obj2[i_pageidname] = '1';
	window['keyvalues'] = $.extend(window['keyvalues'],obj,obj2);
	$('#returnFirstPage1').hide();
	mylist.getNewData();
	$(o).parent().find('.cur').removeClass('cur');
	$(o).addClass('cur');
	return false;
}

if(typeof mixinItem === 'undefined'){mixinItem = {}}
var Item = {
	mixins: [mixinItem],
	components: {},
	props: ['item','index','manid'],
	methods: {
		setCookieID:function(sid){
			$.cookie(window['cookieName'],sid,{path:'/',expires:10});
			return true;
		}
	},
	template: '#page-template'
};
if(typeof mixinMylist === 'undefined'){mixinMylist = {}}
var mylist = new Vue({
	el: '#components-demo',
	mixins: [mixinMylist],
	components: {
		'item': Item
	},
	data:function(){
    	return {
			ifFixedFilter2:false,
			ifLoadding:false,
			ifNoMore:false,
			displayedItems: [],
			CurrentPage:1,
			TotalPage: 0,
			TotalRecord:0
		}
	},
	methods: {
		getNext:function(){
			var that = this,i_pageidname = 'pageid';
			if(typeof window['pageidname'] !== 'undefined'){
				i_pageidname = window['pageidname'];
			}
			window['keyvalues'][i_pageidname] = parseInt(window['keyvalues'][i_pageidname])+1
			that.CurrentPage = parseInt(window['keyvalues'][i_pageidname]);
			that.getData();
		},
		getNewData:function(){
			var that = this;
			that.displayedItems.splice(0); 
			that.ifNoMore = false;
			that.getData();
		},
		getData: function () {
			var that = this,i_pageidname = 'pageid';
			if(typeof window['pageidname'] !== 'undefined'){
				i_pageidname = window['pageidname'];
			}
			that.CurrentPage = parseInt(window['keyvalues'][i_pageidname]);
			that.ifLoadding = true;
			axios.get(window['apiurl'],{params:window['keyvalues']}).then(function(res){
				if(typeof res.data.iserror !== 'undefined' && res.data.iserror === 1){
					that.ifNoMore = true;
					that.ifLoadding = false;
					return;
				}
				
				var pushState_txt = '?';
				for(var key in window['keyvalues']){
					pushState_txt += key+'='+window['keyvalues'][key]+'&';
				}
				history.replaceState(null, '', pushState_txt);
				var Data = res.data.Data,i=0;
				that.TotalPage = parseInt(res.data.TotalPage)
				that.TotalRecord = parseInt(res.data.TotalRecord);
				if(that.TotalPage===0){
					that.ifNoMore = true;
					that.ifLoadding = false;
					return false;
				}
				
				for(i=0;i<Data.length;i++){
					Data[i].page_p = that.CurrentPage;
				}
				if(typeof that.mapCurrentData !== 'undefined'){
					Data = that.mapCurrentData(Data);//处理数据
				}
				
				Data.length>0 ?(that.displayedItems = that.displayedItems.concat(Data)):'';
				that.ifLoadding = false;
				(that.TotalPage===0) && (that.ifNoMore = true);
				(that.TotalPage===that.CurrentPage || that.TotalPage<that.CurrentPage) && (that.ifNoMore = true,that.CurrentPage=that.TotalPage);
				//如果不是第一页
				if(that.CurrentPage!==1){
					$('#returnFirstPage1')[0]&&$('#returnFirstPage1').show();
				}
				setTimeout(function(){
					if($.cookie(window['cookieName']) !== undefined){
						$('#item'+$.cookie(window['cookieName']))[0] && $("html,body").scrollTo( $('#item'+$.cookie(window['cookieName'])), { duration:500, axis:'y', offset:-150, onAfter:function(){}});
					}
					$.removeCookie(window['cookieName'],{ path:'/'});
				},100);
			}).catch(function(err){that.ifLoadding = false;alert('抱歉，加载失败了===！');console.log(err);});
		}
	},
	mounted: function(){
		var that = this;
		
		if(typeof window['OnreadyNoGetData'] === 'undefined'){
			that.getData();
		}
		var header_height = 45;
		w_h = $(window).height(),
		filter2 = $('#filter2'),f_top = 0;
		if(!!filter2[0]){
			f_top = filter2.offset().top;
		}
		$(window).bind("scroll",function(){
			var st = $(document).scrollTop();
			var d_h = $(document).height()
			var d = st+w_h;
			if(!!filter2[0]){
				if(st+header_height>f_top){
					if(!that.ifFixedFilter2){
						filter2.addClass('fixedtop');
						that.ifFixedFilter2 = true;
					}
				}else{
					if(!!that.ifFixedFilter2){
						filter2.removeClass('fixedtop');
						that.ifFixedFilter2 = false;
					}
				}
			}
			if((d>d_h-100 || d==d_h) && !that.ifLoadding && !that.ifNoMore){
				
				$('body')[0].scrollTop = $('body')[0].scrollHeight;
				that.getNext();
			}
		});
	},
	created: function () { }
});
function returnFirstpage(){
	var i_pageidname = 'pageid';
	if(typeof window['pageidname'] !== 'undefined'){
		i_pageidname = window['pageidname'];
	}
	window['keyvalues'][i_pageidname] = '1';
	mylist&&mylist.getNewData();
	$('#returnFirstPage1').hide();
}
$.fn.imagesLoaded=function(callback){var $this=$(this),$images=$this.find('img').add($this.filter('img')),len=$images.length,blank='data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==';function triggerCallback(){callback.call($this,$images)}function imgLoaded(event){if(--len<=0&&event.target.src!==blank){setTimeout(triggerCallback);$images.unbind('load error',imgLoaded)}}if(!len){triggerCallback()}$images.bind('load error',imgLoaded).each(function(){if(this.complete||typeof this.complete==="undefined"){var src=this.src;this.src=blank;this.src=src}});return $this};
$.fn.animationEnd=function(a){
	var t=this,d=["webkitAnimationEnd","OAnimationEnd","MSAnimationEnd","animationend"];
	if(a){
		for(var i=0;i<d.length;i++){
			t.on(d[i],a);
		}
	}
	return this;
}
;(function(){
	var isTouch = "ontouchend" in document.createElement("div"), 
	tstart = isTouch ? "touchstart" : "mousedown",
	tmove = isTouch ? "touchmove" : "mousemove",
	tend = isTouch ? "touchend" : "mouseup";
	$(document).on(tstart, function(e) {
		if(!$(e.target).hasClass("disable")) $(e.target).data("isMoved", 0);
	});
	$(document).on(tmove, function(e) {
		if(!$(e.target).hasClass("disable")) $(e.target).data("isMoved", 1);
	});
	$(document).on(tend, function(e) {
		if(!$(e.target).hasClass("disable") && $(e.target).data("isMoved") == 0) $(e.target).trigger("tap");
	});
	
	$(document).ready(function(){
		var header_back = $('#header_back');
		header_back.click(function(e){
			e.preventDefault();
			if(header_back.attr('href')!=='#'){window.location.href = header_back.attr('href');return false;}
			if(!!header_back.attr('onclick')&&header_back.attr('onclick')!==''){return false;}
			if(history.length>1){
				window.history.go(-1);
			}else{
				if(window.location.href.indexOf('house')!==-1){
					window.location.href = window['SiteUrl']+'house/index';
				}else{
					window.location.href = window['SiteUrl'];
				}
			}
		});
	});
})(jQuery);
function setCookie_p(pageid){
	$.cookie(window['cookieNameP'],pageid,{path:'/',expires:10});
	return true;
}
$.fn.tap = function(fn){ 
	var collection = this, 
	isTouch = "ontouchend" in document.createElement("div"), 
	tstart = isTouch ? "touchstart" : "mousedown",
	tmove = isTouch ? "touchmove" : "mousemove",
	tend = isTouch ? "touchend" : "mouseup",
	tcancel = isTouch ? "touchcancel" : "mouseout";
	collection.each(function(){
		var i = {};
		i.target = this;
		$(i.target).on('click',function(e){e.preventDefault();});
		$(i.target).on(tstart,function(e){
			var p = "touches" in e ? e.touches[0] : (isTouch ? window.event.touches[0] : window.event);
			i.startX = p.clientX;
			i.startY = p.clientY;
			i.endX = p.clientX;
			i.endY = p.clientY;
			i.startTime = + new Date;
		});
		$(i.target).on(tmove,function(e){
			var p = "touches" in e ? e.touches[0] : (isTouch ? window.event.touches[0] : window.event);
			i.endX = p.clientX;
			i.endY = p.clientY;
		});
		$(i.target).on(tend,function(e){
			if((+ new Date)-i.startTime<300){
				if(Math.abs(i.endX-i.startX)+Math.abs(i.endY-i.startY)<20){
					var e = e || window.event;
					e.preventDefault();
					fn.call(i.target);
				}
				i.startTime = undefined;
				i.startX = undefined;
				i.startY = undefined;
				i.endX = undefined;
				i.endY = undefined;
			}
		});
	});
	return collection;
}
function formatNumber(n) {
  n = n.toString()
  return n[1] ? n : '0' + n
}
function formatTime(date) {
  var year = date.getFullYear()
  var month = date.getMonth() + 1
  var day = date.getDate()
  var hour = date.getHours()
  var minute = date.getMinutes()
  var second = date.getSeconds()
 // return [year, month, day].map(formatNumber).join('-') + ' ' + [hour, minute, second].map(formatNumber).join(':');
 return formatNumber( year ) + '-'+ formatNumber( month ) +'-'+ formatNumber( day ) + ' ' + formatNumber( hour ) + ':'+ formatNumber( minute ) +':'+ formatNumber( second ) ;
}
function formatTime_s(date) {
  var year = date.getFullYear()
  var month = date.getMonth() + 1
  var day = date.getDate()
  var hour = date.getHours()
  var minute = date.getMinutes()
  var second = date.getSeconds()
  //return [year, month, day].map(formatNumber).join('-');
  return formatNumber( year ) + '-'+ formatNumber( month ) +'-'+ formatNumber(  day )  ;
}

function formatTime_cn(date) {
  var year = date.getFullYear()
  var month = date.getMonth() + 1
  var day = date.getDate()
  var hour = date.getHours()
  var minute = date.getMinutes()
  var second = date.getSeconds()
  return formatNumber( year ) + '年'+ formatNumber( month ) +'月'+ formatNumber(  day ) +'日' ;
}

function formatTime_short(date) {
	var month = date.getMonth() + 1 ;
	var day = date.getDate() ;
	var hour = date.getHours() ;
	var minute = date.getMinutes() ;
	var second = date.getSeconds() ;
	return formatNumber( month ) +'-'+ formatNumber( day )  ;
}
function formatTime_diff(timestamp) {
    // 补全为13位
    var arrTimestamp = (timestamp + '').split('');
    for (var start = 0; start < 13; start++) {
        if (!arrTimestamp[start]) {
            arrTimestamp[start] = '0';
        }
    }
    timestamp = arrTimestamp.join('') * 1;
    var minute = 1000 * 60;
    var hour = minute * 60;
    var day = hour * 24;
    var halfamonth = day * 15;
    var month = day * 30;
    var now = new Date().getTime();
	if(!!window['system_datetime']){
		
		now = new Date(window['system_datetime']).getTime();
		
	}
    var diffValue = now - timestamp;
    // 如果本地时间反而小于变量时间
    if (diffValue < 0) {
        return '不久前';
    }
    // 计算差异时间的量级
    var monthC = diffValue / month;
    var weekC = diffValue / (7 * day);
    var dayC = diffValue / day;
    var hourC = diffValue / hour;
    var minC = diffValue / minute;
    // 数值补0方法
    var zero = function (value) {
        if (value < 10) {
            return '0' + value;
        }
        return value;
    };
    // 使用
    if (monthC > 12) {
        // 超过1年，直接显示年月日
        return (function () {
            var date = new Date(timestamp);
            return date.getFullYear() + '年' + zero(date.getMonth() + 1) + '月' + zero(date.getDate()) + '日';
        })();
    } else if (monthC >= 1) {
        return parseInt(monthC) + "月前";
    } else if (weekC >= 1) {
        return parseInt(weekC) + "周前";
    } else if (dayC >= 1) {
        return parseInt(dayC) + "天前";
    } else if (hourC >= 1) {
        return parseInt(hourC) + "小时前";
    } else if (minC >= 1) {
        return parseInt(minC) + "分钟前";
    }
    return '刚刚';
}
Date.prototype.Format = function (fmt) { 
	var o = {
		"M+": this.getMonth() + 1, //月份 
		"d+": this.getDate(), //日 
		"h+": this.getHours(), //小时 
		"m+": this.getMinutes(), //分 
		"s+": this.getSeconds(), //秒 
		"q+": Math.floor((this.getMonth() + 3) / 3), //季度 
		"S": this.getMilliseconds() //毫秒 
	};
	if (/(y+)/.test(fmt)) fmt = fmt.replace(RegExp.$1, (this.getFullYear() + "").substr(4 - RegExp.$1.length));
	for (var k in o)
	if (new RegExp("(" + k + ")").test(fmt)) fmt = fmt.replace(RegExp.$1, (RegExp.$1.length == 1) ? (o[k]) : (("00" + o[k]).substr(("" + o[k]).length)));
	return fmt;
}
function changeTwoDecimal(x){//保留两位小数，四舍五入
	var f_x = parseFloat(x);
	if (isNaN(f_x)){
		console.info('function:changeTwoDecimal->parameter error');
		return false;
	}
	if(f_x.toString().lastIndexOf('.')!==-1){
		f_x=f_x.toFixed(2);
	}
	return f_x;
}
function changeTwoDecimal2(x){//保留两位小数，不四舍五入
	var f_x = parseFloat(x);
	if (isNaN(f_x)){
		console.info('function:changeTwoDecimal->parameter error');
		return false;
	}
	f_x=f_x.toFixed(3);
	f_x = f_x.substring(0,f_x.lastIndexOf('.')+3);
	return f_x;
}
function fixedWanQianNum(num){
	var result = 0;
	if(num===''||num===0){
		return result;
	}
	if(num>9999){
		result = num/10000;
		if(result.toString().lastIndexOf('.')!==-1){
			result=result.toFixed(1);
		}
		result = result.toString() + '万';
	}else if(num>999){
		result = num/1000;
		if(result.toString().lastIndexOf('.')!==-1){
			result=result.toFixed(1);
		}
		result = result.toString() + '千';
	}else{
		result = num.toString();
	}
	return result;
}
function TransparentHeader(){//头部透明，下拉后变非透明
	var header = $('#header'),header_height = header.height();
	header.addClass('header_transparent');
	$('body').eq(0).css({'padding-top':'0'});
	$(window).on('scroll', function() {
		var scrollH = $(this).scrollTop();
		if (scrollH > header_height){
			header.removeClass('header_transparent');
		}else{
        	header.addClass('header_transparent');
		}
	});
}
function is_weixn(){//是否在微信客户端浏览器中打开
    var ua = navigator.userAgent.toLowerCase();
    if(ua.match(/MicroMessenger/i)=="micromessenger") {
        return true;
    } else {
        return false;
    }
}
function isIOS(){
	var Na=window.navigator.userAgent.toLowerCase(),q={},Hd;
	q.isChrome=function(){return-1<Na.indexOf('chrome')||-1<Na.indexOf('CrMo')};
	q.isDesktopSafari=function(){return!q.isIOS()&&-1!==Na.search('safari')};
	var lb;-1<Na.indexOf('iphone')?
	lb='iphone':-1<Na.indexOf('ipad')?lb='ipad':-1<Na.indexOf('android')&&q.isChrome()?lb='chromeandroid':-1<Na.indexOf('android')||-1<Na.indexOf('htc_evo3d')?lb='android':-1<Na.indexOf('playbook')?lb='playbook':-1<Na.indexOf('ipod')?lb='ipod':-1<navigator.platform.indexOf('Win')&&(lb='windows',(Hd=/msapphost\/(\d+\.\d+)/i.exec(Na))&&(Hd=parseFloat(Hd[1])));lb||(lb='unknown');
	q.type=lb;
	return'iphone'===q.type||'ipad'===q.type||'ipod'===q.type;
}
function set_i_weixinapp_share(shareTitle,shareContent,shareImg,shareLink){
	var url = nowdomain+'request.ashx?action=weixinfx&jsoncallback=?&url='+encodeURIComponent(window.location.href);
	$.getJSON(url,function(data){
		if(data[0].islogin !== '1'){
			//没有开启微信分享或配置有误
			console.info('没有开启微信分享或配置有误');
		}else{
			wx.config({
				debug: false, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
				appId: data[0].MSG.appid, // 必填，公众号的唯一标识
				timestamp: parseInt(data[0].MSG.timestamp), // 必填，生成签名的时间戳
				nonceStr: data[0].MSG.noncestr, // 必填，生成签名的随机串
				signature: data[0].MSG.signature,// 必填，签名，见附录1
				jsApiList: ['onMenuShareTimeline','onMenuShareAppMessage','onMenuShareQQ','getLocation','openLocation','startRecord','stopRecord','playVoice','pauseVoice','stopVoice','onVoicePlayEnd','uploadVoice','translateVoice','chooseImage','uploadImage','getLocalImgData','downloadImage'] // 必填，需要使用的JS接口列表，所有JS接口列表见附录2
			});
			wx.ready(function(){
				wx.onMenuShareAppMessage({
					title: shareTitle, // 分享标题
					desc: shareContent, // 分享描述
					link: shareLink, // 分享链接
					imgUrl: shareImg, // 分享图标
					type: '', // 分享类型,music、video或link，不填默认为link
					dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空
					success: function(){},
					cancel: function(){}
				});
				wx.onMenuShareQQ({
					title: shareTitle, // 分享标题
					desc: shareContent, // 分享描述
					link: shareLink, // 分享链接
					imgUrl: shareImg, // 分享图标
					success: function(){},
					cancel: function(){}
				});
				wx.onMenuShareTimeline({
					title: shareTitle, // 分享标题
					link: shareLink, // 分享链接
					imgUrl: shareImg, // 分享图标
					success: function(){},
					cancel: function(){}
				});
				//gps导航
				if(typeof window['WX_GPS_DAOHANG'] !== 'undefined'){
					var chraddress_str = $('#chraddress_str').html();
					var chrtitle_str = $('#chrtitle_str').html();
					$('#daohang_gps,#daohang_gps2').click(function(e){
						e.preventDefault();
						var x = $(this).attr('data-x'),y = $(this).attr('data-y');
						wxopenLocation(x,y,chraddress_str,chrtitle_str);
					});
				}
				if(typeof window['WX_GPS_DAOHANG_PEISON'] !== 'undefined'){
					$('#peisong_order_list').on('click','.daohang_gps',function(e){
						e.preventDefault();
						var t = $(this);
						var x = t.attr('data-x'),y = t.attr('data-y');
						var chraddress_str = t.parent().parent().find('.chraddress_str').html();
						var chrtitle_str = t.parent().parent().find('.chrtitle_str').html();
						wxopenLocation(x,y,chraddress_str,chrtitle_str);
					});
					
				}
			});
		}
	});
}
function wxopenLocation(shop_x,shop_y,chraddress,chrtitle){
    //转换百度坐标为腾讯坐标
    qq.maps.convertor.translate(new qq.maps.LatLng(shop_y,shop_x), 3, function(res){
        latlng = res[0];
        wx.openLocation({
            latitude: latlng.lat, // 纬度，浮点数，范围为90 ~ -90
            longitude: latlng.lng, // 经度，浮点数，范围为180 ~ -180。
            name: chrtitle, // 位置名
            address: chraddress, // 地址详情说明
            scale: 28, // 地图缩放级别,整形值,范围从1~28。默认为最大
            infoUrl: '' // 在查看位置界面底部显示的超链接,可点击跳转
        });
    });
}
$.fn.share2015 = function(){
	new nativeShare('nativeShare',shareconfig);
	
	var t = $(this),node = $('#bdsharebuttonbox');
	
	if(!!is_weixn()){set_i_weixinapp_share(shareTitle,shareContent,shareImg,shareLink);}
	node.find('.cancal').click(function(e){
		e.preventDefault();
		node.slideUp();
		$('#mask').hide();
	});
	t.click(function(e){
		e.preventDefault();
		if(isapp === '1'){
			YDB.Share(shareTitle,shareContent,shareImg,shareLink);
		}else{
			node.slideDown();
			$('#mask').show();
		}
	});
}
function windowToShow(action_name,to_url,ok_url){//从微信客户端中跳出//微信客户端中的支付宝问题
	var html = '<s class="s s1"></s><s class="s s2"></s>'+
	'<div class="txt">请在菜单中选择在浏览器中打开，<br>以完成'+action_name+'。</div>'+
	'<a href="'+ok_url+'" class="btn">已完成'+action_name+'</a>　<a href="'+ok_url+'" class="btn">取消并返回</a>';
	var divs = document.createElement('div');
	divs.id = 'alipayForSafari';
	divs.className = 'alipay_for_safari';
	divs.innerHTML = html;
	$('body').append(divs);
	$('#alipayForSafari').show();
	history.pushState(null, '', to_url);
}
function MSGwindowShow_JSON(data){
	
	if(data.action === "pay" && data.classid === "2" ){
		payAppsubmitGo(data);
	}else{
		if(data.islogin === '1'){
			$('#isrep').val('');
			$('#parentid').val('');
			$('#cmt_txt').html('');
			$('#chrcontent').html('');
			$('#chrcontent2').val('');
			$('#closeReply').trigger('click');
			if(data.isopen === '0'){
				MSGwindowShow('revert','0','恭喜你，回复成功！请耐心等待系统审核！','','');
			}else{
				successPostRevert(data);
			}
		}else{
			MSGwindowShow('revert','0',data.error,'','');
		}
	}
}
function payAppsubmitGo(MSGkeyval){
	if(isapp ==="1"){
		var	YDB = new YDBOBJ();
		if(MSGkeyval.Payid === "7" ){//拉起微信app支付
			YDB.SetWxpaySend(MSGkeyval.appid, MSGkeyval.partnerid, MSGkeyval.prepayid, MSGkeyval.package_, MSGkeyval.noncestr, MSGkeyval.timestamp, MSGkeyval.sign, MSGkeyval.return_url, MSGkeyval.attach);
		}
		else if(MSGkeyval.Payid === "1" ){//拉起阿里app支付
			
		}
	}
}
function windowlocationhref(url){
	if(url.length > 5){window.location.href=url;}
}
function MSGwindowShow(action,showid,str,url,formcode,i_option){
	if(!!$('#form_submit_disabled')[0]){
		$('#form_submit_disabled').removeClass('disabled').prop('disabled',false);
	}
	var sys_tips = '<div class="sys_tips" id="sys_tips" style="display:none;"><div class="inner" id="sys_tips_inner">'+
		'<div class="ico" id="sys_tips_ico"></div>'+
		'<div class="bd">'+
			'<div id="sys_tips_info" class="txt1"></div>'+
			'<div id="sys_tips_info2" class="txt2"></div>'+
			'<div class="btn"><a href="#" class="btn2" id="sys_tips_submit">确定</a></div>'+
		'</div>'+
		'<div class="close_btn" id="sys_tips_close" style="display:none;"></div>'+
	'</div></div>';
	if(!$('#sys_tips')[0]){
		$('body').append(sys_tips);
	}
	var sys_tips = $('#sys_tips'),sys_tips_inner = sys_tips.find('.inner'),sys_tips_ico = $('#sys_tips_ico'),sys_tips_info = $('#sys_tips_info'),sys_tips_info2 = $('#sys_tips_info2'),sys_tips_submit = $('#sys_tips_submit'),sys_tips_close = $('#sys_tips_close');
	
	if(typeof i_option !== 'undefined'){
		sys_tips_submit.html(i_option.btntxt);
		if(!!i_option.isCloseBtn){
			sys_tips_close.show().click(function(){
				sys_tips.hide();
			});
		}
		sys_tips_ico.addClass(i_option.icotype);
		sys_tips_submit.addClass(i_option.btntype);
		sys_tips_info2.html(i_option.bd2txt);
	}
	if(action === "pay"){
		$('#have_login').hide();
		if(showid=="2"){
			document.getElementById('formcode').value=formcode;//赋值code
			document.forms['submitpay'].submit();//提交支付
			//这里添加支付中信息提示窗口
		}else if(showid=="1"){
			showConsole('恭喜您！',!0);
		}else if(showid=="0"){
			alert(str);
			if(url.length > 5){window.location.href=url;}
		}else{
			alert(str);
		}
		document.getElementById('formcode').value="payok";//设置默认值防止二次提交
	}else if(action === "jifen"){
		if(typeof formcode !== 'undefined' && formcode!=='0' && formcode!==0){
			str = str + '，增加'+formcode +window['jifenneme']+'！';
		}
		showConsole('提示',true);
	}else if(action === "jiaoyou_buy"){
		if(typeof getPageInfo !== 'undefined'){
			getPageInfo();
		}
	}else if(action === "jiaoyou_keep"){
		$('#keeptxt').html('已收藏');
		$('#keepnum').html(formcode);
	}else if(action === "jiaoyou_flowers"){
		$('#flowernum').html(formcode);
		$('#flower').find('.closes').trigger('click');
		showConsole('提示',false);
	}else{
		if(showid=="7"){ //不提示，成功并调用上层页面的对应方法
			if(typeof MSGwindowShow_curPage !== 'undefined'){MSGwindowShow_curPage.call(this,url);}
			return false;
		}
		if(showid=="0"){ //只提示不跳转
			showConsole('提示',false);
		}else if(showid=="1"){ //提示加跳转
			showConsole('提示',true);
		}else if(showid=="2"){ //直接跳转
			windowlocationhref(url);
		}else if(showid=="3"){ //错误信息加跳转
			showConsole('出错了',true);
		}else if(showid=="4"){ //错误信息加只提示不跳转
			showConsole('出错了',false);
			$('#submit_1')[0]&&$('#submit_1').removeClass('disabled').prop('disabled',false);
			if(typeof getCode !== 'undefined'){getCode();}//刷新一次验证码
		}else if(showid=="5"){ //成功并由页面刷上层页面
			showConsole('提示',false);
		}else if(showid=="6"){ //成功并调用上层页面的对应方法
			showConsole('提示',false);
		}else if(showid=="8"){ //成功并调用上层页面的对应方法
			showConsole('提示',false);
		}else{
			return false;
		}
	}
	function showConsole(tit,isredirect){
		sys_tips_info.html(str);
		sys_tips_submit.unbind('click');
		sys_tips_submit.bind('click',function(e){
			e.preventDefault();
			sys_tips.hide();
			isredirect&&windowlocationhref(url);
			if(showid === '5'){
				window.parent.location.href=window.parent.location.href;
			}
			if(showid === '6'){
				if(typeof MSGwindowShow_curPage !== 'undefined'){MSGwindowShow_curPage.call(this);}
			}
			if(showid === '8'){
				if(typeof MSGwindowShow_curPage2 !== 'undefined'){MSGwindowShow_curPage2.call(this);}
			}
		});
		sys_tips.show();
		//var w_h = $(window).height(),d_h = sys_tips.height(),s_h = $(document).scrollTop(),top_val = (w_h-d_h)/2;
		//sys_tips.css({'top':top_val+'px'});
		var d_h = sys_tips_inner.height(),top_val = parseInt(d_h/2);
		sys_tips_inner.css({'margin-top':-top_val+'px'});
	}
}
//地图测距
window['GPSpoint'] = null;
function reloadLocation(callback){//切换地址 恢复当前位置
	var geolocation = new BMap.Geolocation();
	geolocation.getCurrentPosition(function(r){
		if(this.getStatus() == BMAP_STATUS_SUCCESS){
			//alert('您的位置：'+r.point.lng+','+r.point.lat);
			callback&&callback.call(this,r.point);
		}
		else {
			if(typeof keyvalues !== 'undefined'){getPagingGlobal();}
			MSGwindowShow('location','0','抱歉，我们没有获取到您的位置信息','','');
		}        
	},{enableHighAccuracy: true});
}
function getLocation(callback){
	var geolocation = new BMap.Geolocation();
	geolocation.getCurrentPosition(function(r){
		if(this.getStatus() == BMAP_STATUS_SUCCESS){
			callback&&callback.call(this,r.point);
			
			var date = new Date();
			date.setTime(date.getTime()+(300*1000));//只能这么写，10表示10秒钟
			$.cookie('myPoint',r.point.lng+','+r.point.lat,{path:'/',expires:date});
			window['GPSpoint'] = r.point;
		}
		else {
			if(typeof keyvalues !== 'undefined'){getPagingGlobal();}
			MSGwindowShow('location','0','抱歉，我们没有获取到您的位置信息','','');
		}        
	},{enableHighAccuracy: true});
}
function showMapGPSaddDizhi(point){
	var geoc = new BMap.Geocoder();
	geoc.getLocation(point, function(rs){
		var addComp = rs.addressComponents;
		$('#s_address').val(addComp.district + addComp.street + addComp.streetNumber).attr('placeholder','通过地图标注或输入地址');
		$('#shop_x').val(point.lng);
		$('#shop_y').val(point.lat);
	});
}
function showMapGPSre(point){
	if(typeof showGPSLocation !== 'undefined'){
		showGPSLocation(point);
		window['GPSpoint'] = point;
	}
	var geoc = new BMap.Geocoder();
	geoc.getLocation(point, function(rs){
		var addComp = rs.addressComponents;
		$('#curLocation2').html(addComp.district + addComp.street + addComp.streetNumber);
	});
}
function showMapGPS(point){
	var map = window['Lmap'] || new BMap.Map();
	window['GPSpoint'] = point;
	setMap(map,point);
	if(typeof keyvalues !== 'undefined'){
		getPagingGlobal({"p":"1",'x':point.lng,'y':point.lat},true);
	}
}
function showMapBD(longitude, latitude){
	if(longitude===''){return;}
	var map = window['Lmap'] || new BMap.Map();
	var myPoint = new BMap.Point(longitude, latitude); //GPS坐标
	setMap(map,myPoint);
}
function setMap(map,point){
	var mapPointList = $('#mapPoint').find('.item');
	//逆向地址解析
	var geoc = new BMap.Geocoder();
	geoc.getLocation(point, function(rs){
		var addComp = rs.addressComponents;
		$('#curLocation').html(addComp.district + addComp.street + addComp.streetNumber);
	});
	if(typeof window['ifReloadNewGpsList'] !== 'undefined' && !!window['ifReloadNewGpsList']){
		//$('#pagingList').empty();
		window['ifReloadNewGpsList']=false;
		getPagingGlobal({"p":"1",'x':point.lng,'y':point.lat},true);
	}
	//列表距离
	mapPointList.each(function(){
		var mapPoint = $(this);
		var dataX = mapPoint.attr('data-x'),dataY = mapPoint.attr('data-y');
		if(dataX === '' || dataX ==='0'){return;}
		var pointB = new BMap.Point(dataX,dataY);  // 商家坐标
		var txt = '<span class="getDistance">'+(map.getDistance(point,pointB)/1000).toFixed(2)+'<\/span>km';
		$(this).find('.juli').html(txt);
	});
}

//filter
function showFilter(option){
	var node = $('#'+option.ibox),
		fullbg = $('#'+option.fullbg),
		ct1 = $('#'+option.content1),
		ct2 = $('#'+option.content2),
		ctp1 = ct1.find('.innercontent'),
		ctp2 = ct2.find('.innercontent'),
		currentClass = 'current';
	var tabs = node.find('.tab .item'),
		conts = node.find('.inner');
	//fullbg.css({'height':$(document).height()+'px'});
	
	var timelist = node.find('.inner > ul > li').filter(function(index) {
			return $('ul', this).length > 0;
		}),
		timelist_no = node.find('.inner > ul > li').filter(function(index) {
			return $('ul', this).length === 0;
		}),
		childUL = null;
	timelist.each(function(){
		var that = $(this);
		that.addClass('hasUL');
		that.children('a').addClass('hasUlLink');
	});
	timelist_no.each(function(){
		var that = $(this);
		that.addClass('noUL');
		that.children('a').addClass('noUlLink');
	});
	ct1.on("click",".noUlLink",function(e){
		if($(this).attr('data-ajax')==='1'){////////////////////////////////////////////////////////////////
			var index=0;
			if(!!$(this).parent().attr('index')){
				index = $(this).parent().attr('index')
			}
			tabs.eq(index).attr('data-hasbigid',$(this).parent().attr('categoryid'));
		}
	});
	ct1.on("click",".hasUlLink",function(e){
		e.preventDefault();
		var that = $(this).parent();
		if(!window['myScroll_inner']){
			window['myScroll_inner'] = new IScroll('#'+option.content2, {
				click: true,
				scrollX: false,
				scrollY: true,
				scrollbars: false,
				interactiveScrollbars: true,
				shrinkScrollbars: 'scale',
				fadeScrollbars: true
			});
		}
		if($(this).attr('data-ajax')==='1'){////////////////////////////////////////////////////////////////
			var index=0;
			if(!!$(this).parent().attr('index')){
				index = $(this).parent().attr('index')
			}
			tabs.eq(index).attr('data-hasbigid',$(this).parent().attr('categoryid'));
		}
		setTimeout(function(){
			ctp1.find('.hasUL_current').removeClass('hasUL_current');
			that.addClass('hasUL_current');
			ctp2.html('<ul>'+that.find('ul').html()+'</ul>').show();
			ct1.css({'width':'50%'});
			ct2.show();
			window['myScroll_inner'].refresh();
		},100);
	});
	tabs.each(function(i){
		$(this).bind("click",function(e){
			e.preventDefault();
			if($(this).attr('data-isopen')==='1'){
				hide_nav();
				return false;
			}
			tabs.attr('data-isopen','0');
			$(this).attr('data-isopen','1');
			if(!window['myScroll_parent']){
				window['myScroll_parent'] = new IScroll('#'+option.content1, {
					click: true,
					scrollX: false,
					scrollY: true,
					scrollbars: false,
					interactiveScrollbars: true,
					shrinkScrollbars: 'scale',
					fadeScrollbars: true
				});
			}
			node.addClass('filter-fixed');
			ctp1[0].innerHTML = conts.eq(i).html();
			fullbg.fadeIn('fast');
			tabs.removeClass(currentClass);
			tabs.eq(i).addClass(currentClass);
			if($(this).attr('data-hasbigid') !== undefined){
				var triggerEle = ct1.find('.hasUL[categoryid="'+$(this).attr('data-hasbigid')+'"]');
				ct1.css({'width':'50%'}).show();
				ct2.show();
				triggerEle.find('.hasUlLink').trigger('click');
				
				var triggerEle = ct1.find('.noUL[categoryid="'+$(this).attr('data-hasbigid')+'"]');
				if(!!triggerEle[0]){
					ct1.css({'width':'100%'}).show();
					ct2.hide();
					triggerEle.addClass('hasUL_current');
				}
				
			}else{
				ct2.hide();
				ct1.css('width','100%').show();
			}
			setTimeout(function(){
				window['myScroll_parent'].refresh();
			},100);
			if($(this).attr('data-more') === '1'){
				node.addClass('filter-fixed-btn');
			}else{
				node.removeClass('filter-fixed-btn');
			}
		});
	});
	fullbg.bind('click',function(e){
		e.preventDefault();
		hide_nav();
	});
	function hide_nav(){
		node.removeClass('filter-fixed').removeClass('filter-fixed-btn');
		fullbg.fadeOut('fast');
		timelist.removeClass('hasUL_current');
		tabs.removeClass(currentClass).attr('data-isopen','0');
		ct1.css('width','100%').hide();
		ct2.hide();
	}
	
	option.callback && option.callback.call(this);
}
//遮罩页
function showNewPage(tit,html,callback){
	var windowIframe = $('#windowIframe'),windowIframeTitle = $('#windowIframeTitle'),windowIframeBody = $('#windowIframeBody');
	function showBox(){
		windowIframe.show();
		//$('body').css({'height':$(window).height()+'px','overflow':'hidden'});
		$('.p_main').hide();
		$('.wrapper').hide();
	}
	function hideBox(){
		windowIframe.hide();
		//$('body').css({'height':'auto','overflow':'visible'});
		$('.wrapper').show();
		$('.p_main').show();
	}
	var addEditAddressInit = function(){
		if(windowIframe.attr('data-loaded') === '0'){
			var w_h = $(window).height();
			windowIframeTitle.html(tit);
			windowIframeBody.html(html);
			windowIframe.css({'min-height':w_h+'px'});
		}
		showBox();
		callback&&callback.call(this);
		
	};
	
	addEditAddressInit();
	windowIframe.on('click','.close',function(e){
		e.preventDefault();
		hideBox();
	});
}
function getCategory(node,sid,callback){
	var url = window['siteUrl']+'request.ashx?jsoncallback=?&action=category&id='+sid;
	$.getJSON(url,function(data){
		var d = data[0].MSG;
		window['loadCat']++;
		callback&&callback.call(this,node,d);
		
	});
}
var IDC2 = (function(){
	jQuery.extend(jQuery.easing,{easeOutCubic:function(t,e,i,n,o){return n*((e=e/o-1)*e*e+1)+i}});
	var closeGG = function(node){
		var node = $('#'+node),btn = node.find('.close');
		if(!!node.find('a')[0]){node.show();}
		btn.click(function(){
			node.slideUp('easeOutCubic');
		});
	}
	var loginout = function(siteUrl){
		if(isapp === '1'){ YDB.CloseGPS();}
		var url = siteUrl+"request.ashx?action=loginout&json=1&jsoncallback=?&date=" + Math.random();
		$.getJSON(url,function(data){
			if(data[0].islogin === '0'){
				if(data[0].bbsopen === "open"){
					var   f=document.createElement("IFRAME")   
					f.height=0;
					f.width=0;
					f.src=data[0].bbsloginurl;
					if (f.attachEvent){
						f.attachEvent("onload", function(){
							setTimeout(function(){window.location.href=siteUrl;},50);
						});
					} else {
						f.onload = function(){
							setTimeout(function(){window.location.href=siteUrl;},50);
						};
					}
					document.body.appendChild(f);
				}else{
					setTimeout(function(){window.location.href=siteUrl;},50);
				}
			}else{
				alert("对不起，操作失败！");
			}
		}).error(function(){alert("对不起，操作失败！");});
	}
	var showLogin = function(){
		var loginIco = $('#login_ico'),
			login_inner = $('#login_inner'),
			login_ico = $('#login_ico');
		loginIco.click(function(){
			login_inner.slideToggle('easeOutCubic');
		});
	}
	var isLogin = function(siteUrl,siteName,source){
		var sourceS = source || '';
		var url = siteUrl+"request.ashx?action=islogin&tempid="+sourceS+"&json=1&jsoncallback=?",
			node = $("#login_inner"),login_ico = $('#login_ico'),txt='';
		var hash = '?from='+encodeURIComponent(window.location.href);
		
		$.getJSON(url,function(data){
			if(data[0].islogin==="1"){
				txt="<p><span class=\"username\">"+data[0].name+"</span>，您好！欢迎登录"+siteName+"！<br><a href=\""+siteUrl+"member\">[管理中心]</a>　　<a href=\"javascript:IDC2.loginout('"+siteUrl+"');\">[退出]</a></p><input value=\"1\" id=\"isLogin\" type=\"hidden\" /><input value=\""+data[0].jibie+"\" id=\"user_jibie\" type=\"hidden\" />";
				login_ico.addClass('ico_ok');
				//loadWEBmessage();//消息系统
				if(typeof getUserState !== 'undefined'){
					window['userDate'] = data[0];
					getUserState();
				}
			}else if(data[0].islogin==="2"){
				MSGwindowShow('login','1','请先绑定手机号，再继续浏览本站内容！',data[0].url,'');
			}else{
				$('#login_ico').attr({'href':siteUrl+'member/login.html'+hash});
				txt='<p>您好，欢迎来到'+siteName+'！<br><a href="'+siteUrl+'member/login.html'+hash+'">[登录]</a>　　　<a href="'+siteUrl+'member/register.html">[注册]</a><input value="0" id="isLogin" type="hidden" /><input value="" id="user_jibie" type="hidden" /></p>';
				if(typeof getUserState !== 'undefined'){
					window['userDate'] = {};
					getUserState();
				}
			}
			node.html(txt);
		});
	}
	var getLoginUserInfo = function(siteUrl,siteName,source){
		var sourceS = source || '';
		var node = $("#login_inner"),LoginUserInfo = $('#LoginUserInfo'),login_ico = $('#login_ico'),txt='';
		var hash = '?from='+encodeURIComponent(window.location.href);
		
		var LoginUserInfo_userid = LoginUserInfo.val();
		if(LoginUserInfo_userid !== ''){
			var LoginUserInfo_chrname = LoginUserInfo.attr('data-chrname'),
				LoginUserInfo_chrpic = LoginUserInfo.attr('data-chrpic'),
				LoginUserInfo_isadmin = LoginUserInfo.attr('data-isadmin'),
				LoginUserInfo_styleid = LoginUserInfo.attr('data-styleid');
			txt="<p><span class=\"username\">"+LoginUserInfo_chrname+"</span>，您好！欢迎登录"+siteName+"！<br><a href=\""+siteUrl+"member\">[管理中心]</a>　　<a href=\"javascript:IDC2.loginout('"+siteUrl+"');\">[退出]</a></p><input value=\"1\" id=\"isLogin\" type=\"hidden\" /><input value=\""+LoginUserInfo_styleid+"\" id=\"user_jibie\" type=\"hidden\" />";
			login_ico.addClass('ico_ok');
		}else{
			$('#login_ico').attr({'href':siteUrl+'member/login.html'+hash});
			txt='<p>您好，欢迎来到'+siteName+'！<br><a href="'+siteUrl+'member/login.html'+hash+'">[登录]</a>　　　<a href="'+siteUrl+'member/register.html">[注册]</a><input value="0" id="isLogin" type="hidden" /><input value="" id="user_jibie" type="hidden" /></p>';
		}
		node.html(txt);
	}
	var tabADS = function(node){
		var obj = node;
		var currentClass = "current";
		var tabs = obj.find(".tab-hd").find(".item");
		var conts = obj.find(".tab-cont");
		var t;
		tabs.eq(0).addClass(currentClass);
		conts.hide();
		conts.eq(0).show();
		tabs.each(function(i){
			$(this).click(function(){
				conts.hide().eq(i).show();
				tabs.removeClass(currentClass).eq(i).addClass(currentClass);
			});
		});
	}
	var textMarquee = function(e){
		var n=$(e),r=n.width(),w=$(window).width(),i=n.html(),s=0,speed=Math.round(r/w*30);
		if(r<w){return;}
		n.html(i+i),s=r;
		var o=s/speed,
			u="marque"+(new Date).valueOf(),
			a="@-webkit-keyframes "+u+" { 0% {-webkit-transform:translate3d(0,0,0)} 100% {-webkit-transform:translate3d(-"+s+"px,0,0)}}\n";
		a+=a.replace(/\-webkit\-/g,"");
		$("head").append("<style>"+a+"</style>");
		var f=u+" "+o+"s linear infinite";
		n.css({"-webkit-animation":f,animation:f});
	}
	var footWorker = function(){
		var t = $('#shangjiaSelect'),node = $('#shangjiaSelectPo');
		t.click(function(e){
			e.preventDefault();
			if(t.attr('data-isShow')==='0'){
				node.show();
				t.attr('data-isShow','1');
			}else{
				node.hide();
				t.attr('data-isShow','0');
			}
		})
	}
	return {
		loginout:loginout,
		isLogin:isLogin,
		getLoginUserInfo:getLoginUserInfo,
		showLogin:showLogin,
		closeGG:closeGG,
		tabADS:tabADS,
		textMarquee:textMarquee,
		footWorker:footWorker
	}
})();
$.fn.radioForm = function(){
	this.each(function(){
		var list = $(this).find('.gx_radio');
		var forname = $(this).attr('data-name');
		var sid=$('input[name="'+forname+'"]:checked').attr('value');
		if(sid !=='' && !!sid){
			$(this).find('.gx_radio').removeClass('current');
			$(this).find('.gx_radio[data-val="'+sid+'"]').addClass('current');
		}
		list.click(function(e){
			e.preventDefault();
			$('input[name="'+forname+'"][value="'+$(this).attr('data-val')+'"]').prop('checked',true);
			list.removeClass('current');
			$(this).addClass('current');
		});
	});
}
$.fn.radioForm2 = function(){
	this.each(function(){
		var list = $(this).find('.gx_radio');
		list.click(function(e){
			e.preventDefault();
			$('#'+$(this).attr('data-id')+$(this).attr('data-val')).prop('checked',true);
			list.removeClass('checked');
			$(this).addClass('checked');
		});
	});
}
function setStatenum(selector){
	var statenum = Math.round(Math.random()*1E15);
	$(selector).val(statenum);
}
$.fn.get_TG_num = function(selector){
	var _t = $(this),list = _t.find(selector),arr_id = [],txt_id='';
	list.each(function(index,item){
		arr_id.push($(item).attr('data-tgid'));
	});
	txt_id = arr_id.join(',');
	var url = window['siteUrl']+'request.ashx?action=chrnum&key=tg&jsoncallback=?&id='+txt_id;
	$.getJSON(url, function(data){
		if(data[0]['islogin'] === '1'){
			for(var i=0;i<data[0]['MSG'].length;i++){
				for(var k in data[0]['MSG'][i]){
					_t.find('.tg_chrnum_'+k).html(data[0]['MSG'][i][k][0]['chrnum'])
				}
			}
		}
	});
}
$.fn.mUploadFile = function(){
	var t = $(this),btn = t.find('.a-upload'),showFileName = t.find('.showFileName');
	btn.on("change","input[type='file']",function(){
		var filePath=$(this).val();
		if(filePath.indexOf("jpg")!=-1 ||filePath.indexOf("jpeg")!=-1 || filePath.indexOf("png")!=-1 || filePath.indexOf("gif")!=-1){
			var arr=filePath.split('\\');
			var fileName=arr[arr.length-1];
			showFileName.html(fileName);
		}else{
			showFileName.html("您上传文件类型有误！");
			return false 
		}
	});
}
$.fn.zan_total = function(styleid,sid,val){
	var t = $(this);
	var urls = nowdomain+'request.ashx?action=dianzan&styleid='+styleid+'&id='+sid+'&jsoncallback=?';
	t.click(function(e){
		e.preventDefault();
		$.getJSON(urls,function(data){
			if(data[0].islogin === '1'){
				t.html(data[0].MSG[0][val]);
				
				var pn = t.parent();
				pn.addClass('ani_zanyixia');
				pn.animationEnd(function(){
					pn.removeClass('ani_zanyixia');
				});
				
			}else{
				MSGwindowShow('shopping','0',data[0].error,'','');
			}
		});
	});
}
$.fn.setTimerHe = function(callback){
	var hour_min_txt = 'hour_min';
	var mask = $('#mask');
	var hour_min = $('#'+hour_min_txt);
	function showTimerHe(o){
		var selector = o.attr('name')+'_'+o.attr('data-value');
		hour_min.attr({'data-forele':selector}).slideDown();
		var sibling = o.siblings('.setTimer');
		var cont = sibling.val();
		var type = sibling.attr('data-type');
		hour_min.find('.item').removeClass('disable');
		if(cont !== ''){
			if(type==='0'){
				hour_min.find('.item[title="'+cont+'"]').addClass('disable').prevAll('.item').addClass('disable');
			}else{
				hour_min.find('.item[title="'+cont+'"]').addClass('disable').nextAll('.item').addClass('disable');
			}
		}
	}
	hour_min.on('click','.closes',function(e){
		hour_min.slideUp();
		mask.hide();
	});
	hour_min.on('click','.item',function(e){
		e.preventDefault();
		if(!!$(this).hasClass('disable')){return false;}
		$('#'+hour_min.attr('data-forele')).val($(this).html());
		hour_min.slideUp();
		mask.hide();
		callback&&callback.call(this,$(this).attr('title'));
		return false;
	});
	mask.click(function(event){
		hour_min.slideUp();
		mask.hide();
	});
	return this.each(function(){
		var t = $(this);
		t.click(function(){
			showTimerHe($(this));
			mask.show();
		});
	});
}

var message_pid="-1";
var message_isstop = false;//页面是否丢失服务权
var message_isforced = false;//是否被强制拉回服务权页面,被丢失时又强制拉回权时,完全停止弱探测
function loadWEBmessage(){
	var url = window['siteUrl']+'api/request.ashx?pid=' +message_pid + '&jsoncallback=?';
	$.getJSON(url,function(data){
		if(data[0].islogin === '1'){WebMessageShow(data);}
		if(data[0].islogin === '1' || data[0].islogin === '0'){
			/*if( message_pid != '-1' &&  message_pid != data[0].pid){
		  		$('#message_show').html('活动页面丢失,被重新找回连接权');
		    }*/
			message_pid=data[0].pid;
			window.setTimeout(function(){loadWEBmessage()},200);//高速探测:间隔时间短100-200毫秒,弱探测:间隔1-2分钟以上
		}else{
			/*$('#message_show').html('信息获取被另一页面取代，本页面抓取信息进入弱探测');*/
			message_isstop = true;
			if(message_isforced){
				message_isforced=false;
			}else{
				if( message_pid === '-1' )message_pid='0';
			    window.setTimeout(function(){loadWEBmessage()},1*60000);////被取代后每2分钟尝试一次连接,检测活动页面是否丢失
			}
		}
	}).error(function(err){//失败2分钟后尝试一次
		window.setTimeout(function(){loadWEBmessage()},2*60000);
	});
	/* 
	data[0].islogin:0无信息,1:有信息MSG,2:停止高速探测,改为弱探测区别是间隔时间.
	*/
	$(window).blur(function(){
		RunOnunload();
	});
	$(window).focus(function(){
		newloadWEBmessage();
	});
}
function newloadWEBmessage(){
	//当页面发生任何刷新或鼠标动作或任意操作时,表示前活动页面已经不是焦点页面,当前页面重新初始参数强行抓回信息获取权
	//问题:如何防止本页面并行执行loadWEBmessage(),自动执行一次,强制执行一次.
	if(message_isstop){
	  	message_isstop = false;
		message_isforced =true;
    	message_pid="-1";
	    loadWEBmessage();
    }
}
function RunOnunload(){//当前页面关闭时执行,将程序里当前链接关闭,无需返回任何数据
	var url = window['siteUrl']+'api/request.ashx?action=close&pid=' +message_pid + '&jsoncallback=?';
	$.getJSON(url,function(data){});
}
function WebMessageShow(data){
	var idata = data[0]['MSG'];
	var newOrderId='webMessage';
	function countItem(){
		var len = $('#'+newOrderId).find('.item').length;
		$('#WebMessageNum').html(len);
		if(len === 0){
			$('#'+newOrderId).hide();	
		}
	}
	if(typeof idata['mp3'] !== 'undefined' && idata['mp3'] !==''){
		WebMessageMusic(idata['mp3']);
	}
	if(!$('#'+newOrderId)[0]){
		var divs = document.createElement('div');
		divs.id = newOrderId;
		$('body').append(divs);
		divs.innerHTML = '<div class="hd">您有<span id="WebMessageNum">0</span>条新信息</div><div class="bd" id="WebMessageInner"></div><a href="#" class="close">收起</a><a href="#" class="remove">移除</a>';
		$('#'+newOrderId).find('.close').click(function(e){
			e.preventDefault();
			$('#WebMessageInner').slideToggle();
			$(this).toggleClass('open');
		}).end().find('.remove').click(function(e){
			e.preventDefault();
			$('#'+newOrderId).hide();
		}).end().on( "click", ".view", function(e){
			if(typeof idata['notViewCloseALL'] !=='undefined' && idata['notViewCloseALL'] === '1'){//点击查看移除全部同类型消息
				$(this).parent().parent().remove();
			}else{
				$('#'+newOrderId).find('.tplid_'+$(this).attr('data-tplid')).remove();
			}
			countItem();
		}).on( "click", ".del", function(e){
			e.preventDefault();
			$(this).parent().parent().remove();
			countItem();
		});
	}else{
		$('#'+newOrderId).show();
		$('#WebMessageInner').slideDown();
	}
	var txt = $('<div class="item tplid_'+idata.tplid+'">'+idata.title+'<p class="date">'+idata.dtappenddate+'</p><span class="panel"><a href="'+idata.smsurl+'" class="view" data-tplid="'+idata.tplid+'">查看详细</a> <a href="#" class="del">忽略</a></span><s class="s"></s></div>');
	$('#WebMessageInner').prepend(txt);
	$('#WebMessageNum').html(parseInt($('#WebMessageNum').html())+1);
}
window['if_played_mp3'] = false;
function WebMessageMusic(file){
	if(!$('#html5_jplayer')[0]){
		$('body').append('<audio id="html5_jplayer" controls="false" hidden="true"></audio>');
		$(window).one('click',function(){
			$('#html5_jplayer').attr('src',file);
			$('#html5_jplayer')[0].play();
		});
		
	}else{
		$('#html5_jplayer').attr('src',file);
		$('#html5_jplayer')[0].play();
	}
	return false;
}
function showloupanAddTG(Loupan_loupanid){
	window['heightV'] = 318;
	var mask = $('#mask');
	var inner_iframe = $('#inner_iframe');
	inner_iframe.css({'top':'auto','bottom':'0','height':'0px'});
	mask.show();
	inner_iframe.show().animate({'height':window['heightV']+'px'},500,function(){});
	var myiframe = '<iframe src="../request.aspx?action=addtg&id='+Loupan_loupanid+'" scrolling="no" frameBorder="0" width="100%" height="'+window['heightV']+'"></iframe>';
	inner_iframe[0].innerHTML=myiframe;
	return false;
}
function LoginHide(){
	$('#inner_iframe').animate({'height':'0px'},500,function(){$('#mask').hide();});
}
//生成海报
$.fn.showHtml2canvas = function(){
	var that = $(this);
	var html = '<div class="html2canvas_fixed" id="html2canvas_fixed"><div class="inner">'+
		'<div class="hd">长按保存图片，分享给朋友！</div>'+
		'<img src="" id="html2canvas_fixed_img" />'+
		'<div class="closes"></div>'+
		'<div class="btn">我知道了</div>'+
	'</div></div>';
	$('body').eq(0).append(html);
	var html2canvas_fixed = $('#html2canvas_fixed'),html2canvas_fixed_img = $('#html2canvas_fixed_img');
	that.click(function(e){
		e.preventDefault();
		if(typeof mediaplay!=='undefined'){
			mediaplay.endVideo();
		}
		html2canvas(document.querySelector("#html2canvas_node"), {scale:2}).then(function(canvas){
			var dataURL = canvas.toDataURL("image/jpeg", .9);
			html2canvas_fixed_img.attr('src',dataURL);
			html2canvas_fixed.fadeIn();
		});
	});
	html2canvas_fixed.on('click','.btn,.closes',function(e){
		e.preventDefault();
		html2canvas_fixed.fadeOut();
	});
}
//返回顶部
$.fn.fixedBar = function(){
	var that = $(this);
	$(window).scroll(function() {
        if ($(window).scrollTop() > 100) {
			that.stop(true,true).fadeIn();
        }else{
            that.stop(true,true).fadeOut();
        }
    });
    that.click(function() {
        $('body,html').animate({ scrollTop: 0 }, 500);
        return false;
    });
}
function getQuan(shopid){
	var o_quan = $('#o_quan');
	var url = siteUrl+'request.ashx?action=getshopcard&shopid='+shopid+'&jsoncallback=?';
	$.getJSON(url,function(data){
		if(data[0].islogin === '1'){
			var arr = data[0].MSG;
			for(var i=0;i<arr.length;i++){
				if(parseInt(arr[i].nousercardcount) > 0){
					arr[i].disable = '';
				}else{
					if(arr[i].ismycard === "0"){
						arr[i].disable = 'disable';
					}else{
						arr[i].disable = '';
					}
				}
				var TPL=$('#tp_quan').html().replace(/[\n\t\r]/g, '');
				$('#o_quan').append(Mustache.to_html(TPL, data[0].MSG[i]));
			}
			$('#tab_item_quan')[0]&&$('#tab_item_quan').show();
			o_quan.on('click','li',function(e){
				e.preventDefault();
				if(!!$(this).hasClass('success1') || !!$(this).hasClass('disable')){return false;}
				lingQuan($(this),$(this).attr('data-picinum'),$(this).attr('data-styleid'));
			});
			if(typeof pageOneQuan !=='undefined'){
				pageOneQuan.call(this,data[0].MSG[0]);
			}
		}else{
			//MSGwindowShow('lingQuan','0',data[0].error,'','');
		}
	});
}
function lingQuan(o,picinum,styleid){
	if($('#isLogin').val()!=='1'){
		MSGwindowShow('lingQuan','1','您还没有登录哦！',siteUrl+'member/login.html?from='+encodeURIComponent(window.location.href),'');
		return false;
	}
	var url = siteUrl+'request.ashx?action=setusercard&picinum='+picinum+'&styleid='+styleid+'&jsoncallback=?';
	$.getJSON(url,function(data){
		if(data[0].islogin === '1'){
			o.addClass('success1');
		}else{
			MSGwindowShow('lingQuan','0',data[0].error,'','');
		}
	});
}
function getHongbao(){//优惠券
	if($.cookie("wap_hongbao") === '1'){
		return false;
	}
	var url = window['siteUrl']+'request.ashx?action=ismyhongbao&ishtml=1&jsoncallback=?';
	//moban:pc/main/default/member/IsMyHongbao.html
	$.getJSON(url,function(data){
		if(data[0].islogin === '1'){
			$.cookie("wap_hongbao",'1',{domain:"."+window['SiteYuming'],path:'/',expiress:1})
			$('body').append(data[0].MSG).on('click','#hongbaoNode .close',function(e){
				e.preventDefault();
				$('#hongbaoNode').hide();
				$('#mask').hide();
			});
			$('#mask').show();
			var node = $('#hongbaoNode');
			node.css({'margin-top':'-'+parseInt(node.height()/2)+'px'});
			node.find('li').each(function(){
				if(parseInt($(this).attr('data-nousercardcount'))<1){
					$(this).addClass('disable');
				}
				if($(this).attr('data-moneymin') === '0'){
					$(this).find('.moneymin').html('无门槛券');
				}
			});
			node.on('click','li',function(e){
				//e.preventDefault();
				if(!!$(this).hasClass('success1') || !!$(this).hasClass('disable')){return false;}
				lingQuan($(this),$(this).attr('data-picinum'),$(this).attr('data-styleid'));
			});
		}
	});
}
function gethongbaoList(tableid,arr){//微信红包，列表状态
	var cid = arr;
	if(!!$.isArray(arr)){
		cid = arr.join(',');
	}
	var url ='/api/hongbao.ashx?action=ishb&tableid='+tableid+'&cid='+cid+'&jsoncallback=?';
	$.getJSON(url,function(data){
		if(data[0].islogin === '1'){
			if(data[0].MSG.length !== 0){
				for(var i=0;i<data[0].MSG.length;i++){
					$('#item'+data[0].MSG[i]).find('.hashongbao_forbox,.hashongbao_fortit').addClass('display1');
				}
				
			}
		}
	});
}
function getFashareData(shareStyle,callback){//
	var url = '/wap/pinche/pageShareStyle?pageShareStyle='+shareStyle;
	$.ajax({url:url,success:function(res){
		callback&&callback.call(this,res);
	}});
}
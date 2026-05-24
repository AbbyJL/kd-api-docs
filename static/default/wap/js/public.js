var niulock = 1;
var niunum = 1;
var map;
var geoc;

 
  
$(function () {
	$('body').on('click','#layer-url-btn',function (){
		showLoader();
        var $url = this.href;
        $.get($url, function (data){
            if(data.code==1){
				hideLoader();
				layer.open({content:data.msg,skin:'msg',time:2 });
				setTimeout(function (){
					location.href = data.url;
				},3000);
            }else{
				hideLoader();
				layer.open({content:data.msg,skin:'msg',time:2 });
				setTimeout(function (){
					lock = 0;
				},3000);
            }
        }, "json");
        return false;
    });
});



var Cookie = window.Cookie = window.Cookie || {};

Cookie._valid_key = function(key){
    return (new RegExp("^[^\\x00-\\x20\\x7f\\(\\)<>@,;:\\\\\\\"\\[\\]\\?=\\{\\}\\/\\u0080-\\uffff]+\x24")).test(key);
}
Cookie.set = function(key, value, expire){
    if(Cookie._valid_key(key)){
        var a = key + "=" + escape(value);
        if(typeof(expire) != 'undefined'){
            var date = new Date();
            expire = parseInt(expire,10);
            date.setTime(date.getTime + expire*1000);
            a += "; expires="+data.toGMTString();
        }
        document.cookie = a;
    }
    return null;
};
Cookie.get = function(key){
    if(Cookie._valid_key(key)){
        var reg = new RegExp("(^| )" + key + "=([^;]*)(;|\x24)"),
            result = reg.exec(document.cookie);            
        if(result){
            return result[2] || null;
        }
    }
    return null;
};
Cookie.remove = function(key){
    document.cookie = key+"=;expires="+(new Date(0)).toGMTString();
};


//是否在微信客户端浏览器中打开
function isWeixn(){
    var ua = navigator.userAgent.toLowerCase();
    if(ua.match(/MicroMessenger/i)=="micromessenger") {
        return true;
    } else {
        return false;
    }
}

//isIOS
function isIOS(){
	var Na=window.navigator.userAgent.toLowerCase(),q={},Hd;
	q.isChrome=function(){return-1<Na.indexOf('chrome')||-1<Na.indexOf('CrMo')};
	q.isDesktopSafari=function(){return!q.isIOS()&&-1!==Na.search('safari')};
	var lb;-1<Na.indexOf('iphone')?
	lb='iphone':-1<Na.indexOf('ipad')?lb='ipad':-1<Na.indexOf('android')&&q.isChrome()?lb='chromeandroid':-1<Na.indexOf('android')||-1<Na.indexOf('htc_evo3d')?lb='android':-1<Na.indexOf('playbook')?lb='playbook':-1<Na.indexOf('ipod')?lb='ipod':-1<navigator.platform.indexOf('Win')&&(lb='windows',(Hd=/msapphost\/(\d+\.\d+)/i.exec(Na))&&(Hd=parseFloat(Hd[1])));lb||(lb='unknown');
	q.type=lb;
	return'iphone'===q.type||'ipad'===q.type||'ipod'===q.type;
}

function showWindow2(width,hight,url,title){
	var pageii = layer.open({
  		type: 1,
		content: "<iframe src="+url+"  width='100%' height='600px'></iframe>",
		anim: 'up',style: 'position:fixed; left:0; top:0; width:100%; height:100%; border: none; -webkit-animation-duration: .5s; animation-duration: .5s;'
	});
}

function showWindow(width,hight,url,title){
	var pageii = layer.open({
  		type: 1,
		content: "<iframe src="+url+" id='iframepage' name='iframepage' frameBorder=0 scrolling=no width='100%' onLoad='iFrameHeight()'></iframe>",
		anim: 'up',style: 'position:fixed; left:0; top:0; width:100%; height:100%; border: none; -webkit-animation-duration: .5s; animation-duration: .5s;'
	});
}

function iFrameHeight(){  
  var ifm = document.getElementById("iframepage");  
  var subWeb = document.documentElement.clientHeight;  
  if(ifm != null && subWeb != null){  
   	ifm.height = subWeb;  
  }  
 }  

//ajaxForm
$(function () {
    $('#ajaxForm').ajaxForm({
        success: complete,
        dataType: 'json'
    });
});



//ajaxForm2
$(function () {
    $('#ajaxForm2').ajaxForm({
        success: complete2,
        dataType: 'json'
    });
});
function complete2(data){
	hideLoader();
    if(data.code == 1){
		hui.toast(data.msg);
		setTimeout(function (){
			location.href = data.url;
		},1000);
    }else{
		hui.toast(data.msg);
		lock = 0;
    }
}



function complete(data){
	hideLoader();
    if(data.code == 1){
		layer.open({content:data.msg,skin:'msg',time:2 });
		setTimeout(function (){
			location.href = data.url;
		},1000);
    }else{
		layer.open({
			content:data.msg,
			style: 'background-color:#bd313b;color:#fff;border:none;',
			skin:'msg',
			time:2 
		});
		lock = 0;
    }
}




function getLocation(){
	navigator.geolocation.getCurrentPosition(Local);
}


function dingwei(page, lat, lng) {
    page = page.replace('llaatt', lat);
    page = page.replace('llnngg', lng);
    $.get(page, function (data) {
    }, 'html');
}

//图片layer弹窗
function popUpPic(id){
	//layer.photos({
		//photos: '#layer-photos-demo-'+id,
		//shift: 5 //0-6的选择，指定弹出图片动画类型，默认随机
	//}); 
}

//layerload
function showLoader(msg){
	layer.open({
		type:2,
		content:'加载中...',
		shade: false,
		style: 'color:#06c1ae',
	});
}


//showOpen
function showOpen(title,content,css,btn,time){
	var css  = css ? css : 'background-color: #FF4351; color:#fff;';
	var btn  = btn != false ? btn : '关闭';
	var time  = time ? time : '0';
	if(time == 0){
		layer.open({
			title: [title,css],content:content,btn:btn
		});
	}else{
		layer.open({
			title: [title,css],content:content,btn:btn,time:time
		});
	}
}



function hideLoader(){
    $("#loader").hide();
	layer.closeAll('loading');
}

//默认跳转
function boxmsg(msg, url, timeout, callback){ 
	showLoader();
    layer.open({content:msg,skin:'msg',time:2});
	hideLoader();
    if(url){
		setTimeout(function (){
			window.location.href = url;
		}, timeout ? timeout : 3000);
    }else if(url === 0){
		setTimeout(function (){
			location.reload(true);
		}, timeout ? timeout : 3000);
    }else{
        eval(callback);
    }
}


function boxopen(msg, close, style) {
    layer.open({
        type: 1,
        skin: style, //样式类名
        closeBtn: close, //不显示关闭按钮
        shift: 2,
        shadeClose: true, //开启遮罩关闭
        content: msg
    });
}



function loaddata(page, obj, sc) {
    var link = page.replace('0000', niunum);
    showLoader('正在加载中....');
	var html = '<div class="blank-10"></div><div class="container" style="text-align: center;"><a class="text-center">没有更多内容</a></div>';
    $.get(link, function (data) {
        if (data != 0) {
            obj.append(data);
        }else{
			obj.append(html);
			niulock = 0;
        	hideLoader();
			return;
		}
        niulock = 0;
        hideLoader();
    }, 'html');
    if (sc === true) {
        $(window).scroll(function () {
			var wh = $(window).scrollTop();
			var xh = $(document).height() - $(window).height() - 70;
            if (!niulock && wh >= xh ) {
                niulock = 1;
                niunum++;
                var link = page.replace('0000', niunum);
                showLoader('正在加载中....');
				var timeout = setTimeout(function(){
					niulock = 0;
					hideLoader();
				},5000);
                $.get(link, function (data) {
                    if (data != 0) {
						if(timeout){ //清除定时器
							clearTimeout(timeout);
							timeout = null;
						}
                        obj.append(data);
                    }
                    niulock = 0;
                    hideLoader();
                }, 'html');
            }
        });
    }
}

var input_array = Array();
$(document).ready(function (){
    $("input").each(function () {
        if (!$(this).val()) {
            $(this).val($(this).attr('placeholder'));
        }
        if ($(this).attr('type') == 'password') {
            input_array.push($(this).attr('name'));
            $(this).attr('type', 'text');
        }
    });
    $("input").focus(function () {
        if ($(this).val() == $(this).attr('placeholder')) {
            $(this).val('');
        }
        if (input_array.indexOf($(this).attr('name')) >= 0) {
            $(this).attr('type', 'password');
        }

    }).blur(function () {
        if ($(this).val() == '') {
            $(this).val($(this).attr('placeholder'));
        }
        if ($(this).attr('type') == 'password' && $(this).val() == $(this).attr('placeholder')) {
            input_array.push($(this).attr('name'));
            $(this).attr('type', 'text');
        }
    });
	
	hideLoader();
});

function check_user_mobile(url1,url2){
	layer.open({
		type: 1,
		title:'绑定手机号',
		skin: 'layer-ext-demo',
		area: ['90%', '300px'],
		shift:6,
		content: '<div class="padding-big"><div class="form-group"><div class="label"><label>手机号</label></div><div class="field form-inline"><input class="input input-auto" name="mobile" id="mobile" value="" placeholder="填写手机号码" size="20" type="text"> <button class="button margin-top bg-yellow" id="jq_send">获取验证码</button></div></div><div class="form-group"><div class="label" ><label>验证码</label></div><div class="field"><input class="input input-auto" name="yzm" id="yzm" value="" size="10" placeholder="填写验证码" type="text"></div></div><div class="form-button"><button id="go_mobile" class="button bg-yellow edit_post" type="submit">立刻认证</button></div></div>'
	});
	var mobile_timeout;
	var mobile_count = 100;
	var mobile_lock = 0;
	$(function (){
		$("#jq_send").click(function (){

			if(mobile_lock == 0){
				mobile_lock = 1;
				$.ajax({
					url: url1,
					data: 'mobile=' + $("#mobile").val(),
					type: 'post',
					success: function (data) {
						if(data.status == 'success'){
							mobile_count = 60;
							layer.open({content:data.msg,skin:'msg',time:2});
							BtnCount();
						}else{
							mobile_lock = 0;
							layer.open({content:data.msg,skin:'msg',time:2});
						}
					}
				});
			}

		});
	});
	
	BtnCount = function () {
		if (mobile_count == 0) {
			$('#jq_send').html("重新发送");
			mobile_lock = 0;
			clearTimeout(mobile_timeout);
		}
		else {
			mobile_count--;
			$('#jq_send').html("重新发送(" + mobile_count.toString() + ")秒");
			mobile_timeout = setTimeout(BtnCount, 1000);
		}
	};
	
	$('#go_mobile').click(function(){
		var ml = $('#mobile').val();
		var y = $('#yzm').val();
		$.post(url2,{mobile:ml,yzm:y},function(result){										
			if(result.status == 'success'){
				layer.open({content:result.msg,skin:'msg',time:2});
				setTimeout(function(){
					location.reload(true);
				},3000);
			}else{
				layer.open({content:result.msg,skin:'msg',time:2});
			}														
		},'json');
	})
	$('.layui-layer-content').css('padding','15px');
}



function change_user_mobile(url1,url2){
	layer.open({
		type: 1,
		title:'更换手机号',
		skin: 'layer-ext-demo',
		area: ['90%', '300px'],
		content: '<div class="padding-big">手机号<br/><input name="mobile" id="mobile" type="text" size="13" class="input input-auto"/><button class="button margin-top bg-yellow" type="button" id="jq_send">获取验证码</button><br/><div class="blank-10"></div>验证码<br/><input class="input input-auto" size="10" name="yzm" id="yzm" type="text"/><br><div class="blank-10"></div><input type="submit" value="立刻认证" class="button bg-yellow"  id="go_mobile"/></div>'
	});
	var mobile_timeout;
	var mobile_count = 100;
	var mobile_lock = 0;
	$(function (){
		$("#jq_send").click(function (){
			if (mobile_lock == 0) {
				mobile_lock = 1;
				$.ajax({
					url: url1,
					data: 'mobile=' + $("#mobile").val(),
					type: 'post',
					success: function (data){
						if(data.status == 'success'){
							mobile_count = 60;
							layer.open({content:data.msg,skin:'msg',time:2});
							BtnCount();
						}else{
							mobile_lock = 0;
							layer.open({content:data.msg,skin:'msg',time:2});
						}
					}
				});
			}

		});
	});
	
	
	BtnCount = function (){
		if (mobile_count == 0) {
			$('#jq_send').html("重新发送");
			mobile_lock = 0;
			clearTimeout(mobile_timeout);
		}else {
			mobile_count--;
			$('#jq_send').html("重新发送(" + mobile_count.toString() + ")秒");
			mobile_timeout = setTimeout(BtnCount, 1000);
		}
	};
	
	
	$('#go_mobile').click(function(){
		var ml = $('#mobile').val();
		var y = $('#yzm').val();
		$.post(url2,{mobile:ml,yzm:y},function(result){										
			if(result.status == 'success'){
				layer.open({content:result.msg,skin:'msg',time:2});
				setTimeout(function(){
					location.reload(true);
				},3000);
			}else{
				layer.open({content:result.msg,skin:'msg',time:2});
			}														
		},'json');
	})	
	$('.layui-layer-title').css('color','#ffffff').css('background','#2fbdaa');
	
}

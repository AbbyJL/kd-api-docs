// 引入微信脚本
var wx_miniprogram;
if(navigator.userAgent.toLowerCase().match(/micromessenger/) && typeof(wx) == 'undefined') {
    document.head.appendChild(document.createElement('script')).src = 'https://res.wx.qq.com/open/js/jweixin-1.3.2.js?v=' + ~(-new Date());
}



// 判断设备类型，ios全屏
var device = navigator.userAgent;
if (document.getElementsByTagName("html")[0] && (device.indexOf('huoniao') > -1 || window.__wxjs_environment == 'miniprogram')) {
    var bodyEle = document.getElementsByTagName('html')[0];
    bodyEle.className += " huoniao_iOS";
}

if(window.__wxjs_environment == 'miniprogram'){
    var bodyEle = document.getElementsByTagName('html')[0];
    bodyEle.className += " wx_miniprogram";
}

!function(s, t) {
    function u() {
        var a = x.getBoundingClientRect().width;
        a / A > 540 && (a = 540 * A);
        var d = a / 7.5;
        x.style.fontSize = d + "px",
        C.rem = s.rem = d
    }
    var v, w = s.document, x = w.documentElement, y = w.querySelector('meta[name="viewport"]'), z = w.querySelector('meta[name="flexible"]'), A = 0, B = 0, C = t.flexible || (t.flexible = {});
    // y.remove();
    // y = false;
    if (y) {
        // console.warn("将根据已有的meta标签来设置缩放比例");
        var D = y.getAttribute("content").match(/initial\-scale=([\d\.]+)/);
        D && (B = parseFloat(D[1]),
        A = parseInt(1 / B))
    } else {
        if (z) {
            var E = z.getAttribute("content");
            if (E) {
                var F = E.match(/initial\-dpr=([\d\.]+)/)
                  , G = E.match(/maximum\-dpr=([\d\.]+)/);
                F && (A = parseFloat(F[1]),
                B = parseFloat((1 / A).toFixed(2))),
                G && (A = parseFloat(G[1]),
                B = parseFloat((1 / A).toFixed(2)))
            }
        }
    }
    if (!A && !B) {
        var H = (s.navigator.appVersion.match(/android/gi),
        s.navigator.appVersion.match(/iphone/gi))
          , I = s.devicePixelRatio;
        A = H ? I >= 3 && (!A || A >= 3) ? 3 : I >= 2 && (!A || A >= 2) ? 2 : 1 : 1,
        B = 1 / A
    }
    if (x.setAttribute("data-dpr", A),
    !y) {
        if (y = w.createElement("meta"),
        y.setAttribute("name", "viewport"),
        y.setAttribute("content", "width=device-width, initial-scale=" + B + ", maximum-scale=" + B + ", minimum-scale=" + B + ", user-scalable=no, viewport-fit=cover"),
        x.firstElementChild) {
            x.firstElementChild.appendChild(y)
        } else {
            var J = w.createElement("div");
            J.appendChild(y),
            w.write(J.innerHTML)
        }
    }
    s.addEventListener("resize", function() {
        clearTimeout(v),
        v = setTimeout(u, 300)
    }, !1),
    "complete" === w.readyState ? w.body.style.fontSize = 12 * A + "px" : w.addEventListener("DOMContentLoaded", function(b) {
        // w.body.style.fontSize = 12 * A + "px"
    }, !1),
    u(),
    C.dpr = s.dpr = A,
    C.refreshRem = u,
    C.rem2px = function(c) {
      var d = parseFloat(c) * this.rem;
      return "string" == typeof c && c.match(/rem$/) && (d += "px"),
      d
    }
    ,
    C.px2rem = function(c) {
      var d = parseFloat(c) / this.rem;
      return "string" == typeof c && c.match(/px$/) && (d += "rem"),
      d
    }

	  s.addEventListener("pageshow", function(b) {

      //iPhoneX适配
      var meta = document.getElementsByTagName('meta');
      for(var i = 0; i < meta.length; i++){
        if(meta[i]['name'] == 'viewport'){
            meta[i].setAttribute('content', meta[i]['content'] + ', viewport-fit=cover');
        }
      }

			b.persisted && (clearTimeout(v),
			v = setTimeout(u, 300))

      // 判断设备类型，ios全屏
			var device = navigator.userAgent;
			if (document.getElementsByTagName("body")[0] && (device.indexOf('huoniao_iOS') > -1 || window.__wxjs_environment == 'miniprogram')) {
				var bodyEle = document.getElementsByTagName('body')[0];
				bodyEle.className += " huoniao_iOS";
			}

	  }, false);
}(window, window.lib || (window.lib = {}));



//注册客户端webview
function setupWebViewJavascriptBridge(callback){
	
	if(window.WebViewJavascriptBridge){
		return callback(WebViewJavascriptBridge);
	}else{
		document.addEventListener("WebViewJavascriptBridgeReady", function() {
			return callback(WebViewJavascriptBridge);
		}, false);
	}
}





//获取客户端设备信息
var appInfo = {"device": "", "version": ""};
var pageBack;

window.onload = function(){

    if(navigator.userAgent.toLowerCase().match(/micromessenger/)) {
        wx.miniProgram.getEnv(function (res) {
            wx_miniprogram = res.miniprogram;
            window.wx_miniprogram_judge = true;

            if(wx_miniprogram) {
                var bodyEle = document.getElementsByTagName('html')[0];
                bodyEle.className += " huoniao_iOS wx_miniprogram";

                var Days = 30;
                var exp = new Date();
                exp.setTime(exp.getTime() + Days * 24 * 60 * 60 * 1000);
                document.cookie = "HN_isWxMiniprogram=1;path=/;expires=" + exp.toGMTString();
            }else{
                hn_delCookie('HN_isWxMiniprogram');
            }
        });

        if(!wx_miniprogram){
            hn_delCookie('HN_isWxMiniprogram');
        }
    }else{
      window.wx_miniprogram_judge = true;
    }

	setupWebViewJavascriptBridge(function(bridge) {

		//获取APP信息
		bridge.callHandler("getAppInfo", {}, function(responseData){
			var data = JSON.parse(responseData);
			appInfo = data;
		});

		//APP端后退、目前只有安卓端有此功能
		var deviceUserAgent = navigator.userAgent;
		if (deviceUserAgent.indexOf('huoniao') > -1) {
			$('.header .back, .goBack').bind('click', function(e){
				e.preventDefault();
				bridge.callHandler("goBack", {}, function(responseData){});
			});
		}

		// 开启下拉刷新
    // bridge.callHandler("setDragRefresh", {"value": "on"}, function(){});

        //显示隐藏菜单
        bridge.registerHandler("toggleAppMenu", function(data, responseCallback) {
            $('.header-search .dropnav').click();
        });

		//后退触发
        bridge.registerHandler("pageBack", function(data, responseCallback) {
            typeof pageBack == "function" && pageBack(data);
        });

	});

	//退出
	var logoutBtn = document.getElementsByClassName("logout")[0];
	if(logoutBtn && logoutBtn != undefined){
		logoutBtn.onclick = function(){
			var device = navigator.userAgent;
			if(device.indexOf('huoniao_Android') > -1){
                $(this).html('loading...');
                setupWebViewJavascriptBridge(function(bridge) {
                  bridge.callHandler('appLogout', {}, function(){});
                  bridge.callHandler("goBack", {}, function(responseData){});
                  bridge.callHandler('pageReload',	{},	function(responseData){});
				});
			}else if(device.indexOf('huoniao_iOS') > -1){
                $(this).html('loading...');
				setupWebViewJavascriptBridge(function(bridge) {
                  bridge.callHandler('appLogout', {}, function(){});
                  bridge.callHandler("goBack", {}, function(responseData){});
                  bridge.callHandler('pageReload',	{},	function(responseData){});
				});
			}
            location.href = 'logout.html';
		};
	}

	$('.header').on('touchmove', function(e){
    e.preventDefault();
  })

	if($("#navlist").size() > 0){
	  var myscroll_nav = new iScroll("navlist", {vScrollbar: false});
	  $('.header-search .dropnav').click(function(){
	    var a = $(this), header = a.closest('.header');
	    if(!header.hasClass('open')) {
          toggleDragRefresh('off');
	      header.addClass('open');
	      $('.btmMenu').hide();
	      $('.fixFooter').hide();
	      $('#navBox').css({'top':'0.9rem', 'bottom':'0'}).show();
				var device = navigator.userAgent;
	      if (device.indexOf('huoniao_iOS') > -1) {
	        // $('#navBox').css({'top':'calc(0.9rem + 20px)', 'bottom':'0'});
	        $('#navBox').css({'top':'0', 'bottom':'0'});
	      }
	      $('#navBox .bg').css({'height':'100%','opacity':1});
	      myscroll_nav.refresh();
	    }else {
          toggleDragRefresh('on');
	      header.removeClass('open');
	      closeShearBox();
	    }
	  })

    }


    //如果没有菜单内容，则隐藏APP端右上角菜单
    if (device.indexOf('huoniao') > -1 && ($('.dropnav').size() == 0 || $('#navlist_4').size() == 0)) {
        setTimeout(function(){
            setupWebViewJavascriptBridge(function(bridge) {
                bridge.callHandler('hideAppMenu', {}, function(){});
            });
        }, 500);
    }


  $('#cancelNav').click(function(){
      closeShearBox();
  })


  $('#shearBg').click(function(){
      closeShearBox();
  })

  $('#navlist li').click(function(){
      setTimeout(function(){
          closeShearBox();
      }, 500);
  })

  //模块链接跳原生
  $('#navlist_4').delegate('a', 'click', function(e){
      var t = $(this), name = t.attr('data-name'), code = t.attr('data-code'), href = t.attr('href');
      if(href != 'javascript:;' && device.indexOf('huoniao') > -1){
          e.preventDefault();
          setupWebViewJavascriptBridge(function(bridge) {
              bridge.callHandler('redirectNative', {'name': name, 'code': code, 'link': href}, function(){});
          });
      }
  });


  function closeShearBox(){
    $('.fixFooter').show();
    $('.header').removeClass('open');
    $('#navBox').hide();
    $('#navBox .bg').css({'height':'0','opacity':0});
  }


  // 清除列表cookie
  $('#navlist_4 li').click(function(){
    var t = $(this);
    if (!t.hasClass('HN_PublicShare')) {
      window.sessionStorage.removeItem('house-list');
      window.sessionStorage.removeItem('maincontent');
      window.sessionStorage.removeItem('detailList');
      window.sessionStorage.removeItem('video_list');
    }
  })

  var JuMask = $('.JuMask'), JubaoBox = $('.JubaoBox');

  // 判断是不是需要举报按钮
  if (typeof JubaoConfig != "undefined") {
    $('.HN_Jubao').show();
  }

  // 举报
  $('.HN_Jubao').click(function(){
    $('.Jubao-'+JubaoConfig.module).show();
    JubaoShow();
    JuMask.addClass('show');
  })

  // 关闭举报
  $('.JubaoBox .JuClose, .JuMask').click(function(){
    JubaoBox.hide();
    JuMask.removeClass('show');
  })


  // 选择举报类型
  $('.JuSelect li').click(function(){
    var t = $(this), dom = t.hasClass('active');
    t.siblings('li').removeClass('active');
    if (dom) {
      t.removeClass('active');
    }else {
      t.addClass('active');
    }
  })

  // 举报提交
  $('.JubaoBox-submit').click(function(){
    var t = $(this);
    if(t.hasClass('disabled')) return;
    if ($('.JuSelect .active').length < 1) {
      showErr('请选择举报类型');  //请选择举报类型
    }else if ($('#JubaoTel').val() == "") {
      showErr('请填写您的联系方式');  //请填写您的联系方式
    }else{
      var type = $('.JuSelect .active').text();
      var desc = $('.JuRemark textarea').val();
      var phone = $('#JubaoTel').val();

      if(JubaoConfig.module == "" || JubaoConfig.action == "" || JubaoConfig.id == 0){
        showErr('Error!');
        setTimeout(function(){
          JubaoBox.hide();
          JuMask.removeClass('show');
        }, 1000);
        return false;
      }

      t.addClass('disabled').html('loading...');

      $.ajax({
        url: masterDomain+"/app/api/jubao",
        data: "service=member&template=complain&module="+JubaoConfig.module+"&dopost="+JubaoConfig.action+"&aid="+JubaoConfig.id+"&type="+encodeURIComponent(type)+"&desc="+encodeURIComponent(desc)+"&phone="+encodeURIComponent(phone),
        type: "GET",
        dataType: "jsonp",
        success: function(data){
          t.removeClass('disabled').html('提交');  //提交
          if (data && data.state == 100) {
            showErr('举报成功');  //举报成功！
            setTimeout(function(){
              JubaoBox.hide();
              JuMask.removeClass('show');
            }, 1500);

          }else{
            showErr(data.info);
          }
        },
        error: function(){
          t.removeClass('disabled').html('提交');  //提交
          showErr('网络错误，请稍候重试');  //网络错误，请稍候重试！
        }
      });

    }
  });

    //在线联系
 var userinfo,toUserinfo,chatToken,toChatToken;

 //创建websocket
 var kumanIMLib = function (wsHost) {

        var lib = this;

        this.timeOut = 30000;  // 每30秒发送一次心跳
        this.timeOutObj = null;

        // 重置心跳
        this.reset = function(){
            clearTimeout(this.timeOutObj);
            lib.start();
        }

        // 启动心跳
        this.start = function(){
            lib.timeOutObj = setInterval(function(){
                lib.socket.send('HeartBeat');
            }, lib.timeOut);
        }

        // 初始化连接
        if (window['WebSocket']) {
            this.socket = new WebSocket(wsHost);
            //this.socket.onopen = this.evt.onopen;  // 连接成功

            // 关闭
            this.socket.onclose = function(){
                lib.socket = new WebSocket(lib.socket.url);
            };

            // 异常
            this.socket.onerror = function(){
                this.close();
            };

            // 收到消息
            this.socket.onmessage = function (evt) {
                lib.reset();  //重置心跳
                var msg = JSON.parse(evt.data);
                switch (msg.type) {
                    case "init":
                        console.log(msg.info.content);
                        break;
                    default:
                        if(userinfo['uid'] == msg.info.to && msg.info.type == 'member'){

                        }
                        break;
                }

            };

        } else {
            alert('您的浏览器不支持WebSockets.');
            return false;
        }

        this.start();  //启动心跳检测

    };

  //获取token
  function getToken(id){
  	if(!id){
  		id = '';
  	}
  	 $.ajax({
        url: '/app/api/getImToken?userid='+id,
        type: 'post',
        dataType: 'json',
        success: function(data){
            if(data.state == 100){
                var info = data.info;
				//创建连接
				if(!id){
				  userinfo = info;
	              chatToken = info.token;
	              chatServer = info.server;
	              AccessKeyID = info.AccessKeyID;
				  chatLib = new kumanIMLib(chatServer + "?AccessKeyID=" + AccessKeyID + "&token=" + chatToken + "&type=member");

				}else{
					toUserinfo = info;
					toChatToken = info.token;
				}
           }else{
                window.location.href = masterDomain+'/wap/passport/login?laiyuan=7';
				return false;
            }

        },
        error: function(){
            console.log('网络错误，初始化失败！');
        }
    });
  }

 function msgto(msg,type){
		var time = Math.round(new Date().getTime()/1000).toString();
        var data = {
            content: msg,
            contentType: type,
            from: chatToken,
            fid: userinfo['uid'],
            to: toChatToken,
            tid: toUserinfo['uid'],
            type: "person",
            time: time
        }
        $.ajax({
            url: '/app/api/sendImChat',
            data: data,
            type: 'post',
            dataType: 'json',
            success: function(data){
                chatLib.reset();
            },
            error: function(){

            }
        });


}

if(typeof(imconfig) != "undefined"){
   getToken(imconfig.chatid);
   getToken();
}

$('.chat_to-Link').click(function(){
  	var type = $(this).attr('data-type');
  	var userid = $.cookie(cookiePre+"login_user");
	if(userid == null || userid == ""){
		window.location.href = masterDomain+'/wap/passport/login?laiyuan=8';
		return false;
	}

	/*
	 1.获取自己的token
	 2.获取好友的token
	 3.发送消息
	 4.跳转链接
	 * */
	if(type == 'detail'){
		msgto(imconfig,'link');
	}
	if(device.indexOf('huoniao') > -1 && userinfo && toUserinfo){
      var param = {
        from: userinfo['uid'],
        to: toUserinfo['uid'],
      };
      setupWebViewJavascriptBridge(function(bridge) {
        bridge.callHandler('invokePrivateChat',  param, function(responseData){
        	console.log(responseData)
        });
      });
      return false;
    }else{
    	window.location.href = user_member+'/im/chat-'+toUserinfo['uid']+'.html'
    }

  });


  // 显示举报
  function JubaoShow(){
    JubaoBox.show();
    var jubaoHeight = JubaoBox.height();
    JubaoBox.css('margin-top', -(jubaoHeight / 2));
  }

  // 显示错误
  function showErr(txt){
    $('.JuError').text(txt).show();
    setTimeout(function(){
      $('.JuError').fadeOut();
    }, 2000)
  }


    //重置小程序中所有a链接
    $('body').delegate('a', 'click', function(e){
        if(wx_miniprogram){
            var t = $(this), url = t.attr('data-url') ? t.attr('data-url') : t.attr('href'), href = url.toLowerCase();
            if(href != '' && href != 'javascript:;' && href != '#' && href != '###' && href.indexOf('.jpg') < 0 && href.indexOf('.gif') < 0 && href.indexOf('.png') < 0 && href.indexOf('.jpeg') < 0 && href.indexOf('tel:') < 0){
                e.preventDefault();
                wx.miniProgram.navigateTo({url: '/pages/redirect/index?url=' + encodeURIComponent(url)});
            }
        }
    });


    //城市下拉菜单自动选中当前城市
    var cityIdSelectObj = $('select#cityid');
    if(cityIdSelectObj.size() > 0 && (cityIdSelectObj.val() == 0 || cityIdSelectObj.val() == '')){
        var cityInfo = $.cookie('HN_siteCityInfo');
        cityInfo = eval('('+cityInfo+')');
        var cityInfoID = cityInfo.cityid;
        cityIdSelectObj.find("option[value='"+cityInfoID+"']").attr("selected", 'selected');
        cityIdSelectObj.siblings('#cityid_dummy').val(cityInfo.name);
    }

    var cityIdSelectObj = $('select#city');
    if(cityIdSelectObj.size() > 0 && (cityIdSelectObj.val() == 0 || cityIdSelectObj.val() == '')){
        var cityInfo = $.cookie('HN_siteCityInfo');
        cityInfo = eval('('+cityInfo+')');
        var cityInfoID = cityInfo.cityid;
        cityIdSelectObj.find("option[value='"+cityInfoID+"']").attr("selected", 'selected');
        cityIdSelectObj.siblings('#city_dummy').val(cityInfo.name);
    }


    //内容页增加快速导航
    var pathname = location.pathname, pathnameArr = pathname.split('/'), pathfile = pathnameArr[pathnameArr.length-1];
    huoniao.operaJson('/app/api/getFastNavigationRule', 'service=siteConfig&action=getFastNavigationRule', function(data){
        if(data && data.state == 100){
            var info = data.info, busiDomain = info.member.busiDomain, userDomain = info.member.userDomain, fabuArr = info.fabu, cartArr = info.cart;
            var weixin = info.weixin, qr = weixin.qr, name = weixin.name, mQr = weixin.mQr, mName = weixin.mName;

            //不需要的页面
            if(
                pathname != '' &&
                pathname != 'index.html' &&
                pathname != '/' &&
                location.href.indexOf(busiDomain) < 0 &&
                location.href.indexOf(userDomain) < 0 &&
                location.href.indexOf('changecity') < 0 &&
                location.href.indexOf('login') < 0 &&
                location.href.indexOf('register') < 0 &&
                location.href.indexOf('complain') < 0 &&
                location.href.indexOf('sso') < 0 &&
                location.href.indexOf('logout') < 0 &&
                location.href.indexOf('fpwd') < 0 &&
                location.href.indexOf('resetpwd') < 0 &&
                location.href.indexOf('memberVerifyEmail') < 0 &&
                location.href.indexOf('memberVerifyPhone') < 0 &&
                location.href.indexOf('getUserInfo') < 0 &&
                location.href.indexOf('bindMobile') < 0 &&
                location.href.indexOf('suggestion') < 0 &&
                location.href.indexOf('order') < 0 &&
                location.href.indexOf('pay') < 0 &&
                location.href.indexOf('confirm') < 0 &&
                location.href.indexOf('cart') < 0 &&
                location.href.indexOf('buy') < 0 &&
                // location.href.indexOf('waimai') < 0 &&
                location.href.indexOf('address') < 0 &&
                location.href.indexOf('map') < 0 &&
                location.href.indexOf('fabu') < 0 &&
                location.href.indexOf('h_detail') < 0 &&
                location.href.indexOf('sharePage') < 0 &&
                location.href.indexOf('.html') > 0
            ){

                var liArr = [];
                liArr.push('<li class="f-homePage"><a href="'+info.basehost+'">首页</a></li>');  //首页

                if(qr || mQr) {
                    liArr.push('<li class="f-weChat"><a href="javascript:;">微信号</a></li>');  //微信号


                    $('body').append('<div class="popupNavWechat">\n' +
                        '\t<div class="conWechat">\n' +
                        '\t\t<a href="javascript:;" class="closeWechat">×</a>\n' +
                        (qr ? '\t\t<dl><dt><img src="'+qr+'"></dt><dd>'+name+'<br>微信中长按识别</dd></dl>\n' : '') +
                        (mQr ? '\t\t<dl><dt><img src="'+mQr+'"></dt><dd>'+mName+'<br>微信中长按识别</dd></dl>\n' : '') +
                        '\t</div>\n' +
                        '</div>');

                }

                liArr.push('<li class="f-user"><a href="'+userDomain+'">我的</a></li>');  //我的

                //验证是否有发布需求
                for (var i = 0; i < fabuArr.length; i++){
                    if(location.href.indexOf(fabuArr[i].domain) > -1){
                        liArr.push('<li class="f-fabu"><a href="'+fabuArr[i].link+'">发布</a></li>');  //发布
                        break;
                    }
                }

                //验证是否有购物车需求
                for (var i = 0; i < cartArr.length; i++){
                    if(location.href.indexOf(cartArr[i].domain) > -1){

                        $('.wechat, .gocart, .my').remove();

                        liArr.push('<li class="f-cart"><a href="'+cartArr[i].link+'">发布</a></li>');  //发布
                        break;
                    }
                }
				var popcls = 'fn-hide';
				if(typeof touch_poster !='undefined'){   //如果有touch_poster,则需要显示按钮
					popcls = '';
					var userShareId = $.cookie((window.cookiePre ? window.cookiePre : 'HN_') + 'userid');
					if(userShareId){
					  var img = ($('.html2_qrcode').find('img').size()>0)?$('.html2_qrcode').find('img'):$('.html2_qrcode1').find('img');
					  var imgUrl = img.attr('src');
					  img.attr('src',imgUrl+"?fromShare="+userShareId);
					}
					getdetail_poster()

				}
                $('body').append('<div class="popupRightBottom">\n' +
					'    <a href="javascript:;" class="postFast '+popcls+'"><em class="smsk"></em></a> \n'  +
					'    <div></div> \n' +
                    '    <div class="fastNav">\n' +
                    '        <button><em class="smsk"></em></button>\n' +
                    '        <div class="slideMain"><div class="slideFastNav">\n' +
                    '            <a href="javascript:;" class="closeNav"><em class="smsk"></em></a>\n' +
                    '            <ul class="fn-clear">\n' + liArr.join('') +
                    '            </ul>\n' +
                    '        </div></div>\n' +
                    '    </div>\n' +
                    '    <div class="fastTop"><em class="smsk"></em><i></i></div>\n' +
                    '</div>');

                //显示导航内容
                $('body').delegate('.popupRightBottom button', 'click', function(){
                   var slideFastNav = $('.popupRightBottom .slideFastNav');
                   var fastNavBtn = $('.popupRightBottom .fastNav button');
				   var postNavBtn = $('.popupRightBottom .postFast');
				   var topNavBtn = $('.popupRightBottom .fastTop');
                   if(slideFastNav.hasClass('showNav')){
					   $('.popupRightBottom .fastNav .slideMain').hide()
                       slideFastNav.addClass('hideNav');
                       fastNavBtn.removeClass('openNav');
					   topNavBtn.removeClass('hidefast');
					   postNavBtn.removeClass('hidefast');
                       setTimeout(function(){
                           slideFastNav.removeClass('showNav');
                           slideFastNav.removeClass('hideNav');
                       }, 200);
                   }else {
					   $('.popupRightBottom .fastNav .slideMain').show()
                       fastNavBtn.addClass('openNav');
                       slideFastNav.addClass('showNav').removeClass('hideNav');
					   topNavBtn.addClass('hidefast');
					   postNavBtn.addClass('hidefast');
                   }
                });

				//页面滚动
				$(window).scroll(function(){
					if($('.popupRightBottom .fastNav button').hasClass('openNav')){
						$('.popupRightBottom .fastNav button').click()
					}
				});



				// 生成海报
				 $(document).delegate('.postFast','click',function(e){
					$('html').addClass('noscroll')
					$(".html2_mask").show();
					$('.html2canvas_fixed').addClass('show');
					getdetail_poster();
				 });
				// 生成海报2
				function getdetail_poster(){
					if($('.html2Wrap').size()>0 ){
						var html2canvas_fixed = $('#html2canvas_fixed'),html2canvas_fixed_img = $('#html2canvas_fixed .html2_img img');
							if(html2canvas_fixed_img.size()==0){
								//生成图片
								html2canvas(document.querySelector("#html2_node"), {
								           'backgroundColor':null,
								           'useCORS':true,
										   'dpi': window.devicePixelRatio * 2,
										   'scale': 2,

								  }).then(canvas => {
										  var a = canvasToImage(canvas);
											$('#html2canvas_fixed .html2_img').html(a);
											$(".html2_mask img").hide();
									});
									 function canvasToImage(canvas) {
										    var image = new Image();
											image.setAttribute("crossOrigin",'anonymous')
										    
											var imageBase64 = canvas.toDataURL("image/png",0.6);
											imageBase64 = imageBase64.replace('data:image/png;base64,', '');											
											
											 $.ajax({
												url: "/app/api/upload",
												type: "POST",
												data: {
													mod: 'siteConfig',
													type: 'card',
													name: 'poster.png',
													base64: 'base64',
													Filedata: imageBase64
												},
												dataType: "json",
												success: function (response) {
													image.src = response.turl;
												},
												error: function (xhr, status, error) {
																			 
												}
											})
									        return image;
								  }
							}else{
								$('.html2canvas_fixed').addClass('show');
							}



						setTimeout(function(){
							if(($('.html2canvas_fixed').outerHeight()+10)>$(window).height()*.9){
								$('.html2_mask_bottom').fadeIn();
							}else{
								$('.html2_mask_bottom').fadeOut()
							}
						},500)

					}
					return false;
				}
				
				$('.html2canvas_fixed').on('click','.html2_close',function(e){
					e.preventDefault();
					$('.html2canvas_fixed').removeClass('show')
					$(".html2_mask").hide();
					$('html').removeClass('noscroll');
					$('.html2_mask_bottom').fadeOut();
				});
				$('.html2_mask').click(function(e){
					e.preventDefault();
					$('.html2canvas_fixed').removeClass('show')
					$(".html2_mask").hide();
					$('html').removeClass('noscroll');
					$('.html2_mask_bottom').fadeOut();
				})




				 //长按
				    var flag=1  //设置长按标识符
				    var timeOutEvent=0;
				    $(".html2canvas_fixed .html2_img").on({
				        touchstart: function(e){
				            if(flag){
				                clearTimeout(timeOutEvent);
				                timeOutEvent = setTimeout("longPressPoster()",800);
				            }
				            // e.preventDefault();
				        },
				        touchmove:function () {
				            clearTimeout(timeOutEvent);
				            timeOutEvent = 0;
				        },
				        touchend:function () {
				            flag=1;
				        }

				    });


                //隐藏导航内容
                $('body').delegate('.popupRightBottom .closeNav', 'click', function(){
                    var slideFastNav = $('.popupRightBottom .slideFastNav');
                    slideFastNav.addClass('hideNav');
                    setTimeout(function(){
                        slideFastNav.removeClass('showNav');
                        slideFastNav.removeClass('hideNav');
                    }, 200);
                });

                //显示微信公众号
                $('body').delegate('.popupRightBottom .f-weChat', 'click', function(){
                    $('.popupNavWechat').css("visibility", "visible");
                });

                //隐藏微信公众号
                $('body').delegate('.popupNavWechat .closeWechat', 'click', function(){
                    $('.popupNavWechat').css("visibility", "hidden");
                });

                //返回顶部
                $('body').delegate('.fastTop', 'click', function(){
                    document.scrollingElement.scrollTop = 0;
                });

                //隐藏返回顶部
                $(window).on("scroll", function(){
                    if($(window).scrollTop() > 400) {
                        $('.popupRightBottom .fastTop').css("visibility", "visible");
                    }else{
                        $('.popupRightBottom .fastTop').css("visibility", "hidden");
                    }
                });

                //隐藏原有按钮
                $('.gotop, .wechat-fix').remove();

            }
        }
    });

};

//开启关闭下拉刷新
function toggleDragRefresh(val){
    if (device.indexOf('huoniao') > -1) {
        setTimeout(function(){
            setupWebViewJavascriptBridge(function (bridge) {
                bridge.callHandler("setDragRefresh", {"value": val}, function () {
                });
            });
        }, 500);
    }
}
     //长按执行的方法
	function longPressPoster(){
	    var imgsrc = $(".html2canvas_fixed .html2_img").find('img').attr('src');
	    if(imgsrc==''||imgsrc==undefined){
	        alert(langData['siteConfig'][44][94]);//下载失败，请重试
	        return 0
	    }
	    flag=0;
	    setupWebViewJavascriptBridge(function(bridge) {
	        bridge.callHandler(
	            'saveImage',
	            {'value': imgsrc},
	            function(responseData){
	                if(responseData == "success"){
	                    setTimeout(function(){
	                        flag=1;
	                    }, 200)
	                }
	            }
	        );
	    });
	}
//输出货币标识
function echoCurrency(type){
	var pre = (typeof cookiePre != "undefined" && cookiePre != "") ? cookiePre : "HN_";
	var currencyArr = $.cookie(pre+"currency");
	if(currencyArr){
		var currency = JSON.parse(currencyArr);
		if(type){
			return currency[type]
		}else{
			return currencyArr['short'];
		}
	}else if(typeof cfg_currency != "undefined"){
		if(type){
			return cfg_currency[type]
		}else{
			return cfg_currency['short'];
		}
	}
}


var huoniao = {

	//转换PHP时间戳
	transTimes: function(timestamp, n){
		update = new Date(timestamp*1000);//时间戳要乘1000
		year   = update.getFullYear();
		month  = (update.getMonth()+1<10)?('0'+(update.getMonth()+1)):(update.getMonth()+1);
		day    = (update.getDate()<10)?('0'+update.getDate()):(update.getDate());
		hour   = (update.getHours()<10)?('0'+update.getHours()):(update.getHours());
		minute = (update.getMinutes()<10)?('0'+update.getMinutes()):(update.getMinutes());
		second = (update.getSeconds()<10)?('0'+update.getSeconds()):(update.getSeconds());
		if(n == 1){
			return (year+'-'+month+'-'+day+' '+hour+':'+minute+':'+second);
		}else if(n == 2){
			return (year+'-'+month+'-'+day);
		}else if(n == 3){
			return (month+'-'+day);
		}else{
			return 0;
		}
	}

	//数字格式化
	,number_format: function(number, decimals, dec_point, thousands_sep) {
		var n = !isFinite(+number) ? 0 : +number,
				prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
				sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
				dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
				s = '',
				toFixedFix = function (n, prec) {
					var k = Math.pow(10, prec);
					return '' + Math.round(n * k) / k;
				};

		s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
		if (s[0].length > 3) {
			s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
		}
		if ((s[1] || '').length < prec) {
			s[1] = s[1] || '';
			s[1] += new Array(prec - s[1].length + 1).join('0');
		}
		return s.join(dec);
	}

	//将普通时间格式转成UNIX时间戳
	,transToTimes: function(timestamp){
		var new_str = timestamp.replace(/:/g,'-');
    new_str = new_str.replace(/ /g,'-');
    var arr = new_str.split("-");
    var datum = new Date(Date.UTC(arr[0],arr[1]-1,arr[2],arr[3]-8,arr[4],arr[5]));
    return datum.getTime()/1000;
	}

	//获取附件不同尺寸
	,changeFileSize: function(url, to, from){
		if(url == "" || url == undefined) return "";
		if(to == "") return url;
		var from = (from == "" || from == undefined) ? "large" : from;
		if(hideFileUrl == 1){
			return url + "&type=" + to;
		}else{
			return url.replace(from, to);
		}
	}

	//获取字符串长度
	//获得字符串实际长度，中文2，英文1
	,getStrLength: function(str) {
		var realLength = 0, len = str.length, charCode = -1;
		for (var i = 0; i < len; i++) {
		charCode = str.charCodeAt(i);
		if (charCode >= 0 && charCode <= 128) realLength += 1;
		else realLength += 2;
		}
		return realLength;
	}

	//异步操作
	,operaJson: function(url, action, callback){
		$.ajax({
			url: url,
			data: action,
			type: "POST",
			dataType: "json",
			success: function (data) {
				typeof callback == "function" && callback(data);
			},
			error: function(){}
		});
	}

}


var utils = {
    canStorage: function(){
        if (!!window.localStorage){
            return true;
        }
        return false;
    },
    setStorage: function(a, c){
        try{
            if (utils.canStorage()){
                localStorage.removeItem(a);
                localStorage.setItem(a, c);
            }
        }catch(b){
            if (b.name == "QUOTA_EXCEEDED_ERR"){
                alert("您开启了秘密浏览或无痕浏览模式，请关闭");
            }
        }
    },
    getStorage: function(b){
        if (utils.canStorage()){
            var a = localStorage.getItem(b);
            return a ? JSON.parse(localStorage.getItem(b)) : null;
        }
    },
    removeStorage: function(a){
        if (utils.canStorage()){
            localStorage.removeItem(a);
        }
    },
    cleanStorage: function(){
        if (utils.canStorage()){
            localStorage.clear();
        }
    }
};


var	scrollDirect = function (fn) {
  var beforeScrollTop = document.body.scrollTop;
  fn = fn || function () {
  };
  window.addEventListener("scroll", function (event) {
      event = event || window.event;

      var afterScrollTop = document.body.scrollTop;
      delta = afterScrollTop - beforeScrollTop;
      beforeScrollTop = afterScrollTop;

      var scrollTop = $(this).scrollTop();
      var scrollHeight = $(document).height();
      var windowHeight = $(this).height();
      if (scrollTop + windowHeight > scrollHeight - 10) {
          fn('up');
          return;
      }
      if (afterScrollTop < 10 || afterScrollTop > $(document.body).height - 10) {
          fn('up');
      } else {
          if (Math.abs(delta) < 10) {
              return false;
          }
          fn(delta > 0 ? "down" : "up");
      }
  }, false);
}

//计算广告尺寸
function calculatedAdvSize(obj){
  var obj = $('#' + obj);
  if(!obj.parent().height()){
    obj.css({'min-height': '2.5rem'});
  };
  if(obj.size() > 0){
    obj.find('h6').html('尺寸【'+parseInt(obj.width() * 2)+' × '+parseInt(obj.height() * 2)+'】px');
  }
}

function hn_getCookie(key){
   var arr,reg = RegExp('(^| )'+key+'=([^;]+)(;|$)');
   if (arr = document.cookie.match(reg))
        return decodeURIComponent(arr[2]);
   else
        return null;
}

function hn_delCookie(key){
    var date = new Date();
    date.setTime(date.getTime() - 1);
    var delValue = hn_getCookie(key);
    if (!!delValue) {
        document.cookie = key+'='+delValue+';expires='+date.toGMTString();
    }
}

function returnHumanTime(t,type) {
    var n = new Date().getTime() / 1000;
    var c = n - t;
    var str = '';
    if(c < 60) {
        str = '刚刚';
    } else if(c < 3600) {
        str = parseInt(c / 60) + '分钟前';
    } else if(c < 86400) {
        str = parseInt(c / 3600) + '小时前';
    } else if(c < 604800) {
        str = parseInt(c / 86400) + '天前';
    } else {
        str = huoniao.transTimes(t,type);
    }
    return str;
}
function returnHumanClick(click){
   if(click >= 10000){
       click = (click/10000).toFixed(1) + '万';
   }
   return click;
}

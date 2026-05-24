

document.head.appendChild(document.createElement('script')).src = '/static/default/wap/new1/js/ui/clipboard.min.js?v=' + ~(-new Date());
if(typeof(wx) == 'undefined') {
    document.head.appendChild(document.createElement('script')).src = 'https://res.wx.qq.com/open/js/jweixin-1.3.2.js?v=' + ~(-new Date());
}

$(function(){

    function entityToString(str){
      var div = document.createElement('div');
      div.innerHTML = str.replace(/&amp;/g, '&');
      return div.innerText;
    }
    wxconfig && (wxconfig.title = entityToString(wxconfig.title));
    wxconfig && (wxconfig.description = entityToString(wxconfig.description));

    if(wxconfig && wxconfig.imgUrl.indexOf('siteConfig/logo') > -1 && shareAdvancedUrl){
        wxconfig.imgUrl = shareAdvancedUrl;
    }

    if(wxconfig){
		
      var userid = userId;
	  
      if(userid){
        wxconfig.link = wxconfig.link.indexOf('?') > -1 ? (wxconfig.link + '&fromShare=' + userid) : (wxconfig.link + '?fromShare=' + userid)
      }
    }

    //小程序
    if(navigator.userAgent.toLowerCase().match(/micromessenger/)) {
        wx.miniProgram.getEnv(function (res) {
            if (res.miniprogram) {
                wx.miniProgram.postMessage({
                    data: {
                        title: wxconfig.title,
                        imgUrl: wxconfig.imgUrl,
                        desc: wxconfig.description,
                        link: wxconfig.link
                    }
                });
            }
        });
    }

 
	
	var shareHtml = '<div class="HN_PublicShare_shearBox fn-hide"id="HN_PublicShare_shearBox"><div class="HN_PublicShare_sheark1"><div class="HN_PublicShare_sheark2"><div class="HN_PublicShare_HN_style_32x32"><ul class="fn-clear">' +
	  '<li> <a class="HN_button_qzone" href="http://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey?url=' + wxconfig.link + '&desc=' + wxconfig.title + '"></a>QQ空间</li>' +	//QQ空间
	  '<li><a class="HN_button_tsina" href="http://service.weibo.com/share/share.php?url=' + wxconfig.link + '&desc=' + document.title + '"></a>新浪微博</li>' +		//新浪微博
	  '<li><a class="HN_button_tweixin"></a>微信</li>' +// 微信
	  '<li><a class="HN_button_ttqq"></a>QQ好友</li>' +	//QQ好友
	  '<li><a class="HN_button_comment"><span class="HN_txt jtico jtico_comment"></span></a>朋友圈</li>' +	//朋友圈
	  '<li class="facebook"> <a class="HN_button_fb " href="https://www.facebook.com/sharer/sharer.php?u='+encodeURI(wxconfig.link)+'"></a>facebook</li>' +	//facebook
      '<li><a class="HN_button_code"><span class="HN_txt jtico jtico_code"></span></a>二维码</li>' +	//二维码
	  '<li><button class="HN_button_link" data-clipboard-action="copy"  data-clipboard-text="'+ wxconfig.link +'"></button>复制链接</li>' +		//复制链接
	  '</ul></div></div> <div class="HN_PublicShare_cancel" id="HN_PublicShare_cancelShear">取消</div></div>' +	//取消
	  '<div class="HN_PublicShare_bg" id="HN_PublicShare_shearBg"></div></div ><div class="HN_PublicShare_shearBox HN_PublicShare_codeBox" id="HN_PublicShare_codeBox"><div class="HN_PublicShare_sheark1">' +
	  '<img src="" alt="" width="130" height="130"><p>让朋友扫一扫访问当前网页</p>' +	//让朋友扫一扫访问当前网页
	  '<div class="HN_PublicShare_cancel" id="HN_PublicShare_cancelcode">取消</div>' +	//取消
	  '</div><div class="HN_PublicShare_bg"></div></div><div class="HN_PublicShare_zhiyin fn-hide"><div class="HN_PublicShare_bg"><div class="HN_PublicShare_zhibox"><img src="/static/default/wap/new1/images/HN_Public_sharezhi.png" alt=""></div></div></div>';

	$("body").append(shareHtml);

	var hnShare = {
		showShareBox: function(){
			$('#HN_PublicShare_shearBox').removeClass('fn-hide').animate({'bottom': '0'}, 200);
			$('#HN_PublicShare_shearBox .HN_PublicShare_bg').css({'height':'100%','opacity':1});

		}

		,closeShearBox: function(){
			$('#HN_PublicShare_shearBox').animate({'bottom': '-100%'}, 200);
			$('#HN_PublicShare_shearBox .HN_PublicShare_bg').css({'height':'0','opacity':0});
		}

		,showQRBox: function(){
			$('#HN_PublicShare_shearBox').animate({'bottom': '-100%'}, 200);
			$('#HN_PublicShare_codeBox').animate({'bottom': '0'}, 200);
		}
		,closeQRBox: function(){
            $('#HN_PublicShare_codeBox').animate({'bottom': '-100%'}, 200);
            $('.HN_PublicShare_shearBox .HN_PublicShare_bg').css({'height':'0','opacity':0});
      	}
      	,showSRBox: function(){
			$('.HN_PublicShare_zhiyin').show();
			$('.HN_PublicShare_zhiyin .HN_PublicShare_bg').css({'height':'100%','opacity':1});
      	}
      	,closeSRBox: function(){
			$('.HN_PublicShare_zhiyin').hide();
    	    $('.HN_PublicShare_zhiyin .HN_PublicShare_bg').css({'height':'0','opacity':0});
      	}
	}


	var device = navigator.userAgent;
    var clipboardShare;

	$("body").delegate(".HN_PublicShare", "tap click", function(){
		var device = navigator.userAgent;
		if(device.indexOf('huoniao') <= -1){

            if(!clipboardShare){
                clipboardShare = new ClipboardJS('.HN_button_link');
                clipboardShare.on('success', function(e) {
                    alert('复制成功');
                });

                clipboardShare.on('error', function(e) {
                    alert('复制失败');
                });
            }

			var QzoneUrl = 'http://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey?url='+wxconfig.link+'&desc='+wxconfig.title,
			TsinaUrl = 'http://service.weibo.com/share/share.php?url='+wxconfig.link+'&desc='+wxconfig.title;
			$('.HN_button_qzone').attr("href",QzoneUrl);
			$('.HN_button_tsina').attr("href",TsinaUrl);
			hnShare.showShareBox();

		//客户端下调用原生分享功能
		}else{
            setupWebViewJavascriptBridge(function(bridge) {
				bridge.callHandler("appShare", {
					"platform": "all",
					"title": wxconfig.title,
					"url": wxconfig.link,
					"imageUrl": wxconfig.imgUrl,
					"summary": wxconfig.description
				}, function(responseData){
					var data = JSON.parse(responseData);
				})
		  });
      }

      //隐藏浮动菜单
      $('.fixFooter').show();
	  $('.header').removeClass('open');
	  $('#navBox_4').hide();
	  $('#navBox_4 .bg').css({'height':'0','opacity':0});

      return false;
	});
	$("body").delegate(".HN_PublicShare", "touchend", function(e){
		//取消点透事件，增加此代码会导致不能分享
	});

	//单独分享
	$("body").delegate(".HN_PublicShare_Singel", "tap click", function(event){
		event.preventDefault();
		var id = $(this).attr("data-id");
		var device = navigator.userAgent;
		if(device.indexOf('huoniao') > -1){
			setupWebViewJavascriptBridge(function(bridge) {
				bridge.callHandler("appShare", {
					"platform": id,
					"title": wxconfig.title,
					"url": wxconfig.link,
					"imageUrl": wxconfig.imgUrl,
					"summary": wxconfig.description
				}, function(responseData){
					var data = JSON.parse(responseData);
				})
			});
		}else{
			if(id == "Qzone"){
				location.href = 'http://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey?url='+wxconfig.link+'&desc='+document.title;
			}else if(id == "sina"){
				location.href = 'http://service.weibo.com/share/share.php?url='+wxconfig.link+'&desc='+document.title;
			}else if(id == "wechat"){
				var code = masterDomain+'/app/api/qrcode?data='+encodeURIComponent(wxconfig.link);
				hnShare.showQRBox();
				hnShare.showSRBox();
				$('#HN_PublicShare_codeBox img').attr('src', code);
			}else if(id == "QQ"){
				var code = masterDomain+'/app/api/qrcode?data='+encodeURIComponent(wxconfig.link);
				hnShare.showQRBox();
				hnShare.showSRBox();
				$('#HN_PublicShare_codeBox img').attr('src', code);
			}
		}
	});

	

	$("#HN_PublicShare_shearBg").click(function(){
		hnShare.closeShearBox();
		hnShare.closeQRBox();
		hnShare.closeSRBox();
	});

	

	$(".HN_PublicShare_bg").click(function(){
		hnShare.closeShearBox();
		hnShare.closeQRBox();
		hnShare.closeSRBox();
	});

	$("body").delegate(".HN_PublicShare_bg", "touchend", function(e){
		//取消点透事件，增加此代码会导致不能分享
		 // e.preventDefault();
	});



	$("#HN_PublicShare_cancelShear").click(function(){
		hnShare.closeShearBox();
	});



	$("#HN_PublicShare_cancelcode,#HN_PublicShare_cancelShear").click(function(){
		hnShare.closeShearBox();
		hnShare.closeQRBox();
	});



	$(".HN_button_code").click(function(){
		var code = masterDomain+'/app/api/qrcode?data='+encodeURIComponent(wxconfig.link);
		hnShare.showQRBox();
		$('#HN_PublicShare_codeBox img').attr('src', code);
	});
	
	$('.HN_button_tweixin, .HN_button_ttqq, .HN_button_comment').click(function(){
		hnShare.closeShearBox();
		hnShare.showSRBox();
	})


	//微信分享
    if(navigator.userAgent.toLowerCase().match(/micromessenger/)) {
        wx.config({
            debug: false,
            appId: wxconfig.appId,
            timestamp: wxconfig.timestamp,
            nonceStr: wxconfig.nonceStr,
            signature: wxconfig.signature,
            jsApiList: ['onMenuShareTimeline', 'onMenuShareAppMessage', 'onMenuShareQQ', 'onMenuShareWeibo', 'onMenuShareQZone', 'openLocation', 'scanQRCode']
        });
        wx.ready(function () {
            wx.onMenuShareAppMessage({
                title: wxconfig.title,
                desc: wxconfig.description,
                link: wxconfig.link,
                imgUrl: wxconfig.imgUrl,
                trigger: function (res) {
                    hnShare.closeSRBox();
                },
            });
            wx.onMenuShareTimeline({
                title: wxconfig.title,
                link: wxconfig.link,
                imgUrl: wxconfig.imgUrl,
            });
            wx.onMenuShareQQ({
                title: wxconfig.title,
                desc: wxconfig.description,
                link: wxconfig.link,
                imgUrl: wxconfig.imgUrl,
            });
            wx.onMenuShareWeibo({
                title: wxconfig.title,
                desc: wxconfig.description,
                link: wxconfig.link,
                imgUrl: wxconfig.imgUrl,
            });
            wx.onMenuShareQZone({
                title: wxconfig.title,
                desc: wxconfig.description,
                link: wxconfig.link,
                imgUrl: wxconfig.imgUrl,
            });
        });
    }

	// 复制链接


});

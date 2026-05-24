
$(function(){


    //公共样式名
    var notice = "hn_memberPublicNotice",
        header = "hn_memberPublicNotice_header",
        up     = "hn_memberPublicNotice_up",
        down   = "hn_memberPublicNotice_down",
        close  = "hn_memberPublicNotice_close",
        body   = "hn_memberPublicNotice_body",
        title  = "hn_memberPublicNotice_title",
        time   = "hn_memberPublicNotice_time",
        yes    = "hn_memberPublicNotice_yes";

    var timer, audio, step = 0, _title = document.title;
    var cookieNoticeHide = $.cookie("HN_memberPublicNotice_hide");
    var cookie = $.cookie("hide");

	//消息通知音频
	if(window.HTMLAudioElement){
		audio = new Audio();
		audio.src = "/static/default/mp3/1.mp3";
	}

  function setCookie(name, value, hours) { //设置cookie
     var d = new Date();
     d.setTime(d.getTime() + (hours * 60 * 60 * 1000));
     var expires = "expires=" + d.toUTCString();
     document.cookie = name + "=" + value + "; " + expires;
  }



    //会员中心和手机版不需要显示
    var userid = typeof cookiePre == "undefined" ? null : $.cookie(cookiePre+"login_user");
    $('.chat_to-Link').click(function(){
    	if(userid==null||userid==undefined){
    		huoniao.login();
    	}
    })
    
    
    if(typeof(memberPage) == "undefined" && !navigator.userAgent.match(/(iPhone|iPod|Android|ios)/i) && userid != null){
      	 new_element1=document.createElement("script");
		new_element1.setAttribute("type","text/javascript");
		new_element1.setAttribute("src",masterDomain + "/static/default/wap/new1//im/BenzAMRRecorder.js?v=" + ~(-new Date()));
		document.body.appendChild(new_element1);
      
		new_element=document.createElement("script");
		new_element.setAttribute("type","text/javascript");
		new_element.setAttribute("src",masterDomain + "/static/default/wap/new1/js/im/im-formatted.js?v=" + ~(-new Date()));
		document.body.appendChild(new_element);
		
       
    }



});

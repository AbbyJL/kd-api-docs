window.Widget.Share = window.Widget.Share || {};

var Waimai_Url = "<{link ctl='/' http='waimai'}>";
Widget.Share.init = function(params){
    Widget.Share._params = params;
    if(checkIsAppClient()){
    }else if(checkIsWeixin()){
        Widget.Share.initwxshare();
    }else{
        Widget.Share.webshare(Widget.Share._params);
    }   
}

Widget.Share.onShare = function(callback){ 
    //点击按钮调用
    callback = callback || function(){};
    if(checkIsAppClient()){
        Widget.Share.AppShare(JSON.stringify(Widget.Share._params));
    }else if(checkIsWeixin()){
        var html = '<div class="mask_bg_share" onclick="close_mask();" style="opacity:0.7 !important;"></div><div onclick="close_mask();" class="share_phone"><img src="/themes/waimai/static/img/sharePic.png"></div>';
        html += '<div class="shareWayMask" style="display: block;">';

        if(Widget.Share._params.downImg){
            html += '<ul><li class="savePic"><a href="javascript:;" ><p>长按上方图片保存</p></a></li></ul>';
        }
        html += '<a class="cancel" style="cursor:pointer;">取消</a></div>';
        $("body").append(html);
        $(".mask_bg_share,#sharePicMask").show();

        $(document).on("click",".shareWayMask .cancel",function(){
            close_mask();
        });

        Widget.Share.WeixinShare(Widget.Share._params, callback);
    }else{   
        $('.sharePicMask,.shareWayMask,.mask_bg').show();       
    }
}


Widget.Share.initwxshare = function(){
    window.WXJS_CFG.jsApiList = [
        'checkJsApi',
        'onMenuShareAppMessage',
        'onMenuShareTimeline',
        'onMenuShareQQ',
    ];
    wx.config(window.WXJS_CFG);
}

Widget.Share.webshare  = function(params){
    var html = '<div class="mask_bg" style="display: none; cursor:pointer;"></div>';
    html += '<div class="shareWayMask" style="display: none;"><ul>';
    html += '<li class="bdsharebuttonbox"><a href="javascript:;" data-cmd="weixin"><img src="/themes/waimai/static/img/share/btn_wechat_share@3x.png" width="44" data-cmd="weixin"><p>微信</p></a></li>';
    html += '<li class="bdsharebuttonbox"><a href="javascript:;" target="_blank" data-cmd="sqq"><img src="/themes/waimai/static/img/share/btn_qq_share@3x.png" width="44" data-cmd="sqq"><p>QQ</p></a></li>';
    html += '<li class="bdsharebuttonbox"><a href="javascript:;" target="_blank" data-cmd="weixin"><img src="/themes/waimai/static/img/share/btn_wechats_share@3x.png" width="44" data-cmd="weixin"><p>朋友圈</p></a></li>';
    html += '<li class="bdsharebuttonbox"><a href="javascript:;" target="_blank" data-cmd="qzone"><img src="/themes/waimai/static/img/share/btn_qqs_share@3x.png" width="44" data-cmd="qzone"><p>空间</p></a></li>';
    

    if(params.downImg){
        html += '<li class="savePic"><a href="'+params.downImg+'" download="downImg"><img src="/themes/waimai/static/img/share/btn_img_share@3x.png" width="44"><p>保存图片</p></a></li>';
    }

    html += '</ul><a class="cancel" style="cursor:pointer;">取消</a></div>';
    html += '<style>.bdshare-button-style0-16 a, .bdshare-button-style0-16 .bds_more{}</style>';

    $("body").append(html);

    $(document).on("click",".shareWayMask .cancel,.mask_bg",function(){
        $(".mask_bg_share").remove();
        $('.sharePicMask,.shareWayMask,.mask_bg').hide();
    });

    window._bd_share_config ={
        "common":{"bdSnsKey":{},bdText : params.title,bdDesc : params.desc,bdUrl : params.link,bdPic : params.imgUrl,"bdMini":"0","bdStyle":"888","bdSize":"888"},
        "share":{},"image":{"viewList":["qzone","tsina","tqq","renren","weixin"],
        "viewText":"分享到：","viewSize":"16"},
        "selectShare":{"bdContainerClass":null,"bdSelectMiniList":["qzone","tsina","tqq","renren","weixin"]}
    };
    
    with(document)0[(getElementsByTagName('head')[0]||body).appendChild(createElement('script')).src='/static/cdn/share/js/share.js?v=89860593.js?cdnversion='+~(-new Date()/36e5)];
}

Widget.Share.AppShare = function(params){
    window.JHAPP.onShare(params);
}

Widget.Share.WeixinShare = function(params, callback){
    callback = callback || function(){};
    wx.ready(function(){
        wx.onMenuShareAppMessage({
            title: params.title, 
            desc: params.desc, 
            link: params.link, 
            imgUrl: params.imgUrl, 
            type: '', 
            dataUrl: '', 
            success: function () {
                layer.open({content: '分享成功！', time: 1});
                setTimeout(function(){
                    callback();
                }, 1000);
            },
            cancel: function () { 
            }
        });

        // 分享到朋友圈
        wx.onMenuShareTimeline({
            title: params.title, 
            link: params.link, 
            imgUrl: params.imgUrl, 
            success: function () { 
                layer.open({content: '分享成功！', time: 1});
                setTimeout(function(){
                    callback();
                }, 1000);
            },
            cancel: function () { 
            }
        });

        // 分享到手机QQ
        wx.onMenuShareQQ({
            title: params.title, 
            desc: params.desc, 
            link: params.link, 
            imgUrl: params.imgUrl, 
            success: function () { 
                layer.open({content: '分享成功！', time: 1});
                setTimeout(function(){
                    callback();
                }, 2000);
            },
            cancel: function () { 
            }
        });
    });
}

function close_mask(){
    $(".mask_bg_share").remove();
    $(".share_phone").remove();
    $('.sharePicMask,.shareWayMask,.mask_bg').hide();
}

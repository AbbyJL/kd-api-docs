
window.KT = window.KT || {version: "1.0a"};
window.Widget = window.Widget || {};
(function(K, $){
K.$GUID = "KT";
//Global 容器
window.$_G = K._G = {};
$_G.get = function(key){
    return K._G[key];
};
$_G.set = function(key, value, protected_) {
    var b = !protected_ || (protected_ && typeof K.G[key] == "undefined");
    b && (K._G[key] = value);
    return K._G[key];
};

//生成全局GUID
K.GGUID = function(){
    var guid = K.$GUID;
    for (var i = 1; i <= 32; i++) {
        var n = Math.floor(Math.random() * 16.0).toString(16);
        guid += n;
    }
    return guid.toUpperCase();
};
K.Guid = function(){
    return K.$GUID + $_G._counter++;
};
$_G._counter = $_G._counter || 1;

//cookie
var Cookie = window.Cookie = window.Cookie || {};
//验证字符串是否合法的cookie键名
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
            a += "; expires="+date.toGMTString();
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



var MsgBox = Widget.MsgBox = Widget.MsgBox || {};
MsgBox.__Index = 0;
MsgBox.success=function(msg, options, callback){
    MsgBox.hide();
    if(typeof(options) == 'function'){
        callback = options;
        options = {};
    }
    callback = callback || function(ret){};
    options = $.extend({/*style:"background-color: #000;filter: alpha(opacity=60);background-color: rgba(0,0,0,0.6);color: #fff;border: none;",*/"time":2,type:0},options||{});
    options["end"] = callback;
    options["content"] = msg;
    MsgBox.__Index = layer.open(options);
};
MsgBox.error=function(msg,options,callback){
    MsgBox.hide();
    if(typeof(options) == 'function'){
        callback = options;
        options = {};
    }
    callback = callback || function(ret){}
    options = $.extend({/*style:"border:none; background-color:#78BA32; color:#fff;",*/"time":3,type:0},options||{});
    options["end"] = callback;
    options["content"] = msg;
    MsgBox.__Index = layer.open(options);
};
MsgBox.alert = function(msg, callback){
    MsgBox.hide();
    callback = callback || function(ret){};
    options["end"] = callback;
    options["content"] = msg;
    MsgBox.__Index = layer.open({content: msg, btn: ['确认'], end: callback});
}
MsgBox.confirm = function(msg, options, callback){
    MsgBox.hide();
    if(typeof(options) == 'function'){
        callback = options;
        options = {shadeClose: false, btn: ['确认', '取消']};
    }
    callback = callback || function(ret){};
    options["content"] = msg;
    options["btn"]  =options["btn"] || ['确认', '取消'];
    options["yes"] = function(index){callback(true);layer.close(index);}
    options["no"] = function(index){callback(false);layer.close(index);}
    MsgBox.__Index = layer.open(options);
}
MsgBox.notice=function(options){
    MsgBox.hide();
    MsgBox.__Index = layer.open(options);
};
MsgBox.load=function(msg,options){
    MsgBox.hide();
    options = $.extend({time:120,type:2,shade:false,shadeClose:false},options||{});
    MsgBox.__Index = layer.open(options);
};
MsgBox.show=function(options,callback){
    options = options||{};
    options['end'] = callback || function(){};
    MsgBox.__Index = layer.open(options);
};
MsgBox.hide=function(){
    layer.close(MsgBox.__Index);
};



Widget.Dialog = Widget.Dialog || {};

Widget.Dialog.Load = function(link,title,width,handler){
    var option = {width:500,autoOpen:false,modal:true};
    var opt = $.extend({},option);
    handler = handler || function(){};
    title = title || "";
    opt.width = width || opt.width; 
    Widget.MsgBox.load("数据努力加载中。。。", 5000); 
    if(link.indexOf("?")<0){
        link += "?MINI=load";
    }else{
        link += "&MINI=load";
    }
    $.get(link, function(content){
        layer.open({
            type: 1,
            title:title,
            area: opt.width+'px',
            skin: 'layui-layer-rim', //加上边框
            content: content,
            success : function(){
                Widget.MsgBox.hide();handler();
            }
        });
    });
};
window.Dialog_callback = [];
Widget.Dialog.iframe = function(link, title, width, handler){
    var option = {width:700,modal:true};
    var opt = $.extend({},option);
    opt.title = title || "";
    opt.width = width || 700;
    Widget.MsgBox.success("数据处理中...");
    Widget.MsgBox.load("数据努力加载中...");
    var callback = K.GGUID();
    if(link.indexOf("?")<0){
        link += "?MINI=LoadIframe&callback="+callback;
    }else{
        link += "&MINI=LoadIframe&callback="+callback;
    }

    layer.open({
        type: 2,
        title:title,
        area: opt.width+'px',
        skin: 'layui-layer-rim', //加上边框
        content: link,
        success : function(){
            Widget.MsgBox.hide();handler();
        }
    });
    return ;
}

})(window.KT, window.jQuery);
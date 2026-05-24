!
function(a) {
    a();
} (function() {
   var H;
   H = {
	   //打开app
       openApp:function(urlScheme,callbackMethod){
           var message = {methodName:'openApp',callbackMethod:callbackMethod,urlScheme:urlScheme}
           BSLJSBridge(message);
       },
	   //检查app是否存在返回1，不存在0
       checkApp:function(urlScheme,callbackMethod){
           var message = {methodName:'checkAppInstalled',callbackMethod:callbackMethod,urlScheme:urlScheme}
           BSLJSBridge(message);
       },
       //控制底部菜单栏
       AppBottom:function(param,foreve) {
           if(param == null)
               var message = {methodName:'controlBottomTabLayout',foreve:foreve};
           else
               var message = {methodName:'controlBottomTabLayout',show:''+param+'',foreve:foreve};
           BSLJSBridge(message);
       },
       //控制顶部导航栏
       AppTop:function(param,foreve) {
           if(param == null)
               var message = {methodName:'controllNavigateLayout',foreve:foreve};
           else
               var message = {methodName:'controllNavigateLayout',show:''+param+'',foreve:foreve};
           BSLJSBridge(message);
       },
	   //控制左侧栏
       AppLeft:function (state) {
           var message = {'methodName':'controlLeftMenuLayout','show':state};
           BSLJSBridge(message);
       },
	   //控制横竖屏
	   AppScreen:function (orientation) {
           var message = {methodName:'setScreenOrientation',orientation:orientation};
           BSLJSBridge(message);
       },
	 
	   //拷贝文字
       CopyText: function (txt){
           var message = {methodName:'copyText',content:txt};
           BSLJSBridge(message);
       },
	   //复制当前页地址
	   CopyUrl:function (){
           var message = {methodName:'copyUrlToClipboard'};
           BSLJSBridge(message);
       },
	   //浏览器打开当前网页
       OpenWeb:function (url) {
           if(url == ''){
           var message = {methodName:'awakeOtherWebview'}
           BSLJSBridge(message);
           }else{
           var message = {methodName:'awakeOtherWebview',webviewUrl:url}
           BSLJSBridge(message);
           }
       },
	   //获取Build值(IOS)
	   Build: function (callbackmethod)
       {
           var message = {methodName:'getBuild',callbackMethod:callbackmethod}
           BSLJSBridge(message);
       },
	   //清除App缓存
       CCache:function () {
           var message = {methodName: 'cleanCache'};
           BSLJSBridge(message);
       },
	   //二维码
       Qcode: function (resulttype,callbackMethod) {//二维码。
            var message = {methodName:'qrcoder',controlQRCodeResult:resulttype,callbackMethod:callbackMethod};
            BSLJSBridge(message);
		},
	   //GPS定位
       GPS:function (callbackMethod){
           var message = {methodName:'getLocation',callbackMethod:callbackMethod}
           BSLJSBridge(message);
       },
       StartGPS:function (callbackMethod,distance){
           var message = {methodName:'getLocation',callbackMethod:callbackMethod,keep:'1',distance:distance}
           BSLJSBridge(message);
       },
       StopGPS:function (){
           var message = {methodName:'stopLocation'}
           BSLJSBridge(message);
       },
	   //获取设备标识符
	   PhoneID:function (callbackMethod) {
           var message = {methodName:'getDeviceIdentifier',callbackMethod:callbackMethod};
           BSLJSBridge(message);
       },
	   //验证指纹，成功1，失败0
	   touchID:function(callbackMethod){
           var message = {methodName:'fingerprint',callbackMethod:callbackMethod}
           BSLJSBridge(message);
       },
	   //获取WiFi名称
       WifiSsid:function(callbackMethod){
           var message = {methodName:'getWifiSSID',callbackMethod:callbackMethod}
           BSLJSBridge(message);
       },
	   
	   //第三方登录：QQ，微信，微博
       Login: function(platform, forwardurl, callbackMethod) {
           var message = {methodName: 'login',platform: platform,forwardurl: forwardurl,callbackMethod: callbackMethod};
           BSLJSBridge(message);
       },
	   //支付：支付宝，微信
       Pay: function (order, paytype, callbackMethod) {
           var message = {methodName: 'pay',order: order,paytype: paytype,callbackMethod: callbackMethod};
           BSLJSBridge(message);
       },
       WXPay:function (ProductName, Desicript, Price, OuttradeNo,attach, callbackMethod) {
           var message = {methodName: 'WXPay',productName:ProductName,desicript:Desicript,price:Price,outtradeNo:OuttradeNo,attach:attach,callbackMethod:callbackMethod};
           BSLJSBridge(message);
       },
	   //分享：QQ，微信，微博，复制当前网址，系统分享(IOS)
	   Share: function(content, imageurl, targetUrl, title,callbackMethod) {
           var message = { methodName : 'share', content: content,imageurl: imageurl,targetUrl: targetUrl,'title': title, 'callbackMethod': callbackMethod };
           BSLJSBridge(message);
       },
	   //分享图片
       ShareImg: function(imageurl,callbackMethod) {
           var message = { methodName : 'shareImage', imageurl: imageurl, callbackMethod: callbackMethod };
           BSLJSBridge(message);
       },
	   //分享当前截图
       ShareCImg: function(callbackMethod) {
           var message = { methodName : 'shareCutImage', callbackMethod: callbackMethod };
           BSLJSBridge(message);
       },
       ShareMultiImage:function(data,descript)
       {
           var message = {methodName:'ShareMultiImage',data:data,descript:descript};
           BSLJSBridge(message);
       },
	   //注册极光推送
	   JPushTag:function (tag,callbackmethod){
           if(tag == ''){
           return false;
           }
           var message = { methodName : 'registerPushTag', tag:tag,callbackMethod:callbackmethod};
           BSLJSBridge(message);
       },
       //新增加的
       AppTopL:function (fun,imageUrl,forever){//设置导航栏左按钮功能
       var message = {methodName:'ctrlNavLeftBtnFun',funcNum:fun,buttonImage:imageUrl,forever:forever};
           BSLJSBridge(message);
       },
       AppTopR:function (fun,imageUrl,forever){//设置导航栏右按钮功能
           var message = {methodName:'ctrlNavRightBtnFun',funcNum:fun,buttonImage:imageUrl,forever:forever};
           BSLJSBridge(message);
       },
       SetBrightness:function(value,system)//设置屏幕亮度
       {
           var message = {methodName:'setBrightness',brightness:value,system:system};
           BSLJSBridge(message);
       },
       checkMap:function(callbackMethod,mapType)//检查是否安装地图 AppleMap BDMap GDMap GGMap TXMap
       {
           var message = {methodName:'checkMap',callbackMethod:callbackMethod,mapType:mapType};
           BSLJSBridge(message);
       },
       openMap:function(callbackMethod,mapType)
       {
           var message = {methodName:'openMap',callbackMethod:callbackMethod,mapType:mapType};
           BSLJSBridge(message);
       },
       //打开地图并导航
   navMap:function(startlat,startlon,endlat,endlon,fromLocation,toLocation,callbackMethod,mapType)
       {
           var message = {methodName:'navMap',startlat:startlat,startlon:startlon,endlat:endlat,endlon:endlon,fromLocation:fromLocation,toLocation:toLocation,callbackMethod:callbackMethod,mapType:mapType};
           BSLJSBridge(message);
       },
       checkBDMap:function(callbackMethod)//检查是否安装百度地图
       {
           var message = {methodName:'checkBDMap',callbackMethod:callbackMethod};
           BSLJSBridge(message);
       },
       //打开百度地图
       openBDMap:function(callbackMethod)
       {
           var message = {methodName:'openBDMap',callbackMethod:callbackMethod};
           BSLJSBridge(message);
       },
       //打开百度地图并导航
       navBDMap:function(startlat,startlon,endlat,endlon,callbackMethod)
       {
           var message = {methodName:'navBDMap',startlat:startlat,startlon:startlon,endlat:endlat,endlon:endlon,callbackMethod:callbackMethod};
           BSLJSBridge(message);
       },
   
       checkGDMap:function(callbackMethod)//检查是否安装的高德
       {
           var message = {methodName:'checkGDMap',callbackMethod:callbackMethod};
           BSLJSBridge(message);
       },
       //打开高德地图并导航
       openGDMap:function(callbackMethod)
       {
           var message = {methodName:'openGDMap',callbackMethod:callbackMethod};
           BSLJSBridge(message);
       },
       //打开高德地图并导航（起点维度，起点经度，终点维度，终点经度）
       navGDMap:function(startlat,startlon,endlat,endlon,callbackMethod)
       {
           var message = {methodName:'navGDMap',startlat:startlat,startlon:startlon,endlat:endlat,endlon:endlon,callbackMethod:callbackMethod};
           BSLJSBridge(message);
       },
       checkGGMap:function(callbackMethod)//检查是否安装谷歌地图
       {
           var message = {methodName:'checkGGMap',callbackMethod:callbackMethod};
           BSLJSBridge(message);
       },
       //打开谷歌地图
       openGGMap:function(callbackMethod)
       {
           var message = {methodName:'openGGMap',callbackMethod:callbackMethod};
           BSLJSBridge(message);
       },
       //打开谷歌地图并导航（起点维度，起点经度，终点维度，终点经度）
       navGGMap:function(startlat,startlon,endlat,endlon,callbackMethod)
       {
           var message = {methodName:'navGGMap',startlat:startlat,startlon:startlon,endlat:endlat,endlon:endlon,callbackMethod:callbackMethod};
           BSLJSBridge(message);
       },
       checkTXMap:function(callbackMethod)//检查是否安装腾讯地图
       {
           var message = {methodName:'checkTXMap',callbackMethod:callbackMethod};
           BSLJSBridge(message);
       },
       //打开腾讯地图
       openTXMap:function(callbackMethod)
       {
           var message = {methodName:'openTXMap',callbackMethod:callbackMethod};
           BSLJSBridge(message);
       },
       //打开腾讯地图并导航（起点维度，起点经度，终点维度，终点经度）
       navTXMap:function(startlat,startlon,endlat,endlon,callbackMethod)
       {
           var message = {methodName:'navTXMap',startlat:startlat,startlon:startlon,endlat:endlat,endlon:endlon,callbackMethod:callbackMethod};
           BSLJSBridge(message);
       },
       //打开苹果地图
       openAppleMap:function(callbackMethod)
       {
           var message = {methodName:'openAppleMap',callbackMethod:callbackMethod};
           BSLJSBridge(message);
       },
       //打开苹果地图并导航（起点维度，起点经度，终点维度，终点经度）
       navAppleMap:function(startlat,startlon,endlat,endlon,callbackMethod)
       {
           var message = {methodName:'navAppleMap',startlat:startlat,startlon:startlon,endlat:endlat,endlon:endlon,callbackMethod:callbackMethod};
           BSLJSBridge(message);
       },
       ShareImgWithTxt: function(platform,content, imageurl, targetUrl, title,callbackMethod) {//分享platform:WXTimeline(朋友圈)、WXSession（微信好友）、QQZone（QQ空间）、QQSession（QQ好友）、WB（新浪微博）
           var message = { methodName : 'ShareImgWithTxt', content: content,imageurl: imageurl,targetUrl: targetUrl,'title': title,callbackMethod: callbackMethod,platform:platform};
           BSLJSBridge(message);
       },
       ShareImgByPlatfrom: function(platform,imageurl,callbackMethod) {//分享图片
           var message = { methodName : 'ShareImgByPlatfrom', imageurl: imageurl, callbackMethod: callbackMethod,platform:platform};
           BSLJSBridge(message);
       },
       ShareCImgByPlatform: function(platform,callbackMethod) {//分享截图
           var message = { methodName : 'ShareCImgByPlatform', callbackMethod:callbackMethod,platform:platform};
           BSLJSBridge(message);
       },
   //title：标题。description:描述。videoUrl:视频地址，thumbImgUrl:缩略图,style:0，发送给好友，1发送到朋友圈。
       ShareVideo:function(platform,title,description,thumbImgUrl,videoUrl,callbackMethod)
       {
           var message = {methodName:'ShareVideo',title:title,description:description,videoUrl:videoUrl,thumbImgUrl:thumbImgUrl,platform:platform,callbackMethod:callbackMethod};
           BSLJSBridge(message);
       },
       ShareMusic:function(platform,title,description,thumbImgUrl,musicDataUrl,musicUrl,callbackMethod)
       {
           var message = {methodName:'ShareMusic',title:title,description:description,musicUrl:musicUrl,musicDataUrl:musicDataUrl,thumbImgUrl:thumbImgUrl,platform:platform,callbackMethod:callbackMethod};
           BSLJSBridge(message);
       },
       hideStateBar:function(hidden)//隐藏状态栏（hidden：yes（1）显示 ，（0）no隐藏）
       {
           var message = {methodName:'setStatusBarHidden',hidden:hidden};
           BSLJSBridge(message);
       },
       getClipboard:function(callbackMethod)//获取粘贴板内容
       {
           var message = {methodName:'getClipboardText',callbackMethod:callbackMethod};
           BSLJSBridge(message);
       },
       cacheSize:function(callbackMethod)//检测缓存大小（单位是兆）
       {
           var message = {methodName:'getCacheSize',callbackMethod:callbackMethod};
           BSLJSBridge(message);
       },
       tabbarColor:function(color,forever)//修改tabbar的背景颜色（十六进制颜色值，透明度0~1）
       {
           var message = {methodName:'tabbarColor',color:color,forever:forever};
           BSLJSBridge(message);
       },
       navbarColor:function(color,forever)//修改navbar的背景颜色（十六进制颜色值，透明度0~1）
       {
           var message = {methodName:'navbarColor',color:color,forever:forever};
           BSLJSBridge(message);
       },
       downRefresh:function(open,forever)//关闭打开下拉刷新
       {
           var message = {methodName:'downRefresh',open:open,forever:forever};
           BSLJSBridge(message);
       },
       checkWX:function(callbackMethod)//检查微信是否安装
       {
           var message = {methodName:'checkWX',callbackMethod:callbackMethod};
           BSLJSBridge(message);
       },
       checkZFB:function(callbackMethod)//检查支付宝是否安装
       {
           var message = {methodName:'checkZFB',callbackMethod:callbackMethod};
           BSLJSBridge(message);
       },
       msgRing:function()//调用系统通知铃声
       {
           var message = {methodName:'msgRing'};
           BSLJSBridge(message);
       },
       setAVVolume:function(value)//设置系统音量(value 范围值0~1)
       {
           var message = {methodName:'setVolume',volume:value};
           BSLJSBridge(message);
       },
       getAVVolume:function(callbackMethod)//多媒体音量大小
       {
           var message = {methodName:'getVolume',callbackMethod:callbackMethod};
           BSLJSBridge(message);
       },
       OpenVideo:function(videoUrl,title)//播放视频
       {
           var message = {methodName:'playVideo',videoUrl:videoUrl,title:title};
           BSLJSBridge(message);
       },
       GetNetType:function(callbackMethod)//获取网络状态
       {
           var message = {methodName:'getNetType',callbackMethod:callbackMethod};
           BSLJSBridge(message);
       },
       MonitorNetWork:function(callbackMethod)//监听网络状态
       {
           var message = {methodName:'monitorNetWork',callbackMethod:callbackMethod};
           BSLJSBridge(message);
       },
       CloseMonitorNetWork:function()//监听网络状态
       {
           var message = {methodName:'CloseMonitorNetWork'};
           BSLJSBridge(message);
       },
       GetBrightness:function(callbackMethod)//获取屏幕亮度
       {
           var message = {methodName:'brightness',callbackMethod:callbackMethod};
           BSLJSBridge(message);
       },
       GetVersion:function(callbackMethod)//app版本号
       {
           var message = {methodName:'getVersion',callbackMethod:callbackMethod};
           BSLJSBridge(message);
       },
       OpenBrowser:function(callbackMethod,type,url)//type浏览器类型:UC、QQ、Google、SYS（系统自带浏览器）,url要打开的地址,callbackMethod返回值（0成功,1没有安装,2打开失败）
       {
           var message = {methodName:'openBrowser',type:type,callbackMethod:callbackMethod,url:url};
           BSLJSBridge(message);
       },

        //改变菜单栏
        ChangeTabbar:function(content){
            var message ={methodName:'ChangeTabbar',content:content};
            BSLJSBridge(message);
        },

       //手机信息
       PhoneInfo:function(callbackMethod){
           var message = {methodName:'phoneInfo',callbackMethod:callbackMethod}
           BSLJSBridge(message);
       },
   
       //导航栏透明度
       SetNavBarAlpha: function (alpha,foreve)
       {
           var message = {methodName:'navbarAlpha',alpha:alpha,foreve:foreve}
           BSLJSBridge(message);
       },
       //菜单栏透明度
       SetTabbarAlpha: function (alpha,forever)
       {
           var message = {methodName:'tabbarAlpha',alpha:alpha,forever:forever}
           BSLJSBridge(message);
       },
       //指定标题
       SetTitleName: function (title,color)
       {
           var message = {methodName:'navbarTitle',title:title,color:color}
           BSLJSBridge(message);
       },
       GetContact:function(callbackMethod)//获取一个联系人信息
       {
           var message = {methodName:'getContact',callbackMethod:callbackMethod};
           BSLJSBridge(message);
       },
       GetAllContact:function(callbackMethod)//获取所有联系人信息
       {
           var message = {methodName:'getAllContact',callbackMethod:callbackMethod};
           BSLJSBridge(message);
       },
       AddContact:function(contact,callbackMethod)//添加一条联系人信息
       {
           var message = {methodName:'addContact',contact:contact,callbackMethod:callbackMethod};
           BSLJSBridge(message);
       },
       ShowImages:function(imgs,title,orientation)//js浏览多个图片imgs:['http://','http://']
       {
           var message = {methodName:'OpenImages',imgs:imgs,title:title,orientation:orientation};
           BSLJSBridge(message);
       },
        KeepBright:function(unlock)//屏幕常亮
       {
           var message = {methodName:'UnlockScreen',unlock:unlock};
           BSLJSBridge(message);
       },
       AudioPlayBG:function(open)//后台播放音乐
       {
           var message = {methodName:'AudioPlayBG',open:open};
           BSLJSBridge(message);
       },
       Screenshot:function(open,callbackMethod)//检测截屏
       {
           var message = {methodName:'MonitorScreenshot',open:open,callbackMethod:callbackMethod};
           BSLJSBridge(message);
       },
       SlideHideTab:function(open)//滑动隐藏菜单栏
       {
           var message = {methodName:'SlideHideTab',open:open};
           BSLJSBridge(message);
       },
       SlideHideNav:function(open)//滑动隐藏导航栏
       {
           var message = {methodName:'SlideHideNav',open:open};
           BSLJSBridge(message);
       },
       Vibrator:function()//震动
       {
           var message = {methodName:'Vibration'};
           BSLJSBridge(message);
       },
       ControlSlide:function(funt)//修改左右滑功能
       {
           var message = {methodName:'ControlSlide',funt:funt};
           BSLJSBridge(message);
       },
       IsOpenNotice:function(callbackMethod)
       {
           var message = {methodName:'pushMsg',callbackMethod:callbackMethod}
           BSLJSBridge(message);
       },
       CheckBiometrics:function(callbackMethod)
       {
           var message = {methodName:'CheckBiometrics',callbackMethod:callbackMethod}
           BSLJSBridge(message);
       },
       CreateWindow:function(url)
       {
           var message = {methodName:'OpenWindow',url:url}
           BSLJSBridge(message);
       },
       GetStepCount:function(callbackMethod)//计步器
       {
           var message = {methodName:'GetStepCount',callbackMethod:callbackMethod};
           BSLJSBridge(message);
       },
       OpenXCX:function(name,path,type,callbackMethod)//打开小程序
       {
           var message = {methodName:'OpenMiniProgram',name:name,path:path,type:type,callbackMethod:callbackMethod};
           BSLJSBridge(message);
       },
       IAP:function(orderId,productId,CBUrl,callbackMethod)//苹果支付
       {
           var message = {methodName:'IAP',orderId:orderId,productId:productId,CBUrl:CBUrl,callbackMethod:callbackMethod};
           BSLJSBridge(message);
       },
       QrPhoto:function(type,callbackMethod)//识别相册中的二维码
       {
           var message = {methodName:'QRCodeAlbum',controlQRCodeResult:type,callbackMethod:callbackMethod};
           BSLJSBridge(message);
       },
       QrUrl:function(type,url,callbackMethod)//识别地址中的二维码
       {
           var message = {methodName:'QRCodeImgUrl',controlQRCodeResult:type,url:url,callbackMethod:callbackMethod};
               BSLJSBridge(message);
       },
        CloseTopWindow:function()//关闭多窗口
        {
            var message = {methodName:'CloseTopWindow'};
            BSLJSBridge(message);
        },
        CloseTopWindowRefresh:function()//关闭多窗口并刷新
        {
            var message = {methodName:'CloseTopWindowRefresh'};
            BSLJSBridge(message);
        },
        DownloadFile:function(imageUrlArray,callbackMethod)
        {
            var message = {methodName:'DownloadFile',imageUrlArray:imageUrlArray,callbackMethod:callbackMethod};
            BSLJSBridge(message);
        },
       DownloadFileByPath:function(imageUrlArray,path,callbackMethod)
       {
       var message = {methodName:'DownloadFileByPath',imageUrlArray:imageUrlArray,callbackMethod:callbackMethod};
       BSLJSBridge(message);
       },
        CheckFirstInstall:function(callbackMethod)//检查是否是第一次安装
        {
            var message = {methodName:'CheckFirstInstall',callbackMethod:callbackMethod};
            BSLJSBridge(message);
        },
       UNReplacementResource:function()
       {
           var message = {methodName:'UNReplacementResource'};
           BSLJSBridge(message);
       },
       ReplacementResource:function()
       {
           var message = {methodName:'ReplacementResource'};
           BSLJSBridge(message);
       },
       StatusBarTextColor:function(style)//修改电池栏颜色  0 白 1 黑
       {
            var message = {methodName:'StatusBarTextColor',style:style};
            BSLJSBridge(message);
       },
   };
   BSL = H;
   });

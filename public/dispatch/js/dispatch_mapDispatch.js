/**
@param 调度中心-》地图派单 js
@author zem
*/
var windowwidth = $(window).width();
var windowheight = $(window).height();
var map,marker; 
var makemaplineNumber = 0;
var lnglatarray = new Array(); 
var orderstatusarr = new Array();
var orderclerkidarr = new Array();
var is_clerk_dir = false;
var moveflag = true;
init();
  //status 配送单状态 1 新订单 2待派单 3派单未确认 4已接单 5已到店 6已取单 7已送达 8已结算 9关闭
$(function(){  
	//加载选取派单订单列表
	loaddispatchOrderList();
	$("#btnRefresh").bind('click',function(){
		 loaddispatchOrderList();	
	});
});
//颜色数组
var color = [];
	color[0] = "#009933", color[1] = "#ff00ff", color[2] = "#cc0066", color[3] = "#33cc99", color[4] = "#660000", color[5] = "#330099", color[6] = "#ff6699", color[7] = "#ff9933", color[8] = "#cccc33", color[9] = "#ffcc00"
	,color[10] ="#d3217b",color[11] ="#d3217b",color[12] ="#e0620d",color[13] ="#50d608",color[14] ="#13227a",color[15] ="#f4ea2a",color[16] ="#1296db",color[17] ="#d4237a",color[18] ="#d81e06",color[19] ="#1afa29";
function loaddispatchOrderList(){
	if( orderid !== '' && orderid != undefined ){
		$("#ghmapDispatchOrder").html('');
		var orderlist  = ajaxback(siteurl+'/admin/dispatch/ajaxgetDispatchOrder?datatype=json&orderid='+orderid,{}); 
		if(orderlist.flag == false){ 
			//初始化地图
			
			var odlist = orderlist.content.orderlist.templist;
			
			if( odlist.length > 1 ){
				var  hradhtmls = '<div class="ghpsb-content-head"><span class="pull-right">当前共'+odlist.length+'单,已选中<span id="ghpsb-sel-count">'+odlist.length+'</span>单</span><div class="opacity-back"></div></div>';
				$("#ghmapDispatchOrder").append(hradhtmls);
			}
			
			for(i=0;i<odlist.length;i++){
 				var orderlisthtml = '';
					orderlisthtml +=' <div class="ghpsb-entry"> ';
				    orderlisthtml +=' <div class="opacity-back"></div> ';
				    orderlisthtml +=' <div class="psb_row ghpsb-head" style="background-color:'+color[i]+'"> ';
					orderlisthtml +=' <div class="col-md-4"> ';
					if( odlist[i].ordertype == 2 ){
						orderlisthtml +='【预】';
					}else{
						orderlisthtml +='【即】';
					}
 					orderlisthtml +=''+odlist[i].time_waitpost_name+'</div> ';
					orderlisthtml +=' <div class="col-md-4 border-left">距离：'+odlist[i].juli+'</div> ';
					orderlisthtml +=' <div class="col-md-4 border-left">价格：￥'+odlist[i].goodscost+' ';
					if( odlist.length > 1 ){
						orderlisthtml +=' <label class="checkbox checkbox-inline bm-checkbox"><span  bizlng="'+odlist[i].bizlng+'" orderid="'+odlist[i].id+'" status="'+odlist[i].status+'"  clerk_id="'+odlist[i].clerk_id+'"  bizlat="'+odlist[i].bizlat+'" receiver_lng="'+odlist[i].receiver_lng+'" receiver_lat="'+odlist[i].receiver_lat+'" num="'+i+'" class="orderspan ospanCurcolor" style="font-size:12px;"></span></label>';
					}
					orderlisthtml +=' </div> ';
				    orderlisthtml +=' </div> ';
				    orderlisthtml +=' <div style="clear:both;"></div> ';
				    orderlisthtml +=' <div class="ghpsb-loc"><span class="round-span" style="background-color:'+color[i]+'">取</span>'+odlist[i].bizaddress+'</div> ';
				    orderlisthtml +=' <div class="ghpsb-loc"><span class="round-span" style="background-color:'+color[i]+'">送</span>'+odlist[i].receiver_address+'</div> ';
				    orderlisthtml +=' <div class="ghpsb-line"></div> ';
				    orderlisthtml +=' <div class="ghpsb-rider">'+odlist[i].stationname+' ';
					if( odlist[i].clerk_id > 0 ){
						orderlisthtml +='骑手：'+odlist[i].clerk_name+'('+odlist[i].clerk_phone+')';
					}else{
						orderlisthtml +='骑手：-';
 					}
 					orderlisthtml +=' </div> ';
				    orderlisthtml +=' </div> '; 
				$("#ghmapDispatchOrder").append(orderlisthtml);
				
				
				
				var templnglatarr = [];
 				templnglatarr.push(i);
				templnglatarr.push(odlist[i].bizlng);
				templnglatarr.push(odlist[i].bizlat);
				templnglatarr.push(odlist[i].receiver_lng);
				templnglatarr.push(odlist[i].receiver_lat);
				lnglatarray.push(templnglatarr);	

				orderstatusarr.push(odlist[i].status);
				if( odlist[i].clerk_id > 0){
					orderclerkidarr.push(odlist[i].clerk_id);
				}
				
				
			}
			bindorderlistmore();
			Initializationmap(); 
			setTimeout("map.setFitView()",500);
		
			
			
		}else{
			diaerror("获取失败");
		}
	}else{
			diaerror("获取失败");
	} 
	
	 
	
}  

 
function bindorderlistmore(){
	$('#ghmapDispatchOrder .ghpsb-entry label span').unbind();
	$('#ghmapDispatchOrder .ghpsb-entry label span').bind('click',function(){
		if( $(this).hasClass('ospanCurcolor') ){
			$(this).removeClass('ospanCurcolor');
			$(this).addClass('ospancolor');
		}else{
			$(this).addClass('ospanCurcolor');
			$(this).removeClass('ospancolor');
		} 
 			makemaplineNumber = 0;
			lnglatarray = [];
			orderstatusarr = [];
			orderclerkidarr = [];
			tempcolorarr = [];
		 
			var temporderarr = [];
			var selectobj = $('#ghmapDispatchOrder .ghpsb-entry label span.ospanCurcolor');
			 $(selectobj).each(function(index){
					var templnglatarr = [];
					templnglatarr.push($(this).attr('num'));
					templnglatarr.push($(this).attr('bizlng'));
					templnglatarr.push($(this).attr('bizlat'));
					templnglatarr.push($(this).attr('receiver_lng'));
					templnglatarr.push($(this).attr('receiver_lat'));
					lnglatarray.push(templnglatarr); 
					
					$(selectobj).each(function(){
						temporderarr.push($(this).val());
					  });
					  orderid = temporderarr.join(','); 
					  orderstatusarr.push($(this).attr('status'));
						if( $(this).attr('clerk_id') > 0){
							orderclerkidarr.push($(this).attr('clerk_id'));
						}
					  
					  
			  });
			 
			  $("#ghpsb-sel-count").text(lnglatarray.length);
				map.clearMap(); 
			  //加载配送员地图位置列表
				loaddispatchlist();

				//加载选中订单勾画地图路线
				loadmakemapline(); 
				return false;
 	});
	
	
}
var tempcolorarr = [];
//加载选中订单勾画地图路线
function loadmakemapline(){ 
 	if(lnglatarray.length > 0){ 
			map.clearMap(); 
		  //加载配送员地图位置列表
			loaddispatchlist(); 
		for(i=0;i<lnglatarray.length;i++){
			var temparrarr = [];
  			tempcolorarr.push(lnglatarray[i][0]);
 			directionReq(lnglatarray[i][1],lnglatarray[i][2],lnglatarray[i][3],lnglatarray[i][4]); 
		} 
		return false; 
	}   
}

//var temorrarr = [];
//汽车路径（起点，终点）
function directionReq(bizlng,bizlat,receiver_lng,receiver_lat){ 
	console.log('=====directionReq======');				
	var requestlink = 'https://restapi.amap.com/v3/direction/driving?origin='+bizlng+','+bizlat+'&destination='+receiver_lng+','+receiver_lat+'&extensions=all&output=json&key='+map_webservice_key+'&callback=makemapline';
	
	console.log(requestlink);	
    $.getScript(requestlink); 
}
//汽车路径（起点，途径点，终点）
function waypointDirectionReq(bizlng,bizlat,waypoints_lng,waypoints_lat,receiver_lng,receiver_lat){ 
					
	var requestlink = 'https://restapi.amap.com/v3/direction/driving?origin='+bizlng+','+bizlat+'&destination='+receiver_lng+','+receiver_lat+'&waypoints='+waypoints_lng+','+waypoints_lat+'&extensions=all&output=json&key='+map_webservice_key+'&callback=makemapline';
    $.getScript(requestlink); 
}

//在地图中画路线
function makemapline(datas){
	 console.log('======makemapline在地图中画路线=========');
	if(tempcolorarr.length > 0){
		var aa = tempcolorarr[makemaplineNumber];
	}else{
		var aa = 0;
	}
  	  
    	if( datas.status == 1 && datas.info == 'OK' ){
		//temorrarr.push(datas.route);
		var origin = datas.route.origin; //取
		var destination = datas.route.destination; //送
		//标记取餐位置
		var originlnglatarr = origin.split(',');
		var originarr = new Array(); 
		originarr.push(originlnglatarr[0]);
		originarr.push(originlnglatarr[1]);
		if( is_clerk_dir == false ){  
			 var htmls = '<div class="ghpsb_market_bm"><img src="'+siteurl+'/public/dispatch/images/map-point-shadow.png"><div class="ghpsb_css_marker" style="background-color: '+color[aa]+';"></div><div class="ghpsb_marker_text">取</div></div>';
			 marker = new AMap.Marker({
				 position: originarr, //    [116.405467, 39.907761]
				 content : htmls
			  });  
			 marker.setMap(map); 
		}	
		//标记送餐位置
		var destinationlnglatarr = destination.split(',');
		var destinationarr = new Array(); 
		destinationarr.push(destinationlnglatarr[0]);
		destinationarr.push(destinationlnglatarr[1]);
		if( is_clerk_dir == false ){ 
			var htmls_sc = '<div class="ghpsb_market_bm"><img src="'+siteurl+'/public/dispatch/images/map-point-shadow.png"><div class="ghpsb_css_marker" style="background-color:'+color[aa]+';"></div><div class="ghpsb_marker_text">送</div></div>';
			var marker_sc = new AMap.Marker({
				position: destinationarr, //    [116.405467, 39.907761]
				content : htmls_sc
			});  
			marker_sc.setMap(map); 
		}
	//画取餐到送餐路线
	var stepsarr = datas.route.paths[0].steps;
 		var combination =  new Array();
		combination.push(originarr);
 		for(i=0;i<stepsarr.length;i++){
			var polylinearr = [];
			var tempolyline = stepsarr[i].polyline;
			var polylinearr = tempolyline.split(";");
			
			for(j=0;j<1;j++){
				var arr =   new Array();;
				var rr = polylinearr[j];
				var aaa = rr.split(",");
				arr.push(aaa[0]);
				arr.push(aaa[1]);
   				combination.push(arr);
				 
 			}
		}  
	combination.push(destinationarr);
  	 var polyline = new AMap.Polyline({
        path: combination,          //设置线覆盖物路径
        strokeColor: color[aa], //线颜色
        strokeOpacity: 1,       //线透明度
        strokeWeight: 5,        //线宽
        strokeStyle: "solid",   //线样式
        strokeDasharray: [10, 5] //补充线样式
    });
    polyline.setMap(map);
  	makemaplineNumber++;
 		
	}
	
	
	
}
function init(){
	
		//初始化地图对象，加载地图 初始化加载地图时，若center及level属性缺省，地图默认显示用户当前城市范围
map = new AMap.Map('dispatchMap', {
	resizeEnable: true,
	//view:new AMap.View2D(),
	zoom: 14,
});

setmapstyle('blue_night'); //默认设置为夜色版
AMap.plugin(['AMap.ToolBar','AMap.Scale','AMap.Riding'],function(){ 
	setmapstyle('blue_night'); //默认设置为夜色版
	var scale = new AMap.Scale({
        visible: true
    });
    toolBar = new AMap.ToolBar({
        visible: true,
		position: 'RB'
    });  
    map.addControl(scale);
    map.addControl(toolBar); 
	toolBar.hideDirection(); 
	 
})
 
	
}
//初始化地图
function Initializationmap(){
	
	


//加载选中订单勾画地图路线
loadmakemapline(); 
 
  
bindclickmapmore();	
	
	 
	
}


//加载配送员地图位置列表
function loaddispatchlist(){
	
	var clerklist  = ajaxback(siteurl+'/admin/dispatch/dispatchlist?datatype=json',{'allianceid':""+allianceid+"",'stationid':""+stationid+"",'psgroupid':""+psgroupid+"",'bizdistrictid':""+bizdistrictid+""}); 
	  if(clerklist.flag == false){
		  var clerkworklist = clerklist.content.clerklist.lists.worklist.list;
 		  for( i=0;i<clerkworklist.length;i++ ){ 
			  var posilnglat = clerkworklist[i].posilnglat; 
			    if( posilnglat != '' && posilnglat != ',' && posilnglat != '0.0,0.0'){
					  var lnglatarray = posilnglat.split(","); 
					  var temparray = new Array(); 
					  temparray.push(lnglatarray[0]);
					  temparray.push(lnglatarray[1]);
					  var knightlnglat = temparray;
					  var knigclerkid = clerkworklist[i].clerkid;
					  var knightname = clerkworklist[i].clerkname;
					  var workLastTime = clerkworklist[i].workLastTime;
					  var psingOrderCount = clerkworklist[i].psingOrderCount;
					  var htmls = '<div  onclick="PopupinfoWindow(\''+knigclerkid+'\',\''+posilnglat+'\',\''+knightname+'\',1);" onmouseover="mouseClerkInfo(\''+knigclerkid+'\',\''+posilnglat+'\',\''+knightname+'\');"  onmouseout="mouseOutClerkInfo(\''+knigclerkid+'\',\''+posilnglat+'\',\''+knightname+'\');" class="amap-marker-content" style="opacity: 1;"><div class="bm_marker_container"><div class="bm_circle_marker normal-color"></div><div class="marker-text-container"><div class="marker-text-back"></div><span class="marker-span" >'+workLastTime+' '+knightname+'['+psingOrderCount+'单]</span></div></div></div>';
					  marker = new AMap.Marker({
							position: temparray, //    [116.405467, 39.907761]
							content : htmls
					  });  
					 marker.setMap(map);
			   }
		  }
		  bindclickmapmore();
	  } 
	
}

function bindclickmapmore(){
	  
}
//鼠标移动地图配送员上
function mouseClerkInfo(clerkid,knightlnglat,knightname){
	if( moveflag == true && lockclick() ){
		//moveflag = false;
		makemaplineNumber = 1;
		for(i=0;i<20;i++){
					tempcolorarr.push(i);
		 }
 		tempcolorarr = [];
		var htmls_canvas_back = '<canvas id="ghpsb_canvas_back"  width="'+windowwidth+'"  height="'+windowheight+'"    style="width:100%;height:100%; position:absolute;top:0;left:0; z-index:99;background-color:black;opacity:0.3;"></canvas>';
		$('.amap-layers').append(htmls_canvas_back); 
		
		var htmls_canvas_forarc = '<canvas id="ghpsb_canvas_forarc" width="'+windowwidth+'"  height="'+windowheight+'"  style=" width:100%;height:100%;position:absolute;top:0;left:0; z-index:100;"></canvas>';
		 $('.amap-layers').append(htmls_canvas_forarc); 
		setTimeout("mouseClerkInfo_func('"+clerkid+"','"+knightlnglat+"','"+knightname+"')",200); 
		 
 	}
}
function mouseClerkInfo_func(clerkid,knightlnglat,knightname){
 	 //moveflag = true;
	 if( moveflag == true ){
		PopupinfoWindow(clerkid,knightlnglat,knightname,0);
	 }
	 return false;
}
//鼠标移走地图配送员上
function mouseOutClerkInfo(){ 
 	if( moveflag == true  && lockclick() ){
		moveflag = false; 
		is_clerk_dir =false;
		makemaplineNumber = 0;
 		tempcolorarr = [];
		if(lnglatarray.length > 0){ 
			for(i=0;i<lnglatarray.length;i++){
				var temparrarr = [];
				tempcolorarr.push(lnglatarray[i][0]);
			} 
 		} 
		$("#ghpsb_canvas_back").remove();
		$("#ghpsb_canvas_forarc").remove();
		setTimeout("mouseOutClerkInfo_func()",200);  
	}
}
function mouseOutClerkInfo_func(){ 
		moveflag = true; 
		//加载配送员地图位置列表
		//loaddispatchlist(); 
		//加载选中订单勾画地图路线
		loadmakemapline();   
		return false;
}
 //切换地图 常规版和夜色版 
	 function setmapstyle(style) {
		  map.setMapStyle(style);
		  if( style == 'normal' ){
			 $("#dispatchMap").removeClass('map-blue-night');
		  }else{
			$("#dispatchMap").addClass('map-blue-night');
		  }
	 }

	//
	function PopupinfoWindow(clerkid,knightlnglat,knightname,type){ 
	
		console.log('====type====');
		console.log(type);

				if(type==1){
					moveflag = false;
					makemaplineNumber = 1;
				}else{
					makemaplineNumber = 1;
				}
			 


	
	
				tempcolorarr = []; 
				for(i=0;i<20;i++){
					tempcolorarr.push(i);
				} 
			    is_clerk_dir = true;
 		 setTimeout("PopupinfoWindow_func('"+clerkid+"','"+knightlnglat+"','"+knightname+"','"+type+"')",100);
	}  
function PopupinfoWindow_func(clerkid,knightlnglat,knightname,type){
	 
 				var tempc  = ajaxback(siteurl+'/admin/dispatch/clerkorderlist3?datatype=json',{'clerk_id':clerkid});
				if(tempc.flag == false){
					 
				}else{
					diaerror(tempc.content);
				} 
				var clerklnglatarr = knightlnglat.split(',');
				clerklnglatarr.push(clerklnglatarr[0]);
				clerklnglatarr.push(clerklnglatarr[1]);
				var clerkinfo = tempc.content.clerkinfo.info;
				var orderlist = tempc.content.clerkodlst.templist;
				var statusname = tempc.content.clerkodlst.statusname;
		 
	if( orderlist.length > 0 ){
		     	
					
			for(i=0;i<orderlist.length;i++){ 
 				if( orderlist[i].status > 3 && orderlist[i].status < 6   ){  // 取 送
				
					var origin = orderlist[i].bizlng+','+orderlist[i].bizlat; //取
					var destination = orderlist[i].receiver_lng+','+orderlist[i].receiver_lat; //送
 					//标记取餐位置
					var originlnglatarr = origin.split(',');
					var originarr = new Array(); 
				    originarr.push(originlnglatarr[0]);
					originarr.push(originlnglatarr[1]);
					var htmls = '<div class="ghpsb_market_bm"><img src="'+siteurl+'/public/dispatch/images/map-point-shadow.png"><div class="ghpsb_css_marker" style="background-color: '+color[i+1]+';"></div><div class="ghpsb_marker_text">取</div></div>';
					marker = new AMap.Marker({
							position: originarr, //    [116.405467, 39.907761]
							content : htmls
					});  
					 marker.setMap(map);  
					 
					 
					 
					//根据起点、终点勾画路线
				/* 	var combination = new Array();
					combination.push(clerklnglatarr);
					combination.push(originlnglatarr);
					 var polyline = new AMap.Polyline({
						path: combination,          //设置线覆盖物路径
						strokeColor: color[i+1], //线颜色
						strokeOpacity: 1,       //线透明度
						strokeWeight: 5,        //线宽
						strokeStyle: "solid",   //线样式
						strokeDasharray: [10, 5] //补充线样式
					});	
					polyline.setMap(map); */
					
 					//标记送餐位置
					var destinationlnglatarr = destination.split(',');
					var destinationarr = new Array(); 
					destinationarr.push(destinationlnglatarr[0]);
					destinationarr.push(destinationlnglatarr[1]);
					var htmls_sc = '<div class="ghpsb_market_bm"><img src="'+siteurl+'/public/dispatch/images/map-point-shadow.png"><div class="ghpsb_css_marker" style="background-color:'+color[i+1]+';"></div><div class="ghpsb_marker_text">送</div></div>';
					var marker_sc = new AMap.Marker({
						position: destinationarr, //    [116.405467, 39.907761]
						content : htmls_sc
					});  
					marker_sc.setMap(map);
					
					
					//根据起点、终点勾画路线
					/* var combination = new Array();
					combination.push(originlnglatarr);
					combination.push(destinationarr);
					 var polyline = new AMap.Polyline({
						path: combination,          //设置线覆盖物路径
						strokeColor: color[i+1], //线颜色
						strokeOpacity: 1,       //线透明度
						strokeWeight: 5,        //线宽
						strokeStyle: "solid",   //线样式
						strokeDasharray: [10, 5] //补充线样式
					});
					polyline.setMap(map); */
					//根据起点、途经点、终点勾画路线
 			    waypointDirectionReq(clerklnglatarr[0],clerklnglatarr[1],originlnglatarr[0],originlnglatarr[1],destinationlnglatarr[0],destinationlnglatarr[1]);
				
				
 				 
 				}else if( orderlist[i].status == 6 ){// 送
					var destination = orderlist[i].receiver_lng+','+orderlist[i].receiver_lat; //送
					//标记送餐位置
					var destinationlnglatarr = destination.split(',');
					var destinationarr = new Array(); 
					destinationarr.push(destinationlnglatarr[0]);
					destinationarr.push(destinationlnglatarr[1]);
					var htmls_sc = '<div class="ghpsb_market_bm"><img src="'+siteurl+'/public/dispatch/images/map-point-shadow.png"><div class="ghpsb_css_marker" style="background-color:'+color[i+1]+';"></div><div class="ghpsb_marker_text">送</div></div>';
					var marker_sc = new AMap.Marker({
						position: destinationarr,  
						content : htmls_sc
					});  
					marker_sc.setMap(map); 
					
					
					//根据起点、终点勾画路线
				/* 	var combination = new Array();
					combination.push(clerklnglatarr);
					combination.push(destinationarr);
					 var polyline = new AMap.Polyline({
						path: combination,          //设置线覆盖物路径
						strokeColor: color[i+1], //线颜色
						strokeOpacity: 1,       //线透明度
						strokeWeight: 5,        //线宽
						strokeStyle: "solid",   //线样式
						strokeDasharray: [10, 5] //补充线样式
					});
					polyline.setMap(map);
					 */
				 directionReq(clerklnglatarr[0],clerklnglatarr[1],destinationlnglatarr[0],destinationlnglatarr[1]);
				 
				 
				}
				
				
				
			}
			
			
	
			
			 
		if(type==1){	
			
			var operationordertype = 0; //1指派 2改派 3只有一个订单订单属于他 4多订单中有不同的操作，无法操作订单
			
			
			console.log('====operationordertype3333333333====');
			console.log(orderstatusarr);
			
			
			if( orderstatusarr.length > 1){
				if( (jQuery.inArray("1", orderstatusarr) >= 0 ||  jQuery.inArray("2", orderstatusarr) >= 0)  &&  ( jQuery.inArray("3", orderstatusarr) >= 0 ||  jQuery.inArray("4", orderstatusarr) >= 0  ||  jQuery.inArray("5", orderstatusarr) >= 0  ||  jQuery.inArray("6", orderstatusarr) >= 0  )  ){
					operationordertype = 4; //订单中有不同状态的没法操作
				}else if(  jQuery.inArray("3", orderstatusarr) >= 0 ||  jQuery.inArray("4", orderstatusarr) >= 0  ||  jQuery.inArray("5", orderstatusarr) >= 0  ||  jQuery.inArray("6", orderstatusarr) >= 0  ){
					operationordertype = 2; //改派 
				}else if(  jQuery.inArray("1", orderstatusarr) >= 0 ||  jQuery.inArray("2", orderstatusarr) >= 0  ){
					operationordertype = 1; //指派 
				}
			}else{
				if( jQuery.inArray("1", orderstatusarr) >= 0 ||  jQuery.inArray("2", orderstatusarr) >= 0   ){
					operationordertype = 1; //指派
				}else if(  jQuery.inArray("3", orderstatusarr) >= 0 ||  jQuery.inArray("4", orderstatusarr) >= 0  ||  jQuery.inArray("5", orderstatusarr) >= 0  ||  jQuery.inArray("6", orderstatusarr) >= 0  ){
					if(  jQuery.inArray(clerkid, orderclerkidarr) >= 0  ){
						operationordertype = 3; //订单已属于他
					}else{
						operationordertype = 2; //改派
					}
				}
			} 
			operationordertype = 2; //改派
			console.log('====实例化信息窗体====');
			console.log(operationordertype);
			
		//实例化信息窗体
			content=[];
			var contenthtml = '<div ><div class="dispatchinfo"> ';
				contenthtml += '<div class="info"><div class="info-top" style="opacity: 0.8; background-color: rgb(0, 171, 228);"> ';
				contenthtml += '<div style="color: rgb(255, 255, 255);">'+clerkinfo.clerkname+'&nbsp;['+clerkinfo.clerkphone+']&nbsp;['+orderlist.length+']</div> ';
				contenthtml += '<span onclick="closeInfoWindow();" aria-hidden="true" style="position: absolute; right: 0px; color: white; margin: 2px 10px 0px 0px; font-size: 20px; cursor: pointer;">×</span></div> ';
				contenthtml += '<div class="info-middle" style="background-color: white;"> ';
				contenthtml += ' <table class="table table-bordered table-striped"><thead> ';
				contenthtml += ' <tr><th class="text-center">状态</th><th class="text-center">商家</th><th class="text-center">收货地址</th><th class="text-center">金额</th></tr></thead><tbody> ';
				for(i=0;i<orderlist.length;i++){
					contenthtml += '<tr><td>'+orderlist[i].statusname+'</td><td>'+orderlist[i].bizaddress+'</td><td>'+orderlist[i].receiver_address+'</td><td>'+orderlist[i].goodscost+'</td></tr> ';
				} 
				contenthtml += '</tbody></table></div> ';
				if( operationordertype == 1 ){
					contenthtml += '<div class="info-appointRider"  ><button onclick="assignOrder('+clerkinfo.clerkid+');" clerkid="'+clerkinfo.clerkid+'" class="ghbtn ghbtn-default btn-repay reassignOrder">指派给"'+clerkinfo.clerkname+'"</button></div>';
				}else if( operationordertype == 2 ){
					contenthtml += '<div class="info-appointRider"  ><button onclick="reassignOrder('+clerkinfo.clerkid+');" clerkid="'+clerkinfo.clerkid+'" class="ghbtn ghbtn-default btn-repay reassignOrder">改派给"'+clerkinfo.clerkname+'"</button></div>';
				}else if( operationordertype == 3 ){
					contenthtml += '<div class="info-appointRider"  ><button   clerkid="'+clerkinfo.clerkid+'" class="ghbtn ghbtn-default btn-repay reassignOrder">订单已属于他</button></div>';
				}
				
				contenthtml += '<div  class="info-bottom" style="position: relative; top: 0px; margin: 0px auto;"><img src="http://webapi.amap.com/images/sharp.png"></div></div></div></div>';  
			content.push(contenthtml);
			var infoWindow = new AMap.InfoWindow({
				isCustom: true,  //使用自定义窗体
				//content: createInfoWindow(title,content.join("<br>")),
				 content: content.join("<br>")  ,
				offset: new AMap.Pixel(20, -70)//-113, -140
			}); 
		 var temparray = knightlnglat.split(",");
 		 infoWindow.open(map,temparray);
		} 
		 
	}else{
		if(type==1){	
			
			var operationordertype = 0; //1指派 2改派 3只有一个订单订单属于他 4多订单中有不同的操作，无法操作订单


			console.log('====orderstatusarr====');
			console.log(jQuery.inArray("1", orderstatusarr));
			
			if( orderstatusarr.length > 1){
				if( (jQuery.inArray("1", orderstatusarr) >= 0 ||  jQuery.inArray("2", orderstatusarr) >= 0)  &&  ( jQuery.inArray("3", orderstatusarr) >= 0 ||  jQuery.inArray("4", orderstatusarr) >= 0  ||  jQuery.inArray("5", orderstatusarr) >= 0  ||  jQuery.inArray("6", orderstatusarr) >= 0  )  ){
					operationordertype = 4; //订单中有不同状态的没法操作
				}else if(  jQuery.inArray("3", orderstatusarr) >= 0 ||  jQuery.inArray("4", orderstatusarr) >= 0  ||  jQuery.inArray("5", orderstatusarr) >= 0  ||  jQuery.inArray("6", orderstatusarr) >= 0  ){
					operationordertype = 2; //改派 
				}else if(  jQuery.inArray("1", orderstatusarr) >= 0 ||  jQuery.inArray("2", orderstatusarr) >= 0  ){
					operationordertype = 1; //指派 
				}
			}else{
				if( jQuery.inArray("1", orderstatusarr) >= 0 ||  jQuery.inArray("2", orderstatusarr) >= 0   ){
					operationordertype = 1; //指派
				}else if(  jQuery.inArray("3", orderstatusarr) >= 0 ||  jQuery.inArray("4", orderstatusarr) >= 0  ||  jQuery.inArray("5", orderstatusarr) >= 0  ||  jQuery.inArray("6", orderstatusarr) >= 0  ){
					if(  jQuery.inArray(clerkid, orderclerkidarr) >= 0  ){
						operationordertype = 3; //订单已属于他
					}else{
						operationordertype = 2; //改派
					}
				}
			} 
			
			console.log('====operationordertype22222222222222====');
			console.log(operationordertype);
			
			
		//实例化信息窗体
			content=[];
			var contenthtml = '<div ><div class="dispatchinfo"> ';
				contenthtml += '<div class="info"><div class="info-top" style="opacity: 0.8; background-color: rgb(0, 171, 228);"> ';
				contenthtml += '<div style="color: rgb(255, 255, 255);">'+clerkinfo.clerkname+'&nbsp;['+clerkinfo.clerkphone+']&nbsp;['+orderlist.length+']</div> ';
				contenthtml += '<span onclick="closeInfoWindow();" aria-hidden="true" style="position: absolute; right: 0px; color: white; margin: 2px 10px 0px 0px; font-size: 20px; cursor: pointer;">×</span></div> ';
				contenthtml += '<div class="info-middle" style="background-color: white;"> ';
				contenthtml += ' <table class="table table-bordered table-striped"><thead> ';
				contenthtml += ' <tr><th class="text-center">状态</th><th class="text-center">商家</th><th class="text-center">收货地址</th><th class="text-center">金额</th></tr></thead><tbody> ';
				for(i=0;i<orderlist.length;i++){
					contenthtml += '<tr><td class="tudou">'+orderlist[i].statusname+'</td><td>'+orderlist[i].bizaddress+'</td><td>'+orderlist[i].receiver_address+'</td><td>'+orderlist[i].goodscost+'</td></tr> ';
				} 
				contenthtml += '</tbody></table></div> ';
				if( operationordertype == 1 ){
					contenthtml += '<div class="info-appointRider"  ><button onclick="assignOrder('+clerkinfo.clerkid+');" clerkid="'+clerkinfo.clerkid+'" class="ghbtn ghbtn-default btn-repay reassignOrder">指派给"'+clerkinfo.clerkname+'"</button></div>';
				}else if( operationordertype == 2 ){
					contenthtml += '<div class="info-appointRider"  ><button onclick="reassignOrder('+clerkinfo.clerkid+');" clerkid="'+clerkinfo.clerkid+'" class="ghbtn ghbtn-default btn-repay reassignOrder">改派给"'+clerkinfo.clerkname+'"</button></div>';
				}else if( operationordertype == 3 ){
					contenthtml += '<div class="info-appointRider"  ><button   clerkid="'+clerkinfo.clerkid+'" class="ghbtn ghbtn-default btn-repay reassignOrder">订单已属于他</button></div>';
				}
				
				contenthtml += '<div  class="info-bottom" style="position: relative; top: 0px; margin: 0px auto;"><img src="http://webapi.amap.com/images/sharp.png"></div></div></div></div>';  
			content.push(contenthtml);
			var infoWindow = new AMap.InfoWindow({
				isCustom: true,  //使用自定义窗体
				//content: createInfoWindow(title,content.join("<br>")),
				 content: content.join("<br>")  ,
				offset: new AMap.Pixel(20, -70)//-113, -140
			}); 
		 var temparray = knightlnglat.split(",");
 		 infoWindow.open(map,temparray);
		} 
		
	}
	
	
		 
 		 return false;
}	
	
	
//指派订单
function assignOrder(clerkid){
	operationOrder_fun(clerkid,1);
}	
//改派订单	
function reassignOrder(clerkid){
 	operationOrder_fun(clerkid,2);		
}	
function operationOrder_fun(clerkid,type){
		var orderids = orderid;
 					$("#GH_POPUP").toggle();
					$("#operationPopup").html('');
					$("#operationPopup").toggle();
					var content = htmlback(siteurl+'/admin/dispatch/ajaxoperationOrder',{'orderids':orderids,'clerkid':clerkid,'type':type});
					if(content.flag == false){
						var datacontent =  $.trim(content.content); 
						if( datacontent == ''  ){ 
						}else{
							  $('#operationPopup').html(datacontent); 
							   bindclickoperation();
						} 
					} 
					
					
 					return false;
			 
	
}
function bindclickoperation(){
	//关闭或取消 派单操作
	$(".cancelOperation").bind('click',function(){
		$("#GH_POPUP").toggle();
		$("#operationPopup").html('');
		$("#operationPopup").toggle();
	});
	//选择派单原因(如选择其它需在input中输入原因)
	$('.reasonSel input[name="reason"]').bind('click',function(){
		var checkedreasonid = $(this).val();
 		if( checkedreasonid == 0 ){
			$('#re-reason').removeAttr("readOnly");
			$('#re-reason').css({'backgroundColor':'#ffffff'});
		}else{
			$('#re-reason').val('');
			$('#re-reason').attr({ readonly: 'true' });
			$('#re-reason').css({'backgroundColor':'#e8e8e8'});
		}
	});
	//确认指派
	$("#sureAssign").bind('click',function(){
		operationOrder(1);
		return false;	 
	});
	//确认改派
	$("#sureReAssign").bind('click',function(){
		operationOrder(2);
		return false;	 
	});
	function operationOrder(type){ 
		var operation_orderids = $('input[name="operation_orderids"]').val();
		var operation_clerkid = $('input[name="operation_clerkid"]').val();
		var checkedreasonid = $('.reasonSel input[name="reason"]:checked').val();
		var reasonContent = '';
		if( checkedreasonid > 0 ){
			reasonContent = $('.reasonSel input[name="reason"]:checked').attr('reason');
		}else{
			reasonContent = $('#re-reason').val();
		}
 		if( reasonContent == '' || reasonContent == undefined  ){
 			diaerror("请选择原因或者填写其它原因");
 			return false;
		}
		var templingk = siteurl+'/admin/dispatch/operationorders?random=@random@&datatype=json';
		templingk = templingk.replace('@random@', 1+Math.round(Math.random()*1000))
		var tempc = ajaxback(templingk,{'orderids':operation_orderids,'clerkid':operation_clerkid,'reason':reasonContent,'operationtype':type}); 
		if(tempc.flag ==false){
				if( type == 2){
					diaerror("改派成功");
				}else{
					diaerror("指派成功");
				}
 			    $("#GH_POPUP").toggle();
				$("#operationPopup").html('');
				$("#operationPopup").toggle();
				loaddispatchOrderList();
		}else{
			diaerror(tempc.content);
		}
	
	}
	
	
} 

 function setZoomAndCenter(posilnglat){
	 var temparray = posilnglat.split(",");
	 map.setCenter(temparray);
 } 
 function closeInfoWindow(){
		map.clearInfoWindow();
		map.clearMap(); 
		$("#ghpsb_canvas_back").remove();
		$("#ghpsb_canvas_forarc").remove();
		moveflag = true;
		is_clerk_dir =false;
		makemaplineNumber = 0;
 		 tempcolorarr = [];
		//加载配送员地图位置列表
		loaddispatchlist(); 
		//加载选中订单勾画地图路线
		loadmakemapline(); 
		return false;
 }
 
 var click_button = false;
function doubleclick(){
	click_button = false;
}
function lockclick(){
	 if(click_button == false){
			click_button = true;
			setTimeout("doubleclick()", 400); 
			return true;
	 }else{
		 return false;
	 }
} 
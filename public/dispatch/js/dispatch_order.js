/**
@param 调度-》调度中心 js
@author zem
*/

$(function(){
alliance(); 
//加载订单列表 
 loadorderlist(); 
loadordertotal();
loaddispatchlist();
});

function alliance(){
    var url =siteurl+'/admin/dispatch/get_clerk_select?random=@random@';
    $.ajax({
        type: 'post',
        async:false,
        data:{'allianceid':$('#alliance1 option:selected').val(),'requesttype':1},
        url: url.replace('@random@', 1+Math.round(Math.random()*1000)),
        dataType: 'html',
        success: function(content) {
            $('#showalldate').html(content);
            $('#station1').html($('#new_station').html());
            $('#psgroup').html($('#new_psgroup').html());
            $('#bizdistrict').html($('#new_bizdistrict').html());
			
			select_aspbInfo();
			
        },
        error: function(content) {

        }
    });
}


function station(){
    var url =siteurl+'/admin/dispatch/get_clerk_select?random=@random@';
    $.ajax({
        type: 'post',
        async:false,
        data:{'allianceid':$('#alliance1 option:selected').val(),'stationid':$('#station1 option:selected').val()},
        url: url.replace('@random@', 1+Math.round(Math.random()*1000)),
        dataType: 'html',
        success: function(content) {
            $('#showalldate').html(content);
            $('#psgroup').html($('#new_psgroup').html());
            $('#bizdistrict').html($('#new_bizdistrict').html());
			
			select_aspbInfo();
			
        },
        error: function(content) {

        }
    });
}



//应该要改改
function loaddispatchlist(){
	$('#dispatchList').html('');  
	var content = htmlback(siteurl+'/admin/dispatch/getDispatchLists',{'allianceid':""+allianceid+"",'stationid':""+stationid+"",'psgroupid':""+psgroupid+"",'bizdistrictid':""+bizdistrictid+"",'starttime':$('input[name="starttime"]').val(),'endtime':$('input[name="starttime"]').val()}); 
 	if(content.flag == false){
		var datacontent =  $.trim(content.content); 
		if( datacontent == ''  ){ 
		}else{
 			  $('#dispatchList').append(datacontent);  
		} 
	}else{
	    console.log("加载失败");
	} 
	bindclerkmose();
	selectDispatchStatus();
}
//筛选/更新配送员状态（全部、在岗、离岗）
function selectDispatchStatus(){
	var sel_dispatchStatus = $('select[name="dispatchStaList"]').val();
 	var sel_counts = $('#dispatchStaList option').eq(sel_dispatchStatus).attr('counts');
 	
	var real_workCounts = $("#workListBox").attr('counts');
	var real_noworkCounts = $("#noworkListBox").attr('counts');
	var all_realcounts = Number(real_workCounts)+Number(real_noworkCounts);
	 
	
	if( sel_dispatchStatus == 0 ){
		$("#workListBox").show();
		$("#noworkListBox").show(); 
	}
	if( sel_dispatchStatus == 1 ){
		$("#workListBox").show();
		$("#noworkListBox").hide(); 
	}
	if( sel_dispatchStatus == 2 ){
		$("#workListBox").hide();
		$("#noworkListBox").show(); 
	}
	
	if( sel_counts != all_realcounts ){
			$('select[name="dispatchStaList"]').find('option').eq(0).attr('counts',all_realcounts)
			$('select[name="dispatchStaList"]').find('option').eq(0).text("全部（"+all_realcounts+"）");
	}
	if( sel_counts != real_workCounts ){
			$('select[name="dispatchStaList"]').find('option').eq(1).attr('counts',real_workCounts)
			$('select[name="dispatchStaList"]').find('option').eq(1).text("在岗（"+real_workCounts+"）");
	}
	if( sel_counts != real_noworkCounts ){
			$('select[name="dispatchStaList"]').find('option').eq(2).attr('counts',real_noworkCounts)
			$('select[name="dispatchStaList"]').find('option').eq(2).text("离岗（"+real_noworkCounts+"）");
 }
	
} 
//加载 不同订单状态统计数量
function loadordertotalnotime(){
	 
	 
	  var templingk = siteurl+'/admin/dispatch/ordertj?random=@random@&datatype=json';
		templingk = templingk.replace('@random@', 1+Math.round(Math.random()*1000));
		if( query == '' || query == undefined){
			bizid = 0;
			bizname = '';
		}
		var tempc = ajaxback(templingk,{'starttime':""+starttime+"",'endtime':""+starttime+"",'qtype':""+qtype+"",'query':""+query+"",'bizid':""+bizid+"",'bizname':""+bizname+"",'allianceid':""+allianceid+"",'stationid':""+stationid+"",'psgroupid':""+psgroupid+"",'bizdistrictid':""+bizdistrictid+""}); 
		if(tempc.flag ==false){
 			 var tjarray = tempc.content.tj; 
			$('#statusOrderMenu li.type_neworder').find('a font').text(tjarray.neworder==0?'':tjarray.neworder);
			
			
			var handorderNum = $('#statusOrderMenu li.type_handorder').find('a font').text();
			if( handorderNum < tjarray.handorder){
				 palywav();  //播放文件
			} 
			$('#statusOrderMenu li.type_handorder').find('a font').text(tjarray.handorder==0?'':tjarray.handorder); 
			$('#statusOrderMenu li.type_sendnosure').find('a font').text(tjarray.sendnosure==0?'':tjarray.sendnosure);
			$('#statusOrderMenu li.type_hasorder').find('a font').text(tjarray.hasorder==0?'':tjarray.hasorder);
			$('#statusOrderMenu li.type_hasPickup').find('a font').text(tjarray.hasPickup==0?'':tjarray.hasPickup);
			$('#statusOrderMenu li.type_served').find('a font').text(tjarray.served==0?'':tjarray.served);
			$('#statusOrderMenu li.type_hascancel').find('a font').text(tjarray.hascancel==0?'':tjarray.hascancel);
			$('#statusOrderMenu li.type_allorder').find('a font').text(tjarray.allorder==0?'':tjarray.allorder);
		}else{
			diaerror(tempc.content);
		}
		 
		
}
//加载 不同订单状态统计数量
function loadordertotal(){
	 
	 
	  var templingk = siteurl+'/admin/dispatch/ordertj?random=@random@&datatype=json';
		templingk = templingk.replace('@random@', 1+Math.round(Math.random()*1000));
		if( query == '' || query == undefined){
			bizid = 0;
			bizname = '';
		}
		var tempc = ajaxback(templingk,{'starttime':""+starttime+"",'endtime':""+starttime+"",'qtype':""+qtype+"",'query':""+query+"",'bizid':""+bizid+"",'bizname':""+bizname+"",'allianceid':""+allianceid+"",'stationid':""+stationid+"",'psgroupid':""+psgroupid+"",'bizdistrictid':""+bizdistrictid+""}); 
		if(tempc.flag ==false){
 			 var tjarray = tempc.content.tj; 
			$('#statusOrderMenu li.type_neworder').find('a font').text(tjarray.neworder==0?'':tjarray.neworder);
			
			
			var handorderNum = $('#statusOrderMenu li.type_handorder').find('a font').text();
			if( handorderNum < tjarray.handorder){
				 palywav();  //播放文件
			} 
			$('#statusOrderMenu li.type_handorder').find('a font').text(tjarray.handorder==0?'':tjarray.handorder); 
			$('#statusOrderMenu li.type_sendnosure').find('a font').text(tjarray.sendnosure==0?'':tjarray.sendnosure);
			$('#statusOrderMenu li.type_hasorder').find('a font').text(tjarray.hasorder==0?'':tjarray.hasorder);
			$('#statusOrderMenu li.type_hasPickup').find('a font').text(tjarray.hasPickup==0?'':tjarray.hasPickup);
			$('#statusOrderMenu li.type_served').find('a font').text(tjarray.served==0?'':tjarray.served);
			$('#statusOrderMenu li.type_hascancel').find('a font').text(tjarray.hascancel==0?'':tjarray.hascancel);
			$('#statusOrderMenu li.type_allorder').find('a font').text(tjarray.allorder==0?'':tjarray.allorder);
		}else{
			diaerror(tempc.content);
		}
		 setTimeout("loadordertotal()",60000);  
		
}
var playwave =true; //icon_voice_close
var is_palywav = '';
$(function(){
 	if(  $.cookie('is_palywav') == 'is_palywav'    ){
		$(".topFunc .icon_voice").addClass('icon_voice_close');
	}else{
		$(".topFunc .icon_voice").removeClass('icon_voice_close');
	}
});
$(".topFunc .icon_voice").click(function(){
	if( $(this).hasClass('icon_voice_close') ){
		$(this).removeClass('icon_voice_close');
		playwave = true;
		$.cookie('is_palywav', ''); 
	}else{
		$(this).addClass('icon_voice_close');
		playwave = false;
		$.cookie('is_palywav', 'is_palywav'); 
	}
});

//播放铃声通知
function palywav(){
	if(playwave == true){
		
		if(navigator.userAgent.indexOf("Chrome") > -1){  
			$("#statusOrderMenu").append('<audio src="'+siteurl+'/public/dispatch/wave.mp3" type="audio/mp3" autoplay=”autoplay” hidden="true"></audio>');
		}else if(navigator.userAgent.indexOf("Firefox")!=-1){  
			$("#statusOrderMenu").append('<embed src="'+siteurl+'/public/dispatch/wave.mp3" type="audio/mp3" hidden="true" loop="false" mastersound></embed>');
		}else if(navigator.appName.indexOf("Microsoft Internet Explorer")!=-1 && document.all){ 
			$("#statusOrderMenu").append('<object classid="clsid:22D6F312-B0F6-11D0-94AB-0080C74C7E95"><param name="AutoStart" value="1" /><param name="Src" value="'+siteurl+'/public/dispatch/wave.mp3" /></object>');
		}else if(navigator.appName.indexOf("Opera")!=-1){ 
			$("#statusOrderMenu").append('<embed src="'+siteurl+'/public/dispatch/wave.mp3" type="audio/mpeg" loop="false"></embed>');
		}else{ 
			$("#statusOrderMenu").append('<embed src="'+siteurl+'/public/dispatch/wave.mp3" type="audio/mp3" hidden="true" loop="false" mastersound></embed>'); 
		} 
	}
 
} 





//加载订单列表
function loadorderlist(){
 	//loadordertotal();
    page =1;
    $('#orderlist').html('加载中...');
	if( query == '' || query == undefined){
		bizid = 0;
		bizname = '';
	}

	/*
	var content = htmlback(siteurl+'/admin/dispatch/orderlist',{'i_type':""+i_type+"",'starttime':""+starttime+"",'endtime':""+endtime+"",'qtype':""+qtype+"",'query':""+query+"",'bizid':""+bizid+"",'bizname':""+bizname+"",'allianceid':""+allianceid+"",'stationid':""+stationid+"",'psgroupid':""+psgroupid+"",'bizdistrictid':""+bizdistrictid+""});
 	if(content.flag == false){
		var datacontent =  $.trim(content.content); 
		
		console.log('========datacontent=========');
		console.log(datacontent);
				
		if( datacontent == ''  ){ 
		}else{
 			  $('#orderlist').html(datacontent);  
			  bindclickorderlist();
		} 
	}else{
	    console.log("加载失败");
	} 
	
	  
	 
	
	var ajaxurl = siteurl+'/admin/dispatch/orderlist?random=@random@'; 
    $.ajax({
        type: 'post',
        async:false,
        data:{'allianceid':$('#alliance1 option:selected').val(),'requesttype':1},
        url: url.replace('@random@', 1+Math.round(Math.random()*1000)),
        dataType: 'html',
        success: function(content) {
            $('#showalldate').html(content);
            $('#station1').html($('#new_station').html());
            $('#psgroup').html($('#new_psgroup').html());
            $('#bizdistrict').html($('#new_bizdistrict').html());
			
			select_aspbInfo();
			
        },
        error: function(content) {

        }
    });
	*/ 
	
  var ajaxurl = siteurl+'/admin/dispatch/orderlist?random=@random@'; 
	$.ajax({
       type: 'POST',
       async:false,
       url: ajaxurl.replace('@random@', 1+Math.round(Math.random()*1000)),
       data: {'i_type':""+i_type+"",'starttime':""+$('input[name="starttime"]').val()+"",'endtime':""+$('input[name="starttime"]').val()+"",'qtype':""+qtype+"",'query':""+query+"",'bizid':""+bizid+"",'bizname':""+bizname+"",'allianceid':""+allianceid+"",'stationid':""+stationid+"",'psgroupid':""+psgroupid+"",'bizdistrictid':""+bizdistrictid+""},
      dataType: 'html',
	  success: function(content) {  
				var datacontent =  $.trim(content); 
				
				//console.log('========datacontent=========');
				//console.log(datacontent);
				
				
				if( datacontent == ''  ){ 
				}else{
					  $('#orderlist').html(datacontent);  
					  bindclickorderlist();
				} 
	  },
      error: function(content) { 
			console.log("加载失败");
	   }
   });  
   
 
	
	$('.czorder').remove();
	orderidarray = new Array();
	orderclerkidarray = new Array();
	
	//setTimeout('loaddispatchlist()',100);
	
}
//订单列表翻页
function pageloadorder(pagenum){
	$('#orderlist').html('加载中...');  
	if( query == '' || query == undefined){
		bizid = 0;
		bizname = '';
	}
	var content = htmlback(siteurl+'/admin/dispatch/orderlist',{'page':pagenum,'i_type':""+i_type+"",'starttime':""+$('input[name="starttime"]').val()+"",'endtime':""+$('input[name="starttime"]').val()+"",'qtype':""+qtype+"",'query':""+query+"",'bizid':""+bizid+"",'bizname':""+bizname+"",'allianceid':""+allianceid+"",'stationid':""+stationid+"",'psgroupid':""+psgroupid+"",'bizdistrictid':""+bizdistrictid+""});
 	if(content.flag == false){
		var datacontent =  $.trim(content.content); 
		if( datacontent == ''  ){ 
		}else{
 			  $('#orderlist').html(datacontent); 
			  bindclickorderlist();
		} 
	}else{
	    console.log("加载失败");
	}  
	$('.czorder').remove();	
}

//切换订单状态
$("#statusOrderMenu li").click(function(){
	$('#muti_map_dispatch').remove();
	$("#statusOrderMenu li").removeClass('active');
	$(this).addClass('active');
	var curordertype = $(this).attr('data');
	i_type = curordertype;
	$.cookie('cur_order_status', i_type); 
	setTimeout('loadorderlist()',50);
});
//查询订单列表
$("#searBtn").click(function(){
	starttime = $('input[name="starttime"]').val();
	endtime = $('input[name="endtime"]').val();
	qtype = $('select[name="qtype"]').val();
	query = $('input[name="query"]').val();
	select_aspbInfo();
	loaddispatchlist();
	loadordertotalnotime();
	setTimeout('loadorderlist()',50);
}); 
function select_aspbInfo(){
			allianceid = $('select[name="allianceid"]').val();
			stationid = $('select[name="stationid"]').val();
			psgroupid = $('select[name="psgroupid"]').val();
			bizdistrictid = $('select[name="bizdistrictid"]').val();
		 
	
}	 
//关闭订单详情弹窗
function closedOrderDet(){
	$("#GH_POPUP").toggle();
	$("#orderDetContent").toggle();
	$("#orderDetContent").html('');
}
//同店铺下所有订单
function searchsameShop(shopid,shopname){
	bizid = shopid;
	bizname = shopname;
	$('select[name="qtype"]').find('option').eq(1).attr("selected",true);
 	$('input[name="query"]').val(shopname);
	query = shopname;
	loadorderlist(); 
}
//查找配送员
$('#clerknameinput').bind('keyup',function(e){
	 var keyCode = window.event ? e.keyCode:e.which;	
	 if( keyCode ==13){ 
		 $('.shade').remove();
		 var clerknamevalue = $('#clerknameinput').val(); 
		 $(".staffCon").css({'position':'relative'});
		 var clerlkHeight = $('.staffCon').height();
		 
		 var searchflag = false;
		 if( clerknamevalue != '' ){
			 var clerkobj = $('.staffCon li');
			  $(clerkobj).each(function(i,value){
					var clerkname = $(this).attr('clerkname');
					if( clerkname == clerknamevalue ){ 
						searchflag  = true; 
						var serpsyHeight = $(this).position().top; 
						var zzhtmls = '<div class="shade" style="width:298px;height:'+serpsyHeight+'px;top:0px;background-color: #000;opacity: 0.5;position: absolute;"></div>';
						$('.staffCon').append(zzhtmls);
						var zzhtmls = '<div class="shade" style="width:298px;height:'+(clerlkHeight-45-serpsyHeight)+'px;top:'+(serpsyHeight+44)+'px;background-color: #000;opacity: 0.5;position: absolute;"></div>';
						$('.staffCon').append(zzhtmls);
						return false;
					}  
			  });
			  if(searchflag == false){
				  diaerror('没有搜索到相应的骑手名！');
			  }
		 }
		 
	 }else{
		  var clerknamevalue = $('#clerknameinput').val(); 
		  if( clerknamevalue == ''  ){
			  $('.shade').remove();
		  }
	 }
});


function bindclickorderlist(){
	
	//查看订单详情
	$(".showOrderDet").click(function(){
		var orderid = $(this).attr('data');
		$("#GH_POPUP").toggle();
		$("#orderDetContent").html('');
		$("#orderDetContent").toggle();
		var content = htmlback(siteurl+'/admin/dispatch/oneorder',{'orderid':orderid});
		if(content.flag == false){
			var datacontent =  $.trim(content.content); 
			if( datacontent == ''  ){ 
			}else{
				  $('#orderDetContent').html(datacontent);  
			} 
		}  
		return false;
	});	
	// 选择订单并勾画配送员列表操作订单按钮
	$("#orderlist tr").click(function(){ 
	//status 配送单状态 1 新订单 2待派单 3派单未确认 4已接单 5已到店 6已取单 7已送达 8已结算 9关闭
		var mycars=new Array();
			mycars[1]="所选订单暂无抢单或者分配配送员";
			mycars[2]="所选订单已指派配送员";
			mycars[3]="所选订单已指派配送员,待确认";
			mycars[4]="所选订单配送员已接单";
			mycars[5]="所选订单配送员已到店";
			mycars[6]="所选订单配送员已取餐";
			mycars[7]="所选订单已送达";
			mycars[8]="所选订单已完成";
			mycars[9]="所选订单已取消";
		var orderid = $(this).attr('orderid');
		var type = $("#statusOrderMenu li.active").attr('data');
		var templingk = siteurl+'/admin/dispatch/oneorder?random=@random@&datatype=json';
		templingk = templingk.replace('@random@', 1+Math.round(Math.random()*1000))
		var tempc = ajaxback(templingk,{'orderid':orderid}); 
		if(tempc.flag ==false){
 			 
			 var orderinfo = tempc.content.order.orderinfo;
 			 var status = orderinfo.status;
			 if( type == 'neworder' ){
 				 if( status == 1 ){
					selectOrderH(orderinfo,1);  
				 }else{
					diaerror(mycars[status]);
				 }
			 }else if( type == 'handorder' ){
				 if( status == 1  ){
					selectOrderH(orderinfo,2); 
				 }else{
					diaerror(mycars[status]);
				 }
			 }else if( type == 'sendnosure' ){
				 if( status == 3  ){
					selectOrderH(orderinfo,2); 
				 }else{
					diaerror(mycars[status]);
				 }
			 }else if( type == 'hasorder' ){
				 if( status == 4 || status == 5  ){
					selectOrderH(orderinfo,2); 
				 }else{
					diaerror(mycars[status]);
				 }
			 }else if(  type == 'hasPickup'   ){
				 if( status == 6  ){
					selectOrderH(orderinfo,2);  
				 }else{
					diaerror(mycars[status]);
				 }
			 }else if( type == 'allorder' ){
				 
			 }else{
				
			 }
		}else{
			diaerror(tempc.content);
		}
		
		return false;	 
	});
	
	
	function selectOrderH(orderinfo,cztype){
 		$(".dispatchorder_"+orderinfo.id).toggleClass('disord_bg');
		orderidarray = new Array();
		orderclerkidarray = new Array();
		$("#orderlist .dispOrdBox tr").each(function(i,obj){
			if( $(obj).hasClass('disord_bg') ){
			    if( $(obj).attr('orderid') > 0 ){
					orderidarray.push($(obj).attr('orderid'));
				} 
				if( $(obj).attr('clerkid') > 0  ){
					orderclerkidarray.push($(obj).attr('clerkid'));
				} 
			}
		}); 
		$('.czorder').remove();	
		maketype = cztype;
	 
		if( orderidarray.length > 1     ){
			if(   $('#muti_map_dispatch').length == 0  ){
				var inmapAssignHtmls = '<div id="muti_map_dispatch"><div class="control-back"></div><div id="muti_map_dispatch_content"><span id="muti_map_dispatch_count">已选'+orderidarray.length+'单</span><button class="  muti_map_dispatch_btn">进入批量地图派单</button></div></div>';
				$('.dispatCon').append(inmapAssignHtmls);
			}else{
				$('#muti_map_dispatch_count').text('已选'+orderidarray.length+'单');
			}
			
		  bindordermapDispatch();
			
		}else{
			$('#muti_map_dispatch').remove();
		}
		
	} 
	
		
	//进入订单地图派单
	$(".ordermapDispatch").click(function(){
		if( lockclick() ){
			var orderid = $(this).attr('data');	
			var aid = $('select[name="allianceid"]').val();
			var sid = $('select[name="stationid"]').val();
			var gid = $('select[name="psgroupid"]').val();
			var bid = $('select[name="bizdistrictid"]').val();
			var str = '';
			if(aid > 0){
				str += '&aid='+aid+'';
			}
			if(sid > 0){
				str += '&sid='+sid+'';
			}
			if(gid > 0){
				str += '&gid='+gid+'';
			}
			if(bid > 0){
				str += '&bid='+bid+'';
			}
  			window.open(siteurl+'/admin/dispatch/mapDispatch?orderid='+orderid+''+str);	
		}		
	});	
	
	
	
} 
function bindordermapDispatch(){

	//进入批量订单地图派单
			$('#muti_map_dispatch .muti_map_dispatch_btn').bind('click',function(){
				if( lockclick() ){
  				var inmapOrderids = orderidarray.join(',');
				var aid = $('select[name="allianceid"]').val();
				var sid = $('select[name="stationid"]').val();
				var gid = $('select[name="psgroupid"]').val();
				var bid = $('select[name="bizdistrictid"]').val();
				var str = '';
				if(aid > 0){
					str += '&aid='+aid+'';
				}
				if(sid > 0){
					str += '&sid='+sid+'';
				}
				if(gid > 0){
					str += '&gid='+gid+'';
				}
				if(bid > 0){
					str += '&bid='+bid+'';
				}
				window.open(siteurl+'/admin/dispatch/mapDispatch?orderid='+inmapOrderids+''+str);
				}
			});
	
}
function bindclerkmose(){
     
	//鼠标移动 加载配送员配送订单列表
 	 $("#dispatchList li").hover(function(){ 
		if( clerkflag == false ){
			return false;
		} 
		$("#dispatchList .showDetOrder").hide();
         var clerkid = $(this).attr('clerkid');
		 $("#dispatchList #showDetOdC_"+clerkid).show();
		 $("#dispatchList #showDetOdC_"+clerkid).html('');
		 
			var content = htmlback(siteurl+'/admin/dispatch/clerkorderlist2',{'clerk_id':clerkid,'starttime':$('input[name="starttime"]').val(),'endtime':$('input[name="starttime"]').val()});
			if(content.flag == false){
				var datacontent =  $.trim(content.content); 
				if( datacontent == ''  ){ 
				}else{
					  $("#dispatchList #showDetOdC_"+clerkid).html(datacontent);   
				} 
			} 
			if( orderidarray.length > 0 ){
				if( maketype == 1 ){
					$(this).append('<div clerkid='+clerkid+' orderids='+orderidarray.join(",")+' class="czorder assignOrder staSta assign">指派<span>'+orderidarray.length+'</span>单</div>');
				}
				if( maketype == 2 ){
					$(this).append('<div clerkid='+clerkid+' orderids='+orderidarray.join(",")+'  class="czorder reassignOrder staSta reas">改派<span>'+orderidarray.length+'</span>单</div>');
				}
				 
 				if( orderclerkidarray.length > 0 && orderclerkidarray.indexOf(clerkid) >= 0    ){
					$(this).append('<div class="czorder staSta alrbel">订单已属于他</div>');
				} 
				
				//指派订单给配送员
				$(".assignOrder").click(function(){
					var clerkid = $(this).attr('clerkid');
					var orderids = $(this).attr('orderids');
 					$("#GH_POPUP").toggle();
					$("#operationPopup").html('');
					$("#operationPopup").toggle();
					var content = htmlback(siteurl+'/admin/dispatch/ajaxoperationOrder',{'orderids':orderids,'clerkid':clerkid,'type':1});
					if(content.flag == false){
						var datacontent =  $.trim(content.content); 
						if( datacontent == ''  ){ 
						}else{
							  $('#operationPopup').html(datacontent); 
							 bindclickoperation();
						} 
					} 
 					return false;
				});
				//改派订单给配送员
				$(".reassignOrder").click(function(){
					var clerkid = $(this).attr('clerkid');
					var orderids = $(this).attr('orderids');
 					$("#GH_POPUP").toggle();
					$("#operationPopup").html('');
					$("#operationPopup").toggle();
					var content = htmlback(siteurl+'/admin/dispatch/ajaxoperationOrder',{'orderids':orderids,'clerkid':clerkid,'type':2});
					if(content.flag == false){
						var datacontent =  $.trim(content.content); 
						if( datacontent == ''  ){ 
						}else{
							  $('#operationPopup').html(datacontent); 
							   bindclickoperation();
						} 
					} 
 					return false;
				});
			}
			clerkflag = false; 
     },function(){
			 clerkflag = true; 
 			 var clerkid = $(this).attr('clerkid');
			 $(".czorder").remove();
			 setTimeout("hideclerkorder("+clerkid+")",500);	 
      });	
	//鼠标移动到 某配送员订单列表事件
	$(".showDetOrder").hover(function(){
		var clerkid = $(this).parent().attr('clerkid');
 		$(this).show();
		$("#dispatchclerkid_"+clerkid).find('.czorder').show();
	},function(){
		var clerkid = $(this).parents().find('li').attr('clerkid');
		 $(this).hide();
		$("#dispatchclerkid_"+clerkid).find('.czorder').hide();
		  clerkflag = true; 
      });	


} 
 function hideclerkorder(clerkid){
	$("#dispatchList #showDetOdC_"+clerkid).hide();
	$("#dispatchList #showDetOdC_"+clerkid).html('');
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
				loadorderlist();
		}else{
			diaerror(tempc.content);
		}
	
	}
	
	
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
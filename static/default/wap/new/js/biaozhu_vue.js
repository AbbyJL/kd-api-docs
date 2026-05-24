window['Lmap'] = null;
window['LmapMarker'] = null;
window['defaultMarker'] = null;
window['GPSpoint'] = null;
var locationHtml = '<div class="searchbar2 searchbar_geo" style="margin-bottom:0;">'+
	/*'<div class="guess_local clearfix">我们猜您在：<span id="curLocation2">未知位置</span></div>'+*/
	'<form id="myform2" method="get" style="margin:10px 10px 0; padding-bottom:5px; border-bottom:0 none;">'+
		'<span class="city">'+window['CITY']+'<s class="s"></s></span>'+
		'<input type="text" id="meSleKey2" class="s_ipt" placeholder="您在哪儿？" />'+
		'<input type="submit" class="s_btn po_ab" value="搜索">'+
	'</form></div>'+
	'<div id="l-map" style="height:300px;"></div>'+
	'<ul id="r-result" class="r-result"></ul>';
function titControl(){
	this.defaultAnchor = BMAP_ANCHOR_TOP_LEFT;
	this.defaultOffset = new BMap.Size(parseInt($(window).width()/2-90,10),10);
}
titControl.prototype = new BMap.Control();
titControl.prototype.initialize = function(map){
	var div = document.createElement("div");
	div.appendChild(document.createTextNode("请拖动地图标注您的位置"));
	div.style.color = "#ffffff";
	div.style.fontSize = "14px";
	div.style.padding = "3px 0";
	div.style.width = "180px";
	div.style.textAlign = "center";
	div.style.backgroundColor = "rgba(255,153,51,.6)";
	map.getContainer().appendChild(div);
	return div;
}
function ZoomControl(){
	this.defaultAnchor = BMAP_ANCHOR_TOP_LEFT;
	this.defaultOffset = new BMap.Size(parseInt($(window).width()/2-60,10),101);
}
ZoomControl.prototype = new BMap.Control();
ZoomControl.prototype.initialize = function(map){
	var div = document.createElement("div");
	div.appendChild(document.createTextNode("我在这里"));
	div.style.color = "#ffffff";
	div.style.fontSize = "15px";
	div.style.padding = "5px 0px 0px 14px";
	div.style.width = "106px";
	div.style.height = "48px";
	div.style.cursor = "pointer";
	div.style.background = "url("+window['Default_tplPath']+"images/iamhere.png) no-repeat 0 0";
	div.style.backgroundSize = "120px auto";
	div.onclick = function(e){
		var lng = window['Lmap'].getCenter().lng;
		var lat = window['Lmap'].getCenter().lat;
		var point = new BMap.Point(lng,lat);
		var geoc = new BMap.Geocoder();
		geoc.getLocation(point, function(rs){
			var addComp = rs.addressComponents,addTitle = '';
			if(rs.surroundingPois.length>0){
				addTitle = rs.surroundingPois[0].title
			}else{
				addTitle = addComp.street + addComp.streetNumber;
			}
			sonzhizheli(lng,lat,addTitle,addComp.district + addComp.street + addComp.streetNumber);
		});
	}
	map.getContainer().appendChild(div);
	return div;
}
function LocationControl(){
	this.defaultAnchor = BMAP_ANCHOR_BOTTOM_RIGHT;
	this.defaultOffset = new BMap.Size(10,10);
}
LocationControl.prototype = new BMap.Control();
LocationControl.prototype.initialize = function(map){
	var div = document.createElement("div");
	div.style.width = "30px";
	div.style.height = "30px";
	div.style.border = "1px solid #ddd";
	div.style.borderRadius = "2px";
	div.style.background = "rgba(255,255,255,.5) url("+window['Default_tplPath']+"images/default-40x40.png) no-repeat 5px 5px";
	div.style.backgroundSize = "20px auto";
	map.getContainer().appendChild(div);
	div.onclick = function(e){
		e.preventDefault();
		var point=window['LmapMarker'].getPosition();
		searchLocation2(point);
		window['Lmap'].panTo(point);
	}
	return div;
}
function addOverlayLocation(e){
	var ico = new BMap.Icon(window['Default_tplPath']+"images/markers_new2_4ab0bc5.png", new BMap.Size(14,14),{anchor: new BMap.Size(7, 4),imageOffset:new BMap.Size(-105, 0),imageSize:new BMap.Size(150, 150)});
	window['LmapMarker'] = new BMap.Marker(e,{icon:ico});
	window['LmapMarker'].disableMassClear();
	window['Lmap'].addOverlay(window['LmapMarker']);
	window['Lmap'].addControl(new LocationControl());
	if($('#s_search_key').val()!==''){
		//return;
	}
	window['Lmap'].panTo(e);
	searchLocation2(e);
}
function showGPSLocation(point){
	addOverlayLocation(point);
}
function locationCallBack(){
	var form = $('#myform2'),ipt = $('#meSleKey2');
	getLocation(showMapGPSre);
	$('#curLocation2').bind('click',function(e){
		e.preventDefault();
		window['GPSpoint']&&window['Lmap'].centerAndZoom(window['GPSpoint'],18);
	});
	window['Lmap'] = new BMap.Map("l-map",{enableMapClick :false});
	window['Lmap'].addControl(new BMap.NavigationControl({anchor: BMAP_ANCHOR_TOP_RIGHT, type: BMAP_NAVIGATION_CONTROL_SMALL}));
	window['Lmap'].addControl(new ZoomControl());
	window['Lmap'].addControl(new titControl());
	
	
	window['Lmap'].addEventListener("dragend", function(e){
		searchLocation2(window['Lmap'].getCenter());
	});
	window['Lmap'].addEventListener("touchend", function(e){
		searchLocation2(window['Lmap'].getCenter());
	});
	var shop_x = $('#shop_x'),shop_y = $('#shop_y'),s_search_key = $('#s_search_key'),s_search_key_val = s_search_key.val();
	if(shop_x.val() !== '' && shop_x.val() !== '0'){
		var defaultPoint = new BMap.Point(shop_x.val(),shop_y.val());
		window['Lmap'].centerAndZoom(defaultPoint,18);
		var ico = new BMap.Icon(window['Default_tplPath']+"images/markers_new2_4ab0bc5.png", new BMap.Size(15,15),{anchor: new BMap.Size(7, 7),imageOffset:new BMap.Size(-150, -232),imageSize:new BMap.Size(300, 300)});
		window['defaultMarker'] = new BMap.Marker(defaultPoint,{icon:ico});
		window['defaultMarker'].disableMassClear();
		window['Lmap'].addOverlay(window['defaultMarker']);
		searchLocation2(defaultPoint);
	}else{
		window['Lmap'].centerAndZoom(window['CITY'],18);
	}
	
	
	form.submit(function(e){
		if(ipt.val() === '' || !ipt.val()){
			MSGwindowShow('location','4','请输入小区、单位、学校、商圈、地址','','');
			return false;
		}
		searchLocation(ipt.val(),window['CITY']);
		e.preventDefault();
	});
}
function sonzhizheli(lng,lat,title,txt){
	mypage['Shop_x'] = lng;
	mypage['Shop_y'] = lat;
	mypage['Chraddress'] = txt;
	mypage['Address'] = txt;
	//$('#chraddress').val(txt+title).attr('placeholder','标注成功，请手动输入地址').show();
	//$('#s_search_key').val(title);
	$('#windowIframe').find('.close').trigger('click');
	if(typeof window['biaozhuSuccess']!=='undefined'){window['biaozhuSuccess'].call(this,new BMap.Point(lng,lat),title,txt);}
}
function searchLocation2(point){
	var mOption = {
		poiRadius : 500,           //半径为500米内的POI,默认100米
		numPois : 12                //列举出12个POI,默认10个
	}
	var myGeo = new BMap.Geocoder();
	window['Lmap'].clearOverlays();  
	//window['Lmap'].addOverlay(new BMap.Circle(point,50,{strokeWeight:'1px',fillColor:'#36c',fillOpacity:'.3'}));
	myGeo.getLocation(point,function mCallback(rs){
		var allPois = rs.surroundingPois;       //获取全部POI（该点半径为100米内有6个POI点）
		var txt = '';
		for (var i = 0; i < allPois.length; i ++){
			txt += '<li data-point-lng="'+allPois[i].point.lng+'" data-point-lat="'+allPois[i].point.lat+'"><p class="title">'+allPois[i].title+'</p><span class="txt">'+allPois[i].address+'</span><a href="#" class="btn">我在这里</a></li>';
		}
		if(allPois.length === 0){
			txt += '<li><p>暂无地址数据</p><span class="txt">轻击“我在这里”标注后手动输入地址</span></li>';
		}
		document.getElementById("r-result").innerHTML = txt;
		$('#r-result').on('click','.title',function(e){
			e.preventDefault();
			var point=new BMap.Point($(this).parent().attr('data-point-lng'),$(this).parent().attr('data-point-lat'));
			window['Lmap'].panTo(point);
		});
		$('#r-result').find('.btn').click(function(e){
			e.preventDefault();
			/*var lng = window['Lmap'].getCenter().lng;
			var lat = window['Lmap'].getCenter().lat;*/
			var lng = $(this).parent().attr('data-point-lng');
			var lat = $(this).parent().attr('data-point-lat');
			sonzhizheli(lng,lat,$(this).parent().find('.title').html(),$(this).parent().find('.txt').html());
		});
	},mOption);	
}
function searchLocation(val,city){
	window['Lmap'].centerAndZoom(city,11);
	setTimeout(function(){
		var options = {
			pageCapacity:6,
			onSearchComplete: function(results){
				if (local.getStatus() == BMAP_STATUS_SUCCESS){
					var txt = '';
					for (var i = 0; i < results.getCurrentNumPois(); i ++){
						if(i===0){
							window['Lmap'].panTo(new BMap.Point(results.getPoi(i).point.lng,results.getPoi(i).point.lat));
						}
						txt += '<li data-point-lng="'+results.getPoi(i).point.lng+'" data-point-lat="'+results.getPoi(i).point.lat+'"><p class="title">'+results.getPoi(i).title+'</p><span class="txt">'+results.getPoi(i).address+'</span><a href="#" class="btn">我在这里</a></li>';
					}
					document.getElementById("r-result").innerHTML = txt;
					$('#r-result').on('click','.title',function(e){
						e.preventDefault();
						var point=new BMap.Point($(this).parent().attr('data-point-lng'),$(this).parent().attr('data-point-lat'));
						window['Lmap'].panTo(point);
					});
					$('#r-result').find('.btn').click(function(e){
						e.preventDefault();
						var lng = window['Lmap'].getCenter().lng;
						var lat = window['Lmap'].getCenter().lat;
						sonzhizheli(lng,lat,$(this).parent().find('.title').html(),$(this).parent().find('.txt').html());
					});
				}
			}
		};
		var local = new BMap.LocalSearch(window['Lmap'], options);
		local.search(val);
	},200);
}
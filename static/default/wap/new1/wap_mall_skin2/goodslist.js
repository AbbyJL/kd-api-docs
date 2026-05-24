$(function() {

    //APP端取消下拉刷新
    toggleDragRefresh('off');


    //以图搜图
    $('.imgsearch-holder').bind('click', function(){
        image = '';
        $(this).hide();
        $('#keywords').attr('placeholder', '商品/品牌');
        $('.imgsearch').show();
        getList(1);
    });

    //上传单张图片
    var uploadHolder;
	function mysub(id){
        var t = $("#"+id), p = t.parent(), uploadHolder = t.siblings('.imgsearch-btn');

        var data = [];
        data['mod'] = 'shop';
        data['filetype'] = 'image';
        data['type'] = 'single';

        $.ajaxFileUpload({
          url: "/app/api/upload",
          fileElementId: id,
          dataType: "json",
          data: data,
          success: function(m, l) {
            if (m.state == "SUCCESS") {
                location.href = '?image=' + m.url;
            } else {
              uploadError(m.state, id, uploadHolder);
            }
          },
          error: function() {
            uploadError("网络错误，请重试！", id, uploadHolder);
          }
      	});

	}

	function uploadError(info, id, uploadHolder){
		alert(info);
		uploadHolder.removeClass('disabled');
	}

	$(".imgsearch-btn").bind("click", function(){
		var t = $(this), inp = t.siblings("input");
		if(t.hasClass("disabled")) return;
		inp.click();
	})

	$("#Filedata_imgsearch").bind("change", function(){
		if ($(this).val() == '') return;
		$(this).siblings('.imgsearch-btn').addClass('disabled');
	    mysub($(this).attr("id"));
	})


	var dom = $('#screen'), mask = $('.mask'), disk = $('.disk'),
		areaScroll = infoScroll = sortScroll = moreScroll = null,
		areaArr = infoArr = sortArr = moreArr = sortSecondArr = [],
		chooseScroll = function(obj){return new iScroll(obj, {vScrollbar: false, mouseWheel: true, click: true});};
	var listArr = [];

	var detailList, getParid;
  detailList = new h5DetailList();
  setTimeout(function(){detailList.removeLocalStorage();}, 800);

	var	isload = false, isClick = true,
			xiding = $(".choose"),
			chtop = parseInt(xiding.offset().top),
			device = navigator.userAgent;

	var dataInfo = {
			parid: '',
			typeid: '',
			typename: '',
			orderby: '',
			orderbyName: '',
			keywords: '',
			price1: '',
			price2: '',
			listArr: '',
			isBack: true
	};


	//避免分类调用错位
	function getTypeList(tid, ite, cid){
		if (tid != 0) {
			setTimeout(function(){getOperaJson(tid, ite, cid)}, 300)
		}else {
			getOperaJson(tid, ite, cid);
		}
	}

	function getOperaJson(tid, ite, cid){
		huoniao.operaJson("/wap/goods/getTypeList", "tid="+tid, function(data){
			if(data.state == 100){
				infoScroll = 1;
				var list = [];
				list.push('<div class="fchoose fn-left active" id="choose'+tid+'">');
				list.push('<ul>');
				list.push('<li data-id="'+tid+'" data-lower="0" class="arrow0">不限</li>');
				//第一级
				if(tid == 0){
					for(var i = 0; i < data.info.length; i++){
						if (typeid == data.info[i].typeid) {
							active = " class='active arrow1'";
						}else {
							active = ' class="arrow1"';
						}
						list.push('<li data-id="'+data.info[i].typeid+'"'+active+' data-lower="1">'+data.info[i].typename+'</li>');
					}
				}else{
					for(var i = 0; i < data.info.length; i++){
						var lower, active = "", subnav = data.info[i].subnav;
						if(data.info[i].id == typeid || data.info[i].id == cid){
							active = " class='active'"
						}
						if(data.info[i].type == 1){
							lower = 1;
						}else {
							lower = 0;
						}
						list.push('<li data-id="'+data.info[i].id+'"'+active+' data-lower="'+lower+'" class="arrow'+lower+'">'+data.info[i].typename+'</li>');
					}
				}
				list.push('</ul>');
				list.push('</div>');
				$("#info-box").append(list.join(""));
				chooseScroll('choose'+tid);
				var flength = $('.fchoose').length;
				if (flength > 0 && flength < 4) {
					$('.fchoose').css('width', 100 / flength + '%');
					$('#info-box').css({'position':'absolute', 'left': 0});
				}else {
					var left = (flength - 3) * 33.3;
					$('.fchoose').css('width', '33.3%');
					$('.fchoose:last').css({'position':'absolute', 'right': (-left) + '%'});
					$('#info-box').css({'position':'absolute', 'left': (-left) + '%'});
				}
			}
		});
	}

	//点击分类验证是否有子级
	$("#info-box").delegate("li", "click", function(){
		var t = $(this), active = t.attr("class"), id = t.attr("data-id"), pClass = t.parent().parent().attr("class"), ite = 0,
        lower = t.attr('data-lower'), txt = t.text();
    t.closest('.fchoose').nextAll('.fchoose').remove();
		if(pClass != undefined && pClass.indexOf("exp") > -1){
			t.parent().parent().parent().parent().find("li").removeClass("active");
		}else{
			ite = t.parent().parent().parent().index();
			t.siblings("li").removeClass("active");
		}
		t.addClass("active");

	    if (lower != 'undefined' && lower == 1) {
	      $('.fchoose').removeClass('active');
	      getTypeList(id, ite, 0);
	    }else {
			disk.hide();
			dom.hide();
			$('.choose-local').hide();
			$('.choose-tab li').removeClass('active');
			$('.choose-tab li:first').attr('data-id', id).find('span').text(txt);

			var flength = $('.fchoose').length;
			if (flength > 0 && flength < 4) {
				$('.fchoose').css('width', 100 / flength + '%');
				$('#info-box').css({'position':'absolute', 'left': 0});
			}else {
				var left = (flength - 3) * 33.3;
				$('.fchoose').css('width', '33.3%');
				$('.fchoose:last').css({'position':'absolute', 'right': (-left) + '%'});
				$('#info-box').css({'position':'absolute', 'left': (-left) + '%'});
			}
		 	$('.choose-tab li').removeClass('active');
			$('.choose-box .choose-local').hide();disk.hide();
			getList(1);
   		}
	});


	// 筛选框
	$('.choose-tab li').click(function(){
		var $t = $(this), index = $t.index(), box = $('.choose-box .choose-local').eq(index);
		if (box.css("display")=="none") {
		 	$t.addClass('active').siblings().removeClass('active');
		 	box.show().siblings().hide();dom.hide();
		 	if (index == 0 && infoScroll == null) {
		 		getTypeList(0, 0, 0);
		 		if(typeid){
					setTimeout(getTypeList(typeid, 0, 0), 10000);
				}
		 	}
		 	if (index == 1 || index == 2 && sortScroll == null) {
				sortScroll = chooseScroll('sort-box');
		 	}
		 	if (index == 3 && moreScroll == null) {
		 		moreScroll = chooseScroll("choose-more");
		 	}
		 	disk.show();
		 }else{
			 	$t.removeClass('active');
			 	box.hide();disk.hide();
			}
	});


	// 一级筛选  排序
	$('.sale').click(function(){
		disk.hide();
		$('.price').attr("data-id",'');
		if($(this).hasClass('active')){
			$(this).attr("data-id",1);
		}else{
			$(this).attr("data-id",2);
		}
		getList(1);
	})
	$('.price').click(function(){
		disk.hide();
		$('.sale').attr("data-id",'');
		if($(this).hasClass('active')){
			$(this).attr("data-id",4);
		}else{
			$(this).attr("data-id",3);
		}
		getList(1);
	})
	

	// 确认
	$('.btn_confirm').click(function(){
		$(this).closest('.choose-local').hide();
		disk.hide();
		$('.choose-tab li').removeClass('active');
		getList(1);
	});
	$("#search").click(function(){
		getList(1);
	});

	// 重置
	$('.btn_reset').click(function() {
		$('.price1,.price2').val('');
	});


	// 遮罩层
	$('.disk').on('click',function(){
		disk.hide();dom.hide();
		$('.choose-local').hide();
		$('.choose-tab li').removeClass('active');
	})

	// 遮罩层
	$('.disk').on('touchmove',function(){
		disk.hide();dom.hide();
		$('.choose-local').hide();
		$('.choose-tab li').removeClass('active');
	})

	$('.confirm, .drop-range input').on('touchmove', function(e){
		e.preventDefault();
	})




	$('#maincontent').delegate('.li a', 'click', function(){
		var t = $(this), url = t.attr('data-url'), id = t.closest('li').attr('data-id');

		var orderby = $('.choose-tab .orderby').attr('data-id'),
				orderbyName = $('.choose-tab .orderby span').text(),
				typeid = $('.choose-tab .typeid').attr('data-id'),
				typename = $('.choose-tab .typeid span').text();
				firstId = $('#choose0 li.active').attr('data-id');
				keywords = $('#keywords').val();
				price1 = $('.drop-range .price1').text();
				price2 = $('.drop-range .price2').text();

		if (firstId == undefined) {
			if (getParid == undefined) {
				parid = $('.choose-tab .typeid').attr('data-id');
			}else {
				parid = getParid;
			}
		}else {
			parid = firstId;
		}

		dataInfo.parid = parid;
		dataInfo.typeid = typeid;
		dataInfo.typename = typename;
		dataInfo.orderby = orderby;
		dataInfo.orderbyName = orderbyName;
		dataInfo.keywords = keywords;
		dataInfo.price1 = price1;
		dataInfo.price2 = price2;
		dataInfo.listArr = listArr;

		detailList.insertHtmlStr(dataInfo, $("#maincontent").html(), {lastIndex: atpage});

		setTimeout(function(){location.href = url;}, 500);

	})

	// 下拉加载
	var isload = false;
	$(document).ready(function() {
		$(window).scroll(function() {
			var allh = $('body').height();
			var w = $(window).height();
			var scroll = allh - w;
			if ($(window).scrollTop() + 50 > scroll && !isload) {
				atpage++;
				getList();
			};
		});
	});


	//初始加载
	if($.isEmptyObject(detailList.getLocalStorage()['extraData']) || !detailList.isBack()){
    getList();
	}else {
		getData();
		setTimeout(function(){
			detailList.removeLocalStorage();
		}, 500)
	}

	//数据列表
	function getList(tr){

		isload = true;

		//如果进行了筛选或排序，需要从第一页开始加载
		if(tr){
			atpage = 1;
			$(".list-box ul").html("");
		}

		$(".list-box .loading").remove();
		$(".list-box").append('<div class="loading">加载中...</div>');

		//请求数据
		var data = [];
		data.push("pageSize="+pageSize);
		data.push("page="+atpage);

		var orderbyType = $('.choose-tab li').eq(1).attr('data-id');
		if(orderbyType != undefined && orderbyType != ''){
			data.push("orderby="+orderbyType);
		}
		var orderbyType = $('.choose-tab li').eq(2).attr('data-id');
		if(orderbyType != undefined && orderbyType != ''){
			data.push("orderby="+orderbyType);
		}

		// 商品类别
		var typeid = $('.choose-tab li').eq(0).attr('data-id');
		if(typeid != undefined && typeid != ''){
			data.push("typeid="+typeid);
		}


		// 价格
		var price1 = $('.drop-range .price1').val();
		var price2 = $('.drop-range .price2').val();
		if(price1 || price2){
			data.push("price="+price1+','+price2);
		}
		var keywords = $('#keywords').val();
		if(keywords != null && keywords != ''){
			data.push("title="+keywords);
		}
        if(image){
            data.push('image='+image);
        }
		$.ajax({
			url: "/wap/goods/slist?limited=6",
			data: data.join("&"),
			type: "GET",
			dataType: "jsonp",
			success: function (data) {
				if(data){
					if(data.state == 100){
						$(".list-box .loading").remove();
						var list = data.info.list, lr, html = [];
						if(list.length > 0){
							for(var i = 0; i < list.length; i++){
								lr = list[i];
								var pic = lr.litpic == false || lr.litpic == '' ? '/static/images/blank.gif' : lr.litpic;
								var specification = lr.specification

								html.push('<li class="fn-clear" data-id="'+lr.id+'">');

								html.push('<div class="img-box">');
								html.push('<a href="'+lr.url+'">');
								html.push('<img src="'+pic+'" alt="">');
								html.push('</a></div>');
								html.push('<div class="txt-box">');
								html.push('<a href="'+lr.url+'">');
								html.push('<h3>'+lr.title+'</h3>');
								html.push('<div class="txt-price">');
								html.push('<span class="sprice"><em>'+echoCurrency('symbol')+'</em>'+lr.price+'</span> <span class="sellnum">销量'+lr.sales+'笔</span> <span class="spos">'+lr.addr+' </span>');
								html.push('</div></a>');
								html.push('<a href="'+lr.storeurl+'"><div class="txt-info">');
								html.push('<span>'+lr.storeTitle+'</span>');
								html.push('<span class="goshop">进店 <s></s></span>');
								html.push('</div></a>');
								html.push('</div>');
								html.push('</li>');

								listArr[lr.id] = lr;
							}

							$(".list-box ul").append(html.join(""));
							isload = false;

							//最后一页
							if(atpage >= data.info.pageInfo.totalPage){
								isload = true;
								$(".list-box").append('<div class="loading">最后一页</div>');
							}

						//没有数据
						}else{
							isload = true;
							$(".list-box").append('<div class="loading">没有数据了</div>');
						}

					//请求失败
					}else{
						$(".list-box .loading").html(data.info);
					}

				//加载失败
				}else{
					$(".list-box .loading").html('加载失败');
				}
			},
			error: function(){
				isload = false;
				$(".list-box .loading").html('网络错误');
			}
		});
	}


	// 本地存储的筛选条件
	function getData() {

		var filter = $.isEmptyObject(detailList.getLocalStorage()['filter']) ? dataInfo : detailList.getLocalStorage()['filter'];

		getParid = filter.parid;
		typeid = filter.parid;
		listArr = filter.listArr;
		atpage = detailList.getLocalStorage()['extraData'].lastIndex;

		// 分类选中状态
		if (filter.typename != '') {$('.choose-tab .typeid span').text(filter.typename);}
		if (filter.typeid != '') {
			$('.choose-tab .typeid').attr('data-id', filter.typeid);
		}

		// 排序选中状态
		if (filter.orderby != "") {
			$('.choose-tab .orderby').attr('data-id', filter.orderby);
		}
		if (filter.orderbyName != "") {
			$('.choose-tab .orderby span').text(filter.orderbyName);
		}

		// 筛选选中状态
		if (filter.keywords != "") {
			$('#keywords').text(filter.keywords);
		}
		if (filter.price1 != "") {
			$('.drop-range .price1').text(filter.price1);
		}
		if (filter.price2 != "") {
			$('.drop-range .price2').text(filter.price2);
		}

	}


	// 上滑下滑导航隐藏
	var upflag = 1, downflag = 1, fixFooter = $(".header, .choose-tab, .top");
	//scroll滑动,上滑和下滑只执行一次！
	scrollDirect(function (direction) {
		if (direction == "down") {
			if (downflag) {
				fixFooter.hide();
				downflag = 0;
				upflag = 1;
			}
		}
		if (direction == "up") {
			if (upflag) {
				fixFooter.show();
				downflag = 1;
				upflag = 0;
			}
		}
	});
	// 判断设备类型，ios全屏
	var device = navigator.userAgent;
	if (device.indexOf('huoniao_iOS') > -1) {
		$('body').addClass('huoniao_iOS');
	}

})

// 扩展zepto
$.fn.prevAll = function(selector){
    var prevEls = [];
    var el = this[0];
    if(!el) return $([]);
    while (el.previousElementSibling) {
        var prev = el.previousElementSibling;
        if (selector) {
            if($(prev).is(selector)) prevEls.push(prev);
        }
        else prevEls.push(prev);
        el = prev;
    }
    return $(prevEls);
};

$.fn.nextAll = function (selector) {
    var nextEls = [];
    var el = this[0];
    if (!el) return $([]);
    while (el.nextElementSibling) {
        var next = el.nextElementSibling;
        if (selector) {
            if($(next).is(selector)) nextEls.push(next);
        }
        else nextEls.push(next);
        el = next;
    }
    return $(nextEls);
};

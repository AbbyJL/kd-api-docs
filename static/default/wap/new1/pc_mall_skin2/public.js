var shopInit,glocart,cartbtn,carcountObj,cartwrap,cartlist,cartul,cartft,ishow;
$(function(){
    $("img").scrollLoading();
    
    var sy = true;
    $('.topad .closebtn').click(function () {
        $(this).parent('.topad').hide();
    });

    $('.type span').click(function () {
        $(this).addClass('curr');
        $(this).siblings().removeClass('curr');
        if($(this).index() == 1){
            $('#sform').attr('action', storeUrl);
        }else{
            $('#sform').attr('action', productUrl);
        }
    });

    // 导航
    $('.nav li').mouseover(function () {
        $(this).addClass('active');
        $(this).siblings().removeClass('active');
    });
    $(".nav li").mouseout(function () {
        $(this).removeClass('active');
    });
    // $('.nav li').click(function () {
    //     $(this).addClass('curr');
    //     $(this).siblings().removeClass('curr');
    // });

    /* 左侧导航 */
       $(".category-popup li").hover(function(){
        var t = $(this);
        t.siblings("li").removeClass("active");
        t.siblings("li").find(".sub-category").hide();
       

        if(!t.hasClass("active")){
            t.addClass("active");
            t.find(".sub-category").show();
        }
    }, function(){
        $(this).removeClass("active");
        $(this).find(".sub-category").hide();
    });


       //右侧手机查看
    $('.floatnav .code').hover(function () {
        $('.floatnav .qrcode').addClass('show');
    },function () {
        $('.floatnav .qrcode').removeClass('show');
    });
    //右侧收藏

    $('.floatnav .sc').hover(function () {
        console.log(111);
        $('.floatnav .scdex').addClass('show');
    },function () {
        $('.floatnav .scdex').removeClass('show');
    });


    //购物车列表的显示与隐藏
    $(".topcart").hover(function(){
        $(this).find(".cart-con").show();
        $(this).addClass("hover");
        if(!ishow){
            shopInit.list();
        }
    }, function(){
        $(this).find(".cart-con").hide();
        $(this).removeClass("hover");
        ishow = false;
    });

    // 购物车
    glocart     = $(".topcart"),
        cartbtn     = glocart.find(".cart-btn"),
        carcountObj = cartbtn.find("i"),
        cartwrap    = glocart.find(".cart-con"),
        cartlist    = glocart.find(".cartlist"),
        cartul      = cartlist.find("ul"),
        cartft      = cartlist.find(".cartft");

    //初始计算购物车数量
    if(glocart.length > 0){
        shopInit.list();
    }

    //鼠标经过显示删除按钮
    cartwrap.delegate("li", "mouseover", function(){
        $(this).find(".del").show();
    });
    cartwrap.delegate("li", "mouseout", function(){
        $(this).find(".del").hide();
    });

    //删除购物车内容
    cartwrap.delegate(".del", "click", function(){
        shopInit.del($(this));
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
                var action = $('#sform').attr('action');
                location.href = action + '?image=' + m.url;
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

    $('.imgsearch-holder').bind('click', function(){
        $(this).hide();
    });




});



//操作购物车
shopInit = {

    //购物车新增
    add: function(data){

        //shopInit.list();  //新增前先更新，避免其他页面删除之后，当前页面直接新增还会保留删除前的数据

        var ishas = 0;
        cartul.find(".loading").remove();

        cartwrap.find("li").each(function(){
            var t = $(this), id = t.attr("data-id"), specation = t.attr("data-specation"), count = t.attr("data-count");
            //验证是否已经存在，如果有则更新数量
            if(id == data.id && specation == data.specation){
                ishas = 1;
                var ncount = Number(data.count) + Number(count);
                t.find(".c").html(ncount);
                t.attr("data-count", ncount);
            }
        });

        glocart.find(".empty").hide();

        if(!ishas){
            var li = $('<li data-id="'+data.id+'" data-specation="'+data.specation+'" data-count="'+data.count+'" class="fn-hide">'+
                '<a href="'+data.url+'" target="_blank" class="pic"></a>'+
                '<div class="info">'+
                '<h5><a href="'+data.url+'" target="_blank">'+data.title+'</a></h5>'+
                '<p><span><strong>'+echoCurrency('symbol')+'</strong> × <strong class="c">'+data.count+'</strong></span><a href="javascript:;" class="del">删除</a></p>'+
                '</div>'+
                '</li>');
            cartul.append(li);
            li.slideDown();
        }

        cartlist.show();
        this.update();
    }

    //更新购物车
    ,update: function(){

        var totalCount = 0, totalPrice = 0, data = [];
        cartwrap.find("li").each(function(){
            var t = $(this), id = t.attr("data-id"), specation = t.attr("data-specation"), count = t.attr("data-count"), price = t.attr("data-price");
            if(count != undefined){
                totalCount += Number(count);
                totalPrice += parseFloat(count * price);
            }
            data.push(id+","+specation+","+count);
        });

        carcountObj.html(totalCount);

        if(totalCount == 0){
            glocart.find(".empty").show();
            cartlist.hide();
        }else{
            cartft.find("em").html(totalCount);
            cartft.find("strong").html(totalPrice.toFixed(2));
        }

        // $.cookie(cookiePre+"shop_cart", data.join("|"), {expires: 7, domain: cookieDomain, path: '/'});

        this.database('update', data.join("|"));

    }

    //删除购物车内容
    ,del: function(t){
        var thi = this,
            t = t.closest("li");

        t.slideUp(300, function(){
            t.remove();
            ishow = true;
            thi.update();
        });
    }

    //删除全部
    ,deleteAll: function(){
        this.database('update', '');
        // $.cookie(cookiePre+"shop_cart", null);
        cartul.html("");
        this.update();
    }

    //加载列表
    ,list: function(){

        var thi = this;
        glocart.find(".empty").hide();
        cartlist.show();
        cartul.html('<div class="loading">加载中...</div>');

        //异步获取购物车信息
        $.ajax({
            url: '/home/goods/getCartList',
            type: "GET",
            dataType: "jsonp",
            success: function (data) {
                if(data && data.state == 100){

                    cartul.html("");
                    cartlist.show();

                    var info = data.info;
                    for(var i = 0; i < info.length; i++){
                        var li = $('<li data-id="'+info[i].id+'" data-specation="'+info[i].specation+'" data-count="'+info[i].count+'" data-price="'+info[i].price+'">'+
                            '<a href="'+info[i].url+'" target="_blank" class="pic" title="'+info[i].title+'"><img src="'+info[i].thumb+'" /></a>'+
                            '<div class="info">'+
                            '<h5><a href="'+info[i].url+'" target="_blank" title="'+info[i].title+'">'+info[i].title+'</a></h5>'+
                            '<p title="'+info[i].speInfo+'">'+info[i].speInfo+'</p>'+
                            '<p><span><strong>'+echoCurrency('symbol')+info[i].price+'</strong> × <strong class="c">'+info[i].count+'</strong></span><a href="javascript:;" class="del">元</a></p>'+
                            '</div>'+
                            '</li>');
                        cartul.append(li);
                    }
                    thi.update();

                }else{
                    glocart.find(".empty").show();
                    cartlist.hide();
                    carcountObj.html(0);
                }
            },
            error: function(){
                cartul.html('<div class="loading"><font color="ff0000">网络错误</font></div>');
            }
        });

    }

    // 数据库
    , database: function(type, content, callback){
        var type = type == undefined ? 'get' : type;
        var data = [];
        data.push('module=shop');
        data.push('type='+type);
        if(type == 'update'){
            data.push('content='+content);
        }
        $.ajax({
            url: '/home/goods/operateCart',
            type: 'post',
            data: data.join('&'),
            dataType: 'json',
            success: function(data){
                if(data && data.state == 100){
                    if(type == 'get'){
                        callback(data.info.content);
                    }
                }
            },
            error: function(){

            }
        })
    }


};



/**
 * 计算运费
 * @param $bearfreight           是否包邮 0：自定义  1：免费
 * @param $valuation             计价方式 0：按件  1：按重量  2：按体积
 * @param $express_start         默认运费几件以内
 * @param $express_postage       默认运费
 * @param $express_plus          递增数量
 * @param $express_postageplus   递增费用
 * @param $preferentialStandard  超过数量免费
 * @param $preferentialMoney     超过费用免费
 * @param $weight:               重量
 * @param $volume:               体积
 * @param $price:                单价
 * @param $count:                商品数量
 * @return int
 */
function getLogisticPrice(bearfreight, valuation, express_start, express_postage, express_plus, express_postageplus, preferentialStandard, preferentialMoney, weight, volume, price, count){

    bearfreight = Number(bearfreight);
    valuation = Number(valuation);
    express_start = Number(express_start);
    express_postage = Number(express_postage);
    express_plus = Number(express_plus);
    express_postageplus = Number(express_postageplus);
    preferentialStandard = Number(preferentialStandard);
    preferentialMoney = Number(preferentialMoney);
    weight = Number(weight);
    volume = Number(volume);
    price = Number(price);
    count = Number(count);

    if(bearfreight == 1) return 0;

    //总价
    totalPrice = price * count;

    logistic = 0;

    //计费对象
    obj = count;
    ncount = count;

    //按重量
    if(valuation == 1){
        obj = weight * count;
        ncount = count * weight;

        //按体积
    }else if(valuation == 2){
        obj = volume * count;
        ncount = count * volume;
    }

    //默认运费
    logistic += express_postage;

    //续加
    if(express_start > 0){
        postage = obj - express_start;
        if(postage > 0){
            logistic += Math.floor(postage/express_plus) * express_postageplus;
        }
    }

    //免费政策
    if(preferentialStandard > 0 && ncount >= preferentialStandard && preferentialMoney > 0 && totalPrice >= preferentialMoney){
        logistic = 0;
    }else if((preferentialStandard > 0 && ncount >= preferentialStandard && preferentialMoney <= 0) || (preferentialMoney > 0 && totalPrice >= preferentialMoney && preferentialStandard <= 0)){
        logistic = 0;
    }

    return logistic;

}

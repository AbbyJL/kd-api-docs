$(function(){

	if($('.icon-circle img').attr('src','')){
		$('.icon-circle img').css('background-color','#ccc')
	}else{
		$('.icon-circle img').css('background-color','#fff')
	}
	
	
    //导航
    $.ajax({
        type: "POST",
        url: "/app/api/siteModule",
        dataType: "json",
        data: 'type=1&platform=' + (miniprogram ? 'wx_miniprogram' : ''),
        success: function (data) {
            if(data && data.state == 100){
                var tcInfoList = [], list = data.info;
                for (var i = 0; i < list.length; i++){
                    if(list[i].code != 'special' && list[i].code != 'website'){
                        tcInfoList.push('<li><a href="'+list[i].url+'"><span class="icon-circle"><img src="'+list[i].logo+'"></span><span class="icon-txt">'+list[i].name+'</span></a></li>');
                    }
                }
                $('.tcInfo .swiper-slide ul').html(tcInfoList.join(''));

                // 滑动导航
                var t = $('.tcInfo .swiper-wrapper');
                var swiperNav = [], mainNavLi = t.find('li');
                for (var i = 0; i < mainNavLi.length; i++) {
                    swiperNav.push('<li>'+t.find('li:eq('+i+')').html()+'</li>');
                }

                var liArr = [];
                for(var i = 0; i < swiperNav.length; i++){
                    liArr.push(swiperNav.slice(i, i + 10).join(""));
                    i += 9;
                }

                t.html('<div class="swiper-slide"><ul class="fn-clear">'+liArr.join('</ul></div><div class="swiper-slide"><ul class="fn-clear">')+'</ul></div>');
                new Swiper('.swipre00', {pagination: '.pag00', loop: false, grabCursor: true, paginationClickable: true});

            }else{
                $('.tcInfo').hide();
            }
        },
        error: function(){
            $('.tcInfo').hide();
        }
    });

    //同城头条动态数据
    $.ajax({
        type: "POST",
        url: "/app/api/alist",
        dataType: "json",
        data: 'flag=h&pageSize=10',
        success: function(data) {

            if(data.state == 100){
                var tcNewsHtml = [], list = data.info.list;
                tcNewsHtml.push('<div class="swiper-slide">');
                for (var i = 0; i < list.length; i++){
                    tcNewsHtml.push('<p><a href="'+list[i].url+'"><span>'+list[i].typeName[(list[i].typeName.length)-1]+'</span>'+list[i].title+'</a></p>');
                    if((i + 1) % 2 == 0 && i + 1 < list.length){
                      tcNewsHtml.push('</div>');
                      tcNewsHtml.push('<div class="swiper-slide swiper-no-swiping">');
                    }

                }
                tcNewsHtml.push('</div>');
                $('.tcNews .swiper-wrapper').html(tcNewsHtml.join(''));
                new Swiper('.tcNews .swiper-container', {pagination: '.tcNews .pagination',direction: 'vertical',paginationClickable: true, loop: true, autoplay: 2000, autoplayDisableOnInteraction : false});

            }else{
                $('.tcNews').hide();
            }
        },
        error: function(){
            $('.tcNews').hide();
        }
    });



	//抢购倒计时
    $.ajax({
        url: "/app/api/getSysTime",
        dataType: "jsonp",
        data: 'action=getSysTime',
        success: function(data) {

            var date = new Date();

            var nowtime = data.now;
            var time = data.nextHour - nowtime;

            setInterval(function () {
                var hour = parseInt(time/ 60 / 60 % 24);
                var minute = parseInt(time/ 60 % 60);
                var seconds = parseInt(time% 60);

                $('#time_h').text(hour < 10 ? '0' + hour : hour);
                $('#time_m').text(minute < 10 ? '0' + minute : minute);
                $('#time_s').text(seconds < 10 ? '0' + seconds : seconds);

                time--;
            }, 1000);
        }
    });
	
    //抢购商品 后台数据添加必须为整点
    qgList();
	
	
    function qgList(){
        $.ajax({
          url: "/app/api/systemTime?num=24",
          type: "GET",
          dataType: "jsonp",
          success: function (data) {
            if(data.state == 100){
                var list = data.info.list, now = data.info.now, nowTime = data.info.nowTime, html = [], className='';
                if(list.length > 0){                   
                    var time = list[0].nextHour;                                 
                }
                if(time!='' && time!=undefined){
                    nextHour = time;
                }
                $.ajax({
                        url: "/app/api/slist?limited=4&time="+nextHour+"&page=1&pageSize=2",
                        type: "GET",
                        dataType: "jsonp",
                        success: function (data) {
                            if(data && data.state == 100 && data.info.list.length > 0){
                                var list = data.info.list, ggoodboxhtml = [], likeboxhtml = [], html = [];
                                
                                    for(var i = 0; i < list.length; i++){
                                         html.push('<li data-id="'+list[i].id+'" class="fn-clear">');
                                         html.push('<a href="'+list[i].url+'">');
                                         html.push('<div class="q_img">');
                                         html.push('<img src="'+huoniao.changeFileSize(list[i].litpic, "small")+'" alt="">');
                                         html.push('</div>');
                                         html.push('<p class="q_price"><em>'+echoCurrency('symbol')+'</em>'+list[i].price+'</p>');
                                         html.push('</a>');
                                         html.push('</li>');                                        
                                    }
                                    $(".servericeall-box ul").html(html.join(""));

                            }
                        },
                        error: function(){
                            $('.servericeall-box ul').html('<div class="loading">网络错误，加载失败</div>');//网络错误，加载失败！
                        }
                });
            }
          }
        });
    }

	 //广告位滚动
    new Swiper('.banner .swiper-container', {pagination: '.banner .pagination',paginationClickable: true, loop: true, autoplay: 2000, autoplayDisableOnInteraction : false});
	  $('.next-page').click(function(){
	    $('.pagination .swiper-pagination-switch').eq(1).click();
	  });


	var miniprogram = false;
	if(window.__wxjs_environment == 'miniprogram'){
		miniprogram = true;
	}else{
		if(navigator.userAgent.toLowerCase().match(/micromessenger/)) {
			if(typeof(wx) != 'undefined'){
				wx.miniProgram.getEnv(function (res) {
					miniprogram = res.miniprogram;
				});
			}
		}
	}


    $.fn.numberRock=function(options){
      var defaults={
        speed:24,
        count:100
      };
      var opts=$.extend({}, defaults, options);
      var div_by = 100,count = opts["count"],speed = Math.floor(count / div_by),sum=0, $display = this,run_count = 1,int_speed = opts["speed"];
      var int = setInterval(function () {
        if (run_count <= div_by&&speed!=0) {
          $display.text(sum=speed * run_count);
          run_count++;
        } else if (sum < count) {
          $display.text(++sum);
        } else {
          clearInterval(int);
        }
      }, int_speed);
    }

    //热门推荐
    htList();
	
	
    function htList(){
        $.ajax({
            type: "GET",
            url: "/app/api/ranking?mold=0&page=1&pageSize=30&flag=h&type=topic",
            dataType: "json",
            crossDomain: true,
            success: function (data) {
                if(data && data.state == 100){
                    $('.hot-con .loading').remove();
                    var html1 = [],html2 = [],html3 = [], list = data.info.list;
                    var sum1 = 0,sum2 = 0,sum3 = 0;
                    var len=list.length;
                    var ulen = Math.ceil(len/3);
                    var hlen = 2*ulen;
                    if(len%3 == 1){//当数据分三行有余数为1时  比如 7 10 13 等 需特殊处理
                        hlen = 2*ulen-1
                    }                    
                    var claHot = '',claTj ='',claCom ='',txt='';
                    var sumRec = 0
                    for(var k=0;k<len;k++){//求出所有数据的topic和
                        sumRec += Number(list[k].topic);
                    }
                    var lastUn = Math.round(sumRec/len);//topic的平均值，大于平均值的是热，小于平均值的是普通
                    console.log(data)
                    for (var i = 0; i < len; i++){
                        if(list[i].rec == 1){
                            claTj = 'new_blue';
                            txt = '荐';
                            claHot = '',claCom ='';
                        }else{
                            if(list[i].topic >= lastUn){
                            
                                claHot ='hot';
                                claTj ='',claCom ='';
                                txt = '热';
                            }else{
                                claCom = 'jin_grey';
                                txt = '#';
                                claHot = '',claTj ='';
                            }
                        }                       
                        
                        if(len < 4){                          
                            html1.push('<li><a href="'+list[i].url+'"><span class="hot-tip '+claHot+claTj+claCom+'">'+txt+'</span><span class="hot-txt">'+list[i].title+'</span></a></li>')
                            
                        }else if(len < 6){
                            if(i < 2){
                                html1.push('<li><a href="'+list[i].url+'"><span class="hot-tip '+claHot+claTj+claCom+'">'+txt+'</span><span class="hot-txt">'+list[i].title+'</span></a></li>')
                            }else{
                                html2.push('<li><a href="'+list[i].url+'"><span class="hot-tip '+claHot+claTj+claCom+'">'+txt+'</span><span class="hot-txt">'+list[i].title+'</span></a></li>')
                            }
                        }else {
                            if(i < ulen){
                                html1.push('<li><a href="'+list[i].url+'"><span class="hot-tip '+claHot+claTj+claCom+'">'+txt+'</span><span class="hot-txt">'+list[i].title+'</span></a></li>')
                            }else if(i < hlen){
                                html2.push('<li><a href="'+list[i].url+'"><span class="hot-tip '+claHot+claTj+claCom+'">'+txt+'</span><span class="hot-txt">'+list[i].title+'</span></a></li>')
                            }else{
                                html3.push('<li><a href="'+list[i].url+'"><span class="hot-tip '+claHot+claTj+claCom+'">'+txt+'</span><span class="hot-txt">'+list[i].title+'</span></a></li>')
                            }
                        }
                        
                    }
                   
                    $('.hot-con .fir_ul').html(html1.join(''));
                    $('.hot-con .sec_ul').html(html2.join(''));
                    $('.hot-con .th_ul').html(html3.join(''));

                    $('.hot-con .fir_ul li').each(function(){
                        sum1 += $(this).outerWidth(true);
                    })
                    $('.hot-con .sec_ul li').each(function(){
                        sum2 += $(this).outerWidth(true);
                    })
                    $('.hot-con .th_ul li').each(function(){
                        sum3 += $(this).outerWidth(true);
                    })                   
                    //取出最大值给 ul                   
                    var alWidth = Math.max(sum1,sum2,sum3);
                    $('.hot-con .fir_ul,.hot-con .sec_ul,.hot-con .th_ul').css('width',alWidth+10)
                }else{
                    $('.hot-con').html('<div class="loading">暂无数据</div>');//暂无数据！
                }
            },
            error: function(){
                $('.hot-con').html('<div class="loading">加载失败！</div>');//加载失败！
            }
        });
    }



    //同城活动
    $.ajax({
        url: '/app/api/hlist',
        type: 'get',
        dataType: 'json',
        data:'page=1&pageSize=4&keywords=&typeid=0',

        success: function(data){
            if(data && data.state == 100){
               var newsList = [], list = data.info.list;
               for(var i=0; i<list.length; i++){
                    newsList.push('<div class="activity swiper-slide">');
                    newsList.push('<a href="'+list[i].url+'">');
                    newsList.push('<div class="act-img" >');
                    newsList.push('<img src="'+list[i].litpic+'"/>')                
                    newsList.push('</div>');
                    newsList.push('<div class="act-info">');    
                            newsList.push('<p class="act-name">'+list[i].title+'</p>');
                           newsList.push('<p class="act-time">'+huoniao.transTimes(list[i].began,2)+'开始</p>');//开始
                    newsList.push('</div>');
                    newsList.push('</a>');
                    newsList.push('</div>');

               }
               $('.tc-activity').html(newsList.join(''));
                var swiper = new Swiper('.tc-activity-box .swiper-container', {
                    slidesPerView: 2,
                    spaceBetween:0,
                    pagination: {
                        el: '.swiper-pagination',
                        clickable: true,
                    },
                });
            }else{
               $('.tc-activity').html('<div class="loading">暂无数据</div>');//暂无数据！

              }
        },
        error: function(){
                $('.info-list-box ul').html('<div class="loading">加载失败</div>');//加载失败！
            }

    })

    //商家导航
    $.ajax({
        type: "POST",
        url: "/app/api/businessType",
        dataType: "json",
        data: 'type&pageSize=5',
        success: function (data) {
            if(data && data.state == 100){
                var tcInfoList = [], list = data.info;
                for (var i = 0; i < list.length; i++){
                    if(list[i].code != 'special' && list[i].code != 'paper' && list[i].code != 'website'){
                        tcInfoList.push('<li data-id="'+list[i].id+'">'+list[i].typename+'</li>');
                    }

                }
                $('.tit_ul ul').append(tcInfoList.join(''));

            }else{
                
            }
        },
        error: function(){
        }
    });
	
    //精选商家导航切换
    $('.tit_ul').delegate('li','click',function(){
        $(this).addClass('active').siblings().removeClass('active');
        var typeid = $(this).data('id');
        getList();
    })
	
	
    //贴吧社区
    $.ajax({
        type: "GET",
        url: "/app/api/tlist?istop=1&page=1&pageSize=5",
        dataType: "jsonp",
        success: function (data) {
            if(data && data.state == 100){
                var newsList = [], list = data.info.list;
                for (var i = 0; i < list.length; i++){         
                    newsList.push('<div class="tiezi swiper-slide">');
                    newsList.push('<a href="'+list[i].url+'">');
                    newsList.push('<div class="tie_top fn-clear">');
                    newsList.push('<i></i><strong>热议发帖</strong><s></s>');//热议发帖
                    newsList.push('</div>');
                    newsList.push('<p>'+list[i].title+'</p>');
                    newsList.push('<div class="tie_bot">');
                    newsList.push('<i></i><span>1人参与评论</span>');//1人参与评论
                    newsList.push('</div>');
                    newsList.push('</a>');
                    newsList.push('</div>');
                }
                $('.tieba_con').html(newsList.join(''));
                var swiperTieba = new Swiper('.tieba_wrap .swiper-container', {

                    slidesPerView: 'auto',
                    spaceBetween:0,
                });
            }else{
                $('.tieba_con').html('<div class="loading">暂无数据</div>');//暂无数据！

            }
        },
        error: function(){
            $('.tieba_con').html('<div class="loading">加载失败</div>');//加载失败！
        }
    });

	
	
	
    function getNatureText(num){
        switch (num){
            case '0' :
                return '全职';//全职
            case '1':
                return '兼职';//兼职
            case '2':
                return '临时';//临时
            case '3':
                return '实习';//实习
            default :
                return '未知';//未知
        }
    }
	
   
    // 获取推荐商家
    var lng = lat = 0;
    var page = 1, isload = false;
    function getList(){
        isload = true;
        var pageSize = 3;
        var typeid = $('.tit_ul li.active').data('id');
        $('.business-list-box ul').html('<div class="loading">加载中...</div>');//加载中...
        $.ajax({
            url: masterDomain+'/app/api/store?typeid='+typeid+'&page='+page+'&pageSize='+pageSize+'&lng='+lng+'&lat='+lat,
            type: 'get',
            dataType: 'jsonp',
            success: function(data){
                if(data && data.state == 100){
                    var html = [];
                    for(var i = 0; i < data.info.list.length; i++){
                        var d = data.info.list[i];
                        html.push('<li>');
                        html.push(' <a href="'+d.url+'">');
                        html.push('  <div class="business-img">');
                        html.push('<img src="'+(d.logo ? d.logo : (templets + 'images/fShop.png'))+'" alt="">');
                        html.push('  </div>');
                        html.push('  <p>'+d.title+'</p>')
                        html.push('  </a>');
                        html.push('</li>');
                    }                  
                    $('.business-list-box ul').html(html.join(''));

                }else{
                    $('.business-list-box .loading').text('暂无数据');//暂无数据！
                }
            },
            error: function(){             
                $('.business-list-box .loading').text(' 网络错误，请重试 ');   //    网络错误，请重试     
            }
        })
    }
	
	
    function checkLocal(){
        var local = false;
        var localData = utils.getStorage("user_local");
        if(localData){
            var time = Date.parse(new Date());
            time_ = localData.time;
            if(time - time_ < 3600 * 1000){
                lat = localData.lat;
                lng = localData.lng;
                local = true;
            }

        }
        if(!local){
            HN_Location.init(function(data){
                if (data == undefined || data.address == "" || data.name == "" || data.lat == "" || data.lng == "") {
                    lng = lat = -1;
                    getList();
                }else{
                    lng = data.lng;
                    lat = data.lat;

                    var time = Date.parse(new Date());
                    utils.setStorage('user_local', JSON.stringify({'time': time, 'lng': lng, 'lat': lat, 'address': data.address}));

                    getList();
                }
            })
        }else{
            getList();
        }

    }



    getList();

    $.ajax({
    type: "GET",
    url: "/app/api/agetStatistics",
    dataType: "jsonp",
    success: function (data) {
        if(data.state == 100){
            console.log(data)
            $("#datanums1").html(data.info.business);
            $("#datanums2").html(data.info.info);
            $("#datanums3").html(data.info.tieba);
            
            $(".row-all").on('inview', function(event, isInview) {
                if(isInview){
                    $("#datanums1").numberRock({speed:10,count:$("#datanums1").text()  })
                    $("#datanums2").numberRock({speed:10,count:$("#datanums2").text() })
                    $("#datanums3").numberRock({speed:10,count:$("#datanums3").text() })
                }
            })
        }
    },
    error:function(){
    }
    });



	$('.head-search .areachose, .head-search .search').bind('click', function(){
		location.href = $(this).data('url');
	});


	//扫一扫
	$(".search-scan").delegate(".scan", "click", function(){

		//APP端
		if(device.indexOf('huoniao') > -1){
			setupWebViewJavascriptBridge(function(bridge) {
				bridge.callHandler("QRCodeScan", {}, function callback(DataInfo){
					if(DataInfo){
						if(DataInfo.indexOf('http') > -1){
							location.href = DataInfo;
						}else{
							alert(DataInfo);
						}
					}
				});
			});

		//微信端
		}else if(device.toLowerCase().match(/micromessenger/) && device.toLowerCase().match(/iphone|android/)){

			wx.scanQRCode({
				// 默认为0，扫描结果由微信处理，1则直接返回扫描结果
				needResult : 1,
				desc: '扫一扫',
				success : function(res) {
					if(res.resultStr){
						if(res.resultStr.indexOf('http') > -1){
							location.href = res.resultStr;
						}else if(res.resultStr.indexOf('EAN_13,') > -1){
							var resultStr = res.resultStr.split('EAN_13,');
							location.href = '/app/api/barcodeSearch?type=redirect&code=' + resultStr[1];
						}else{
							alert(res.resultStr);
						}
					}
				},
				fail: function(err){
					alert('网络错误');
				}
			});

		//浏览器
		}else{
			$('.downloadAppFixed').css("visibility","visible");
			$('.downloadAppFixed .con').show();
		}

	});
	
    var ua = navigator.userAgent;
	var appVersion = '1.0';
	if(ua.match(/(iPhone|iPod|iPad);?/i)) {
		appVersion = $('.downloadAppFixed .app dd p').attr('data-ios');
	}else{
		appVersion = $('.downloadAppFixed .app dd p').attr('data-android');
	}
	$('.downloadAppFixed .app dd em').html(appVersion);
	$('.downloadAppFixed .close').bind('click', function(){
		$('.downloadAppFixed .con').hide();
		$('.downloadAppFixed').css("visibility","hidden");
	});



	//验证当前访问页面是否为当前城市
	var changAutoCity = $.cookie("HN_changAutoCity");
	var siteCityInfo = $.cookie("HN_siteCityInfo");
    var changeAutoCity;
	if(changAutoCity == null && siteCityInfo){
		HN_Location.init(function(data){
	    if (data != undefined && data.province != "" && data.city != "" && data.district != "" && !changeAutoCity) {
	      var province = data.province, city = data.city, district = data.district;
				$.ajax({
			    url: "/app/api/verifyCity?region="+province+"&city="+city+"&district="+district,
			    type: "POST",
			    dataType: "json",
			    success: function(data){
			      if(data && data.state == 100){
					var siteCityInfo_ = JSON.parse(siteCityInfo);
					var nowCityInfo = data.info;
					if(siteCityInfo_.cityid != nowCityInfo.cityid){
						$.cookie("HN_changAutoCity", '1', {expires: 1, path: '/'});

						changeAutoCity = $.dialog({
                          width: 250,
                          buttons: {
                            "取消": function() {
                              this.close();
                            },
                            "确定": function() {
                              location.href = nowCityInfo.url;
                            }
                          },
                          content: '<div style="text-align: center">检测到你目前的城市为<div style="font-size: .5rem; color: #ff6600; padding: .1rem 0;"><strong>' + nowCityInfo.name + '</strong></div>是否切换</div>'
                        }).open();

					}
			      }
			    }
			  })
	    }
	  })
	}

});

$(function(){
    /*调起大图 S*/
    $.fn.bigImage({
        artMainCon:".det-con",  //图片所在的列表标签
    });
    var commonLoad = null,page=1,pageSize=20;
        // tabTop = $('.shop-info').offset().top;
	// 收藏
    $('.soucang').click(function(){
        var t = $(this), type = t.hasClass("hascang") ? "del" : "add";
       
	    if(userId == null || userId == ""){
	      location.href = masterDomain + '/wap/passport/login?laiyuan=4';
	      return false;
	    }
		
		
        var t = $(this), type = t.hasClass("hascang") ? "del" : "add";
        if(type == 'add'){
            t.addClass('hascang').addClass('hascang');
            t.find('span').text('已收藏')
        }else{
            t.removeClass('hascang').removeClass('hascang');
             t.find('span').text('收藏')
        }
        $.post("/wap/goods/ajaxCollect?module=shop&temp=detail&type="+type+"&id="+detailID);

    });
   
   
	$('.btn-care').click(function(){
        var t = $(this), type = t.hasClass("cared") ? "del" : "add";
      
	  
	  
	    if(userId == null || userId == ""){
	      location.href = masterDomain + '/wap/passport/login?laiyuan=3';
	      return false;
	    }
		
		
        if(type == 'add'){
            t.addClass('cared').html('已关注');
        }else{
            t.removeClass('cared').html('<i></i>关注');
        }
        $.post("/wap/goods/ajaxCollect?collect&module=shop&temp=store-detail&type="+type+"&id="+storeid);

    });

	$('.markBox').find('a:first-child').addClass('curr');
	new Swiper('.topSwiper .swiper-container', {pagination: {el: '.topSwiper .swiper-pagination',type: 'fraction',} ,loop: false,grabCursor: true,paginationClickable: true,
		on: {
		    slideChangeTransitionStart: function(){
              var len = $('.markBox').find('a').length;
		      var sindex = this.activeIndex;
              if(len==1){
                $('.markBox').find('a:first-child').addClass('curr');
              }else{
                if(sindex > 0){
                    $('.pmark').removeClass('curr');
                    $('.picture').addClass('curr');
                }else{
                    $('.pmark').removeClass('curr');
                    $('.video').addClass('curr');
                }
              }

		    },
		}
	});


  // 图片放大
  var videoSwiper = new Swiper('.videoModal .swiper-container', {pagination: {el:'.videoModal .swiper-pagination',type: 'fraction',},loop: false})
    $(".topSwiper").delegate('.swiper-slide', 'click', function() {
        var imgBox = $('.topSwiper .swiper-slide');
        var i = $(this).index();
        $(".videoModal .swiper-wrapper").html("");
        for(var j = 0 ,c = imgBox.length; j < c ;j++){
            if(j==0){
            	if(detail_video!=''){
                	$(".videoModal .swiper-wrapper").append('<div class="swiper-slide"><video width="100%" height="100%" controls preload="meta" x5-video-player-type="h5" x5-playsinline playsinline webkit-playsinline  x5-video-player-fullscreen="true" id="video" src="'+detail_video+'"  poster="' + imgBox.eq(j).find("img").attr("src") + '"></video></div>');
             	}else{
					$(".videoModal .swiper-wrapper").append('<div class="swiper-slide"><img src="' + imgBox.eq(j).find("img").attr("src") + '" / ></div>');
             	}
             }else{
                $(".videoModal .swiper-wrapper").append('<div class="swiper-slide"><img src="' + imgBox.eq(j).find("img").attr("src") + '" / ></div>');
             }

        }
        videoSwiper.update();
        $(".videoModal").addClass('vshow');
        $('.markBox').toggleClass('show');
        videoSwiper.slideTo(i, 0, false);
        return false;
    });

    $(".videoModal").delegate('.vClose', 'click', function() {
        var video = $('.videoModal').find('video').attr('id');
        $(video).trigger('pause');
        $(this).closest('.videoModal').removeClass('vshow');
        $('.videoModal').removeClass('vshow');
        $('.markBox').removeClass('show');
    });


	// 图文详情
	$('.det-tab li').click(function() {
		$(this).addClass('active').siblings().removeClass('active');
		var index = $(this).index();
		$('.det-con').eq(index).addClass('show').siblings().removeClass('show');
        if(index == 2 && commonLoad == null){
            getComment();
        }
	});

      // 筛选评价
    $('.com-tab li').click(function(){
        var filter = $(this).attr('data-type') || '';
        $(this).addClass('active').siblings().removeClass('active');
        getComment(filter)
    })

	// 导航吸顶
	var navHeight = $('.det-tab').offset().top;
	$(window).on("scroll",function() {
	   var sct = $(window).scrollTop();
	   if ($(window).scrollTop() > navHeight) {
	       $('.det-tab').addClass('topfixed');
	   } else {
	       $('.det-tab').removeClass('topfixed');
	   }
	});

	/*调起大图 S*/
   var mySwiper = new Swiper('.bigBoxShow .bigSwiper', {pagination: {el:'.bigBoxShow .bigPagination',type: 'fraction',},loop: false})
    $(".com-list").delegate('.picbox img', 'click', function() {
        var imgBox = $(this).parents(".picbox").find("img");
        var i = $(imgBox).index(this);
        $(".bigBoxShow .swiper-wrapper").html("");
        for(var j = 0 ,c = imgBox.length; j < c ;j++){
         $(".bigBoxShow .swiper-wrapper").append('<div class="swiper-slide"><div class="swiper-img"><img src="' + imgBox.eq(j).attr("src") + '" / ></div></div>');
        }
        mySwiper.update();
        $(".bigBoxShow").css({
            "z-index": 999999,
            "opacity": "1"
        });
        mySwiper.slideTo(i, 0, false);
        return false;
    });

    $(".bigBoxShow").delegate('.vClose', 'click', function() {
        $(this).closest('.bigBoxShow').css({
            "z-index": "-1",
            "opacity": "0"
        });

    });
  /*调起大图 E*/

  // 店铺推荐
  $('.sbotab li').click(function() {
		$(this).addClass('active').siblings().removeClass('active')
		var index = $(this).index();
		$('.sbocon').eq(index).addClass('sbshow').siblings().removeClass('sbshow');
  });

  // 选择颜色、尺码
    var myscroll = null;
    $('body').delegate('.btn-guige', 'click', function(event) {
        $('.mask').css({'opacity':'1','z-index':'100000'});
        $('.color-box').addClass('sizeShow').show();
        $('.closed').removeClass('sizeHide');
        if(myscroll == null){
            myscroll = new iScroll("color-main", {vScrollbar: false,});
        }
    });

   // 关闭规格弹出层
  $('.mask, .closed').click(function(){
      $('.mask').css({'opacity':'0','z-index':'-1'});
      $('.color-box').removeClass('sizeShow');
  })

  // 获取评论
    var combox = $('.comment-box ul');
    var btn = $('.det-tab li').eq(2);
    function getComment(filter){
        $('.comment-box .morebox').remove();
        combox.html('<div class="loading"><span></span><span></span><span></span><span></span><span></span></div>');
        var data = [];
        filter = filter || '';
        data.push('aid='+detailID);
        data.push('filter='+filter);
        $.ajax({
            url: "/wap/goods/getComment?type=shop-order",//&pageSize=8
            data : data.join("&"),
            type: "GET",
            dataType: "json",
            success: function (data) {
                if(data && data.state == 100){
                    var list = data.info.list,pageinfo = data.info.pageInfo,page = pageinfo.page,html = [];
                    var totalCount = pageinfo.totalCount;
					
					
                    var hp = 0, zp = 0, cp = 0;
                    for(var i = 0; i < list.length; i++){
                        var info = list[i];
                        var nickname = info.user.nickname;
						
						console.log('info.user',info.user);
						
                        var photo = info.user.photo == "" ? '/static/default/wap/new1/images/noPhoto_40.jpg' : info.user.photo;
						console.log('photo',photo);
						
						
                        rat = parseInt(info.sco1);
                        sco1 = rat/5*100;
                        switch (rat) {
                            case 1:
                                hp++;
                                break;
                            case 2:
                                zp++;
                                break;
                            case 3:
                                cp++;
                                break;
                        }
                        // 图集
                        var pics = info.pics;
						console.log('pics',pics);
						
                        html.push('<li>');
                        html.push('<div class="headtop fn-clear">');
                        html.push('<span class="headImg"><img src="'+photo+'" alt=""></span>');
                        html.push('<span class="headName">'+nickname+'</span>');
                        html.push('<div class="starbg"><i style="width:'+sco1+'%;" class="star"></i></div>');
                        html.push('</div>');
                        html.push('<div class="main">');
                        html.push('<p>'+info.content+'</p>');
                        if(pics.length > 0){
                            html.push('<div class="mainPic">');
                            if(pics.length>3){
                                html.push('<span class="picNum">'+pics.length+'张</span>');
                            }
                            html.push('<div class="picbox">');
                            for(var m = 0; m < pics.length; m++){
                                html.push('<img src="'+pics[m].pic+'">');
                            }
                            html.push('</div>');
                            html.push('</div>');
                        }
                        html.push('</div>');
                        html.push('<div class="bottom">'+huoniao.transTimes(info.dtime, 2)+'</div>');
                        html.push('</li>');
                    }


                    if(filter == ''){
                    	$('.all').text(totalCount);
	                    $('.hp').text(hp);
	                    $('.zp').text(zp);
	                    $('.cp').text(cp);
	                }
                    $('.comment-box ul').html(html.join(""));

                }else {
                    $('.comment-box .morebox').remove();
                    $('.comment-box ul').html('<div class="loading">'+data.info+'</div>');
                }
            },
            error: function(){
                $('.comment-box .morebox').remove();
                $('.comment-box ul').html('<div class="loading">网络错误</div>');
            }
        })
    }


    // 倒计时
    timer = setInterval(function(){
        var end = $('body').find('.jsTime').attr("data-time")*1000;  //点击的结束抢购时间的毫秒数
        var newTime = Date.parse(new Date());  //当前时间的毫秒数
        var youtime = end - newTime; //还有多久时间结束的毫秒数
        var seconds = youtime/1000;//秒
        var minutes = Math.floor(seconds/60);//分
        var hours = Math.floor(minutes/60);//小时
        var days = Math.floor(hours/24);//天

        var CDay= days ;
        var CHour= hours % 24 ;
        var CMinute= minutes % 60;
        var CSecond= Math.floor(seconds%60);//"%"是取余运算，可以理解为60进一后取余数
        var c=new Date();
        var millseconds=c.getMilliseconds();
        var Cmillseconds=Math.floor(millseconds %100);
        if(CSecond<10){//如果秒数为单数，则前面补零
          CSecond="0"+CSecond;
        }
        if(CMinute<10){ //如果分钟数为单数，则前面补零
          CMinute="0"+CMinute;
        }
        if(CHour<10){//如果小时数为单数，则前面补零
          CHour="0"+CHour;
        }
        if(CDay<10){//如果天数为单数，则前面补零
          CDay="0"+CDay;
        }
        if(Cmillseconds<10) {//如果毫秒数为单数，则前面补零
          Cmillseconds="0"+Cmillseconds;
        }
        $(".jsTime").find("span.day").html(CDay);
        $(".jsTime").find("span.hour").html(CHour);
        $(".jsTime").find("span.minute").html(CMinute);
        $(".jsTime").find("span.second").html(CSecond);

  }, 1000);

})

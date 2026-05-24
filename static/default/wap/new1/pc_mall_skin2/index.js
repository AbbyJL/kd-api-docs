$(function () {

    //顶部图片轮播
    $(".adbox .slideBox").slide({mainCell:".bd ul",effect:"left",autoPlay:true, autoPage:'<li></li>', titCell: '.hd ul'});

    $("#adbox").hover(function(){
        $(this).find(".prev, .next").fadeIn(300);
    }, function(){
        $(this).find(".prev, .next").hide();
    });

    //右侧小轮播图
    $(".secskill .slideBox").slide({mainCell:".bd ul",effect:"left",autoPlay:true, autoPage:'<li></li>', titCell: '.hd ul'});

    // 领券中心--焦点图
    var swiperNav = [], mainNavLi = $('.slideBox2 .bd').find('li');
    for (var i = 0; i < mainNavLi.length; i++) {
        swiperNav.push($('.slideBox2 .bd').find('li:eq('+i+')').html());
    }
    var liArr = [];
    for(var i = 0; i < swiperNav.length; i++){
        liArr.push(swiperNav.slice(i, i + 3).join(""));
        i += 2;
    }
    $('.slideBox2 .bd').find('ul').html('<li>'+liArr.join('</li><li>')+'</li>');
    $(".slideBox2").slide({titCell:".hd ul", mainCell:".bd ul",effect:"leftLoop", autoPage:"<li></li>",autoPlay: true});

    // 热销榜单--焦点图
    var swiperNav = [], mainNavLi = $('.slideBox3 .bd').find('li');
    for (var i = 0; i < mainNavLi.length; i++) {
        swiperNav.push($('.slideBox3 .bd').find('li:eq('+i+')').html());
    }
    var liArr = [];
    for(var i = 0; i < swiperNav.length; i++){
        liArr.push(swiperNav.slice(i, i + 3).join(""));
        i += 2;
    }
    $('.slideBox3 .bd').find('ul').html('<li>'+liArr.join('</li><li>')+'</li>');
    $(".slideBox3").slide({titCell:".hd ul", mainCell:".bd ul",effect:"leftLoop", autoPage:"<li></li>",autoPlay: true});

    // 商城资讯--焦点图
    var swiperNav = [], mainNavLi = $('.slideBox4 .bd').find('li');
    for (var i = 0; i < mainNavLi.length; i++) {
        swiperNav.push($('.slideBox4 .bd').find('li:eq('+i+')').html());
    }
    var liArr = [];
    for(var i = 0; i < swiperNav.length; i++){
        liArr.push(swiperNav.slice(i, i + 1).join(""));
        i += 0;
    }
    $('.slideBox4 .bd').find('ul').html('<li>'+liArr.join('</li><li>')+'</li>');
    $(".slideBox4").slide({titCell:".hd ul", mainCell:".bd ul",effect:"leftLoop", autoPage:"<li></li>",autoPlay: true});

    // 推荐商家--焦点图
    var swiperNav = [], mainNavLi = $('.slideBox5 .bd').find('li');
    for (var i = 0; i < mainNavLi.length; i++) {
        swiperNav.push($('.slideBox5 .bd').find('li:eq('+i+')').html());
    }
    var liArr = [];
    for(var i = 0; i < swiperNav.length; i++){
        liArr.push(swiperNav.slice(i, i + 3).join(""));
        i += 2;
    }
    $('.slideBox5 .bd').find('ul').html('<li>'+liArr.join('</li><li>')+'</li>');
    $(".slideBox5").slide({titCell:".hd ul", mainCell:".bd ul",effect:"leftLoop", autoPage:"<li></li>",autoPlay: true});



    $('.s_item').hover(function () {
        $(this).find('.price').addClass('bold');
    },function () {
        $(this).find('.price').removeClass('bold');
    });

     //百货食品--选项卡
    $('.Baihuo .gen_tit span').click(function () {
        $(this).addClass('active').siblings().removeClass('active');
        var i = $(this).index();
        $('.Baihuo .g_item').eq(i).addClass('show').siblings().removeClass('show');
    });

    // 百货食品--焦点图
	 $(".slideBox6").slide({mainCell:".bd ul",effect:"left",autoPlay:true, autoPage:'<li></li>', titCell: '.hd ul'});

    //数码手机--选项卡
    $('.Digital .gen_tit span').click(function () {
        $(this).addClass('active').siblings().removeClass('active');
        var i = $(this).index();
        $('.Digital .g_item').eq(i).addClass('show').siblings().removeClass('show');
    });

    // 数码手机--焦点图
     $(".slideBox7").slide({mainCell:".bd ul",effect:"left",autoPlay:true, autoPage:'<li></li>', titCell: '.hd ul'});

    //母婴用品--选项卡
    $('.Muyin .gen_tit span').click(function () {
        $(this).addClass('active').siblings().removeClass('active');
        var i = $(this).index();
        $('.Muyin .g_item').eq(i).addClass('show').siblings().removeClass('show');
    });

    // 母婴用品--焦点图
     $(".slideBox8").slide({mainCell:".bd ul",effect:"left",autoPlay:true, autoPage:'<li></li>', titCell: '.hd ul'});

  //美妆饰品--选项卡
    $('.Meizhuang .gen_tit span').click(function () {
        $(this).addClass('active').siblings().removeClass('active');
        var i = $(this).index();
        $('.Meizhuang .g_item').eq(i).addClass('show').siblings().removeClass('show');
    });

   // 美妆饰品--焦点图
     $(".slideBox9").slide({mainCell:".bd ul",effect:"left",autoPlay:true, autoPage:'<li></li>', titCell: '.hd ul'});

	//兼容性问题
    if($.browser.msie && parseInt($.browser.version) >= 8){
        $('.recommoend .slideBox .bd ul li a .img_small .s_item:nth-child(3n)').css('margin-right','0');
        $('.recommoend .slideBox .bd ul li a:nth-child(3n)').css('margin-right','0');
    }

    counttime();
    //倒计时一次请求
    function counttime(){
        $.ajax({
            url: "/home/goods/systemTime?service=shop",
            type: "GET",
            dataType: "jsonp",
            success:function (data) {
                var list = data.info.list,nowTime = data.info.nowTime,now = data.info.now;
                for(var i = 0; i < list.length; i++){
                    if(now ==list[i].nowTime){
                        var nextHour = list[i].nextHour;
                        var nowTime = data.info.nowTime;
                        var intDiff = nextHour - nowTime;
                        qianggou(nextHour);
                        window.setInterval(function(){
                            i = i < 0 ? 9 : i;
                            $('.daojishi').find(".hm").text("0" + i );
                            i--;
                        }, 100);

                        function timer(intDiff){
                            window.setInterval(function(){
                                var hour=0,
                                    minute=0,
                                    second=0;//时间默认值
                                if(intDiff > 0){
                                    var hour = Math.floor((intDiff / 3600) % 24);
                                    var minute = Math.floor((intDiff / 60) % 60);
                                    var second = Math.floor(intDiff % 60);
                                }

                                $('.daojishi').find(".h").text(hour < 10 ? "0" + hour : hour);
                                $('.daojishi').find(".m").text(minute < 10 ? "0" + minute : minute);
                                $('.daojishi').find(".s").text(second < 10 ? "0" + second : second);
                                intDiff--;
                            }, 1000);
                        }
                        timer(intDiff);

                    }
                }
            }
        });
    }



    function qianggou(nextHour){
       
        $.ajax({
            url: "/home/goods/slist?limited=4&time="+nextHour+"&pageSize=4",
            type: "GET",
            dataType: "jsonp",
            success: function (data) {
				console.log('qianggou',data);
                if(data.state == 100){
                    var list = data.info.list,html = [];

                    for(var i = 0; i < list.length; i++){
                        html.push(' <li><a href="'+list[i].url+'">');
                        html.push('<img src="'+list[i].litpic+'">');
                        html.push('<p class="name">'+list[i].title+'</p>');
                        html.push('<p class="price"><s>'+list[i].mprice+'</s> <span><i>¥</i>'+list[i].price+' </span></p>');
                        html.push('</a></li>');
                    }
                    $("#qgou").append(html.join(""));
                }else{
                    $('.djs').addClass('opacity');
                    $("#qgou").append('<div class="loading">暂无抢购活动</div>');
                }
            }
        });

    }

});

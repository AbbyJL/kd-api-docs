//定义头部高度控件
$(document).ready(function(){
	var height = $(window).scrollTop();
	displaytop(height);
	
	$(window).scroll(function(){
	var height = $(window).scrollTop();
	displaytop(height);
	});
	
	function displaytop(height){
		if(height>50){$("#fengmian_top").css("background-color","#FF6633");}
		if(height<=50){$("#fengmian_top").css("background-color","#ed414a");}
	}
	
});

	
window.onload = function(){
	if($(".swiper-container .swiper-slide").length>1){
		var mySwiper = new Swiper('.swiper-container',{
			autoplay: 3000,    
			loop : true,
			pagination : '.swiper-pagination'
		})
	}
}

//详情页面资讯、置顶、举报箭头部分
$(document).ready(function(){
  $(".view_float_b").click(function(){
  	$("#view_top").show();
  });
  $(".view_top_off").click(function(){
  	$("#view_top").hide();
  });
  $(".view_float_zhedie").click(function(){
	$(".view_float_zhedie_style").toggleClass("view_float_zhedie_style_left");
		var s=$("#view_float").css("right");
	  if(s=="10px"){
		$("#view_float").animate({right:"-40"},500);
	  }else if(s=="-40px"){
		$("#view_float").animate({right:"10"},500);
	 }
  });
  var  shou= localStorage["fenleishoucang"] ? localStorage["fenleishoucang"] : 0;
  if(shou!="1"){
	  $(".fenleishoucang").show();
  }
  $(".fenleiscdet").click(function(){
	  localStorage.setItem("fenleishoucang", "1")
      $(".fenleishoucang").css("display","none");
  });
});


//详情页面已关注部分
$(function(){
	$(".Tzs015").click(function(){
		if($(this).text()=="+关注"){
			$(this).text("已关注");
		}else{
			$(this).text("+关注");
		}
	});
});


//详情页面回复弹出效果部分
$(function(){
	$(".anniu-huifu").click(function(){
		$("#FL_huifu").css("display","block");
	});
	
	$(".FL_huifu01").click(function(){
		$("#FL_huifu").css("display","none");
	});
	
	$(".FL_huifu0231").click(function(){
		$("#FL_huifu").css("display","none");
	});
});
//分类信息——我的 置顶部分
$(function(){
	/*删除信息*/
	$(".shanchu022").click(function(){
		$(this).parents(".Tz").remove();
	});	
	
});


//选择置顶部分效果
$(function(){
	/*我的页面里面的置顶效果*/
	$(".shanchu01").click(function(){		
		if($(this).text()=="我要置顶"){
			$(".pinche_zhiding").css("display","block");
			$(".pinche_zhidingbg").css("display","block");
			$(".pinche_zhidingcon").slideDown();
			$(this).text("已置顶");
		}else{
			$(this).text("我要置顶");
			//layer.msg('置顶已取消');
		}
	});
	/*我的页面置顶效果*/
	$(".pinche_zhidingcon>li").click(function(){
		var indexs=$(this).index();
		var len=$(".pinche_zhidingcon>li").length;
		if(indexs==(len-1)){
			
		}else{
			var txt=$(this).text();
			$(".pinche_lianxi0131").text(txt);
		};
		$(".pinche_zhiding").css("display","none");
		$(".pinche_zhidingbg").css("display","none");
		$(".pinche_zhidingcon").css("display","none");
	});
	/*点击背景隐藏*/
	$(".pinche_zhidingbg").click(function(){
		$(".pinche_zhiding").css("display","none");
		$(this).css("display","none");
		$(".pinche_zhidingcon").css("display","none");
	});
	$(".view_float_c").click(function(){
	});
});

//详情页面电话点击对话框
$(function(){
	$(".view_float_a").click(function(){
		$("#tanchuss").css("display","block");
	});
	$("#tanchuss").click(function(){
		$("#tanchuss").css("display","none");
	});
})


var $loading = $('#loading'),
      totalHeight = 0,
      timer = null, 
      jqueryXhr = null; // jquery-ajax 对象
var isloading=true;

var timeout = null;
var val = 0;



$(function () {
	if($("#loadqiang").length>0 ){
		main.loadqiang();
	}
	if($("#section-linemove").length>0 ){ 
        (function(){
            $(window).scroll(function(){
				if(isloading){
					totalHeight = parseFloat($(window).height()) + parseFloat($(window).scrollTop());
					var totalPages = parseInt($('#section-linemove').data('total-pages'));
					var page = parseInt($('#section-linemove').data('page')) + 1;
					if (totalHeight + 150 >= $(document).height()) {
						if(totalPages > 0 ){
							isloading=false;
							$('#loading').show();
							setTimeout(function(){
								main.nextPageFavourableComments(page, 1);
							}, 50);
						}
					}
				}
            });
        })();
      
	   var p = 1;
       if (sessionStorage[key2]  && setkill=="1") {
            p = parseInt(sessionStorage[key2]);
       }
	   
	   console.log('走这里2');
	   main.nextPageFavourableComments(1,p);
	   
	   if (window.addEventListener && setkill=="1") {

            window.addEventListener('unload',
            function() {
				
				console.log('走这里');
                sessionStorage.setItem(key1, val)
            },
            false);

            window.addEventListener('scroll',
            function() {
                clearTimeout(timeout);
                timeout = setTimeout(function(){
					console.log('走这里3');
                    val = window.pageYOffset;
                },
                500);
            },
            false);

            document.addEventListener('touchmove',
            function() {
                clearTimeout(timeout);
                timeout = setTimeout(function(){
					console.log('走这里4');
                    val = window.pageYOffset;
                },
                500);
            },
            false);
        }
    }
});

var $commentList=$('#FL_pl');

var main={
	loaddata:function(){
		$('#section-linemove').html("");
		$('#section-linemove').data('total-pages', "0");
		$('#section-linemove').data('page', "0");
		$("#emptymyorder").hide();
		$("#prompt").hide();
		main.nextPageFavourableComments(1, 1);
	},
	onImgLoad:function() {
		var imgs = $(".wxuploadimage");
		imgs.each(function(){
			$(this).unbind("click"); 	   
		});
		$(".wxuploadimage").click(function(){
			var imgsSrc = [];
			function reviewImage(src){
				if (typeof window.WeixinJSBridge != 'undefined'){
					WeixinJSBridge.invoke('imagePreview',{
						'current' : src,
						'urls' : imgsSrc
					});
				}
			}
			var src=$(this).data("src");
			$(this).parent().parent().parent().find(".wxuploadimage").each(function(){
				var n=$(this).data("src");
				if( n ){
					imgsSrc.push(n);
				}															   
			});
			reviewImage(src);	
			return false;
		})
	},
	
	
	loadqiang:function(){
		$.ajax({
			type: "post",
			dataType: "json",
			data: {"page":$("#loadqiang").data("page"),id:$("#loadqiang").data("id"),webcode:$("#loadqiang").data("webcode")},
			url: domainurl+"/tool/infomingdanjson/?i="+$("#webstationid").val()+"&version="+new Date(),
			cache:false,
			success: function(d){
				if(d.state== "success") {
					if(d.count==0){
						$("#loadqiang").html("无更多信息");
						$("#loadqiang").removeAttr("onclick");
					}
					$("#loadqiang").data('total-pages', d.count);
					$("#loadqiang").data('page', ($("#loadqiang").data("page")+1));
					$("#details02 ul").append(d.html);
				}else{
					wptAlert(d.msg);
				}
			}
		})
	},
	
	
	nextPageFavourableComments:function(page, cc){
		$('#loading').show();
		if(jqueryXhr){
			jqueryXhr.abort();
		}
		
		
		
		var k2 = sessionStorage[key1];
		console.log('--cc--');
		console.log(cc+'加上'+k2);
		
		if(cc>5){
			console.log('设置顶部距离为0');
			sessionStorage.setItem(key1, 0)
			cc=1;
		}
		jqueryXhr = $.ajax({
			url: domainurl+"/wap/thread/getIndexData?version="+new Date(),
			cache:false,
				data: {"page":page,"cc": cc,"city_id":city_id,"s":$('#section-linemove').data('s'),"h":$("#hongbao").val(),"uid":$("#uid").val(),"status":$("#status").val(),"view":$('#section-linemove').data('view'),"t":$("#categoryid").val(),"infoid":$("#infoid").val(),"keyword":$("#keyword").val(),"haschild":$("#haschild").val(),"webcode":$("#section-linemove").attr("data-webcode")},
			type: "post",
			async: true,       
			timeout: 6000,   
			dataType: "json",
			complete: function (){
				jqueryXhr = null;
			},
			success: function (resp) {
				if(resp.state=="success"){
					var oldpage=page;
					if(page==1){
						page=cc;
					}
					$('#section-linemove').data('total-pages', resp.count);
					$('#section-linemove').data('page', page);
					sessionStorage.setItem(key2, page) ;
					setTimeout(function(){
						$('#section-linemove').append(resp.html);
						$(".textload").each(function(){
							var h = $(this).height();
							if (h <= 75) {
								$(this).parent().parent().parent().parent().find(".hmdetailtog").remove();
							} else {
								$(this).parent().parent().parent().parent().find(".hmdetailtog").show();
								$(this).parent().addClass("max");
							}
							$(this).removeClass("textload");
						})
						$(".setdeletes").each(function(){
							$(this).removeClass("setdeletes");
							var userid=$(this).data("userid")
							var id=$(this).data("id");
							$(this).click(function(){
								wptLoading("加载中...");
								if(userid>0){
									$.getJSON(domainurl+"/wap/thread/getuserinfo",{
											userid: userid
										},
										function (res) {
											loadguanli($(this),id, userid,res.userkill);			
										}
									);
								}else{
									loadguanli($(this),id, userid,0);		
								}
								return false;  
							});
						})
						$('#loading').hide();
						isloading=true;
						if(oldpage==1 && resp.html==""){
							$("#emptymyorder").show();
						}
						if( resp.count <=0 && $("#section-linemove li").length>0)
							$("#prompt").show();
						main.onImgLoad();
						if(oldpage==1 && $("#detailslist").length==0 && setkill=="1"){   
							setTimeout(function() {
								val = sessionStorage[key1] ? sessionStorage[key1] : 0;
								
								console.log('--sessionStorage--');
								console.log(val);
								window.scrollTo(0, val);
							},10);
						}
					}, 1);
					
				}
			},
			error:function (xhr, ts, err){
				setTimeout(function(){
					$loading.hide();
				}, 500);
			}
		});
	},
	
	
	nextPageFavourablePinglun:function(){
		var page=parseInt($("#FL_pl").data('page'));
		 $.ajax({
			url: domainurl+"/wap/thread/infocommentlistjson/?i="+$("#FL_pl").data('id')+"&version="+new Date(),
			cache:false,
			data: {"page":page,"infoid":$("#FL_pl").data('infoid'),"s":$("#FL_pl").data('s'),"webcode":$("#FL_pl").data('webcode')},
			type: "post",
			async: true,    
			timeout: 3000,  
			dataType: "json",
			success: function (resp) {
				if(resp.state=="success"){
					$("#FL_pl").data('total-pages', resp.count);
					$("#FL_pl").data('page', page+1);
					setTimeout(function(){
						$("#FL_pl .FL_gengduo").before(resp.html);
						$(".FL_gengduo").html("查看更多留言");
						if(page==1 && resp.html==""){
							$(".FL_gengduo").unbind("click");
							$(".FL_gengduo").html("目前没有留言");
						}
						if( resp.count ==0 && $("#FL_pl ul").length>0){
							$(".FL_gengduo").html("没有更多留言啦");
							$(".FL_gengduo").removeAttr("onclick");
						}
					}, 1);
					
				}
			},
			error:function (xhr, ts, err){
				setTimeout(function(){
					
				}, 500);
			}
		});
	}
}


function loadguanli(self,tid,userid,userkill){
	
	var str='<div class="btn-confirm delete">删除信息</div>';
	if(userid>0){
		str+='<div class="btn-confirm blacklist">'+(userkill==2?"取消拉黑":"拉黑用户")+'</div><div class="btn-confirm jingyan">'+(userkill==1?"取消禁言":"禁言用户")+'</div>';	
	}
	var sureObj = $(str);
	if(userid>0){
		$(sureObj).filter('.blacklist').off("touchend").one("touchend", function (e) {
			e.preventDefault();
			$.getJSON(domainurl+"/wap/thread/blacklist?userid="+userid+"&userkill="+userkill,
				{
					
				},
				function (res) {
					webToast("操作成功","middle",1000);
				}
			);
		});
		$(sureObj).filter('.jingyan').off("touchend").one("touchend", function (e) {
			e.preventDefault();
			$.getJSON(domainurl+"/tool/jingyan?userid="+userid+"&userkill="+userkill,
				{
					
				},
				function (res) {
					webToast("操作成功","middle",1000);
				}
			);
		});
	}
	$(sureObj).filter('.delete').off("touchend").one("touchend", function (e) {
		e.preventDefault();
		$(document).off('wptConfirm_view:sure').one('wptConfirm_view:sure', function () {
			$.getJSON(domainurl+"/wap/thread/deleteguanliinfo",
				{
					tid: tid
				},
				function (res) {
					if (res.state == "success"){
						$("#friend_"+tid).remove()
					}
				}
			);
		});
		wptConfirm('您是否确认删除这条信息？', {text: '确认删除'});
	});
	
	wptMultiConfirm("", sureObj);
	$(document.body).trigger('wptLoading_view:hide');
}


$(document).ready(function(){ 
	var imgsSrc = [];
	function reviewImage(src) {
		if (typeof window.WeixinJSBridge != 'undefined'){
			WeixinJSBridge.invoke('imagePreview',{
				'current' : src,
				'urls' : imgsSrc
			});
		}
	}
	$(".wenzhangtuimg").each(function(){
		var n=$(this).attr("src");
		if( n ){
			imgsSrc.push(n);
		}															   
	});
	$(".wenzhangtuimg").click(function(){
		var src=$(this).attr("src");
		reviewImage(src);		
	})
	$("#form_post").click(function(){
		uploadcomment($(this).data("topid"),$(this).data("topname"),$(this).data("id"),$("#pinglun_input").val())		;
		return false;
	});
	if( $("#FL_pl").length>0 ){ 
		if($("#FL_pl").attr("data-num")!="0"){
			main.nextPageFavourablePinglun();
		}
		else{
			$(".FL_gengduo").unbind("click");
			$(".FL_gengduo").html("目前没有留言");	
		}
	}
})


function showtextcontent(o){
	if(o.attr("data-rel")=="0"){
		o.parent().find(".Tzs03").removeClass("max");
		o.html("收起");
		o.attr("data-rel","1");
	}else{
		o.parent().find(".Tzs03").addClass("max");
		o.html("全部");
		o.attr("data-rel","0");
	}
}


function replay(a,o){
	var b=o.attr("rel");
	$("#dt_review_box").show();
	$("#form_post").data("topid",a);
	$("#form_post").data("topname",b);
	$("#pinglun_input").attr("placeholder",(b==username?"我也说一句...":"回复"+b));
	$("#form_post").text((b==username?"留言":"回复"));
}


function uploadcomment(topid,topname,id,val){
	var mark=$.trim(val);
	if( mark.length<2  ){
		wptAlert('您太懒了，您输入的留言内容太短');
	}
	else if( mark.length>500){
		wptAlert('您输入的留言内容太长，请重新输入');
	}
	else{
		 wptLoading("正在处理中...");
		$.ajax({
			type: "post",
			dataType: "json",
			data: {"mark":mark,"topid":topid,"topname":topname,id:id},
			url: domainurl+"/wap/thread/addinfocomment/?webcode="+$("#form_post").data('webcode')+"&version="+new Date(),
			cache:false,
			success: function(d){
				if(d.state== "success") {
					 $(document.body).trigger('wptLoading_view:hide');
					 $("#dt_review_box").hide();
					 $("#pinglun_input").val("")
					 webToast(d.msg,"middle",1000);
					 if(d.commentkill=="1")
					 {
						 $("#FL_pl").data('page', "1");
						  $("#FL_pl").html('<div class="FL_gengduo" onclick="main.nextPageFavourablePinglun();">查看更多留言</div>');
						  main.nextPageFavourablePinglun();
					 }
				}else{
					wptAlert(d.msg);
					$(document.body).trigger('wptLoading_view:hide');
				}
			}
		})
	}	
	return false;	
}


function dianzanlist(o,tid){
	o.children("em").children("img").attr("src","/static/default/wap/thread/images/zan2.png");
	o.css("color","#ed414a");
	$.ajax({
		type: "post",
		dataType: "json",
		data: {"tid":tid,"t":0,"webcode":o.attr("data-webcode")},
		url: domainurl+"/wap/thread/addinfozan/?i="+$("#webstationid").val()+"&version="+new Date(),
		cache:false,
		success: function(d){
			if(d.state== "success") 
			{
				var zans=parseInt(o.children("i").text());
				o.children("i").text(zans+1);	
				$(".zannumdet").text(zans+1);
			}
		}
	})
}


function dianzan(o,tid){
	o.children("em").children("img").attr("src","/static/default/wap/thread/images/zan2.png");
	o.css("color","#ed414a");
	$.ajax({
		type: "post",
		dataType: "json",
		data: {"tid":tid,"t":0,"webcode":o.attr("data-webcode")},
		url: domainurl+"/wap/thread/addinfozan/?i="+$("#webstationid").val()+"&version="+new Date(),
		cache:false,
		success: function(d){
			if(d.state== "success") 
			{
				$(".zanlist").show();
				$(".zanlist li").eq(0).after(d.msg);
				var zans=parseInt(o.children("i").text());
				o.children("i").text(zans+1);	
				$(".zannumdet").text(zans+1);
			}
		}
	})
			
}


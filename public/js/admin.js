var lock = 0;


function showLoader(msg) {
	parent.layer.load(2);
}

function hideLoader(){
	$(".tumsgbox").hide();
    lock = 0;
    $("#loader").hide();
	parent.layer.closeAll('loading');
}

function hidde(){
    $(".tumsgbox").hide();
    lock = 0;
}

function success(msg, timeout, callback){
	hideLoader();
    parent.layer.msg(msg);
    setTimeout(function (){
        eval(callback);
    }, timeout ? timeout : 3000);
}


function error(msg, timeout, callback){
	hideLoader();
    parent.layer.msg(msg);
    setTimeout(function () {
        eval(callback);
    }, timeout ? timeout : 3000);
}


function boxmsg(msg, url, timeout, callback){
	hideLoader();
    parent.layer.msg(msg);
    if(url){
        setTimeout(function () {
            window.location.href = url;
        }, timeout ? timeout : 3000);
    }else if (url === 0) {
        setTimeout(function () {
            location.reload(true);
        }, timeout ? timeout : 3000);
    }else{
        eval(callback);
    }

}

function boxmsgcode(msg, url, timeout, callback){
	hideLoader();
	
	var v = $('.yzm_verify').trigger("click");
	
	console.log('v',v)
	
    parent.layer.msg(msg);
    if(url){
        setTimeout(function () {
            window.location.href = url;
        }, timeout ? timeout : 3000);
    }else if (url === 0) {
        setTimeout(function () {
            location.reload(true);
        }, timeout ? timeout : 3000);
    }else{
        eval(callback);
    }

}


//点击后左侧弹内容
$(document).ready(function (e) {
	$(".tips").click(function () {
		var tipnr = $(this).attr('rel');
		layer.tips(tipnr, $(this), {
			tips: [4, '#1ca290'],
			time: 4000
		});
	})
});

//添加弹窗编辑
function showWindow(width,hight,url,title){
	layer.open({
	  type: 2,
	  title:title,
	  area:[width+'px',hight+'px'],
	  fixed: false, 
	  maxmin: true,
	  content: url
	});
}

//get执行并返回结果，执行后带跳转
$(function () {
	$('body').on('click','.rst-url-btn',function (){
        var $url = this.href;
		showLoader();
        $.get($url, function(data){
            if(data.code==1){
                layer.alert(data.msg, {icon: 6}, function (index){
					hideLoader();
                    layer.close(index);
                    window.location.href = data.url;
                });
            }else{
                layer.alert(data.msg, {icon: 5}, function (index){
					hideLoader();
                    layer.close(index);
                });
            }
        }, "json");
        return false;
    });
});


//post执行并返回结果，执行后不带跳转
$(function () {
	$('body').on('click','.confirm-rst-btn',function (){
        var $url = this.href,
        $info = $(this).data('info');
        layer.confirm($info, {icon: 3}, function (index){
            layer.close(index);
			showLoader();
            $.post($url,{}, function(data){
				hideLoader();
                layer.msg(data.msg);
            }, "json");
        });
        return false;
    });
});




function jumpUrl(url){
    if(url){
        location.href = url;
    }else{
        history.back(-1);
    }
}


$(document).ready(function (){
    layer.config({
       extend: 'extend/layer.ext.js'
    });
    $(".export").click(function (){
       var admin_id = $(this).attr('admin_id');
       var url = $(this).attr('rel');
       var info = $(this).attr('info');
       parent.layer.prompt({formType: 1, value: '', title: info}, function (value) {
           if(value != "" && value != null) {
                $.post(url, {admin_id: admin_id,value:value}, function (data) {
                   if(data.status == 'success'){
                       layer.msg(data.msg, {icon: 1});
					   	   layer.close(value);
                           setTimeout(function(){
                           location.href = data.url;
                           },1000)
                        }else{
                            layer.msg(data.msg, {icon: 2});
                        }
						
                    }, 'json')
                }else{
                     layer.msg('填写密码', {icon: 2});
               }
            });
      })
	  
})
//后台导出密码结束			
function yzmCode() { //更换验证码
    $(".yzm_code").click();
}

function dialog(title, content, width, height){
    var dialogHtml = '<div class="dialogBox" title="' + title + '"></div>';
    if ($(".dialogBox").length == 0) {
        $("body").append(dialogHtml);
    }

    $(".dialogBox").attr('title', title);
    $(".dialogBox").html(content);
    $(".dialogBox").dialog({
		show: true,
		hide: true,
        zIndex: 1000,
        width: width ? width : 300,
        height: height ? height : 200,
        modal: true
    });

}


//图片预览
$(function(){
   var x = 10;
   var y = 20;
   $(".tooltip").mouseover(function(e){ 
      var tooltip = "<div id='tooltip'><img src='"+ this.href +"' alt='预览图' height='300'/>"+"<\/div>";
      $("body").append(tooltip);    
      $("#tooltip").css({
         "top": (e.pageY+y) + "px",
         "left":  (e.pageX+x)  + "px"
       }).show("fast"); 
   }).mouseout(function(){  
        $("#tooltip").remove();
   }).mousemove(function(e){
        $("#tooltip") .css({
            "top": (e.pageY+y) + "px",
            "left":  (e.pageX+x)  + "px"
       });
   });
})


function jumpLink(){
	var page = $('#page').val();
	var url = $('#page').attr('url');
	var theendrow = $('#theendrow').attr('data-row');
	
	
	if(page <= 0){
		layer.msg('请输入指定页面',{icon:2});
		return false;
	}
	
	if(parseInt(page) > parseInt(theendrow)){
		layer.msg('最大只能输入页码'+theendrow,{icon:2});
		return false;
	}

	var urlP = url.substring(url.lastIndexOf('p'));  
	var urlDelete = urlP.slice(2); 
	var url2 = url.replace(urlDelete,page+'.html');

	setTimeout(function(){
		layer.msg('正在为您跳转...',{icon:1});
        window.location.href = url2;
   }, 1000);
}	

//复制功能
function clipboard1(id){
	var clipboard = new Clipboard('.btn_1_'+id);
	clipboard.on('success', function(e) {
		console.log(e.text);
		layer.msg('复制【'+e.text+'】成功', {icon:1});
	});
	clipboard.on('error', function(e) {
		layer.msg('复制失败', {icon:5});
	});
}
function clipboard2(id){
	var clipboard = new Clipboard('.btn_2_'+id);
	clipboard.on('success', function(e) {
		console.log(e.text);
		layer.msg('复制【'+e.text+'】成功', {icon:1});
	});
	clipboard.on('error', function(e) {
		layer.msg('复制失败', {icon:5});
	});
}
	
function clipboard4(id){
	var clipboard = new Clipboard('.btn_3_'+id);
	clipboard.on('success', function(e) {
		console.log(e.text);
		layer.msg('复制【'+e.text+'】成功', {icon:1});
	});
	clipboard.on('error', function(e) {
		layer.msg('复制失败', {icon:5});
	});
}
	
	
		
function selectCallBack(id, name, v1, v2){
    $("#" + id).val(v1);
    $("#" + name).val(v2);
    $(".dialogBox").dialog('close');
}


$(document).ready(function (e){
    $(document).on("click", "input[type='submit']", function (e){
        e.preventDefault();
        if(!lock){
            if($(this).attr('rel')){
                $("#"+$(this).attr('rel')).submit();
            }else{
                $(this).parents('form').submit();    
            }
        }
    });
	


    $(document).on("click", "a[mini='act']", function (e){
        e.preventDefault();
		var url = $(this).attr('href');
        if(!lock){
			parent.layer.confirm("您确定要" + $(this).html() + "吗？", {area: '150px', btn: ['是的', '不'], shade: false}, function (){
				showLoader();
                $("#x-frame").attr('src', url);
            })
        }
    });

    //全选
    $(document).on("click", ".checkAll", function (e){
        var child = $(this).attr('rel');
        $(".child_" + child).prop('checked', $(this).prop("checked"));
    });


    $(document).on('click', "a[mini='list']", function (e){
        e.preventDefault();
        if(!lock){
            if (confirm("您确定要" + $(this).html())){
                $(this).parents('form').attr('action', $(this).attr('href')).submit();
            }
        }
    });

    
    $(document).on("click", "a[mini='load']", function (e){
        e.preventDefault();
        if(!lock){
            showLoader();
            var obj = $(this);
            $.get(obj.attr('href'), function (data){
                if(data){
                    dialog(obj.text(), data, obj.attr('w'), obj.attr('h'));
                }
                hideLoader();
            }, 'html');

        }
    });
	
	
    $(document).on("click", "a[mini='select']", function (e){
        e.preventDefault();
        if (!lock) {
            showLoader();
            var obj = $(this);
            dialog(obj.text(), '<iframe id="select_frm" name="select_frm" src="' + obj.attr('href') + '" style="border:0px;width:' + (obj.attr('w') - 30) + 'px;height:' + (obj.attr('h') - 80) + 'px;"></iframe>', obj.attr('w'), obj.attr('h'));
            hideLoader();
        }
    });


    $(".searchG").click(function (){
        if($(this).hasClass('searchGadd')){
            $(this).removeClass("searchGadd");
        }else {
            $(this).addClass("searchGadd");
        }
        $(".admin-select-nr2").slideToggle(200);
        $(".admin-sele-hidden").toggle(400);
    });



});
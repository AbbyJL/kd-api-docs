
var lock = 0;

//显示加载
function showLoader(msg) {
	parent.layer.load(2);
}
//关闭加载
function hideLoader(){
    lock = 0;
    $("#loader").hide();
	parent.layer.closeAll('loading');
}

//ajaxForm
$(function () {
	$('body').on('click','#layer-url-btn',function (){
		showLoader();
        var $url = this.href;
        $.get($url, function (data){
            if (data.code==1) {
				hideLoader();
                layer.msg(data.msg, function(){
					location.href = data.url;
				});
            }else{
				hideLoader();
                layer.msg(data.msg);
				setTimeout(function (){
					lock = 0;
				},3000);
            }
        }, "json");
        return false;
    });
});


//图片预览
$(function(){
   var x = 10;
   var y = 20;
   $(".tooltip").mouseover(function(e){ 
      var tooltip = "<div id='tooltip'><img src='"+ this.href +"' alt='土豆预览图' height='300'/>"+"<\/div>";
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


//ajaxForm
$(function (){
    $('#ajaxForm').ajaxForm({
        success: complete,
        dataType: 'json'
    });
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

//ajaxForm失败不跳转
function complete(data){
	hideLoader();
    if(data.code == 1){
		layer.msg(data.msg, function(){
			location.href = data.url;
			parent.layer.close(parent.layer.getFrameIndex(window.name));//关闭layer弹窗
		});
    }else{
		layer.msg(data.msg);
		setTimeout(function (){
			lock = 0;
		},3000);
    }
}



var input_array = Array();
$(document).ready(function (e) {
    
    $(".tips").click(function () {
        var tipnr = $(this).attr('rel');
        layer.tips(tipnr, $(this), {
            tips: [4, '#1ca290'],
            time: 4000
        });
    })
 });
   
 
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
   
$(document).on("click", "a[mini='load']", function (e){
        e.preventDefault();
        if(!lock){
            var obj = $(this);
            $.get(obj.attr('href'), function (data){
                if(data == 0){
                }else{
                    dialog(obj.text(), data, obj.attr('w'), obj.attr('h'));
                }
                lock = 0;
                ;
            }, 'html');

        }
    });



function dialog(title, content, width, height) {
    var dialogHtml = '<div class="dialogBox" title="' + title + '"></div>';
    if ($(".dialogBox").length == 0) {
        $("body").append(dialogHtml);
    }

    $(".dialogBox").attr('title', title);
    $(".dialogBox").html(content);
    $(".dialogBox").dialog({
        zIndex: 1000,
        width: width ? width : 300,
        height: height ? height : 200, 
        modal: true
    });

}


$(document).ready(function (e) {
    
    $(".tips").click(function () {
        var tipnr = $(this).attr('rel');
        layer.tips(tipnr, $(this), {
            tips: [4, '#1ca290'],
            time: 4000
        });
    })
    //全选
    $(document).on("click", ".checkAll", function (e) {
        var child = $(this).attr('rel');
        $(".child_" + child).prop('checked', $(this).prop("checked"));
    });
});


	$(document).on('click', "a[mini='list']", function (e){
        e.preventDefault();
        if(!lock){
            if (confirm("您确定要" + $(this).html())){
                $(this).parents('form').attr('action', $(this).attr('href')).submit();
            }
        }
    });


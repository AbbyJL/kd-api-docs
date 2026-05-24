
function showLoader(msg) {
	layer.load(2);
}

function hideLoader(){
    $("#loader").hide();
	layer.closeAll('loading');
}

$(function () {
	$('body').on('click','#jin-com-url-btn',function (){
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


//ajaxForm
$(function () {
    $('#ajaxForm').ajaxForm({
        success: complete, // 这是提交后的方法
        dataType: 'json'
    });
});


function complete(data){
    if(data.code == 1){
		layer.msg(data.msg, function(){
			parent.location.href = data.url;
			parent.layer.close(parent.layer.getFrameIndex(window.name));//关闭layer弹窗
		});
    }else{
		layer.msg(data.msg);
		setTimeout(function (){
			lock = 0;
		},3000);
    }
}
//哈土豆添加弹窗编辑
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
//获取城市、地区、商圈的下拉菜单
function get_option(){
		var city_id = 0;
		var area_id = 0;
		var business_id = 0;
		var city_str = '<option value="0">请选择...</option>';
		for (a in cityareas.city) {
			if (city_id == cityareas.city[a].city_id) {
				city_str += '<option selected="selected" value="' + cityareas.city[a].city_id + '">' + cityareas.city[a].name + '</option>';
			} else {
				city_str += '<option value="' + cityareas.city[a].city_id + '">' + cityareas.city[a].name + '</option>';
			}
		}
		$("#city_id").html(city_str);
		$("#city_id").change(function () {
			if ($("#city_id").val() > 0) {
			   var city_id = $("#city_id").val();
					$.ajax({
						  type: 'POST',
						  url: window.CITYURL,
						  data:{cid:city_id},
						  dataType: 'json',
						  success: function(result)
						  {
							 var area_str = ' <option value="0">请选择...</option>';
							for (a in result) {
							  area_str += '<option value="' + result[a].area_id + '">' + result[a].area_name + '</option>';                              
							}
						   $("#area_id").html(area_str);
							$("#business_id").html('<option value="0">请选择...</option>');									
						  }
					});
			} else {
				$("#area_id").html('<option value="0">请选择...</option>');
				$("#business_id").html('<option value="0">请选择...</option>');
			}
		});



		$("#area_id").change(function () {

			if ($("#area_id").val() > 0) {
				area_id = $("#area_id").val();
					$.ajax({
						  type: 'POST',
						  url: window.BUSURL,
						  data:{bid:area_id},
						  dataType: 'json',
						  success: function(result)
						  {
							 var business_str = ' <option value="0">请选择...</option>';
							 for (a in result) {
									business_str += '<option value="' + result[a].business_id + '">' + result[a].business_name + '</option>';
							 }
							$("#business_id").html(business_str);
						 }
					   });
			} else {
				$("#business_id").html('<option value="0">请选择...</option>');
			}
		});


		$("#business_id").change(function () {
			business_id = $(this).val();
		});

}




function changeCAB(c,a,b){
	$("#city_ids").unbind('change');
	$("#area_ids").unbind('change');
	var city_ids = c;
	var area_ids = a;
	var business_ids = b;
	var city_str = ' <option value="0">请选择...</option>';
	for (b in cityareas.city) {
		if (city_ids == cityareas.city[b].city_id) {
			city_str += '<option selected="selected" value="' + cityareas.city[b].city_id + '">' + cityareas.city[b].name + '</option>';
		} else {
			city_str += '<option value="' + cityareas.city[b].city_id + '">' + cityareas.city[b].name + '</option>';
		}
	}
	$("#city_ids").html(city_str);

	$("#city_ids").change(function () {
		if ($("#city_ids").val() > 0) {
			city_id = $("#city_ids").val();
			   $.ajax({
					  type: 'POST',
					  url: window.CITYURL,
					  data:{cid:city_id},
					  dataType: 'json',
					  success: function(result)
					  {
						 var area_str = ' <option value="0">请选择...</option>';
						for (a in result) {
						  area_str += '<option value="' + result[a].area_id + '">' + result[a].area_name + '</option>';                              
						}
					   $("#area_ids").html(area_str);
						$("#business_ids").html('<option value="0">请选择...</option>');										
					  }
				});
			$("#area_ids").html(area_str);
			$("#business_ids").html('<option value="0">请选择...</option>');
		} else {
			$("#area_ids").html('<option value="0">请选择...</option>');
			$("#business_ids").html('<option value="0">请选择...</option>');
		}
	});

	 if (city_ids > 0) {  //编辑加载选中数据     
		var area_str = ' <option value="0">请选择...</option>';
		$.ajax({
		  type: 'POST',
		  url: window.CITYURL,
		  data:{cid:city_ids},
		  dataType: 'json',
		  success: function(result)
		  {
			 for (a in result) {
				if (area_ids == result[a].area_id) {
					area_str += '<option selected="selected" value="' + result[a].area_id + '">' + result[a].area_name + '</option>';
				} else {
					area_str += '<option value="' + result[a].area_id + '">' + result[a].area_name + '</option>';
				}
			  }
			 $("#area_ids").html(area_str);
			}
		});
	}


	$("#area_ids").change(function () {
		if ($("#area_ids").val() > 0) {
			area_id = $("#area_ids").val();
				$.ajax({
					  type: 'POST',
					  url: window.BUSURL,
					  data:{bid:area_id},
					  dataType: 'json',
					  success: function(result)
					  {
						 var business_str = ' <option value="0">请选择...</option>';
						 for (a in result) {
								business_str += '<option value="' + result[a].business_id + '">' + result[a].business_name + '</option>';
						 }
						$("#business_ids").html(business_str);
					 }

				   });
		} else {
			$("#business_ids").html('<option value="0">请选择...</option>');
		}
	});

	if (area_ids > 0) {  //编辑加载选中数据                                 
	   $.ajax({
		  type: 'POST',
		  url: window.BUSURL,
		  data:{bid:area_ids},
		  dataType: 'json',
		  success: function(result)
		  {
			var business_str = ' <option value="0">请选择...</option>';
			for (a in result) {
					if (business_ids == result[a].business_id) {
						business_str += '<option selected="selected" value="' + result[a].business_id + '">' + result[a].business_name + '</option>';
					} else {
					  business_str += '<option value="' + result[a].business_id + '">' + result[a].business_name + '</option>';
					}
			}
			 $("#business_ids").html(business_str);
		  }

	   });

	}


	$("#business_ids").change(function () {
		business_ids = $(this).val();
	});
}





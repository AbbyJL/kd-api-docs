
function showQueue(){
	if(window['QueueFiles'].length===0){return false;}
	showQueueUpLoad(window['QueueFiles'].shift());
}
function showQueueUpLoad(originPhoto){
	window.originFileType = originPhoto.type;
	window.originFileName = originPhoto.name;
	var URL = window.URL || window.webkitURL,originPhotoURL;
	originPhotoURL = URL.createObjectURL(originPhoto);
	$('#image').cropper({autoCropArea:1,built:function(){
		cropAndUpload();
	}}).cropper('replace', originPhotoURL);
}
window['QueueFiles'] = [];
function handler(event){
	var files = event.target.files;
	for(var i=0;i<files.length;i++){
		window['QueueFiles'].push(files[i]);
	}
	showQueue();
}
function cropAndUpload(){
	var size = {
		width:'900',
		height:''
	}
	var croppedCanvas = $('#image').cropper("getCroppedCanvas",size);  // 生成 canvas 对象
	var croppedCanvasUrl = croppedCanvas.toDataURL(window.originFileType); // Base64
	$('#Base64Filename').val(window.originFileName);
	$('#imgFile').val(croppedCanvasUrl);
	setTimeout(function(){$("#fileForm").submit();},100);
}

function set_wx_upload(btn){
	var url = '/request.ashx?action=weixinfx&jsoncallback=?&url='+encodeURIComponent(window.location.href);
	$.getJSON(url,function(data){
		if(data[0].islogin !== '1'){
			console.info('没有开启微信分享或配置有误');
		}else{
			wx.config({
				debug: false, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
				appId: data[0].MSG.appid, // 必填，公众号的唯一标识
				timestamp: parseInt(data[0].MSG.timestamp), // 必填，生成签名的时间戳
				nonceStr: data[0].MSG.noncestr, // 必填，生成签名的随机串
				signature: data[0].MSG.signature,// 必填，签名，见附录1
				jsApiList: ['chooseImage','uploadImage','getLocalImgData'] // 必填，需要使用的JS接口列表，所有JS接口列表见附录2
			});
			wx.ready(function(){
				btn.click(function(e){
					e.preventDefault();
					wx.chooseImage({
						sizeType: ['original'], 
						success: function (res) {
							var localIds = res.localIds;
							if (localIds.length == 0) {return;}
							var i = 0, length = localIds.length;  
							function upload() {
								wx.getLocalImgData({
									localId: localIds[i],
									
									success: function (res) {
										i++;
										var localData = res.localData;
										setTimeout(function(){
											if(!isIOS()){
												localData = 'data:image/jpg;base64,'+localData;
											}
											$('#imgFile').val(localData);
											$("#fileForm").trigger('submit');
										},100);
										if (i < length) {  
											upload();  
										}  
									}
								});
							}  
							upload();
						}
					});
				});
			});
		}
	});
}

$.fn.uploadImgWap = function(){
	var t = $(this),
		btn = $('#upimgFileBtn');
	if(!!is_weixn()){
		set_wx_upload(btn);
		return;
	}
	btn.click(function(e){
		e.preventDefault();
		t.trigger('click');
	});
	t.prop('multiple',true);
}
function upLoad_init(){
	var myForm = $('#fileForm');
	var url = '/app/upload/uploadify';
	if($('#state').val() !==''){url+='?state='+$('#state').val();}
	myForm.bind('submit',function() {
		$('#pageLoaderNode').show();
		$.ajax({type:'POST',url:url,data:myForm.serialize(),dataType:'json',success:function(data){
			$('#pageLoaderNode').hide();
			if(data.error == 1){alert(data.message);return false;}
			//saveMyImage(data);
			var obj = {};
			obj.Filepath=data.url;
			obj.Id = data.id;
			obj.IsFirst = false;
			obj.Smallfilepath = data.smallurl;
			obj.State = data.state;
			mypage.ImgFileList.push(obj);
			showQueue();//调用队列
			myForm.trigger('reset');
		},error:function(){
			MSGwindowShow('house','0','提交失败了哦！','','');
			$('#pageLoaderNode').hide();
			
		}});
		return false;
	});
	$('#Base64File').uploadImgWap();
}

var Item_img = {
	props: ['tag','index','sid','length'],
	methods:{
		delfile:function(picid,index,sid){
			var that = this;
			var url = '/request.ashx?action=delpic&id='+picid+'&table_id='+window['table_id']+'&state='+$('#state').val()+'&timer='+Date.parse(new Date());
			axios.get(url).then(function(res){
				var Data = res.data;
				if(Data.islogin == '1'){
					that.$parent['ImgFileList'].splice(index, 1);
				}
			}).catch(function(err){that.ifLoadding = false;alert('抱歉，加载失败了！');console.log(err);});
		},
		set_FM:function(Sid,sid,index){
			var that = this;
			var isadd = Sid===''?'1':'0'
			var url = '/request.ashx?action=phototop&id='+sid+'&type='+isadd+'&table_id='+window['table_id']+'&state='+$('#state').val()+'&timer='+Date.parse(new Date());
			axios.get(url).then(function(res){
				var Data = res.data;
				if(Data.islogin == '1'){
					that.$parent['ImgFileList'].map(function(val){
						val.IsFirst = false;
					});
					Vue.set(that.$parent['ImgFileList'][index], 'IsFirst', true);
					
					that.$parent.Filepath = that.$parent['ImgFileList'][index].Smallfilepath
				}
			}).catch(function(err){that.ifLoadding = false;alert('抱歉，加载失败了！');console.log(err);});
		},
		move_PrevNext:function(pn,sortval,picid,index){
			var that = this;
			var url = siteUrl+'request.ashx?action=picmove&pn='+pn+'&id='+picid+'&intorder='+sortval+'&table_id='+window['table_id']+'&state='+$('#state').val()+'&timer='+Date.parse(new Date());
			axios.get(url).then(function(res){
				var Data = res.data;
				if(Data.islogin == '1'){
					var arr = that.$parent['ImgFileList'];
					if(pn == 1){
						Vue.set(arr, index , arr.splice(index + 1, 1, arr[index])[0]);
					}else{
						Vue.set(arr, index - 1, arr.splice(index, 1, arr[index - 1])[0]);
					}
				}
			}).catch(function(err){that.ifLoadding = false;alert('抱歉，加载失败了！');console.log(err);});
		}
	},
	template: `<div class="my_prop_imgitem">
		<div class="imgviewNode"><span class="sp_img" :style="{ backgroundImage: 'url('+tag.Smallfilepath+')'}"></span></div>
		<a href="#" @click.prevent.self="delfile(tag.Id,index);" class="del">删除</a>
		<a href="#" @click.prevent.self="set_FM(sid,tag.Id,index);" class="set_FM" :class="tag.IsFirst?'checked':''">设为封面</a>
		<a href="#" v-show="index!=0" @click.prevent.self="move_PrevNext(0,1,tag.Id,index);" class="move_prev">前移</a>
		<a href="#" v-show="index!=length-1" @click.prevent.self="move_PrevNext(1,1,tag.Id,index);" class="move_next">后移</a>
	</div>`
};
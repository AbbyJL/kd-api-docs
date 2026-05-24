$.fn.loadForm = function(url,callbefore,callback,datajson){
	var myForm = $(this);
	myForm.bind('submit',function() {
		if(!!callbefore){
			var result = callbefore.call(this,myForm);
			if(!result){
				return false;
			}
		}
		var submit_btn = myForm.find('button[type="submit"]');
		submit_btn.addClass('disabled').prop('disabled',true);
		var contentType = 'application/x-www-form-urlencoded';
		var idata = myForm.serializeArray();
		if(typeof datajson !=='undefined'){
			idata = JSON.stringify(datajson);
			contentType = 'application/json';
		}
		$.ajax({type:'POST',url:url,data:idata,dataType:'json',contentType:contentType,success:function(data){
			submit_btn.removeClass('disabled').prop('disabled',false);
			if(data.iserror===1){
				if(typeof data.data !== 'undefined'){
					MSGwindowShow('house','0',data.data,'','');
				}else{
					MSGwindowShow('house','0','提交失败了哦！','','');
				}
				return false;
			}
			callback&&callback.call(this,myForm,data);
		},error:function(){
			MSGwindowShow('house','0','提交失败了哦！','','');
			submit_btn.removeClass('disabled').prop('disabled',false);
		}});
		return false;
	});
}

//双项选择
function setFu2Sel(data1,data2,sid,vueName1,vueName2){
	var bigIndex=0,smallIndex=0,i,k;
	for(i=0;i<data1.length;i++){
		if(data1[i].id === parseInt(mypage[vueName1])){
			bigIndex=i;
			break;
		}
	}
	for(k=0;k<data2.length;k++){
		if(data2[k].id === parseInt(mypage[vueName2])){
			smallIndex=k;
			break;
		}
	}
	var mobileSelect = new MobileSelect({
		trigger: '#trigger_'+sid,
		title: '请选择',
		wheels: [
                {data: data1},
                {data: data2}
            ],
		position:[bigIndex,smallIndex],
		connector:'/',
		ensureBtnColor:'#5cc55c',
		callback:function(indexArr, data){
			mypage[vueName1] = data[0].id
			mypage[vueName2] = data[1].id
		}  
	});
	var init_txt_arr = [];
	mobileSelect.getValue().map(function(data){
		init_txt_arr.push(data.value);
	});
	if(mypage[vueName1]==='' || mypage[vueName1] === '0'){//发布页默认选中第一个
		mypage[vueName1] = data1[0].id;
		mypage[vueName2] = data2[0].id;
		if(typeof mypage[vueName1+'Name'] !=='undefined'){
			mypage[vueName1+'Name'] = data1[0].value;
		}
		if(typeof mypage[vueName2+'Name'] !=='undefined'){
			mypage[vueName2+'Name'] = data2[0].value;
		}
		return;
	}
}
//三项选择
function setFu3Sel(data1,data2,data3,sid,vueName1,vueName2,vueName3){
	var bigIndex=0,smallIndex=0,superIndex=0,i,k,p;
	for(i=0;i<data1.length;i++){
		if(data1[i].id === parseInt(mypage[vueName1])){
			bigIndex=i;
			break;
		}
	}
	for(k=0;k<data2.length;k++){
		if(data2[k].id === parseInt(mypage[vueName2])){
			smallIndex=k;
			break;
		}
	}
	for(p=0;p<data3.length;p++){
		if(data2[p].id === parseInt(mypage[vueName3])){
			superIndex=p;
			break;
		}
	}
	var mobileSelect = new MobileSelect({
		trigger: '#trigger_'+sid,
		title: '请选择',
		wheels: [
                {data: data1},
                {data: data2},
				{data: data3}
            ],
		position:[bigIndex,smallIndex,superIndex],
		connector:'',
		ensureBtnColor:'#5cc55c',
		callback:function(indexArr, data){
			mypage[vueName1] = data[0].id
			mypage[vueName2] = data[1].id
			mypage[vueName3] = data[2].id
		}  
	});
	var init_txt_arr = [];
	mobileSelect.getValue().map(function(data){
		init_txt_arr.push(data.value);
	});
	if(mypage[vueName1]==='' || mypage[vueName1] === '0'){//发布页默认选中第一个
		mypage[vueName1] = data1[0].id;
		mypage[vueName2] = data2[0].id;
		mypage[vueName3] = data3[0].id;
		if(typeof mypage[vueName1+'Name'] !=='undefined'){
			mypage[vueName1+'Name'] = data1[0].value;
		}
		if(typeof mypage[vueName2+'Name'] !=='undefined'){
			mypage[vueName2+'Name'] = data2[0].value;
		}
		if(typeof mypage[vueName3+'Name'] !=='undefined'){
			mypage[vueName3+'Name'] = data3[0].value;
		}
		return;
	}
}




//单级选择初始化函数
function setDanSel(data,sid,vueName){
	var dataArr=[],obj = {},i=0,k=0,len=data.length,bigIndex=0;
	var s_for = $('#trigger_for_'+sid);
	
	for(;i<len;i++){
		obj = {};
		obj.id=data[i].Categoryid;
        obj.value=data[i].Chrcategory;
		if(s_for.val()!=='' && s_for.val()!==0){
			if(data[i].Categoryid == s_for.val()){
				bigIndex = i;
			}
		}
		dataArr.push(obj);
	}
	var mobileSelect = new MobileSelect({
		trigger: '#trigger_'+sid,
		title: '请选择',
		wheels: [
			{data:dataArr}
		],
		position:[bigIndex],
		connector:'-',
		ensureBtnColor:'#5cc55c',
		callback:function(indexArr, data){
			mypage[vueName] = data[0].id;
			if(typeof mypage[vueName+'Name']!=='undefined'){
				mypage[vueName+'Name'] = data[0].value;
			}
		}  
	});
	var init_txt_arr = [];
	mobileSelect.getValue().map(function(data){
		init_txt_arr.push(data.value);
	});
	if(s_for.val() === '0'){//发布页默认选中第一个
		mypage[vueName] = dataArr[0].id;
		if(typeof mypage[vueName+'Name'] !=='undefined'){
			mypage[vueName+'Name'] = dataArr[0].value;
		}else{
			$('#trigger_'+sid).html(dataArr[0].value);
		}
		return;
	}
	
	$('#trigger_'+sid).html(init_txt_arr.join('-'));
}
//区域选择初始化函数
function setQuyuSel(data,sid){
	var dataArr=[],obj = {},i=0,k=0,len=data.length,bigIndex=0,smallIndex=0;
	var s_Quyuid = $('#Quyuid'),s_Diduanid = $('#Diduanid');
	for(;i<len;i++){
		obj = {};
		obj.id=data[i].Categoryid;
        obj.value=data[i].Chrcategory;
		
		//取一级的indexof
		if(s_Quyuid.val()!=='' && s_Quyuid.val()!=='0'){
			if(data[i].Categoryid == s_Quyuid.val()){
				bigIndex = i;
			}
		}
		if(data[i].Entities.length !==0){
			obj.childs = [];
			for(k=0;k<data[i].Entities.length;k++){
				var obj_e = {};
				obj_e.id=data[i].Entities[k].Categoryid;
				obj_e.value=data[i].Entities[k].Chrcategory;
				obj.childs.push(obj_e);
				//取二级的indexof
				if(data[i].Entities[k].Categoryid == s_Diduanid.val()){
					smallIndex = k;
				}
			}
		}
		dataArr.push(obj);
	}
	var mobileSelect = new MobileSelect({
		trigger: '#trigger_quyu',
		title: '请选择',
		wheels: [
			{data:dataArr}
		],
		position:[bigIndex,smallIndex],
		connector:'-',
		ensureBtnColor:'#5cc55c',
		callback:function(indexArr, data){
			if(!!mypage.QuDiName){
				if(data.length ===2 ){
					mypage.QuDiName = data[0].value+' - '+data[1].value;
				}else{
					mypage.QuDiName = data[0].value;
				}
			}
			mypage.Quyuid = data[0].id;
			if(data.length ===2 ){ mypage.Diduanid = data[1].id;}else{
				mypage.Diduanid = '';
			}
		}  
	});
	var init_txt_arr = [];
	mobileSelect.getValue().map(function(data){
		init_txt_arr.push(data.value);
	});
	if(s_Quyuid.val()=='' || s_Quyuid.val() == '0'){
		return;
	}
	if(!!mypage.QuDiName){
		mypage.QuDiName = init_txt_arr.join('-');
	}else{
		$('#trigger_quyu').html(init_txt_arr.join('-'));
	}
}
function setCheckbox_vue(data,sid,vueName){
	if(!data){return false;}
	var i=0,len=data.length,obj={};
	console.info(vueName);
	for(;i<len;i++){
		obj = {Categoryid: data[i].Categoryid, Chrcategory:data[i].Chrcategory,checked:false};
		if(!!mypage[vueName]&&mypage[vueName].length!==0){
			for(var k = 0; k<mypage[vueName].length;k++){
				if(parseInt(mypage[vueName][k])===data[i].Categoryid){
					obj.checked = true;
				}
			}
		}
		mypage[vueName+'List'].push(obj);
	}
	return false;
}
//vue表单插件部分
var Item_s = {
	props: ['tag'],
	methods: {
		sel_xiaoqu:function(sid,data){
			this.$parent.Zidingyi = '';
			this.$parent.Loupanid = sid;
			this.$parent.XiaoquName = data.Chrloupan;
			this.$parent.Quyuid = data.Quyuid;
			this.$parent.Diduanid = data.Diduanid;
			this.$parent.Chraddress = data.Chraddress;
			this.$parent.Niandai =  data.Niandai;
			this.$parent.Shop_x = data.X;
			this.$parent.Shop_y = data.Y;
			this.$parent.Shop_z = data.Z;
			this.$parent.xiaoquList.splice(0,this.$parent.xiaoquList.length);
			this.$parent.closeXiaoqu();
			return false;
		}
	},
	template: `<a href="#" @click.prevent.self="sel_xiaoqu(\''+tag.Loupanid+'\',tag)">{{tag.Chrloupan}}<em>{{tag.QuyuName}}</em></a>`
};
var Item_tag = {
	props: ['tag','index','forname'],
	methods: {
		sel_checked:function(checked,index,forname){
			Vue.set(this.$parent[forname+'List'][index], 'checked', !checked);
		}
	},
	template: `<label class="gx_check" :class="tag.checked?'current':''" @click.prevent.self="sel_checked(tag.checked,index,forname)"><input type="checkbox" v-model="tag.checked" class="checkone" :name="forname" :value="tag.Categoryid" />{{tag.Chrcategory}}</label>`
};
var Item_tese = {
	props: ['tag','index','forname','Ccid'],
	methods: {
		sel_checked:function(checked,index,forname){
			Vue.set(this.$parent[forname+'List'][index], 'checked', !checked);
		}
	},
	template: `<label class="gx_check" v-if="(tag.Styleid==29) || (tag.Styleid=='25' && Ccid=='1') || (tag.Styleid=='24' && Ccid=='0') || (tag.Styleid=='26' && Ccid=='2')" :class="tag.checked?'current':''" @click.prevent.self="sel_checked(tag.checked,index,forname)"><input type="checkbox" v-model="tag.checked" class="checkone" :name="forname" :value="tag.Categoryid" />{{tag.Chrcategory}}</label>`
};
var Item_checkbox = {
	props: ['tag','index','forname'],
	methods: {
		sel_checked:function(checked,index,forname){
			Vue.set(this.$parent[forname+'List'][index], 'checked', !checked);
		}
	},
	template: '<label class="gx_checkbox" :class="tag.checked?\'current\':\'\'" @click.prevent.self="sel_checked(tag.checked,index,forname)"><input type="checkbox" v-model="tag.checked" class="checkone" :name="forname" :value="tag.Categoryid" /><i></i>{{tag.Chrcategory}}</label>'
};

var Item_video = {
	props: ['tag','index','tableid','length'],
	methods:{
		delfile:function(tableid,index,cid,remoteid){
			var that = this;
			var url = '/upload/uploadfile.ashx?action=delfile&table_id='+tableid+'&id='+cid+'&remoteid='+remoteid+'&timer='+Date.parse(new Date());
			axios.get(url).then(function(res){
				var Data = res.data;
				if(Data.islogin == '1'){
					that.$parent['VideoFileList'].splice(index, 1);
				}
			}).catch(function(err){that.ifLoadding = false;alert('抱歉，加载失败了！');console.log(err);});
		}
	},
	template: '<div class="progressContainer"><div class="img" :data-url="tag.Filepath"><div class="picHolder"></div><span class="immg" :style="\'background: url(\'+tag.Smallfilepath+\') 50% 50%/cover no-repeat;\'"></span><span class="duration">{{tag.Duration}}</span></div><div class="text"><div class="info"><div class="status">已完成</div></div></div><a href="#" @click.prevent="delfile(tableid,index,tag.Cid,tag.Id)" class="progressCancel progressDels">删除</a></div>'
};
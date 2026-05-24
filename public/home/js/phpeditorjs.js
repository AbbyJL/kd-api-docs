//语言调用切换必须是select选择框
//p1 触发事件
//p2 需要替换内容的
//url 接搜地址
function classlangchange(p1,p2,url){
	p1.change(function(){
		jQuery.post(url,
					{'lang':p1.val()},
					function(r){
						
						p2.find('option').remove();
						p2.html(r.lang);
					},
					'json'
				);
	});
}	

//写cookies
function setCookie(name,value)
{
var Days = 30;
var exp = new Date();
exp.setTime(exp.getTime() + Days*24*60*60*1000);
document.cookie = name + "="+ escape (value) + ";expires=" + exp.toGMTString();
}

function getCookie(name)
{
var arr,reg=new RegExp("(^| )"+name+"=([^;]*)(;|$)");
if(arr=document.cookie.match(reg))
return unescape(arr[2]);
else
return null;
}

function formcheck(element, type, value) {
	$pintu = value.replace(/(^\s*)|(\s*$)/g, "");
	switch (type) {
		case "required":
			return /[^(^\s*)|(\s*$)]/.test($pintu);
			break;
		case "chinese":
			return !formcheck(element, 'required',$pintu)||/^[\u0391-\uFFE5]+$/.test($pintu);
			break;
		case "number":
			return !formcheck(element, 'required',$pintu)||/^([+-]?)\d*\.?\d+$/.test($pintu);
			break;
		case "integer":
			return !formcheck(element, 'required',$pintu)||/^-?[1-9]\d*$/.test($pintu);
			break;
		case "plusinteger":
			return !formcheck(element, 'required',$pintu)||/^[1-9]\d*$/.test($pintu);
			break;
		case "unplusinteger":
			return !formcheck(element, 'required',$pintu)||/^-[1-9]\d*$/.test($pintu);
			break;
		case "znumber":
			return !formcheck(element, 'required',$pintu)||/^[1-9]\d*|0$/.test($pintu);
			break;
		case "fnumber":
			return !formcheck(element, 'required',$pintu)||/^-[1-9]\d*|0$/.test($pintu);
			break;
		case "double":
			return !formcheck(element, 'required',$pintu)||/^[-\+]?\d+(\.\d+)?$/.test($pintu);
			break;
		case "plusdouble":
			return !formcheck(element, 'required',$pintu)||/^[+]?\d+(\.\d+)?$/.test($pintu);
			break;
		case "unplusdouble":
			return !formcheck(element, 'required',$pintu)||/^-[1-9]\d*\.\d*|-0\.\d*[1-9]\d*$/.test($pintu);
			break;
		case "english":
			return !formcheck(element, 'required',$pintu)||/^[A-Za-z]+$/.test($pintu);
			break;
		case "username":
			return !formcheck(element, 'required',$pintu)||/^[a-z]\w{3,}$/i.test($pintu);
			break;
		case "password":
			return !formcheck(element, 'required',$pintu)||/^(?![~`!@#%&*_\$\^\(\)\-\+\.\?\|\[\]\/\\={}|"':;><,]+$)[^\s\u4e00-\u9fa5]{6,20}$/.test($pintu);
			break;	
		case "mobile":
			return !formcheck(element, 'required',$pintu)||/^\s*(15\d{9}|13\d{9}|14\d{9}|17\d{9}|18\d{9})\s*$/.test($pintu);
			break;
		case "phone":
			return !formcheck(element, 'required',$pintu)||/^((\(\d{2,3}\))|(\d{3}\-))?(\(0\d{2,3}\)|0\d{2,3}-)?[1-9]\d{6,7}(\-\d{1,4})?$/.test($pintu);
			break;
		case "tel":
			return !formcheck(element, 'required',$pintu)||/^((\(\d{3}\))|(\d{3}\-))?13[0-9]\d{8}?$|15[89]\d{8}?$|170\d{8}?$|147\d{8}?$/.test($pintu) || /^((\(\d{2,3}\))|(\d{3}\-))?(\(0\d{2,3}\)|0\d{2,3}-)?[1-9]\d{6,7}(\-\d{1,4})?$/.test($pintu);
			break;
		case 'mobi_tel_phone':
			return !formcheck(element, 'required',$pintu)||/^((\(\d{3}\))|(\d{3}\-))?13[0-9]\d{8}?$|15[89]\d{8}?$|170\d{8}?$|147\d{8}?$/.test($pintu) 
			|| /^((\(\d{2,3}\))|(\d{3}\-))?(\(0\d{2,3}\)|0\d{2,3}-)?[1-9]\d{6,7}(\-\d{1,4})?$/.test($pintu)||formcheck(element, 'mobile',$pintu);
			break;
		case "email":
			return !formcheck(element, 'required',$pintu)||/^[^@]+@[^@]+\.[^@]+$/.test($pintu);
			break;
		case "url":
			return !formcheck(element, 'required',$pintu)||/^http:\/\/[A-Za-z0-9]+\.[A-Za-z0-9]+[\/=\?%\-&_~`@[\]\':+!]*([^<>\"\"])*$/.test($pintu);
			break;
		case "ip":
			return !formcheck(element, 'required',$pintu)||/^[\d\.]{7,15}$/.test($pintu);
			break;
		case "qq":
			return !formcheck(element, 'required',$pintu)||/^[1-9]\d{4,10}$/.test($pintu);
			break;
		case "currency":
			return !formcheck(element, 'required',$pintu)||/^\d+(\.\d+)?$/.test($pintu);
			break;
		case "zipcode":
			return !formcheck(element, 'required',$pintu)||/^[1-9]\d{5}$/.test($pintu);
			break;
		case "chinesename":
			return !formcheck(element, 'required',$pintu)||/^[\u0391-\uFFE5]{2,15}$/.test($pintu);
			break;
		case "englishname":
			return !formcheck(element, 'required',$pintu)||/^[A-Za-z]{1,161}$/.test($pintu);
			break;
		case "age":
			return !formcheck(element, 'required',$pintu)||/^[1-99]?\d*$/.test($pintu);
			break;
		case "date":
			return !formcheck(element, 'required',$pintu)||/^((((1[6-9]|[2-9]\d)\d{2})-(0?[13578]|1[02])-(0?[1-9]|[12]\d|3[01]))|(((1[6-9]|[2-9]\d)\d{2})-(0?[13456789]|1[012])-(0?[1-9]|[12]\d|30))|(((1[6-9]|[2-9]\d)\d{2})-0?2-(0?[1-9]|1\d|2[0-8]))|(((1[6-9]|[2-9]\d)(0[48]|[2468][048]|[13579][26])|((16|[2468][048]|[3579][26])00))-0?2-29-))$/.test($pintu);
			break;
		case "datetime":
			return !formcheck(element, 'required',$pintu)||/^((((1[6-9]|[2-9]\d)\d{2})-(0?[13578]|1[02])-(0?[1-9]|[12]\d|3[01]))|(((1[6-9]|[2-9]\d)\d{2})-(0?[13456789]|1[012])-(0?[1-9]|[12]\d|30))|(((1[6-9]|[2-9]\d)\d{2})-0?2-(0?[1-9]|1\d|2[0-8]))|(((1[6-9]|[2-9]\d)(0[48]|[2468][048]|[13579][26])|((16|[2468][048]|[3579][26])00))-0?2-29-)) (20|21|22|23|[0-1]?\d):[0-5]?\d:[0-5]?\d$/.test($pintu);
			break;
		case "idcard":
			
			return !formcheck(element, 'required',$pintu)||/^(\d{6})(\d{4})(\d{2})(\d{2})(\d{3})([0-9]|X)$/.test($pintu);
			break;
		case "bigenglish":
			return !formcheck(element, 'required',$pintu)||/^[A-Z]+$/.test($pintu);
			break;
		case "smallenglish":
			return !formcheck(element, 'required',$pintu)||/^[a-z]+$/.test($pintu);
			break;
		case "color":
			return !formcheck(element, 'required',$pintu)||/^#[0-9a-fA-F]{6}$/.test($pintu);
			break;
		case "ascii":
			return !formcheck(element, 'required',$pintu)||/^[\x00-\xFF]+$/.test($pintu);
			break;
		case "md5":
			return !formcheck(element, 'required',$pintu)||/^([a-fA-F0-9]{32})$/.test($pintu);
			break;
		case "zip":
			return !formcheck(element, 'required',$pintu)||/(.*)\.(rar|zip|7zip|tgz)$/.test($pintu);
			break;
		case "img":
			return !formcheck(element, 'required',$pintu)||/(.*)\.(jpg|gif|ico|jpeg|png)$/.test($pintu);
			break;
		case "doc":
			return !formcheck(element, 'required',$pintu)||/(.*)\.(doc|xls|docx|xlsx|pdf)$/.test($pintu);
			break;
		case "mp3":
			return !formcheck(element, 'required',$pintu)||/(.*)\.(mp3)$/.test($pintu);
			break;
		case "video":
			return !formcheck(element, 'required',$pintu)||/(.*)\.(rm|rmvb|wmv|avi|mp4|3gp|mkv)$/.test($pintu);
			break;
		case "flash":
			return !formcheck(element, 'required',$pintu)||/(.*)\.(swf|fla|flv)$/.test($pintu);
			break;
		case "radio":
			var radio = element.closest('form').find('input[name="' + element.attr("name") + '"]:checked').length;
			return !formcheck(element, 'required',$pintu)||eval(radio == 1);
			break;
		case "compare":
			var obj1 = $("input[name='"+value.type+"']") ,obj2 = $("select[name='"+value.type+"']"),obj3 = $("textarea[name='"+value.type+"']");
			var _val = "";
			if(obj1.length>0){_val = obj1.val();}
			if(obj2.length>0){_val = obj2.val();}
			if(obj3.length>0){_val = obj3.val();}
			if(value.symbol == '<'&& $pintu<_val){
				return !formcheck(element, 'required',$pintu)||true;
			}
			if(value.symbol=='<='&& $pintu<=_val){
					return !formcheck(element, 'required',$pintu)||true;
			}
			if(value.symbol=='>'&& $pintu>_val){
				return !formcheck(element, 'required',$pintu)||true;
			}
			if(value.symbol=='>='&& $pintu>=_val){
				return !formcheck(element, 'required',$pintu)||true;
			}
			if(value.symbol=='=='&& $pintu==_val){
				return !formcheck(element, 'required',$pintu)||true;
			}
			if(value.symbol=='!='&& $pintu!=_val){
				return !formcheck(element, 'required',$pintu)||true;
			}
			return false;
			break;
		case "comptime":
			var obj1 = $("input[name='"+value.type+"']") ,obj2 = $("select[name='"+value.type+"']"),obj3 = $("textarea[name='"+value.type+"']");
			var _val = "";
			if(obj1.length>0){_val = obj1.val();}
			if(obj2.length>0){_val = obj2.val();}
			if(obj3.length>0){_val = obj3.val();}
			
			var r = comptime($pintu,_val);
			if(value.symbol == '<'&& r<0){
					return !formcheck(element, 'required',$pintu)||true;
			}
			if(value.symbol=='<='&&r<=0){
					return !formcheck(element, 'required',$pintu)||true;
			}
			if(value.symbol=='>'&&r>0){
				return !formcheck(element, 'required',$pintu)||true;
			}
			if(value.symbol=='>='&&r>=0){
				return !formcheck(element, 'required',$pintu)||true;
			}
			if(value.symbol=='=='&&r==0){
				return !formcheck(element, 'required',$pintu)||true;
			}
			if(value.symbol=='!='&&r!=0){
				return !formcheck(element, 'required',$pintu)||true;
			}
			return false;
			break;
		default:
			
			var content = $("<div>"+$pintu+"</div>").text();

			if(/s\d+$/.test(type)){
				var sl = parseInt(type.replace('s',''));
				if(sl<content.trim().length){
					return !formcheck(element, 'required',$pintu)||false;
				}
			}
			
			// s50 50个以内   s50-100  50-100以内  必须加 required
			if(/s\d+\-\d+$/.test(type)){
				var sls = type.replace('s','').split('-');
				if(sls[0]>content.trim().length || sls[1]< content.trim().length){	
					return !formcheck(element, 'required',$pintu)||false;
				}
			}
			
			var compare_boolean='';
			if(/compare:/.test(type)){
				type = type.replace('compare:','');
				compare_boolean = 'compare';
			}
			if(/comptime:/.test(type)){
				type = type.replace('comptime:','');
				compare_boolean = 'comptime';
			}
			if(compare_boolean!=''){
				var _type;
				var symbol;
				if(/\=/.test(type)){
					 symbol='==';
					 _type=type.replace('=','');
				 }
				 if(/\>/.test(type)){
					 symbol='>';
					 _type=type.replace('>','');
				 }
				 if(/\</.test(type)){
					 symbol='<';
					 _type=type.replace('<','');
				 }
				 if(/\!\=/.test(type)){
					 symbol='!=';
					 _type=type.replace('!=','');
				 }
				
				 if(/\>\=/.test(type)){
					 symbol='>=';
					 _type=type.replace('>=','');
				 }
				 if(/\<\=/.test(type)){
					 symbol='<=';
					 _type=type.replace('<=','');
				 }
				return formcheck(element, compare_boolean, {'type':_type,'symbol':symbol,replace:function(a,b){return content;}});
			}
			
			return true;
	}
};
function validate_only(obj,e){
	var check_prevent = false;
	var focus_index = 0;
	var validate_num=0;
	if(obj.attr('data-validate') !== undefined){
		validate_num++;
	}
	if(obj.attr('data-validate-msg')!==undefined){
		validate_num++;
	}
	
	if(validate_num==2){     // 规则和提示都有
		
		var val  = obj.val();    // 取得值
		var rule = obj.attr('data-validate');
		var tips_msg = obj.attr('data-validate-msg');
		
		var rules = rule.split(';');           // 规则分为数值
		var tips_msgs = tips_msg.split(';');   // 提示消息分为数值
		var tag  = obj[0].tagName;             // 取得标签名称
		var _tag = tag+'[name=\''+obj.attr('name')+'\']';  // 重新组合要素选择器 
		//console.log(tag+":"+val);
		// 如果是输入框
		if(tag == 'TEXTAREA'){
			var prev_editor = $(_tag).prev();  // 取得输入框的上一个元素
			///console.log(prev_editor);
			var editor = 'editor_'+obj.attr('name');  // 当前元素的编辑器的名称
			// 如果存在编辑器                                         window[editor] 编辑器内容变量
			if(prev_editor.has('.ke-container') && window[editor] !==undefined){
				val = window[editor].html();       // 取得编辑器内容
				_tag = prev_editor;       
			}
		}
		
		
		for(var r_i=0;r_i<rules.length;r_i++){
			
			if(!formcheck(obj,rules[r_i],val)){
				
				focus_index++;
				check_prevent = true;
				//if(focus_index==1)obj.focus();
				var error_message = tips_msgs[r_i];
				
				
				//layer.tips(tips_msgs[r_i], _tag,{tips: [2, '#F24100'],tipsMore:true});
				break;
			}
		}
	}
	if(check_prevent){
		//e.preventDefault();
		return {"is_correct":false,"error_msg":error_message}; // 错误则返回对应的错误消息 
	}
	//console.log(val);
	return {"is_correct":true,"error_msg":""}; // 错误则返回对应的错误消息 
}
function validate_form(_from,e){
	var check_prevent = false;
	var focus_index = 0;
	for(var i in $(_from)[0]){
		var obj = $($(_from)[0].elements[i]);
		var validate_num = 0;
		if(obj.length>0){
			
			if(obj.attr('data-validate') !== undefined){
				validate_num++;
			}
			if(obj.attr('data-validate-msg')!==undefined){
				validate_num++;
			}
			
			if(validate_num==2){
				
				var val  = obj.val();
				var rule = obj.attr('data-validate');
				var tips_msg = obj.attr('data-validate-msg');
				
				var rules = rule.split(';');
				var tips_msgs = tips_msg.split(';');
				var tag  = obj[0].tagName;
				var _tag = tag+'[name=\''+obj.attr('name')+'\']';
				//console.log(tag+":"+val);
				
				if(tag == 'TEXTAREA'){
					var prev_editor = $(_tag).prev();
					///console.log(prev_editor);
					var editor = 'editor_'+obj.attr('name');
					if(prev_editor.has('.ke-container') && window[editor] !==undefined){
						val = window[editor].html();
						_tag = prev_editor;
					}
				}
				for(var r_i=0;r_i<rules.length;r_i++){
					
					if(!formcheck(obj,rules[r_i],val)){
						
						focus_index++;
						check_prevent = true;
						//if(focus_index==1)obj.focus();
						_from.find(".error-msg").html(tips_msgs[r_i]);
							obj.addClass('error-msg-border').keyup(function(){
							$(this).removeClass('error-msg-border');//清除红色框
							_from.find(".error-msg").html("");//清除错误提示
						})
						//layer.tips(tips_msgs[r_i], _tag,{tips: [2, '#F24100'],tipsMore:true});
						break;
					}
				}
				if(check_prevent){break;}
			}
		}
	}
	
	if(check_prevent){
		e.preventDefault();	
		return false;
	}
	return true;
}



function setDynamicValidate(cur_area,opts){
	
	if($(cur_area).length<=0 || $(cur_area).html().trim() == ''||$(cur_area).find('dl').length<=0||/loading\.gif/.test($(cur_area).html().trim())){
		setTimeout(function(){
			setDynamicValidate(cur_area,opts);
		},500);
		//console.log("LOG:Dynamic");
		return ;
	}
	
	for(var k in opts){
		
		var obj1 = $(cur_area).find("input[name='"+k+"']") ,
		obj2 = $(cur_area).find("select[name='"+k+"']") ,
		obj3 = $(cur_area).find("textarea[name='"+k+"']");
		
		var attrs = {'data-validate':opts[k].validate,'data-validate-msg':opts[k].validate_msg};
		obj1.attr(attrs);
		obj2.attr(attrs);
		obj3.attr(attrs);
		if(opts[k].required){
			obj1.parent().parent().find("dt").prepend('<font color="red">*</font>');
			obj2.parent().parent().find("dt").prepend('<font color="red">*</font>');
			obj3.parent().parent().find("dt").prepend('<font color="red">*</font>');
		}
		
	}
}
function comptime(beginTime,endTime) {
    var beginTimes = beginTime.substring(0, 10).split('-');
    var endTimes = endTime.substring(0, 10).split('-');
    //console.log("[TIME]:"+beginTimes);
    //console.log("[TIME]:"+endTimes);
    beginTime = beginTimes[1] + '-' + beginTimes[2] + '-' + beginTimes[0] + ' ' + beginTime.substring(10, 19);
    endTime = endTimes[1] + '-' + endTimes[2] + '-' + endTimes[0] + ' ' + endTime.substring(10, 19);
   // console.log("[TIME]:"+beginTime);
    //console.log("[TIME]:"+endTime);
    var a = ( Date.parse(beginTime)-Date.parse(endTime)) / 3600 / 1000;
    //console.log("[TIME]:"+a);
    return a; 
}


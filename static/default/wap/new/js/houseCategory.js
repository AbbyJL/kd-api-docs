function catSuccess(){
	if(window['loadCat']===0){
		showFilter({ibox:'filter2',content1:'parent_container',content2:'inner_container',fullbg:'fullbg'});
		return false;
	}else{
		setTimeout(function(){catSuccess()},300);
	}
}
function getHouseCat(arr1,arr2){
	window['loadCat'] = arguments.length;
	catSuccess&&catSuccess.call(this);//全部加载成功后回调函数
	var url_arr1='',url1='',url_arr2='',url2='';
	if(typeof arr1 !== 'undefined'){
		url1 = '/api/HouseCategory/SearchHouseCategory';
		$.ajax({url:url1,dataType:'json',data:{'styleids':arr1.join(','),'timer':Math.random()},success:function(data){
			var key,i,txt='',fortxtid='';
			for(key in data){
				txt='';
				switch(key){
					case '1':
					fortxtid='pricerange';
					break;
					case '2':
					fortxtid='pricerange';
					break;
					case '3':
					fortxtid='pricerange';
					break;
					case '4':
					fortxtid='arearange';
					break;
					case '5':
					fortxtid='roomrange';
					break;
					default:
					fortxtid='';
				}
				
				for(i=0;i<data[key].length;i++){
					txt+='<li><a href="#" class="all" id="s_'+fortxtid+'_'+data[key][i]['Categoryid']+'" onclick="return filterClick(this,{'+fortxtid+':\''+data[key][i]['Categoryid']+'\'},\'s_txt_'+fortxtid+'\',\''+data[key][i]['Chrcategory']+'\');">'+data[key][i]['Chrcategory']+'</a></li>';
				}
				$('#cat_'+key).html($('#cat_'+key).html()+txt);
				if(key ==='2'&&$('#cat_1')[0]){
					$('#cat_1').html($('#cat_1').html()+txt);
				}
			}
			window['loadCat']--;
			
		}});
	}
	if(typeof arr2 !== 'undefined'){
		url2 = '/api/Category/SearchCategory';
		$.ajax({url:url2,dataType:'json',data:{'styleids':arr2.join(','),'timer':Math.random()},success:function(data){
			var key,i,txt='',fortxtid='',fortxtid2='',entitys,entitys_len=0;
			for(key in data){
				txt='';
				switch(key){
					case '0':
						fortxtid='region';
						fortxtid2='section';
						txt+='<li><a href="#" onclick="return filterClick(this,{'+fortxtid+':\'-1\','+fortxtid2+':\'-1\'},\'s_txt_'+fortxtid+'\',\'位置\');" id="s_'+fortxtid+'_-1" data-isall="1">不限</a></li>';
						for(i=0;i<data[key].length;i++){
							txt+='<li categoryid="'+data[key][i]['Categoryid']+'"><a href="#" id="s_'+fortxtid+'_'+data[key][i]['Categoryid']+'" class="item" data-ajax="1">'+data[key][i]['Chrcategory']+'</a>';
							entitys_len = data[key][i]['Entities'].length;
							if(entitys_len===0){
								txt+='</li>';
							}else{
								txt+='<ul class="display0"><li><a onclick="return filterClick(this,{'+fortxtid+':\''+data[key][i]['Categoryid']+'\','+fortxtid2+':\'-1\'},\'s_txt_'+fortxtid+'\',\''+data[key][i]['Chrcategory']+'\');" id="s_'+fortxtid2+'_'+data[key][i]['Categoryid']+'" href="#" data-double="1">不限</a></li>';
								for(entitys=0;entitys<entitys_len;entitys++){
									txt+='<li><a href="#" id="s_'+fortxtid2+'_'+data[key][i]['Entities'][entitys]['Categoryid']+'" onclick="return filterClick(this,{'+fortxtid+':\''+data[key][i]['Categoryid']+'\','+fortxtid2+':\''+data[key][i]['Entities'][entitys]['Categoryid']+'\'},\'s_txt_'+fortxtid+'\',\''+data[key][i]['Entities'][entitys]['Chrcategory']+'\');" data-double="1">'+data[key][i]['Entities'][entitys]['Chrcategory']+'</a></li>';
								}
								txt+='</ul></li>';
							}
						}
						$('#cat_'+key).html($('#cat_'+key).html()+txt);
					break;
					case '23':
						fortxtid='shoptype';
					break;
					case '27':
						fortxtid='businesstype';
					break;
					case '14':
						fortxtid='decoratetype';
					break;
					default:
					fortxtid='';
				}
				if(key !== '0'){
					for(i=0;i<data[key].length;i++){
						txt+='<li><a href="#" class="all" id="s_'+fortxtid+'_'+data[key][i]['Categoryid']+'" onclick="return filterClick(this,{'+fortxtid+':\''+data[key][i]['Categoryid']+'\'},\'s_txt_'+fortxtid+'\',\''+data[key][i]['Chrcategory']+'\');">'+data[key][i]['Chrcategory']+'</a></li>';
					}
					$('#cat_'+key).html($('#cat_'+key).html()+txt);
				}
			}
			window['loadCat']--;
		}});
	}
}





function getHouseCatJSON(styleid,sid,vueName,callback){//styleid  对应两个接口
	var url='';
	if(styleid === '0'){
		url = '/api/HouseCategory/SearchHouseCategory';
		$.ajax({'url':url,dataType:'json',data:{'styleids':sid,'timer':Math.random()},success:function(data){
			callback&&callback.call(this,data[sid],sid,vueName);
		}});
	}else{
		url = '/api/Category/SearchCategory';
		$.ajax({'url':url,dataType:'json',data:{'styleids':sid,'type':'1','timer':Math.random()},success:function(data){
			callback&&callback.call(this,data[sid],sid,vueName);
		}});
	}
}
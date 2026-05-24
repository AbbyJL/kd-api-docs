window['iswxsmall'] = false;
wx.miniProgram.getEnv(function(res){//是否微信小程序
	if(res.miniprogram){
		window['iswxsmall'] = true;
		$('body').eq(0).addClass('iswxsmall');
		$('#member_house_index').click(function(e){
			e.preventDefault();
			wx.miniProgram.navigateTo({url: '/pages/index/index'})
		});
		$('.member_house_publish').click(function(e){
			e.preventDefault();
			wx.miniProgram.navigateTo({url: '/pages/memberhouse/publishhouse'})
		});
		$('#member_house_publishxuqiu').click(function(e){
			e.preventDefault();
			wx.miniProgram.navigateTo({url: '/pages/house/publishxuqiu'})
		});
	}
});
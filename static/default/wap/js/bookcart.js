
function remove(sid,goods){
    var res ={};
    for(a in goods){
        if(sid != a){
            res[a]=goods[a];
        }
    }
    return res;
}

window.book = {
    addcart: function(shop_id, datas){
        with(window){
            var goods;
            if(!cookies.isset('book')){
                datas['num'] = 1;
                goods = {};
                goods[shop_id] = [];
                goods[shop_id][0] = datas;
                goods = cookies.stringify(goods);
                cookies.set('book', goods);
            }else{
               
                goods = cookies.get('book');
                goods = cookies.parse(goods);
                //遍历
                for(var sid in goods){
                    if(sid != shop_id){
                        goods = remove(sid,goods);
                    }
                }
                if(!goods[shop_id]){
                    datas['num'] = 0;
                    goods = {};
                    goods[shop_id] = [];
                    goods[shop_id][0] = datas;
                }
                    
                
                var is_in = false, is_here = false;
                for(var sid in goods){
                    if(sid == shop_id){
                        is_in = true;
                        for(var index in goods[sid]){
                            if(goods[sid][index]['menu_id'] == datas['menu_id']){
                                is_here = true;
                                break;
                            }
                        }
                        break;
                    }else{
                        layer.msg('请清空购物车后重新购物');
                        return false;
                    }
                }
                //该店存在
                if (is_in){
                    //商品存在
                    if(is_here){
                        if(window.book.count() < 99){
                            goods[shop_id][index]['num']++;
                        }else{
                            layer.msg('购物车商品数已经满99,不能再添加商品');
                        }
                        goods = cookies.stringify(goods);
                        cookies.set('book', goods);
                    }else{
                        datas['num'] = 1;
                        goods[shop_id].push(datas);
                        goods = cookies.stringify(goods);
                        cookies.set('book', goods);
                    }
                }else{
                    datas['num'] = 1;
                    goods[shop_id] = [];
                    goods[shop_id].push(datas);
                    goods = cookies.stringify(goods);
                    cookies.set('book', goods);
                }
            }
        }
    },
	//添加购物车
    getcart: function (){
        with(window){
            if(!cookies.isset('book')){
                //购物车没商品
                return false;
            }
            var goods = cookies.get('book');
            goods = cookies.parse(goods);
            return goods;
        }
    },
	
    inc: function (shop_id, menu_id){
        var goods = window.book.getcart();
        if(!goods){
            //这种情况暂时不会发生
            layer.msg('该商品不在购物车中,请重新添加');
        }else{
            //假设该商品存在
            for(var i in goods[shop_id]){
                if(goods[shop_id][i]['menu_id'] == menu_id){
                    if(window.book.count() >= 99) {
                        layer.msg('购物车商品数已经满99,不能再添加商品');
                    }else{
                        goods[shop_id][i]['num']++;
                        goods = window.cookies.stringify(goods);
                        window.cookies.set('book', goods);
                    }
                    break;
                }
            }
        }
    },
	
	
    dec: function (shop_id, menu_id){
        var goods = window.book.getcart();
        if(!goods){
            layer.msg('该商品不在购物车中,请重新添加');
        }else{
            for(var i in goods[shop_id]) {
                if(goods[shop_id][i]['menu_id'] == menu_id){
                    if(window.book.itemcount(menu_id) <= 0){
                        return false;
                    }else{
                        goods[shop_id][i]['num']--;
                        goods = window.cookies.stringify(goods);
                        window.cookies.set('book', goods);
                    }
                    break;
                }
            }
        }
    },
	
	
    count: function (shop_id){
        var goods = window.book.getcart();
        if(!goods){
            return '0';
        }else{
            var num = 0;
            for(var i in goods){
                if(i==shop_id){
                    for(var index in goods[i]){
                        num += parseInt(goods[i][index]['num']);
                    }
                }
            }
            return num;
        }
    },
	
    itemcount: function (menu_id){
        var goods = window.book.getcart();
        if(!goods){
            return '0';
        }else{
            var num = 0;
            for(var i in goods){
                for(var index in goods[i]){
                    if(goods[i][index]['menu_id'] == menu_id){
                        num = goods[i][index]['num'];
                    }
                }
            }
            return num;
        }
    },
    totalprice: function (shop_id){
        var goods = window.book.getcart();
        if(!goods){
            return '0';
        }else{
            var num = 0;
            for (var i in goods){
                if(i==shop_id){
                    for (var index in goods[i]) {
                        num += goods[i][index]['num'] * goods[i][index]['book_price'];
                    }
                }
            }
			return num.toFixed(2);//去更新缓存啊
        }
    },
	
    removeby: function (menu_id){
        var goods = window.book.getcart(), 
		r = false;
        if(goods){
            for(var i in goods){
                for(var index in goods[i]) {
                    if(goods[i][index]['menu_id'] == menu_id){
                        goods[i].splice(index, 1);
                        layer.msg(window.book.count());
                        goods = window.cookies.stringify(goods);
                        window.cookies.set('book', goods);
                        r = true;
                        break;
                    }
                }
            }
        }
        return r;
    }
	
	
	
}


var h5LocationInit = {
    fn: !1
};
var HN_Location = {
    init: function(func, notApp, force){

        if(force){
            var cookieData = this.cookie('get');
            if(cookieData){
                func(cookieData);
                return;
            }
        }

        var protocol = location.protocol;
        var ua = navigator.userAgent.toLowerCase();//获取判断用的对象
        if (ua.match(/MicroMessenger/i) == "micromessenger" && site_map != "google") {
            this.wx(func);
        }else if (ua.indexOf('huoniao') > -1 && !notApp){
            this.App(func);
        }else if ("https" === location.protocol.substring(0, 5) && !notApp){
            this.h5(func);
        }else if (site_map == "baidu") {
            this.baiduMap(func);
        }else if (site_map == "google") {
            this.googleMap(func);
        }else if (site_map == "qq") {
            this.qqMap(func);
        }else if (site_map == "amap") {
            this.amap(func);
        }else{
            func();
        }
    }
    ,cookie: function(type, data){
        var pre = window.cookiePre ? window.cookiePre : 'HN_';
        if(type == 'get'){
            var data_ = $.cookie(pre + 'LocationData');
            if(data_){
                return JSON.parse(data_);
            }
        }else if(type == 'set'){
            var now = new Date();
            now.setTime(now.getTime() + 5 * 60 * 1000);  //5分钟
            $.cookie(pre + 'LocationData', JSON.stringify(data), {expires: now, path: '/'});
        }
    }
    ,wx: function(func){
        wx.ready(function() {
            wx.getLocation({
                type: 'gcj02',
                success: function (res) {
                    var lng = res.longitude;
                    var lat = res.latitude;

                    mapObj = new AMap.Map('map_amap');
                    lnglatXY = [lng,lat];
                    // 高德地图定位逆解析，微信端本身使用高德逆解析不用使用其他地图进行解析
                    mapObj.plugin('AMap.Geocoder', function () {
                        var geocoder = new AMap.Geocoder({
                            radius: 1000,
                            extensions: "all"
                        });
                        geocoder.getAddress(lnglatXY, function(status, result) {
                            if(status == "error"){
                                func();
                                return;
                            }
                            if(result.regeocode.pois == "" || result.regeocode.pois == undefined){
                                var LocationName = result.regeocode.addressComponent.street+result.regeocode.addressComponent.streetNumber;
                            }else{
                                var LocationName = result.regeocode.pois[0].name;
                            }
                            var address = result.regeocode.formattedAddress;
                            var province = result.regeocode.addressComponent.province;
                            var city = result.regeocode.addressComponent.city;
                            var district = result.regeocode.addressComponent.district;

                            city = city == '' ? province : city;
                            var data = {'name': LocationName, 'address': address, 'lng': lng, 'lat': lat, 'province': province, 'city': city, 'district': district};
                            HN_Location.cookie('set', data);
                            func(data);

                        });
                    });
                },
                fail: function(err) {
                    func();
                }
            });
        });
    },
    // 调用APP定位
    App: function(func){
        setupWebViewJavascriptBridge(function(bridge) {
            var isLoad;
            bridge.callHandler("getGeocoder", {}, function(responseData){
                var data = JSON.parse(responseData);
                var LocationName = data.name;
                var address = data.address;
                var lng = data.point.lng;
                var lat = data.point.lat;
                var province = data.province;
                var city = data.city;
                var district = data.district;
                isLoad = true;
                var data = {'name': LocationName, 'address': address, 'lng': lng, 'lat': lat, 'province': province, 'city': city, 'district': district};
                if(lng && lat){
                    HN_Location.cookie('set', data);
                    func(data);
                }else{
                    HN_Location.init(func, true);
                }
            });

            //如果10秒还没有请求到，返回空值
            setTimeout(function(){
                if(!isLoad){
                    HN_Location.init(func, true);
                }
            }, 5000);

        });
    },
    //H5定位
    h5: function(func){
        if (navigator.geolocation) {
            if ("function" == typeof func) {
                h5LocationInit.fn = func
            }
            window.touchH5LocationCallback = function(f, g) {
                if(f == null){
                    HN_Location.getLocationByGeocoding(g);
                }else{
                    // getLocationError(f);
                    HN_Location.init(func, true);
                }
                $("#touchH5LocationIframe").remove();
            },
                $('<iframe src="javascript:(function(){ window.navigator.geolocation.getCurrentPosition(function(position){parent && parent.touchH5LocationCallback && parent.touchH5LocationCallback(null,position);}, function(err){parent && parent.touchH5LocationCallback && parent.touchH5LocationCallback(err);}, {enableHighAccuracy: 1, maximumAge: 10000, timeout: 5000});})()" style="display:none;" id="touchH5LocationIframe" ></iframe>').appendTo("body")
        } else {
            // var r = {
            //   tips: "浏览器不支持定位"
            // };
            // "function" == typeof func ? func(r) : "[object Object]" === Object.prototype.toString.call(func) && func.fn && func.fn(r)
            HN_Location.init(func, true);
        }
    },
    // 百度定位
    baiduMap: function(func){
        var geolocation = new BMap.Geolocation();
        geolocation.getCurrentPosition(function(r){
            if(this.getStatus() == BMAP_STATUS_SUCCESS){
                var geoc = new BMap.Geocoder();
                geoc.getLocation(r.point, function(rs){

                    if (rs.surroundingPois == "" || rs.surroundingPois == undefined) {
                        var LocationName = rs.addressComponents.street +rs.addressComponents.streetNumber;
                    }else{
                        var LocationName = rs.surroundingPois[0].title;
                    }
                    var address = rs.address;
                    var lng = rs.point.lng;
                    var lat = rs.point.lat;
                    var province = rs.addressComponents.province;
                    var city = rs.addressComponents.city;
                    var district = rs.addressComponents.district;

                    var data = {'name': LocationName, 'address': address, 'lng': lng, 'lat': lat, 'province': province, 'city': city, 'district': district};
                    HN_Location.cookie('set', data);
                    func(data);

                });
            }
            else {
                func();
                alert('failed'+this.getStatus());
            }
        },{enableHighAccuracy: true})

    },
    // 谷歌定位
    googleMap: function(func){
        if (navigator.geolocation) {

            //获取当前地理位置
            navigator.geolocation.getCurrentPosition(function(position) {
                var coords = position.coords;
                lat = coords.latitude;
                lng = coords.longitude;

                //指定一个google地图上的坐标点，同时指定该坐标点的横坐标和纵坐标
                var latlng = new google.maps.LatLng(lat, lng);
                var geocoder = new google.maps.Geocoder();
                geocoder.geocode( {'location': latlng}, function(results, status) {
                    if (status == google.maps.GeocoderStatus.OK) {

                        var time = Date.parse(new Date());
                        var resultArr = results[0].address_components, address = "" ,LocationName = "",province = "",city = "",district = "";

                        for (var i = 0; i < resultArr.length; i++) {
                            var type = resultArr[i].types[0] ? resultArr[i].types[0] : 0;
                            if (type && type == "street_number") {
                                LocationName = resultArr[i].short_name;
                            }
                            if (type && type == "route") {
                                address = address + resultArr[i].short_name;
                            }
                            if (type && type == "political") {
                                district = resultArr[i].short_name;
                            }
                            if (type && type == "locality") {
                                city = resultArr[i].short_name;
                            }
                            if (type && type == "administrative_area_level_1") {
                                province = resultArr[i].short_name;
                            }
                        }

                        var data = {'name': LocationName + ' ' + address, 'address': address, 'lng': lng, 'lat': lat, 'province': province, 'city': city, 'district': district};
                        HN_Location.cookie('set', data);
                        func(data);

                    } else {
                        func();
                        alert('Geocode was not successful for the following reason: ' + status);
                    }
                });

            }, function getError(error){
                func();
                switch(error.code){
                    case error.TIMEOUT:
                        alert('超时');//超时
                        break;
                    case error.PERMISSION_DENIED:
                        alert('用户拒绝提供地理位置');//用户拒绝提供地理位置
                        break;
                    case error.POSITION_UNAVAILABLE:
                        alert('地理位置不可用');//地理位置不可用
                        break;
                    default:
                        break;
                }
            })
        }else {
            func();
            alert(langData['waimai'][3][72])
        }
    },
    // 腾讯定位
    qqMap: function(func){
        if (navigator.geolocation) {
            var geolocation = new qq.maps.Geolocation(site_map_key, "hn");
            window.addEventListener('message', function(event) {
                // 接收位置信息
                var loc = event.data;

                if(loc && loc.module == 'geolocation'){
                    lat = loc.lat;
                    lng = loc.lng;

                    geocoder = new qq.maps.Geocoder({
                        complete:function(result){
                            var data = result.detail;

                            if (data.nearPois == "" || data.nearPois == undefined) {
                                var LocationName = data.addressComponents.street;
                            }else{
                                var LocationName = data.nearPois[0].name;
                            }
                            var address = data.addressComponents.province+data.addressComponents.city+data.addressComponents.district+data.addressComponents.street+data.addressComponents.streetNumber;
                            var lng = data.location.lng;
                            var lat = data.location.lat;
                            var province = data.addressComponents.province;
                            var city = data.addressComponents.city;
                            var district = data.addressComponents.district;

                            var data = {'name': LocationName, 'address': address, 'lng': lng, 'lat': lat, 'province': province, 'city': city, 'district': district};
                            HN_Location.cookie('set', data);
                            func(data);

                        }
                    });
                    var coord=new qq.maps.LatLng(lat,lng);
                    geocoder.getAddress(coord);

                }else {
                }
            }, false);
        }else{
            func();
        }
    },
    // 高德定位
    amap: function(func){
        mapObj = new AMap.Map('map_amap');
        mapObj.plugin('AMap.Geolocation', function () {
            var geolocation = new AMap.Geolocation({
                enableHighAccuracy: true,//是否使用高精度定位，默认:true
                timeout: 10000,          //超过10秒后停止定位，默认：无穷大
                maximumAge: 0,           //定位结果缓存0毫秒，默认：0
                convert: true           //自动偏移坐标，偏移后的坐标为高德坐标，默认：true
            });

            geolocation.getCurrentPosition(function(status,result){
                if(result.info == "SUCCESS"){

                    var lat = result.position.lat;
                    var lng = result.position.lng;
                    if (result.addressComponent.neighborhood != "") {
                        var LocationName = result.addressComponent.neighborhood;
                    }else if(result.addressComponent.street != ""){
                        var LocationName = result.addressComponent.street+result.addressComponent.streetNumber;
                    }else {
                        var LocationName = result.addressComponent.district;
                    }
                    var address = result.formattedAddress;
                    var province = result.addressComponent.province;
                    var city = result.addressComponent.city;
                    var district = result.addressComponent.district;

                    city = city == '' ? province : city;
                    var data = {'name': LocationName, 'address': address, 'lng': lng, 'lat': lat, 'province': province, 'city': city, 'district': district};
                    HN_Location.cookie('set', data);
                    func(data);


                }else {
                    func();
                }
            });
        });
    },

    //根据GPS坐标获取详细信息
    getLocationByGeocoding: function(c) {
        var f = {
            latitude: c.coords.latitude,
            longitude: c.coords.longitude
        };
        var url = (typeof masterDomain != "undefined" ? masterDomain : "") + "/app/api/getLocationByGeocoding?location=" + f.latitude + "," + f.longitude;
        return void $.ajax({
            url: url,
            dataType: "jsonp",
            success: function(b) {
                if(b && b.state == 100){
                    h5LocationInit.fn && h5LocationInit.fn(b.info)
                }else{
                    HN_Location.init(h5LocationInit.fn, true);
                }
            },
            error: function(){
                HN_Location.init(h5LocationInit.fn, true);
            }
        })
    },

    //GPS获取失败
    getLocationError: function(c) {
        var d = {};
        switch (c.code) {
            case c.PERMISSION_DENIED:
                d.tips = '用户拒绝获取地理位置',//用户拒绝获取地理位置
                    d.reject = !0;
                break;
            case c.POSITION_UNAVAILABLE:
                d.tips = '位置信息不可用';//位置信息不可用
                break;
            case c.TIMEOUT:
                d.tips = '获取用户位置请求超时';//获取用户位置请求超时
                break;
            case c.UNKNOWN_ERROR:
                d.tips = '获取位置失败，请重试' //获取位置失败，请重试
        }
        h5LocationInit.fn && h5LocationInit.fn(d)
    }
}

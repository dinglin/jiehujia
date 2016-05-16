var locHref = window.location.href;
var $sessionStorage = window.sessionStorage;
var $localStorage = window.localStorage;
var index_php;
var myscroll;
var baiduApiKey = "";
var siteFlag = 0;
var configUrl;

var bd_info, bd_sign, app_id, bd_framework;
var globe = {
	updown: true,
	linkFn: "singleTap",
	contextPath: "clutter",
	picUrl: "",
	CONTENT_LOGDING: "loading",
	message: null,
	mOrqing: "m",
	pullDownAction: function() {
		setTimeout(function() {
			myScroll.refresh()
		}, 1e3)
	},
	pullUpAction: function() {
		setTimeout(function() {
			myScroll.refresh()
		}, 1e3)
	},
	skip: function(link) {
		var that = this;
		var t = that.getLink(link);
		switch (t) {
			case 1:
				window.location.href = link;
				break;
			case 2:
				window.location.href = link;
				break;
			case 0:
				window.location.href = link;
				break
		}
	},
	getLink: function(link) {
		if (link.indexOf("clutter/") != -1) {
			return 1
		} else if (link.indexOf("static/") != -1) {
			return 2
		} else {
			return 0
		}
	},
	getWap: function() {
		var t = $localStorage.getItem("isWap");
		if (t == "wap") {
			return true
		} else {
			return false
		}
	},
	isLogin: function() {
		var that = this;
		$.ajax({
			type: "post",
			url: "/clutter/getuseInfo.htm",
			dataType: "json",
			async: false,
			success: function(data) {
				if (data.userName) {
					var _data = data;
					var arr = [];
					var time = that.getServerTime();
					if (_data) {
						if (_data.nickName) {
							var nickName = _data.nickName
						} else {
							var nickName = ""
						}
						arr.push({
							nickName: nickName,
							userName: _data.userName,
							time: time
						})
					}
					$localStorage.setItem("loginMsg", JSON.stringify(arr))
				}
			}
		})
	},
	localStorageIsLogin: function() {
		var that = this;
		if ($localStorage.loginMsg != undefined && $localStorage.loginMsg != null && $localStorage.loginMsg != "") {
			var data = JSON.parse($localStorage.getItem("loginMsg"));
			var time1 = data[0].time;
			var time2 = that.getServerTime();
			var subTime = that.diffDate(time2, time1);
			if (subTime > 30) {
				$localStorage.removeItem("loginMsg");
				that.isLogin()
			}
		} else {
			that.isLogin()
		}
	},
	createDialog: function(stri, type, callback, othercall) {
		var that = this;
		var _h = $("body").height();
		var _w = $("html").width();
		var str = "";
		str += '<div class="modal" style="width:' + _w + "px;height:" + _h + 'px;z-index:19;display:block;"></div>';
		str += '<div class="popup_wrap" style="top: 170px; display: block;z-index:20;">';
		str += '<section class="popup find_popup">';
		str += "<header>提示信息</header>";
		str += "<article>";
		str += "<p>" + stri + "</p>";
		str += "</article>";
		str += "<footer>";
		if (type == 2) {
			str += '<a href="javascript:;" class="ic_yellow">取消</a><a href="javascript:;" class="ic_roseo">确定</a>'
		} else if (type == 1) {
			str += '<a href="javascript:;" class="ic_roseo">确定</a>'
		}
		str += "</footer>";
		str += "</section>";
		str += "</div>";
		$("body").append(str);
		if (type == 2) {
			$("body").on(that.linkFn, ".popup_wrap .ic_yellow", function() {
				if (othercall) {
					othercall()
				}
				that.revmoePop()
			});
			$("body").on("touchend", ".popup_wrap .ic_yellow", function(e) {
				e.preventDefault()
			})
		} else if (type == 1) {
			$("body").on(that.linkFn, ".popup_wrap .ic_roseo", function() {
				that.revmoePop()
			})
		}
		$(".popup_wrap .ic_roseo").bind(that.linkFn, function() {
			if (callback) {
				callback()
			}
		});
		$("body").on("touchend", ".popup_wrap .ic_roseo", function(e) {
			e.preventDefault()
		})
	},
	revmoePop: function() {
		var that = this;
		$(".popup_wrap .ic_roseo").unbind(that.linkFn);
		$(".modal").remove();
		$(".popup_wrap").remove()
	},
	isSessionLocation: function() {
		if ($sessionStorage.locationSite) {
			return true
		} else {
			return false
		}
	},
	cityOrSite: function(json) {
		var that = this;
		var s = that.getUrlParam("site");
		var address;
		if (s == 1) {
			address = json.city
		} else if (s == 2) {
			address = json.station_name
		}
		return address
	},
	getUrlParam: function(name) {
		var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
		var r = window.location.search.substr(1).match(reg);
		if (r != null) return unescape(r[2]);
		return null
	},
	wapToPc: function() {
		window.location.href = "/clutter/wap_to_lvmama.htm"
	},
	cookie: function(name) {
		var _cookieStr = document.cookie;
		var cookieArray = _cookieStr.split("; ");
		for (var i = 0; i < cookieArray.length; i++) {
			var arr = cookieArray[i].split("=");
			if (arr[0] == name) return decodeURIComponent(arr[1])
		}
		return ""
	},
	delCookie: function(name) {
		var date = new Date;
		date.setTime(date.getTime() - 1e4);
		document.cookie = name + "=;expires=" + new Date(0).toGMTString()
	},
	getCookie: function(objName) {
		var arrStr = document.cookie.split(";");
		for (var i = 0; i < arrStr.length; i++) {
			var temp = arrStr[i].split("=");
			if (temp[0] == objName) return decodeURIComponent(temp[1])
		}
	},
	addCookie: function(objName, objValue, objHours) {
		var str = objName + "=" + escape(objValue);
		if (objHours > 0) {
			var date = new Date;
			var ms = objHours * 3600 * 1e3;
			date.setTime(date.getTime() + ms);
			str += ";domain=domain.lvmama.com;expires=" + date.toGMTString()
		} else {
			str += ";domain=domain.lvmama.com;"
		}
		document.cookie = str
	},
	setCookie: function(name, value) {
		var Days = 30;
		var exp = new Date;
		exp.setTime(exp.getTime() + Days * 24 * 60 * 60 * 1e3);
		document.cookie = name + "=" + escape(value) + ";expires=" + exp.toGMTString()
	},
	getServerTime: function() {
		var xmlHttp = false;
		try {
			xmlHttp = new ActiveXObject("Msxml2.XMLHTTP")
		} catch (e) {
			try {
				xmlHttp = new ActiveXObject("Microsoft.XMLHTTP")
			} catch (e2) {
				xmlHttp = false
			}
		}
		if (!xmlHttp && typeof XMLHttpRequest != "undefined") {
			xmlHttp = new XMLHttpRequest
		}
		xmlHttp.open("GET", "/channel/sale/null.txt", false);
		xmlHttp.setRequestHeader("Range", "bytes=-1");
		xmlHttp.send(null);
		severtime = new Date(xmlHttp.getResponseHeader("Date"));
		return severtime
	},
	pagenaviLi: function() {
		var that = this;
		var date = that.getServerTime();
		var month = date.getMonth() + 1;
		var tab_Month = month;
		document.write('<li class="active">' + tab_Month + "月</li>");
		document.write("<li>" + ++tab_Month + "月</li>");
		document.write("<li>" + ++tab_Month + "月</li>");
		document.write("<li>" + ++tab_Month + "月</li>")
	},
	diffDate: function(str1, str2) {
		var d1;
		var d2;
		var difftime = 0;
		if (str1 == "") {
			d1 = new Date
		} else {
			d1 = str1
		} if (str2 == "") {
			d2 = new Date
		} else {
			d2 = str2
		}
		difftime = Date.parse(d1) - Date.parse(d2);
		difftime = difftime / 6e4;
		return difftime.toFixed(0)
	},
	createSearchShadow: function(obj, id, callback) {
		var h = $("." + obj).height();
		if ($("#" + id).length != 0) {
			$("#" + id).empty()
		} else {
			$("body").append('<div id="' + id + '" class="lv-search-list" style="width:100%;height:' + h + 'px;position:absolute;top:92px;background:#eee;z-index:112;"></div>')
		}
		callback()
	},
	isPc: function() {
		var that = this;
		if (/AppleWebKit.*Mobile/i.test(navigator.userAgent) || /MIDP|SymbianOS|NOKIA|SAMSUNG|LG|NEC|TCL|Alcatel|BIRD|DBTEL|Dopod|PHILIPS|HAIER|LENOVO|MOT-|Nokia|SonyEricsson|SIE-|Amoi|ZTE/.test(navigator.userAgent)) {
			if (window.location.href.indexOf("?mobile") < 0) {
				try {
					if (/Android|webOS|iPhone|iPod|BlackBerry/i.test(navigator.userAgent)) {
						that.linkFn = "singleTap"
					} else if (/iPad/i.test(navigator.userAgent)) {
						that.linkFn = "singleTap"
					} else {
						that.linkFn = "click"
					}
				} catch (e) {}
			}
		} else {
			that.linkFn = "click"
		}
	},
	disSearch: function() {
		$("#wrapper5").css("display", "block");
		$("#searchBg").css("display", "none")
	},
	addFavicon: function() {},
	createShadowBody: function() {
		var _h = $("body").height();
		var _w = $("html").width();
		var str = "";
		str += '<div class="modal" style="width:' + _w + "px;height:" + _h + 'px;z-index:19;display:block;"></div>';
		var _bd_framework = this.cookie("bd_framework") || this.getUrlParam("bd_framework") || $localStorage.getItem("bd_framework");
		if (_bd_framework != 1) {
			str += '<div class="popup_wrap" style="display: block;z-index:20;text-align:center;top:45%;color:#fff;"><p style="padding:10px;background:#000;opacity:0.7;border-radius:5px;display:inline;">订单正在提交中，请稍后...</p></div>'
		}
		$("body").append(str)
	},
	isMobile: function(val) {
		if (val == "") {
			return false
		}
		if (!val.match(/^1[3|4|5|7|8][0-9]\d{4,8}$/) || val.length != 11) {
			return false
		} else {
			return true
		}
	}
};
globe.isPc();
Function.prototype.getName = function() {
	return this.name || this.toString().match(/function\s*([^(]*)\(/)[1]
};

if (globe.getUrlParam("bd_framework") == 1) {
	$localStorage.setItem("bd_framework", 1)
}
bd_framework = globe.cookie("bd_framework") || globe.getUrlParam("bd_framework") || $localStorage.getItem("bd_framework");
var loginStatus = false;


String.prototype.Trim = function() {
	return this.replace(/(^\s*)|(\s*$)/g, "")
};
Array.prototype.unique = function(type) {
	var res = [this[0]];
	for (var i = 1; i < this.length; i++) {
		var repeat = false;
		for (var j = 0; j < res.length; j++) {
			if (this[i][type] == res[j][type]) {
				repeat = true;
				break
			}
		}
		if (!repeat) {
			res.push(this[i])
		}
	}
	return res
};
if (navigator.userAgent.indexOf("innerBrower") != -1 || navigator.userAgent.indexOf("LVMM") != -1) {} else {
	$localStorage.setItem("isWap", "wap")
}
$(function() {

	$("body").on(globe.linkFn, "header .icon-svg14", function() {});
	$("body").on(globe.linkFn, ".link", function() {
		window.location.href = $(this).attr("link")
	});
	$("body").on("touchend", ".link", function(e) {
		e.preventDefault()
	});
	
	if ($("#keyword").length != 0) {
		$("#keyword").focus(function(){
			$('#search_extend').show('slow');
		});
	}
	if ($(".search_close").length != 0) {
		$(".search_close").click(function(){
			$('#search_extend').hide();
		});
	}
	if ($(".swiper-main").length != 0) {
		var swiper = new Swiper(".swiper-banner", {
			pagination: ".pagination",
			loop: true,
			autoPlay: 3e3
		});
		$(".pagination .swiper-pagination-switch").click(function() {
			swiper.swipeTo($(this).index())
		});
		$(".swiper-banner .swiper-slide a").each(function() {
			var h = $(this).width() / 320 * 130;
			$(this).height(h)
		})
	}
	if ($("#wrapperScroll").length != 0) {
		function loaded() {
			myscroll = new iScroll("wrapperScroll", {
				useTransition: true,
				vScroll: true,
				hScroll: false,
				lockDirection: true,
				checkDOMChanges: true,
				useTransform: false
			});
			setTimeout(function() {
				document.getElementById("wrapperScroll").style.left = "0"
			}, 100)
		}
		window.addEventListener("DOMContentLoaded", loaded, !1)
	}
});
var base64 = {
	base64EncodeChars: "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/",
	base64DecodeChars: new Array(-1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, 62, -1, -1, -1, 63, 52, 53, 54, 55, 56, 57, 58, 59, 60, 61, -1, -1, -1, -1, -1, -1, -1, 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, -1, -1, -1, -1, -1, -1, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42, 43, 44, 45, 46, 47, 48, 49, 50, 51, -1, -1, -1, -1, -1),
	encode: function(str) {
		var out, i, len;
		var c1, c2, c3;
		len = str.length;
		i = 0;
		out = "";
		while (i < len) {
			c1 = str.charCodeAt(i++) & 255;
			if (i == len) {
				out += this.base64EncodeChars.charAt(c1 >> 2);
				out += this.base64EncodeChars.charAt((c1 & 3) << 4);
				out += "==";
				break
			}
			c2 = str.charCodeAt(i++);
			if (i == len) {
				out += this.base64EncodeChars.charAt(c1 >> 2);
				out += this.base64EncodeChars.charAt((c1 & 3) << 4 | (c2 & 240) >> 4);
				out += this.base64EncodeChars.charAt((c2 & 15) << 2);
				out += "=";
				break
			}
			c3 = str.charCodeAt(i++);
			out += this.base64EncodeChars.charAt(c1 >> 2);
			out += this.base64EncodeChars.charAt((c1 & 3) << 4 | (c2 & 240) >> 4);
			out += this.base64EncodeChars.charAt((c2 & 15) << 2 | (c3 & 192) >> 6);
			out += this.base64EncodeChars.charAt(c3 & 63)
		}
		return out
	},
	decode: function(str) {
		var c1, c2, c3, c4;
		var i, len, out;
		len = str.length;
		i = 0;
		out = "";
		while (i < len) {
			do {
				c1 = this.base64DecodeChars[str.charCodeAt(i++) & 255]
			} while (i < len && c1 == -1);
			if (c1 == -1) break;
			do {
				c2 = this.base64DecodeChars[str.charCodeAt(i++) & 255]
			} while (i < len && c2 == -1);
			if (c2 == -1) break;
			out += String.fromCharCode(c1 << 2 | (c2 & 48) >> 4);
			do {
				c3 = str.charCodeAt(i++) & 255;
				if (c3 == 61) return out;
				c3 = this.base64DecodeChars[c3]
			} while (i < len && c3 == -1);
			if (c3 == -1) break;
			out += String.fromCharCode((c2 & 15) << 4 | (c3 & 60) >> 2);
			do {
				c4 = str.charCodeAt(i++) & 255;
				if (c4 == 61) return out;
				c4 = this.base64DecodeChars[c4]
			} while (i < len && c4 == -1);
			if (c4 == -1) break;
			out += String.fromCharCode((c3 & 3) << 6 | c4)
		}
		return out
	}
};
void
function(winElement, docElement) {
	function getQueryStr(a) {
		var b = String(window.document.location.href),
			c = new RegExp("(^|)" + a + "=([^&]*)(&|$)", "gi").exec(b);
		return c ? c[2] : 0
	}

	function setCookie(a, b, c) {
		c = c || 15;
		var d = new Date;
		d.setTime((new Date).getTime() + 1e3 * c);
		try {
			docElement.cookie = a + "=" + escape(b) + ";path=/;expires=" + d.toGMTString()
		} catch (e) {}
	}

	function getCookie(a) {
		var b = docElement.cookie.match(new RegExp("(^| )" + a + "=([^;]*)(;|$)"));
		return null !== b ? unescape(b[2]) : null
	}

	function findParent(a, b) {
		for (var c = 0; b;) {
			if ((b.nodeName || b.tagName).toLowerCase() === a.toLowerCase()) return b;
			if (c >= 4) return null;
			c++, b = b.parentNode
		}
		return null
	}

	function getAppStart() {
		var hash_ts = getQueryStr("bd_ts"),
			start = 0,
			refer = docElement.referrer,
			now = +new Date,
			hash_cookie = getCookie("bd_hash"),
			st = getCookie("bd_st");
		if (st) {
			try {
				setCookie("bd_st", "", -1), st = eval(st)
			} catch (e) {
				st = {}
			}
			st.r && refer.replace(/#.*/, "").slice(-50) != st.r || (start = st.s)
		} else refer.indexOf("baidu.com") > -1 && hash_ts > 0 && 7 == String(hash_ts).length && hash_cookie != hash_ts && (setCookie("bd_hash", hash_ts, 30), start = parseInt((now + "").slice(0, 6) + hash_ts, 10));
		return now - start >= 2e4 && (start = 0), start
	}

	function extend(a, b) {
		for (var c in a) c && (b[c] = a[c]);
		return b
	}
	window.bd || (window.bd = {}), window.bd._qdc = {
		_v: 1,
		_timing: {},
		_random: Math.random(),
		_st: getAppStart(),
		_is_send: !1,
		_opt: {
			sample: .5,
			log_path: "http://static.tieba.baidu.com/tb/opms/img/st.gif",
			items: ["lt"]
		},
		_check: function() {
			for (var a = this._opt.items, b = this._timing, c = !0, d = a.length - 1; d >= 0; d--) b.hasOwnProperty(a[d]) || (c = !1);
			c && this.send()
		},
		init: function(a) {
			extend(a, this._opt)
		},
		mark: function(a, b) {
			this._st > 0 && (this._timing[a] = b || +new Date - this._st, this._check())
		},
		first_screen: function() {
			var a = document.getElementsByTagName("img"),
				b = +new Date,
				c = [],
				d = this;
			this._setFS = function() {
				for (var a = d._opt.fsHeight || document.documentElement.clientHeight, e = 0; e < c.length; e++) {
					var f = c[e],
						g = f.img,
						h = f.time,
						i = g.offsetTop || 0;
					i > 0 && a > i && (b = h > b ? h : b)
				}
				d._timing.fs = b - d._st
			};
			for (var e = function() {
				this.removeEventListener && this.removeEventListener("load", e, !1), c.push({
					img: this,
					time: +new Date
				})
			}, f = 0; f < a.length; f++) {
				var g = a[f];
				g.addEventListener && !g.complete && g.addEventListener("load", e, !1)
			}
		},
		send: function() {
			if (this._random < this._opt.sample && this._st > 0 && !this._is_send) {
				this._is_send = !0;
				var a = this._timing,
					b = [];
				for (var c in a) b.push(c + "=" + a[c]);
				b.push("_t=" + 1 * new Date);
				var d = document.createElement("img");
				d.src = this._opt.log_path + "?" + "type=bdapp&v=" + this._v + "&app_id=" + this._opt.app_id + "&" + b.join("&")
			}
		}
	}, docElement.addEventListener("DOMContentLoaded", function() {
		bd._qdc.mark("drt")
	}, !1), winElement.addEventListener("load", function() {
		"function" == typeof bd._qdc._setFS && bd._qdc._setFS(), bd._qdc.mark("lt")
	}), docElement.addEventListener("click", function(a) {
		a = a || window.event;
		var b = a.target || a.srcElement,
			c = findParent("a", b);
		if (c) {
			var d = c.getAttribute("href");
			/^#|javascript:/.test(d) || setCookie("bd_st", '({"s":' + +new Date + ',"r":"' + docElement.URL.replace(/#.*/, "").slice(-50) + '"})')
		}
	}, !1), bd._qdc.mark("ht")
}(window, document),
function() {
	function loadScript(a, b) {
		var c, d, e, f = document.head || document.getElementsByTagName("head")[0] || document.documentElement;
		"object" == typeof a && (d = a, a = void 0), e = d || {}, a = a || e.url, b = b || e.success, c = document.createElement("script"), c.async = e.async || !1, c.type = "text/javascript", e.charset && (c.charset = e.charset), c.src = a, f.insertBefore(c, f.firstChild), "function" == typeof b && (document.addEventListener ? c.addEventListener("load", b, !1) : c.onreadystatechange = function() {
			/loaded|complete/.test(c.readyState) && (c.onreadystatechange = null, b())
		})
	}

	function initStack(modules) {
		for (var r_apis = modules, api_tmp, type, i = r_apis.length - 1; i >= 0; i--)
			if (clouda.device || (clouda.device = {}), clouda.mbaas || (clouda.mbaas = {}), api_tmp = device_apis[r_apis[i]] ? device_apis[r_apis[i]] : !1, api_tmp ? type = "device" : (type = "mbaas", api_tmp = mbaas_apis[r_apis[i]] ? mbaas_apis[r_apis[i]] : !1), "undefined" == typeof eval("clouda." + type + "['" + r_apis[i] + "']")) {
				eval("clouda." + type + "['" + r_apis[i] + "'] ={}");
				for (var f_str, j = 0, len = api_tmp.length; len > j; j++) f_str = "function(){stack.push({func:'clouda." + type + "." + r_apis[i] + "." + api_tmp[j] + "',arg:arguments});}", eval("clouda." + type + "['" + r_apis[i] + "']['" + api_tmp[j] + "'] =" + f_str)
			}
	}
	if ("object" == typeof window) {
		"undefined" == typeof window.clouda && (window.clouda = {});
		var stack = [],
			api_version_list = {
				fs: "1.0",
				media: "1.2",
				connection: "1.1",
				geolocation: "1.1",
				app: "1.1",
				pay: "1.1",
				qr: "1.0"
			}, device_apis = {
				accelerometer: [],
				activity: ["start", "checkSupport"],
				battery: ["get", "startListen", "stopListen", "checkSupport"],
				compass: [],
				connection: ["get", "checkSupport"],
				contact: [],
				device: ["getImei", "getSysVersion", "getDeviceModelName", "getScreenSize", "checkSupport"],
				fs: ["post"],
				geolocation: ["get", "startListen", "stopListen", "checkSupport"],
				globalization: ["getlocale", "checkSupport"],
				gyro: [],
				localStorage: [],
				media: ["captureMedia", "operateMedia", "checkSupport"],
				notification: [],
				qr: ["startCapture", "checkSupport"],
				screen: []
			}, mbaas_apis = {
				account: ["login", "closeLoginDialog", "checkSupport"],
				app: ["followSite", "checkFollow", "checkSupport"],
				pay: ["init", "doPay", "checkSupport"],
				socialshare: ["callShare", "checkSupport"]
			};
		"function" != typeof clouda.lightapp && (clouda.lightapp = function(ak, callback) {
			if (clouda.lightapp.ak = ak, clouda.device) "function" == typeof callback && callback();
			else {
				var domain;
				domain = "http:" === window.location.protocol ? "http://apps.bdimg.com" : "https://openapi.baidu.com", loadScript(domain + "/cloudaapi/s/api-0.5.5.js", function() {
					if ("function" == typeof callback && callback(), mystack = stack, mystack.length) {
						for (var qq = 0; qq < mystack.length; qq++) eval(mystack[qq].func).apply(null, mystack[qq].arg);
						mystack.length = 0
					}
				})
			}
			initStack(["activity", "battery", "connection", "device", "fs", "geolocation", "globalization", "media", "qr", "account", "app", "pay"])
		}), "function" != typeof clouda.lightInit && (clouda.lightInit = function(obj, callback) {
			clouda.lightapp.ak = obj.ak;
			var apis = obj.module;
			initStack(apis);
			for (var v, apis_v = [], i = apis.length - 1; i >= 0; i--) v = api_version_list[apis[i]], apis_v[i] = apis[i] + "-" + (v ? v : "1.0");
			var m = apis_v.join("|"),
				domain;
			domain = "http:" === window.location.protocol ? "http://apps.bdimg.com" : "https://openapi.baidu.com";
			var script_url = domain + "/cloudaapi/api-list.js?a=" + encodeURIComponent(m);
			loadScript(script_url, function() {
				if ("function" == typeof callback && callback(), stack.length) {
					for (var qq = 0; qq < stack.length; qq++) eval(stack[qq].func).apply(null, stack[qq].arg);
					stack.length = 0
				}
			})
		})
	}
}();
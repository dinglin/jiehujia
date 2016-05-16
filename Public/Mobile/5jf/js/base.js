$(function(){
    /* 弹出框事件 */
    $('body').delegate('ul[_tabBox] li','click',function(){
        $('section[_sliceTab]').addClass('sliceTab');
    });
    /* 弹出框事件 End */
});
$.fn.countdown = function (option) {//倒计时
    var _this = {
        endTag : "data-time",
        distance : -1,
        rewrite : undefined ,
        rewriteEnd : undefined ,
        timeEnd : undefined ,
        nowtime : 0
    };
    $.extend(_this,option);
    var nowtime =  _this.nowtime,
        endTag = _this.endTag;
    if(!nowtime){        
        if(!getServerTime()){
            var nowtime = new Date();
        }else{
            var nowtime = new Date(getServerTime());
        }
    }
    $(this).each(function () {
        var obj = $(this);
        var _d,_h,_m,_s;
        var newStr = "";
        var nt = {
            d : 0,
            h : 0,
            m : 0,
            s : 0
        }
        var endtime = new Date(obj.attr(endTag));
        // if(obj.attr(endTag).indexOf(":")!=-1){
        //     datetime = endtime.getTime();
        // }else{
        //     datetime = obj.attr(endTag);
        // }
        // var tadaytime = nowtime.getTime(),
        var datetime = obj.attr(endTag);
        var leftsecond = datetime/1000;


        //console.log(leftsecond+"|"+typeof(leftsecond));
        if(leftsecond<=0){
            if (typeof(_this.rewriteEnd) != 'undefined') {
                newStr = _this.rewriteEnd.apply(nt, arguments);
            }
            obj.find(".countDown").html(newStr);
        }else{
            var timeNew = setInterval(function(){
                if(datetime!=obj.attr(endTag)){
                    datetime = obj.attr(endTag);
                    leftsecond = datetime/1000;
                }
                if (leftsecond <= 0) {
                    //console.log(leftsecond+"|error");
                    if (typeof(_this.rewriteEnd) != 'undefined') {
                        newStr = _this.rewriteEnd.apply(nt, arguments);
                    }
                    obj.find(".countDown").html(newStr);
                    clearInterval(timeNew);
                    return;
                }else{
                    leftsecond--;
                }
                _d = parseInt(leftsecond / 3600 / 24);
                _h = parseInt((leftsecond / 3600) % 24);
                _m = parseInt((leftsecond / 60) % 60);
                _s = parseInt(leftsecond % 60);
                nt.d=_d;
                nt.h=_h;
                nt.m=_m;
                nt.s=_s;

                //console.log(leftsecond+"||"+_m+"|"+_s);
                if (typeof(_this.rewrite) != 'undefined') {
                    newStr = _this.rewrite.apply(nt, arguments);
                }
                obj.find(".countDown").html(newStr);
            }, 1000);
        }
    });

    function getServerTime(){//用该函数获取服务器时间 返回给倒计时插件
      var nowTime = 0;
      if(globe.getServerTime()){
        nowTime = globe.getServerTime();
      }
      return nowTime;
    }
};
/*点击展示更多初始化截取字*/
$("#orderExplain .tabChange").each(function(){
    var _this=$(this),
        _textthis = $(this).find("p");
    if(parseInt(_textthis.height())>60){
        _this.addClass("overflow");
        _this.find('.ic_down').css("display","block");
        _this.MBPBtn({
            handler:function(){
                if(_this.hasClass("overflow")){
                    _this.removeClass("overflow");
                }else{
                    _this.addClass("overflow");
                }
            }
        });
    }
});
$.fn.seeMore = function (option) {
    var _this = {
        minHeight : 65
    }
    $.extend(_this,option);
    var minHeight = _this.minHeight;
    $(this).each(function(){
        var that=$(this);
        if(that.find("section").length>2){
            that.find('.ic_di').css("display","block");
            that.MBPBtn({
                handler:function(){
                    if(that.hasClass("overflow")){
                        that.removeClass("overflow");
                    }else{
                        that.addClass("overflow");
                    }
                }
            });
        }else if(!that.find("section").length){
            var thatText = $(this).find("p");
            if(parseInt(thatText.height())>minHeight){
                that.find('.ic_di').css("display","block");
                that.addClass("overflow");
                that.MBPBtn({
                    handler:function(){
                        if(that.hasClass("overflow")){
                            that.removeClass("overflow");
                        }else{
                            that.addClass("overflow");
                        }
                    }
                });
            }else{
            }
        }

    });
}
$.fn.triggerLiAct = function () {   //列表手风琴切换
    var _this = $(this).find(".set-btn");
    _this.each(function (index) {
        var _area = $(this);
        if (!index) {
            _area.addClass("active");
        }
        _area.MBPBtn({
            handler: function(){
                _area.siblings().removeClass("active");
                _area.addClass("active");
            }
        });
    });
};
$.fn.tabChange = function (option) {//tab切换
    var _this = {
        tabName: "tabChange",   //详细条目class
        tabSwitch: "li",        //字段html标签
        callback: undefined,    //callback
        crumbsCallback: undefined   //项目点击后的事件
    };
    $.extend(_this, option);
    var _obj = $(this).find(_this.tabSwitch);
    var _item = $("." + _this.tabName);
    var callback = _this.callback;
    var crumbsCallback = _this.crumbsCallback;
    _obj.each(function (index) {
        var _tabBtn = $(this);
        _item.eq(0).css("display", "block");
        _obj.eq(0).addClass("active");
        //console.log(_this.tabName+"||"+_item.attr("class"));
        if($(_tabBtn).length){
            $(_tabBtn).MBPBtn({
                handler:function(){
                    _obj.removeClass("active").eq(index).addClass("active");
                    _item.css("display", "none").eq(index).css("display", "block");
                    if (typeof(crumbsCallback) != 'undefined') {
                        crumbsCallback.apply(_tabBtn, arguments);
                    }
                }
            });
        }
    });
    if (typeof(callback) != 'undefined') {
        callback.apply(this, arguments);
    }
    return this;
};

$.fn.checkBox = function(option){
    var _this = {
        initNum : [0],                  //初始化已经选中的项目，默认选择第一个元素
        radioName : "checkOption",      //复选的父级class
        radioItemName : "ic_check_a",   //复选元素class
        selectedName : "selected" ,     //复选元素选中状态的class
        selectedVal : undefined         //返回当前点选内容的索引
    };
    $.extend(_this , option);
    var obj = $(this),
        item = obj.find("."+_this.radioName),
        itemChild = item,
        selected = _this.selectedName,
        i = _this.initNum;
    if(!item.hasClass(_this.radioItemName)){
        itemChild = item.find("."+_this.radioItemName);
    }
    item.each(function(index){
        if($.inArray(index ,i)!=-1){
            itemChild.eq(index).addClass(selected);
        }
        $(this).MBPBtn({
            handler: function(){
                if(itemChild.eq(index).hasClass(selected)){
                    itemChild.eq(index).removeClass(selected);
                }else{
                    itemChild.eq(index).addClass(selected);
                }
                if (typeof(_this.selectedVal) != 'undefined') {
                    _this.selectedVal.apply(index, arguments);
                }
            }
        })
    });
};
$.fn.radioBox = function(option){//页面内单选事件
    var _this = {
        initNum : "0",
        radioName : "radioOption",
        radioItemName : "ic_radio_a",
        selectedName : "selected" ,
        selectedVal : undefined ,
        callBack : undefined
    };
    $.extend(_this , option);
    var obj = $(this),
        item = obj.find("."+_this.radioName),
        itemChild = item,
        selected = _this.selectedName,
        i = _this.initNum;
    if(!item.hasClass(_this.radioItemName)){
        itemChild = item.find("."+_this.radioItemName);
    }
    if(i>=0){
        itemChild.eq(i).addClass(selected);
    }
    item.each(function(index){
        $(this).MBPBtn({
            handler: function(){
                itemChild.removeClass(selected);
                itemChild.eq(index).addClass(selected);
                if (typeof(_this.selectedVal) != 'undefined') {
                    _this.selectedVal.apply(index, arguments);
                }
            }
        })
    });
    if (typeof(_this.callBack) != 'undefined') {
        _this.callBack.apply(this, arguments);
    }
};

/*
 popup
 isSuccess-----(true, false)
 callBack ------function a(){}
 time is num
 toastText is string
 buttonBoole is button boole
 */
function popupModal(isSuccess, toastText, callBack, time, buttonBoole){
    var _popupBox = $('div[_popup]');
    var _modal = $('div[_modal]');
    var _modalHeight = $(document).height();
    if(typeof(callBack) === 'function'){
        callBack();
    }
    if(isSuccess){
        setTimeout(function() {
            _popupBox.fadeIn();
            _modal.css({
                'height': _modalHeight
            }).fadeIn();
            _popupBox.find('article p').html(toastText);
        }, time);
    }
    if(!isSuccess){
        setTimeout(function() {
            _popupBox.fadeOut();
            _modal.fadeOut();
        }, time);
    }
    /*button defult is show and false*/
    if(buttonBoole){
        _popupBox.find('.ic_yellow').hide();
    }else{
        _popupBox.find('.ic_yellow').show();
    }
    $('body').delegate('div[_modal]', 'click', function(){
//		  _popupBox.fadeOut();
//		  _modal.fadeOut();
    });
}
function popupModalItem(obj, isSuccess, toastText, callBack, time, buttonBoole){
    var _popupBox = obj;
    var _modal = $('div[_modal]');
    var _top = $(document).scrollTop()+180;
    var _modalHeight = $(document).height();
    if(typeof(callBack) === 'function'){
        callBack();
    }
    if(isSuccess){
        setTimeout(function() {
            _popupBox.css({
                top: _top
            }).fadeIn();
            _modal.css({
                'height': _modalHeight
            }).fadeIn();
            _popupBox.find('article p').html(toastText);
        }, time);
    }
    if(!isSuccess){
        setTimeout(function() {
            _popupBox.fadeOut();
            _modal.fadeOut();
        }, time);
    }
    /*button defult is show and false*/
    if(buttonBoole){
        _popupBox.find('.ic_yellow').hide();
    }else{
        _popupBox.find('.ic_yellow').show();
    }
    $('body').delegate('div[_modal]', 'click', function(){
//			  _popupBox.fadeOut();
//			  _modal.fadeOut();
    });
}
(function(document){//防止误点击
    window.MBP = window.MBP || {};
    MBP.fastButton = function (element, handler) {
        this.element = element;
        this.handler = handler;
        if (element.addEventListener) {
            if (document.hasOwnProperty("ontouchstart")) {
                element.addEventListener('touchstart', this, false);
            }else{
                element.addEventListener('click', this, false);
            }
        }
    };
    MBP.fastButton.prototype.handleEvent = function(event) {
        switch (event.type) {
            case 'touchstart': this.onTouchStart(event); break;
            case 'touchmove': this.onTouchMove(event); break;
            case 'touchend': this.onClick(event); break;
            case 'click': this.onClick(event); break;
        }
    };
    MBP.fastButton.prototype.onTouchStart = function(event) {
        event.stopPropagation();
        this.element.addEventListener('touchend', this, false);
        document.body.addEventListener('touchmove', this, false);
        this.startX = event.touches[0].clientX;
        this.startY = event.touches[0].clientY;
    };
    MBP.fastButton.prototype.onTouchMove = function(event) {
        if(Math.abs(event.touches[0].clientX - this.startX) > 10 ||
            Math.abs(event.touches[0].clientY - this.startY) > 10    ) {
            this.reset();
        }
    };
    MBP.fastButton.prototype.onClick = function(event) {
        event.stopPropagation();
		this.reset();        
        this.handler(event);
        if(event.type == 'touchend') {
            MBP.preventGhostClick(this.startX, this.startY);
        }
       
    };
    MBP.fastButton.prototype.reset = function() {
        this.element.removeEventListener('touchend', this, false);
        document.body.removeEventListener('touchmove', this, false);
    };
    MBP.preventGhostClick = function (x, y) {
        MBP.coords.push(x, y);
        window.setTimeout(function (){
            MBP.coords.splice(0, 2);
        }, 2500);
    };
    MBP.ghostClickHandler = function (event) {
        for(var i = 0, len = MBP.coords.length; i < len; i += 2) {
            var x = MBP.coords[i];
            var y = MBP.coords[i + 1];
            if(Math.abs(event.clientX - x) < 25 && Math.abs(event.clientY - y) < 25) {
                event.stopPropagation();
                event.preventDefault();
            }
        }
    };
    if (document.addEventListener) {
        document.addEventListener('click', MBP.ghostClickHandler, true);
    }
    MBP.coords = [];
})(document);
$.fn.MBPBtn = function (option) {
    var _this = {
        handler:undefined
    };
    $.extend(_this, option);
    $(this).click(_this.handler);
    // $(this).each(function (index) {
    //     var btn = new MBP.fastButton($(this).get(0), _this.handler);
    // });

};
function isEmpty(o) {//补充isEmpty函数
    var i, v; if (typeof(o) === 'object') {
        for (i in o) {
            v = o[i]; if (v !== undefined && typeof(v) !== 'function') {
                return false;
            }
        }
    } return true;
}

//FastClick 重写原有的click事件 请把内容写在该类库前面
function FastClick(layer) {
    var oldOnClick;
    this.trackingClick = false;
    this.trackingClickStart = 0;
    this.targetElement = null;
    this.touchStartX = 0;
    this.touchStartY = 0;
    this.lastTouchIdentifier = 0;
    this.touchBoundary = 10;
    this.layer = layer;
    if (FastClick.notNeeded(layer)) {
        return
    }

    function bind(method, context) {
        return function() {
            return method.apply(context, arguments)
        }
    }
    if (deviceIsAndroid) {
        layer.addEventListener("mouseover", bind(this.onMouse, this), true);
        layer.addEventListener("mousedown", bind(this.onMouse, this), true);
        layer.addEventListener("mouseup", bind(this.onMouse, this), true)
    }
    layer.addEventListener("click", bind(this.onClick, this), true);
    layer.addEventListener("touchstart", bind(this.onTouchStart, this), false);
    layer.addEventListener("touchmove", bind(this.onTouchMove, this), false);
    layer.addEventListener("touchend", bind(this.onTouchEnd, this), false);
    layer.addEventListener("touchcancel", bind(this.onTouchCancel, this), false);
    if (!Event.prototype.stopImmediatePropagation) {
        layer.removeEventListener = function(type, callback, capture) {
            var rmv = Node.prototype.removeEventListener;
            if (type === "click") {
                rmv.call(layer, type, callback.hijacked || callback, capture)
            } else {
                rmv.call(layer, type, callback, capture)
            }
        };
        layer.addEventListener = function(type, callback, capture) {
            var adv = Node.prototype.addEventListener;
            if (type === "click") {
                adv.call(layer, type, callback.hijacked || (callback.hijacked = function(event) {
                    if (!event.propagationStopped) {
                        callback(event)
                    }
                }), capture)
            } else {
                adv.call(layer, type, callback, capture)
            }
        }
    }
    if (typeof layer.onclick === "function") {
        oldOnClick = layer.onclick;
        layer.addEventListener("click", function(event) {
            oldOnClick(event)
        }, false);
        layer.onclick = null
    }
}
var deviceIsAndroid = navigator.userAgent.indexOf("Android") > 0;
var deviceIsIOS = /iP(ad|hone|od)/.test(navigator.userAgent);
var deviceIsIOS4 = deviceIsIOS && (/OS 4_\d(_\d)?/).test(navigator.userAgent);
var deviceIsIOSWithBadTarget = deviceIsIOS && (/OS ([6-9]|\d{2})_\d/).test(navigator.userAgent);
FastClick.prototype.needsClick = function(target) {
    switch (target.nodeName.toLowerCase()) {
        case "button":
        case "select":
        case "textarea":
            if (target.disabled) {
                return true
            }
            break;
        case "input":
            if ((deviceIsIOS && target.type === "file") || target.disabled) {
                return true
            }
            break;
        case "label":
        case "video":
            return true
    }
    return (/\bneedsclick\b/).test(target.className)
};
FastClick.prototype.needsFocus = function(target) {
    switch (target.nodeName.toLowerCase()) {
        case "textarea":
            return true;
        case "select":
            return !deviceIsAndroid;
        case "input":
            switch (target.type) {
                case "button":
                case "checkbox":
                case "file":
                case "image":
                case "radio":
                case "submit":
                    return false
            }
            return !target.disabled && !target.readOnly;
        default:
            return (/\bneedsfocus\b/).test(target.className)
    }
};
FastClick.prototype.sendClick = function(targetElement, event) {
    var clickEvent, touch;
    if (document.activeElement && document.activeElement !== targetElement) {
        document.activeElement.blur()
    }
    touch = event.changedTouches[0];
    clickEvent = document.createEvent("MouseEvents");
    clickEvent.initMouseEvent(this.determineEventType(targetElement), true, true, window, 1, touch.screenX, touch.screenY, touch.clientX, touch.clientY, false, false, false, false, 0, null);
    clickEvent.forwardedTouchEvent = true;
    targetElement.dispatchEvent(clickEvent)
};
FastClick.prototype.determineEventType = function(targetElement) {
    if (deviceIsAndroid && targetElement.tagName.toLowerCase() === "select") {
        return "mousedown"
    }
    return "click"
};
FastClick.prototype.focus = function(targetElement) {
    var length;
    if (deviceIsIOS && targetElement.setSelectionRange && targetElement.type.indexOf("date") !== 0 && targetElement.type !== "time") {
        length = targetElement.value.length;
        targetElement.setSelectionRange(length, length)
    } else {
        targetElement.focus()
    }
};
FastClick.prototype.updateScrollParent = function(targetElement) {
    var scrollParent, parentElement;
    scrollParent = targetElement.fastClickScrollParent;
    if (!scrollParent || !scrollParent.contains(targetElement)) {
        parentElement = targetElement;
        do {
            if (parentElement.scrollHeight > parentElement.offsetHeight) {
                scrollParent = parentElement;
                targetElement.fastClickScrollParent = parentElement;
                break
            }
            parentElement = parentElement.parentElement
        } while (parentElement)
    }
    if (scrollParent) {
        scrollParent.fastClickLastScrollTop = scrollParent.scrollTop
    }
};
FastClick.prototype.getTargetElementFromEventTarget = function(eventTarget) {
    if (eventTarget.nodeType === Node.TEXT_NODE) {
        return eventTarget.parentNode
    }
    return eventTarget
};
FastClick.prototype.onTouchStart = function(event) {
    var targetElement, touch, selection;
    if (event.targetTouches.length > 1) {
        return true
    }
    targetElement = this.getTargetElementFromEventTarget(event.target);
    touch = event.targetTouches[0];
    if (deviceIsIOS) {
        selection = window.getSelection();
        if (selection.rangeCount && !selection.isCollapsed) {
            return true
        }
        if (!deviceIsIOS4) {
            if (touch.identifier === this.lastTouchIdentifier) {
                event.preventDefault();
                return false
            }
            this.lastTouchIdentifier = touch.identifier;
            this.updateScrollParent(targetElement)
        }
    }
    this.trackingClick = true;
    this.trackingClickStart = event.timeStamp;
    this.targetElement = targetElement;
    this.touchStartX = touch.pageX;
    this.touchStartY = touch.pageY;
    if ((event.timeStamp - this.lastClickTime) < 200) {
        event.preventDefault()
    }
    return true
};
FastClick.prototype.touchHasMoved = function(event) {
    var touch = event.changedTouches[0],
        boundary = this.touchBoundary;
    if (Math.abs(touch.pageX - this.touchStartX) > boundary || Math.abs(touch.pageY - this.touchStartY) > boundary) {
        return true
    }
    return false
};
FastClick.prototype.onTouchMove = function(event) {
    if (!this.trackingClick) {
        return true
    }
    if (this.targetElement !== this.getTargetElementFromEventTarget(event.target) || this.touchHasMoved(event)) {
        this.trackingClick = false;
        this.targetElement = null
    }
    return true
};
FastClick.prototype.findControl = function(labelElement) {
    if (labelElement.control !== undefined) {
        return labelElement.control
    }
    if (labelElement.htmlFor) {
        return document.getElementById(labelElement.htmlFor)
    }
    return labelElement.querySelector("button, input:not([type=hidden]), keygen, meter, output, progress, select, textarea")
};
FastClick.prototype.onTouchEnd = function(event) {
    var forElement, trackingClickStart, targetTagName, scrollParent, touch, targetElement = this.targetElement;
    if (!this.trackingClick) {
        return true
    }
    if ((event.timeStamp - this.lastClickTime) < 200) {
        this.cancelNextClick = true;
        return true
    }
    this.cancelNextClick = false;
    this.lastClickTime = event.timeStamp;
    trackingClickStart = this.trackingClickStart;
    this.trackingClick = false;
    this.trackingClickStart = 0;
    if (deviceIsIOSWithBadTarget) {
        touch = event.changedTouches[0];
        targetElement = document.elementFromPoint(touch.pageX - window.pageXOffset, touch.pageY - window.pageYOffset) || targetElement;
        targetElement.fastClickScrollParent = this.targetElement.fastClickScrollParent
    }
    targetTagName = targetElement.tagName.toLowerCase();
    if (targetTagName === "label") {
        forElement = this.findControl(targetElement);
        if (forElement) {
            this.focus(targetElement);
            if (deviceIsAndroid) {
                return false
            }
            targetElement = forElement
        }
    } else {
        if (this.needsFocus(targetElement)) {
            if ((event.timeStamp - trackingClickStart) > 100 || (deviceIsIOS && window.top !== window && targetTagName === "input")) {
                this.targetElement = null;
                return false
            }
            this.focus(targetElement);
            this.sendClick(targetElement, event);
            if (!deviceIsIOS4 || targetTagName !== "select") {
                this.targetElement = null;
                event.preventDefault()
            }
            return false
        }
    } if (deviceIsIOS && !deviceIsIOS4) {
        scrollParent = targetElement.fastClickScrollParent;
        if (scrollParent && scrollParent.fastClickLastScrollTop !== scrollParent.scrollTop) {
            return true
        }
    }
    if (!this.needsClick(targetElement)) {
        event.preventDefault();
        this.sendClick(targetElement, event)
    }
    return false
};
FastClick.prototype.onTouchCancel = function() {
    this.trackingClick = false;
    this.targetElement = null
};
FastClick.prototype.onMouse = function(event) {
    if (!this.targetElement) {
        return true
    }
    if (event.forwardedTouchEvent) {
        return true
    }
    if (!event.cancelable) {
        return true
    }
    if (!this.needsClick(this.targetElement) || this.cancelNextClick) {
        if (event.stopImmediatePropagation) {
            event.stopImmediatePropagation()
        } else {
            event.propagationStopped = true
        }
        event.stopPropagation();
        event.preventDefault();
        return false
    }
    return true
};
FastClick.prototype.onClick = function(event) {
    var permitted;
    if (this.trackingClick) {
        this.targetElement = null;
        this.trackingClick = false;
        return true
    }
    if (event.target.type === "submit" && event.detail === 0) {
        return true
    }
    permitted = this.onMouse(event);
    if (!permitted) {
        this.targetElement = null
    }
    return permitted
};
FastClick.prototype.destroy = function() {
    var layer = this.layer;
    if (deviceIsAndroid) {
        layer.removeEventListener("mouseover", this.onMouse, true);
        layer.removeEventListener("mousedown", this.onMouse, true);
        layer.removeEventListener("mouseup", this.onMouse, true)
    }
    layer.removeEventListener("click", this.onClick, true);
    layer.removeEventListener("touchstart", this.onTouchStart, false);
    layer.removeEventListener("touchmove", this.onTouchMove, false);
    layer.removeEventListener("touchend", this.onTouchEnd, false);
    layer.removeEventListener("touchcancel", this.onTouchCancel, false)
};
FastClick.notNeeded = function(layer) {
    var metaViewport;
    var chromeVersion;
    if (typeof window.ontouchstart === "undefined") {
        return true
    }
    chromeVersion = +(/Chrome\/([0-9]+)/.exec(navigator.userAgent) || [, 0])[1];
    if (chromeVersion) {
        if (deviceIsAndroid) {
            metaViewport = document.querySelector("meta[name=viewport]");
            if (metaViewport) {
                if (metaViewport.content.indexOf("user-scalable=no") !== -1) {
                    return true
                }
                if (chromeVersion > 31 && window.innerWidth <= window.screen.width) {
                    return true
                }
            }
        } else {
            return true
        }
    }
    if (layer.style.msTouchAction === "none") {
        return true
    }
    return false
};
FastClick.attach = function(layer) {
    return new FastClick(layer)
};
if (typeof define !== "undefined" && define.amd) {
    define(function() {
        return FastClick
    })
} else {
    if (typeof module !== "undefined" && module.exports) {
        module.exports = FastClick.attach;
        module.exports.FastClick = FastClick
    } else {
        window.FastClick = FastClick
    }
};
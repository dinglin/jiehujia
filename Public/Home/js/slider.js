(function($) {
    //2015-10-10
    $.fn.extend({
        //slide插件名称
        MySlide: function(options) {
            //默认值
            var defaults = {
                showNum : 1,    //设置滚动的显示个数
                autoScroll : true,    //是否自动滚动，默认为 false
                autoScrollInterval : 3000 ,  //自动滚动间隔，默认为 3000 毫秒，autoScroll = true 时才有效
                scrollUlLeft : 0,    //.scroll_ul 初使化时的 left 值为 0
                prevAllow: true,    //为了防止连续点击上一页按钮
                nextAllow : true,
                circle:false,
                prev_btn:'btn_icon_a',
                next_btn:'btn_icon_b'
            };
            
            var options = $.extend(defaults, options);
            var scrollUl=$("ul",this),
                scrollUlWidth = $(this).find("li").outerWidth(true),    //单个 li 的宽度
                prevBtn=$("."+options.prev_btn,this),// 上一页按钮
                auto = null,
                len=$(this).find("li").length,
                nextBtn=$("."+options.next_btn,this);// 下一页按钮
            
            scrollUl.parent().width(defaults.showNum * scrollUlWidth);//给容器设置宽
            
            //点击上一页
            prevBtn.click(function() {
                if (options.prevAllow&&(options.showNum<len)) {
                    options.prevAllow = false;
                    options.scrollUlLeft = options.scrollUlLeft - scrollUlWidth;
                    scrollUl.css('left', options.scrollUlLeft);
                    //复制最后一个 li 并插入到 ul 的最前面
                    scrollUl.prepend(scrollUl.find('li:last').clone(true));
                    //删除最后一个 li
                    scrollUl.find('li:last').remove();
                    scrollUl.animate({
                        left : options.scrollUlLeft + scrollUlWidth
                    }, 300, function() {
                        options.scrollUlLeft = parseInt(scrollUl.css('left'), 10);
                        options.prevAllow = true;
                    })
                }
            });
             //点击下一页
            nextBtn.click(function() {
                if (options.nextAllow&&(options.showNum<len)) {
                    options.nextAllow = false;
                    scrollUl.animate({
                        left : options.scrollUlLeft - scrollUlWidth
                    }, 300, function() {
                        options.scrollUlLeft = parseInt(scrollUl.css('left'), 10);
                        options.scrollUlLeft = options.scrollUlLeft + scrollUlWidth;
                        scrollUl.css('left', options.scrollUlLeft);
                        //复制第一个 li 并插入到 ul 的最后面
                        scrollUl.append(scrollUl.find('li:first').clone(true));
                        //删除第一个 li
                        scrollUl.find('li:first').remove();
                        options.nextAllow = true;
                    })
                }
            });
            //自动滚动
            if (options.autoScroll&&(options.showNum<len)) {
                auto= setInterval(function() {
                        nextBtn.trigger('click');
                }, options.autoScrollInterval);
                this.hover(function(){
                    clearInterval(auto);
                    auto=null;
                },function(){
                    auto= setInterval(function() {
                        nextBtn.trigger('click');
                    }, options.autoScrollInterval)
                });     
            }
           //return this使JQuery方法可链
            return this;
        },
        slider:function(settings){
          var defaults={
            prev_btn:'',
            next_btn:'',
            speed:1000,
            count:1
          }
          var $yst_box_pics=$(this);
          settings=$.extend(defaults,settings);
          var len=$yst_box_pics.children().length,
            w=$yst_box_pics.children(':first').outerWidth(true),
            count=settings.count,
            speed=settings.speed;
           $yst_box_pics.width(len*w);

        $('.'+settings.next_btn).bind('click',nextFn);
          function nextFn(){
            if(!$yst_box_pics.is(':animated')){
              $yst_box_pics.stop().animate({'marginLeft':-w*count},speed,function(){
                $yst_box_pics.children().slice(0,count).appendTo($yst_box_pics);
                $yst_box_pics.css('marginLeft',0);
              })
            }
          }

          $('.'+settings.prev_btn).bind('click',prevFn);
          function prevFn(){
            if(!$yst_box_pics.is(':animated')){
                $yst_box_pics.css('marginLeft',-w*count).children().slice(len-count).prependTo($yst_box_pics);
               $yst_box_pics.stop().animate({'marginLeft':0},speed)

           }
         }
      }
    });    
})(jQuery);
;(function($, window, document,undefined) {//面向对象的插件开发
	//1.定义Beautifier 的构造函数
	var Slide = function(ele,opt){
		this.$element=ele;
		this.defaults={
			scrollUl:'',//滚动ul的类名
	        showNum : 1,    //设置滚动的显示个数
	        autoScroll : true,    //是否自动滚动，默认为 false
	        autoScrollInterval : 3000 ,  //自动滚动间隔，默认为 3000 毫秒，autoScroll = true 时才有效
	        scrollUlLeft : 0,    //.scroll_ul 初使化时的 left 值为 0
	        prevAllow: true,    //为了防止连续点击上一页按钮
	        nextAllow : true, //为了防止连续点击下一页按钮
	        prev_btn:'btn_icon_a',
	        next_btn:'btn_icon_b'
	    };
	    this.options=$.extend({},this.defaults,opt);
	};
	Slide.prototype={
		beauty : function(){
			return this.$element.css({
		        'color': this.options.color,
		        'fontSize': this.options.fontSize
		    });
		}
	}
	$.fn.myslider = function(options) {
	    var beautifier = new Slide(this,options);
	    return beautifier.beauty()
 	}
})(jQuery, window, document);

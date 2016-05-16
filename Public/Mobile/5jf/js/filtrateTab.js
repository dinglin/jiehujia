$.fn.filtrateTab = function (option, callback , crumbsCallback) {   //筛选切换
    var _this = {
        tabName: "tabChange",   //详细条目class
        tabSwitch: ".tab_select li",        //字段html标签
        maskName: ".lv-mask",   //遮罩默认名称
        maskTop:39,              //需要在头部空出的高度
        submit : undefined ,
        jump : undefined
    };
    $.extend(_this, option);
    var _bar = $(this),
        _obj = $(this).find(_this.tabSwitch),
        _item = $(this).find("." + _this.tabName),
        _mask = _bar.find(_this.maskName),
        maskHeight = $("body").height(),
        _btnState = 0;
    _mask.css({"top":_this.maskTop , "height":maskHeight});
    _obj.each(function (index) {
        var _btn = $(this), //当前一级菜单指向的条目
            _itemX = _item.eq(index);   //当前筛选菜单指向的条目
        if(_itemX.find("li").length=="0"){
            _btn.addClass("disable");
        }
        if(!_btn.hasClass("disable")){
            if(_btn.length){
                var mbp = new MBP.fastButton(_btn.get(0), function () { //二级菜单初始化
                    if(_btn.hasClass("active")){
                        itemHide();
                    }else{
                        _mask.css("display","block");
                        _obj.removeClass("active");
                        _btn.addClass("active");
                        _item.css("display","none");
                        _item.eq(index).css("display","block");
                        maskHeight = $("body").height();
                        _mask.css({"height":maskHeight});
                        if(_itemX.hasClass("filtrate-wrap")){   //如果该菜单是多级筛选菜单的初始化
                            if(_btnState){
                                _btn.addClass("selected");
                            }else{
                                _btn.removeClass("selected");
                            }
                            _itemX.find(".filtrate-list-l1 li").removeClass("active");
                            _itemX.find(".filtrate-list-l1 li:eq(0)").addClass("active");
                            _itemX.find(".filtrate-list-l2 .tabChangeL2").css("display","none");
                            _itemX.find(".filtrate-list-l2 .tabChangeL2:eq(0)").css("display","block");
                            _itemX.find(".filtrate-list-l2 .tabChangeL2").find(".t_list:eq(0)").css("display","block");
                            _itemX.find(".filtrate-list-l3 .tabChangeL3").css("display","none");
                            _itemX.find(".tabChangeL2").each(function(index){
                                var nowIndex = $(this).attr("data-index");
                                //console.log(_itemX.find(".filtrate-list-l2 .selected").length);
                                if(!_itemX.find(".filtrate-list-l2 .selected").length){ 
                                    if(nowIndex!=-1){
                                        _itemX.find(".filtrate-list-l1 li").eq(index).addClass("selected");
                                        $(this).find(".radioOption .ic_radio_a").removeClass("selected");
                                        $(this).find(".radioOption:eq("+nowIndex+") .ic_radio_a").addClass("selected");
                                    }else{
                                        _itemX.find(".filtrate-list-l1 li").eq(index).removeClass("selected");
                                        $(this).find(".radioOption .ic_radio_a").removeClass("selected");
                                    }
                                }else{
                                    _itemX.find(".filtrate-list-l2 .tabChangeL2").each(function(i){
                                        if($(this).find(".selected").length){
                                            _itemX.find(".filtrate-list-l1 li").eq(i).addClass("selected");
                                        }

                                    });
                                }
                            });
                        }
                    }
                });
            }
            if(_itemX.hasClass("filtrate-wrap")){   //如果该菜单是多级筛选菜单
                //btnTriggerHide(_itemX);
                _itemX.tabChange({
                    tabName : "tabChangeL2",
                    callback : function(){
                        _itemX.find("#filtrateReset").click(function(){
                            _btn.removeClass("selected");
                            _itemX.find(".tabChangeL2").each(function(index){
                                var nowIndex = $(this).attr("data-index");
                                _itemX.find(".filtrate-list-l1 li").removeClass("active");
                                _itemX.find(".filtrate-list-l1 li").eq(0).addClass("active");
                                _itemX.find(".filtrate-list-l2 .tabChangeL2").css("display","none");
                                _itemX.find(".filtrate-list-l2 .tabChangeL2").eq(0).css("display","block");
                                _itemX.find(".filtrate-list-l2 .tabChangeL2").find(".t_list").eq(0).css("display","block");
                                _itemX.find(".filtrate-list-l3 .tabChangeL3").css("display","none");
                                _itemX.find(".filtrate-list-l1 li").eq(index).removeClass("selected");
                                $(this).find(".radioOption .ic_radio_a").removeClass("selected");
                            });

                        });
                        _itemX.find("#filtrateSubmit").click(function(){
                            _btnState = 0;
                            _btn.removeClass("selected");
                            _itemX.find(".tabChangeL2").each(function(){
                                var nowIndex = $(this).find(".selected").attr("item-index"),
                                    val = 0;
                                if(nowIndex){
                                    $(this).attr("data-index",$(this).find(".selected").attr("item-index"));
                                    _btnState = 1;
                                    _btn.addClass("selected");
                                    val = $(this).find(".selected").parent().attr("data-val");
                                    //console.info(val);
                                }else{
                                    $(this).attr("data-index","-1");
                                }
                                itemHide();
                                if (typeof(_this.submit) != 'undefined') {
                                    _this.submit.apply(val, arguments);
                                }
                            });
                            
                        })
                     
                    }
                });
                _itemX.find(".tabChangeL2").each(function(index){
                    var filtrateWrapL2=$(this), //三级菜单列表对象
                        filtrateWrapL3 = filtrateWrapL2.find(".filtrate-list-l3 .tabChangeL3"), //四级菜单列表对象
                        selectedIndex = filtrateWrapL2.attr("data-index");
                    if(filtrateWrapL3.length){  //如果有四级菜单做相应操作
                        if(filtrateWrapL3.find(".t_li").length){
                        }
                        filtrateWrapL2.find(".t_list").eq(0).find(".t_li").each(function(childIndex){
                            var fw3Title = filtrateWrapL3.eq(childIndex).find(".t_title");
                            if(fw3Title.length);{
                                var fw3TitleBtn = new MBP.fastButton(fw3Title.get(0) , function(){
                                    filtrateWrapL2.find(".t_list").eq(0).css("display","block");
                                    filtrateWrapL3.css("display","none");
                                });
                            }
                            var fw3DadBtn = new MBP.fastButton($(this).get(0) , function(){
                                filtrateWrapL2.find(".t_list").eq(0).css("display","none");
                                filtrateWrapL3.eq(childIndex).css("display","block");
                            });
                        });
                    }
                    if(selectedIndex!=-1){
                        _btn.addClass("selected");
                        _btnState = 1;
                    }
                    filtrateWrapL2.radioBox({
                        initNum : selectedIndex,
                        selectedVal : function(){
                            filtrateWrapL2.find(".radioOption").eq(this).find(".ic_radio_a").attr("item-index",this);
                            _itemX.find(".filtrate-list-l1 li").eq(index).addClass("selected");
                            _btn.addClass("selected");
                        }
                    });
                });
            }else{
                _itemX.find("li").each(function(){
                    var _itemBtn = $(this);
                    if(_itemBtn.length){
                        _itemBtn.click(function(){
                            itemHide();
                            _btn.find("span").eq(0).html(_itemBtn.html());
                            if (typeof(crumbsCallback) != 'undefined') {
                                crumbsCallback.apply(_itemBtn, arguments);
                            }
                        });
                    }
                });
            }
        }
    });
    var closeObj1 =  _bar.find(_this.maskName);
    btnTriggerHide(closeObj1);
    if(_bar.find(_this.tabSwitch+".disable").length){
        var closeObj2 =  _bar.find(_this.tabSwitch+".disable");
        btnTriggerHide(closeObj2);
    }
    function itemHide(){
        _obj.removeClass("active");
        _item.css("display","none");
        _mask.css("display","none");
    }
    function btnTriggerHide(btn){
        var btnTrigger = new MBP.fastButton(btn.get(0), itemHide);
    }
    if (typeof(callback) != 'undefined') {
        callback.apply(this, arguments);
    }
    return this;
};
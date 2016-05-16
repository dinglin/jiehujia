/// <reference path="jquery/jquery-1.7.1.min.js" />
/// <reference path="jquery/jquery-ui-1.8.17.custom.min.js" />

var list = {
    init: function () {
        this.search();
    },
    search: function () {
        //time
        viDatepickerInit("#s_time");
        //show hide
        $('#sh_option').toggle(function () {
            $('.search_hide').removeClass('hide');
            $(this).removeClass('down_icon').addClass('up_icon');
        },
        function () {
            $('.search_hide').addClass('hide');
            $(this).removeClass('up_icon').addClass('down_icon');
        });

        //option
        $('#search_options ul li').click(function () {
            _this = $(this);
            _this.siblings().children('a').removeClass('option');
            _this.children('a').addClass('option');
        })


    }
    
}
var common = {
    actlist: function () {
        var listwd = $('#actlist li').outerWidth() + 35;
        var listlen = $('#actlist li').length;
        var pagelen = $('#actlist li').length / 3;
        var pagenum = 1;
        $('.page_len').text(pagelen);
        //下一页
        $('#next').click(function () {
            var marginLeft = -parseInt($('#actlist').css("margin-left")); //取margin值负数
            if (isNaN(marginLeft)) {
                marginLeft = 0; //判断margin是否有值
            }
            if (!$('#actlist').is(":animated") && marginLeft < ($('#actlist li').length - 1) * listwd - $(".recommend_box").width()) {
                $('#actlist').animate({ marginLeft: "-=" + listwd * 3 + "px" }, 300, function () {
                    pagenum += 1;
                    $('.page_num').text(pagenum);
                    changeIcon(pagenum);
                });
            }
        });
        //前一页
        $('#prev').click(function () {
            var marginLeft = parseInt($('#actlist').css("margin-left"));
            if (!$('#actlist').is(":animated") && marginLeft <= -listwd) {
                $('#actlist').animate({ marginLeft: "+=" + listwd * 3 + "px" }, 300, function () {
                    pagenum -= 1;
                    $('.page_num').text(pagenum);
                    changeIcon(pagenum);
                });
            }
        });
        function changeIcon(pagenum) {
            if (pagenum == 1) {
                $('#prev').removeClass('prev_tan').addClass('first_prev');
            } else if (pagenum == pagelen) {
                $('#next').removeClass('next_tan').addClass('last_next');
            } else {
                $('#next').removeClass('last_next').addClass('next_tan');
                $('#prev').removeClass('first_prev').addClass('prev_tan');
            }
        }
        //icons
        changeIcon(pagenum);
    }
}

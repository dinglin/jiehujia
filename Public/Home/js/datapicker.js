//datepicker 改成中文
jQuery(function ($) {
    $.datepicker.regional['zh-CN'] = {
        closeText: '关闭',
        prevText: '&#x3c;上月',
        nextText: '下月&#x3e;',
        currentText: '今天',
        monthNames: ['一月', '二月', '三月', '四月', '五月', '六月',
		'七月', '八月', '九月', '十月', '十一月', '十二月'],
        monthNamesShort: ['一', '二', '三', '四', '五', '六',
		'七', '八', '九', '十', '十一', '十二'],
        dayNames: ['星期日', '星期一', '星期二', '星期三', '星期四', '星期五', '星期六'],
        dayNamesShort: ['周日', '周一', '周二', '周三', '周四', '周五', '周六'],
        dayNamesMin: ['日', '一', '二', '三', '四', '五', '六'],
        weekHeader: '周',
        dateFormat: 'yy-mm-dd',
        firstDay: 0,
        isRTL: false,
        showMonthAfterYear: true,
        yearSuffix: '年'
    };
    $.datepicker.setDefaults($.datepicker.regional['zh-CN']);
});
// 初始化datepicker
// 单个参数：selector可以为id ex:#datepicker)，或者class ex:.datepicker)
// 两个参数：selector只能为成对的id: (ex: #id_beginTime, #id_endTime)
function viDatepickerInit() {
    var selector = undefined;
    if (arguments.length == 1) {
        selector = arguments[0];
        $(selector).datepicker({
            numberOfMonths: 1
        });
    }
    else if (arguments.length == 2) {
        var beginDate = arguments[0];
        var endDate = arguments[1];
        var beginDateId = arguments[0].replace('#', '');
        // Set the default value
        selector = beginDate + ", " + endDate;
        // Set the beginTime and endTime's validation
        var dates = $(selector).datepicker({
            dateFormat: 'yy-mm-dd',
            defaultDate: "+1w",
            numberOfMonths: 1,
            onSelect: function (selectedDate) {
                var option = this.id == beginDateId ? "minDate" : "maxDate",
					instance = $(this).data("datepicker"),
					date = $.datepicker.parseDate(
						instance.settings.dateFormat ||
						$.datepicker._defaults.dateFormat,
						selectedDate, instance.settings);
                dates.not(this).datepicker("option", option, date);
            }
        });
    }
    else { }
}
// 初始化时间选择
// ds: 开始时间选择
// de: 结束时间选择
function viDefaultDate(ds, de) {
    var d, y, m, dd, d1, d2;
    d = new Date();
    y = d.getFullYear();
    m = d.getMonth() + 1;
    m = m < 10 ? '0' + m : m;
    dd = d.getDate();
    d1 = y + '-' + m + '-' + (dd < 10 ? '0' + dd : dd);
    d2 = y + '-' + m + '-' + (dd + 1 < 10 ? '0' + (dd + 1) : dd + 1);
    $(ds).val(d1);
    $(de).val(d2);
    viDefaultText(ds, d1);
    viDefaultText(de, d2);
}

(function ($) {
    $.fn.extend({
        viDatepicker: function (options) {
            var settings = $.extend({
                defaultText: 'default text'
            });
            return this.each(function () {
                if (options) {
                    $.extend(settings, options);
                }
            });
        }
    });
})(jQuery);

//$(function () {
//    viDatepickerInit("#id_beginTime", "#id_endTime");
//});
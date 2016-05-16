;(function($){
	var opts = {}, ids = [], optsArray = [], that, currentOpts = {};
	
	var pcData = {'provinces':[]},//省市数据
		caData = {'cities':[]};//市区数据
	var defaultStr = '',
		defaultId = -1,
		defaultName = '请选择';
	
	
	//处理取回的数据
	function handleData(datas){
		var tempArr = [];
		var tempProvinces = [];
		var tempCities = [];
		var tempAreas = [];
		var reProvince=/^[0-9]{2}0{4}$/;//省的格式:前2位为'01~99'，后4位为'0000'  例：010000
		var reCity=/^[0-9]{4}0{2}$/;//城市的格式:前2位为'01~99',中间2位'01~99',后2位为'00' 例:010100
		var reArea=/^[0-9]{6}$/;//区的格式:前2位为'01~99',中间2位'01~99',后2位为'01~99' 例:010101
		
		//循环取得的数据，添加到用于存放省、市、区的临时数组中
		for(var i=0; i<datas.length; i++){
			tempArr = datas[i];
			tempProvinces.push({'id':tempArr.code,'name':tempArr.name, 'cities':[]});
			for(var j=0; j<datas[i].citylist.length; j++){
				tempArr = datas[i].citylist[j];
				tempCities.push({'id':tempArr.code,'name':tempArr.name, 'areas':[]});
				for(var k=0; k<datas[i].citylist[j].arealist.length; k++){
					tempArr = datas[i].citylist[j].arealist[k];
					tempAreas.push({'id':tempArr.code,'name':tempArr.name, 'towns':[]});
				}
			}
		}
		
		//将城市信息添加到对应省份中去
		for(var i=0; i<tempProvinces.length; i++){
			var pId = tempProvinces[i].id.substring(0,2);
			for(var j=0; j<tempCities.length; j++){
				if(tempCities[j].id.substring(0,2) == pId){
					tempProvinces[i].cities.push(tempCities[j]);
				}
			}
		}
		
		//将区信息添加到对应城市中去
		for(var i=0; i<tempCities.length; i++){
			var cId = tempCities[i].id.substring(0,4);
			for(var j=0; j<tempAreas.length; j++){
				if(tempAreas[j].id.substring(0,4) == cId){
					tempCities[i].areas.push(tempAreas[j]);
				}
			}
		}
		
		pcData.provinces = tempProvinces;
		caData.cities = tempCities;
		
	};
	function showProvince(obj, selectPId ,selectCId){
			if(currentOpts.selectAll){
		       defaultStr = '<option value="-1">请选择</option>';
			}else{
				defaultStr = '';
			}
			var tempStr = defaultStr;
			if(selectPId==-1||selectPId=="-1"){
				tempStr= '<option value="-1">请选择</option>'
			}
			var tempId = defaultId;
			var tempName = defaultName;
			var _provinces = pcData.provinces;
			
			var _cities = [];
			if(_provinces.length > 0){
				for(var i=0; i<_provinces.length; i++){
					if(selectPId && selectPId == _provinces[i].id){
						tempStr += '<option value="' + _provinces[i].id + '" selected="selected">' + _provinces[i].name + '</option>';
						tempId = _provinces[i].id;
						tempName = _provinces[i].name;
						_cities = _provinces[i].cities;
						
					}else{
						tempStr += '<option value="' + _provinces[i].id + '">' + _provinces[i].name + '</option>';
					}
				}
			}
			
			$(obj).find('div[name="province"]').html('<select class="opts" id="province">'+ tempStr +'</select>');
		    var $input= $('<input type="hidden" name="province" value="">');
			$input.val($(obj).find('div[name="province"]').find("select").val())
			$(obj).find('div[name="province"]').append($input)
		   return _cities;
	};
	function showProvinceToshow(obj, selectPId ,selectCId){
			if(currentOpts.selectAll){
		       defaultStr = '请选择';
			}else{
				defaultStr = '';
			}
			var tempStr = defaultStr;
			if(selectPId==-1||selectPId=="-1"){
				tempStr= '请选择'
			}
			var tempId = defaultId;
			var tempName = defaultName;
			var _provinces = pcData.provinces;
			var _cities = [];
			if(_provinces.length > 0){
				for(var i=0; i<_provinces.length; i++){
					if(selectPId && selectPId == _provinces[i].id){
						tempStr +=  _provinces[i].name;
						tempId = _provinces[i].id;
						tempName = _provinces[i].name;
						_cities = _provinces[i].cities;
						
					}
				}
			}
			$(obj).find('div[name="province"]').html( tempStr );
		    return _cities;
	};
	function showCity(obj, cities, selectCId){
		
		var tempStr = defaultStr;
		if(selectCId==-1){
			tempStr= '<option value="-1">请选择</option>'
		}
		var tempId = defaultId;
		var tempName = defaultName;
		var _cities = cities? cities : [];
		var _areas = [];
		if(_cities.length > 0){
			for(var i=0; i<_cities.length; i++){
				if(selectCId && selectCId == _cities[i].id){
					tempStr += '<option value="' + _cities[i].id + '"  selected="selected">' + _cities[i].name + '</option>';	
					tempId = _cities[i].id;
					tempName = _cities[i].name;
					_areas = _cities[i].areas;
				}else{
					tempStr += '<option value="' + _cities[i].id + '">' + _cities[i].name + '</option>';
				}
			}
		}
		$(obj).find('div[name="city"]').html('<select class="opts" id="city">'+ tempStr +'</select>');
		var $input= $('<input type="hidden" name="city" value="">');
			$input.val($(obj).find('div[name="city"]').find("select").val())
			$(obj).find('div[name="city"]').append($input)
		return _areas;
	};
	function showCityToshow(obj, cities, selectCId){
		var tempStr = defaultStr;
		if(selectCId==-1){
			tempStr= '请选择'
		}
		var tempId = defaultId;
		var tempName = defaultName;
		var _cities = cities? cities : [];
		var _areas = [];
		if(_cities.length > 0){
			for(var i=0; i<_cities.length; i++){
				if(selectCId && selectCId == _cities[i].id){
					tempStr += _cities[i].name ;	
					tempId = _cities[i].id;
					tempName = _cities[i].name;
					_areas = _cities[i].areas;
				}
			}
		}
		$(obj).find('div[name="city"]').html( tempStr );
		return _areas;
	};
	function showArea(obj, areas, selectAId){
		var tempStr = defaultStr;
		if(selectAId==-1){
			tempStr= '<option value="-1">请选择</option>'
		}
		var tempId = defaultId;
		var tempName = defaultName
		var _areas = areas? areas : [];
		var _towns = [];
		if(_areas.length > 0){
			for(var i=0; i<_areas.length; i++){
				if(selectAId && selectAId == _areas[i].id){
					tempStr += '<option value="' + _areas[i].id + '"  selected="selected">' + _areas[i].name + '</option>';
					tempId = _areas[i].id;
					tempName = _areas[i].name;
					_towns = _areas[i].towns;
				}else{
					tempStr += '<option value="' + _areas[i].id + '">' + _areas[i].name + '</option>';
				}
			}
		}
		$(obj).find('div[name="area"]').html('<select class="opts" id="area">'+ tempStr +'</select>');
		var $input= $('<input type="hidden" name="area" value="">');
			$input.val($(obj).find('div[name="area"]').find("select").val())
			$(obj).find('div[name="area"]').append($input)
		return _towns;
	};
	function showAreaToshow(obj, areas, selectAId){
		var tempStr = defaultStr;
		if(selectAId==-1){
			tempStr= '请选择'
		}
		var tempId = defaultId;
		var tempName = defaultName
		var _areas = areas? areas : [];
		var _towns = [];
		if(_areas.length > 0){
			for(var i=0; i<_areas.length; i++){
				if(selectAId && selectAId == _areas[i].id){
					tempStr += _areas[i].name ;
					tempId = _areas[i].id;
					tempName = _areas[i].name;
					_towns = _areas[i].towns;
				}
			}
		}
		$(obj).find('div[name="area"]').html(tempStr);
		return _towns;
	};
	function createInterval(){
		    var _that = that;
			if(optsArray.length > 0){
				
				for(var i=0; i<optsArray.length; i++){
					if(_that == optsArray[i].obj){
						var spid,scid;
						$(_that).find('select#province').live("change",function(){
							spid=$(this).val();
							ps = showProvince(_that, spid ,scid);
							cs = showCity(_that, ps,-1);
							as = showArea(_that, cs,-1);
						})
						$(_that).find('select#city').live("change",function(){
							spid=$(_that).find('select#province').val();
							scid=$(this).val();
							
							ps = showProvince(_that, spid ,scid);
							cs = showCity(_that, ps, scid);
							as = showArea(_that, cs,-1);
						})
						$(_that).find('select#area').live("change",function(){
							said=$(this).val();
							$(this).siblings(":input[name='area']").val(said);
						})
						break;
					}
				}
			}
	};

	function init(){
		handleData(opts.data);
		currentOpts = opts;
		var spid = $(that).find('input.province').val()?$(that).find('input.province').val():-1;
		var scid = $(that).find('input.city').val()?$(that).find('input.city').val():-1;
		var said = $(that).find('input.area').val()?$(that).find('input.area').val():-1;
		if(pcData.provinces.length > 0){
			for(var i=0; i<pcData.provinces.length; i++){
					for(var j=0; j<pcData.provinces[i].cities.length; j++){
						if(scid && scid == pcData.provinces[i].cities[j].id){
							spid=pcData.provinces[i].id;
						}
					}
			}		
		}
		if(opts.showAddress||$(that).hasClass("selectbox_show")){
			var ps = showProvinceToshow(that, spid ,scid);
			var cs =  showCityToshow(that, ps, scid);
			var as = showAreaToshow(that, cs, said);
		}else{
			var ps = showProvince(that, spid ,scid);
			var cs =  showCity(that, ps, scid);
			var as = showArea(that, cs, said);
			
			optsArray.push({obj:that, opts:opts, ids:{spid:spid, scid:scid, said:said}});
			createInterval();
		}
		
	};
	$.fn.ganged = function(options){
        opts = $.extend({}, $.fn.ganged.defaults, options);
        return this.each(function(){
			that = this;
            init();
        });
    };

    $.fn.ganged.defaults = {
		data : [],
		selectAll:false,
		showAddress:false,
		width : '',
		height : 62
    };
})(jQuery);

/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
function trim( text )
{
  if (typeof(text) == "string")
  {
    return text.replace(/^\s*|\s*$/g, "");
  }
  else
  {
    return text;
  }
}
    function isEmpty( val )
{
  switch (typeof(val))
  {
    case 'string':
      return trim(val).length == 0 ? true : false;
      break;
    case 'number':
      return val == 0;
      break;
    case 'object':
      return val == null;
      break;
    case 'array':
      return val.length == 0;
      break;
    default:
      return true;
  }
}
/*
    用途：检查输入手机号码是否正确
    输入：s：字符串

    返回：如果通过验证返回true,否则返回false
*/

function isMobile( s ){  

var regu =/^[1][0-9][0-9]{9}$/;

var re = new RegExp(regu);

if (re.test(s)) {

return true;

}else{

return false;

}

}


/*

用途：检查输入对象的值是否符合E-Mail格式

输入：str 输入的字符串

返回：如果通过验证返回true,否则返回false

*/

function isEmail( str ){

var myReg = /^[-_A-Za-z0-9]+@([_A-Za-z0-9]+\.)+[A-Za-z0-9]{2,3}$/;

if(myReg.test(str)) return true;

return false;

}



/*

用途：是否为中文

*/

function isChinese( str ){

var myReg = /^[\u4E00-\u9FA5\uf900-\ufa2d]{2,4}$/;

if(myReg.test(str)) return true;

return false;

}

//检测身份证号码
function isCardNo(card)
{
   // 身份证号码为15位或者18位，15位时全为数字，18位前17位为数字，最后一位是校验位，可能为数字或字符X
   var reg = /(^\d{15}$)|(^\d{18}$)|(^\d{17}(\d|X|x)$)/;
   if(reg.test(card) === false)
   {
       return  false;
   }else{
       return ture;
   }
}


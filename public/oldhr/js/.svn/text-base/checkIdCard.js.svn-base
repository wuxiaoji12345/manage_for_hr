/**
 
 * 验证身份证及返回户籍所在地区、出生年月、性别
 
 * 
 
 * @param   string  sId             => 身份证号码
 
 * @return  object  returnResult    => 结果对象
 
 */

function CheckIdCard(sId) {

    //定义地区对象 

    var CityArray = {11: "北京", 12: "天津", 13: "河北", 14: "山西", 15: "内蒙古", 21: "辽宁", 22: "吉林", 23: "黑龙江", 31: "上海", 32: "江苏", 33: "浙江", 34: "安徽", 35: "福建", 36: "江西", 37: "山东", 41: "河南", 42: "湖北", 43: "湖南", 44: "广东", 45: "广西", 46: "海南", 50: "重庆", 51: "四川", 52: "贵州", 53: "云南", 54: "西藏", 61: "陕西", 62: "甘肃", 63: "青海", 64: "宁夏", 65: "新疆", 71: "台湾", 81: "香港", 82: "澳门", 91: "国外"};

    //存放结果信息

    var returnResult = {};

    //严格验证

    var iSum = 0;

    //首先检测是否是第一代身份证

    if (sId.length == 15) {

        //如果是第一代身份证则转换为18位的二代身份证

        sId = sId.replace(/([\d]{6})(\d{9})/, "$119$2x");

    }

    //验证身份证号是否合法

    if (!/^\d{17}(\d|x)$/i.test(sId)) {

        returnResult = {
            result: false,
            errorMsg: "身份证号错误"

        };

        return returnResult;

    }

    sId = sId.replace(/x$/i, "a");

    if (CityArray[parseInt(sId.substr(0, 2))] == null) {

        returnResult = {
            result: false,
            errorMsg: "身份证号中户籍所在地不合法"

        };

        return returnResult;

    }

    //验证身份证号中的生日是否正确

    sBirthday = sId.substr(6, 4) + "-" + Number(sId.substr(10, 2)) + "-" + Number(sId.substr(12, 2));

    var d = new Date(sBirthday.replace(/-/g, "/"));

    if (sBirthday != (d.getFullYear() + "-" + (d.getMonth() + 1) + "-" + d.getDate())) {

        returnResult = {
            result: false,
            errorMsg: "身份证号中出生日期错误"

        };

        return returnResult;

    }

  
     
     //以下代码是对身份证号更进一步的严格验证，测试环境下请注释起来，正式环境可以使用
     
     for (var i = 17; i >= 0; i--){
     
     iSum += (Math.pow(2, i) % 11) * parseInt(sId.charAt(17 - i), 11)
     
     }
     
     if (iSum % 11 != 1) {
     
     returnResult = {
     
     result : false ,
     
     errorMsg : "非法的身份证号"
     
     };
     
     return returnResult;
     
     }
     
 

    //提取生日

    birthdayStr = sId.substr(6, 4) + "-" + sId.substr(10, 2) + "-" + sId.substr(12, 2);

    //提取性别 : 0->女 ; 1->男

    sSex = sId.substr(16, 1) % 2;

    //验证身份证号成功返回对象信息

    returnResult = {
        result: true,
        errorMsg: "提取身份证中信息成功",
        areaName: CityArray[parseInt(sId.substr(0, 2))],
        birthday: birthdayStr,
        sex: sSex

    };

    return returnResult;

}

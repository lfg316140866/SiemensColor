/// <reference path="../../Js/Cmn.js" />
/// <reference path="../../Js/CmnAjax.js" />
/// <reference path="../../Js/CmnMis/CmnMis.js" />
/// <reference path="../../Js/jquery.js" />

InterfaceUrl = "";

$(document).ready(function () {
    //如果网站有子目录，用下面的函数设置下具体的子目录,默认获取Manage前面的，认为是子目录
    var _curUrl = window.location.href;

    _curUrl = _curUrl.replace(/http\:\/\//i,"");

    var _subDir = _curUrl.match(/\/[\s\S]*?\/(?=Manage\/)/i);
   
    if (_subDir != null) {
        Cmn.Func.SetRoot(_subDir[_subDir.length-1]); 
    }

    CmnAjax.Cfg.ProxyUrl = "/TmallAjaxProxy.aspx";
    // CmnAjax.Cfg.ProxyUrl = "";
    var _url = (location.href);
    var _sysName = Cmn.Func.GetParamFromUrl("SysName", _url);
    // InterfaceUrl = "http://csharpframework.cagoe.com/CmnMisItf.aspx";

    if ((_sysName.indexOf("nutrilon")) >= 0) {
        InterfaceUrl = "http://101737739.cmn-actnutrilonapp.taegrid.taobao.com/d/AjaxItf";
    }
    else if ((_sysName.indexOf("onlyJJ")) >= 0) {
        InterfaceUrl = "http://59357538.cmnapp.taegrid.taobao.com/d/AjaxItf"; // http://cmnapp-0.taegrid.taobao.com/d/AjaxItf
    }
    else if (_sysName.indexOf("notricia") >= 0) {
        InterfaceUrl = "http://babyvote-0.taegrid.taobao.com/d/AjaxItf";
    }
    else if (_sysName.indexOf("dongxue") >= 0) {
        CmnAjax.Cfg.ProxyUrl = "";
        InterfaceUrl = "http://dongxue.e-horse.cn/AjaxItf.php";
    }
    else if (_sysName.indexOf("jiangjinbao") >= 0) {
        CmnAjax.Cfg.ProxyUrl = "";
        InterfaceUrl = "http://jiangjinbao.e-horse.cn/CmnMisItf.aspx";
    }
    else if (_sysName.indexOf("phoenix") >= 0) {
        CmnAjax.Cfg.ProxyUrl = "";
        InterfaceUrl = "http://www.phoenix-bicycle.com.cn/CmnMisItf.aspx";
    }
    else if (_sysName.indexOf("phpframework") >= 0) {
        //CmnAjax.Cfg.ProxyUrl = "/Itf/ItfProxy.aspx";
        //InterfaceUrl = "http://phpframework.e-horse.cn/DataItf/AjaxItf.php";

        CmnAjax.Cfg.ProxyUrl = "";
        InterfaceUrl = "/Itf/Php/AjaxItf.php";
    }
    else if (_sysName.indexOf("csharpframework") >= 0) {
        CmnAjax.Cfg.ProxyUrl = "/AjaxProxy.aspx";
        InterfaceUrl = "http://csharpframework.cagoe.com/CmnMisItf.aspx";
    }
    else if (_sysName.indexOf("armani") >= 0) {
        CmnAjax.Cfg.ProxyUrl = "/AjaxProxy.aspx";
        InterfaceUrl = "http://armani2014.ccegroup.cn/siperfume/CmnMisItf.aspx";
    }
    else if (_sysName.indexOf("jianda") >= 0) {
        CmnAjax.Cfg.ProxyUrl = "";
        InterfaceUrl = "http://kinderino2.nurunci.com/DataItf/AjaxItf.php";// "http://jianda.e-horse.cn/DataItf/AjaxItf.php";
    }
    else { //默认为当前网站的接口
        //Cmn.alert("网址错误！不存在的系统名称！");
        CmnAjax.Cfg.ProxyUrl = "";
        InterfaceUrl = Cmn.Func.GetRoot()+"Itf/CSharp/CmnMisItf.aspx";
    }

    if ((_url + "").indexOf("Default.html") >= 0) {
        CmnMis.Frame.Init(InterfaceUrl);
    }
    else if ((_url + "").indexOf("Login.html") >= 0) {

        //CmnMis 框架的选择器配置
        var _misSelector = CmnMis.Frame.Cfg.Selector,
            _securityCode =  $(_misSelector.LoginSecurityCode).not($("input" + _misSelector.LoginSecurityCode));

        //敲击回车  自动登陆
        $(_misSelector.LoginUserName + "," + _misSelector.LoginPassWord + ","
            + _misSelector.LoginSecurityCode).keypress(function (event) {
            if (event.keyCode == 13) { $(_misSelector.LoginbtnLogin).click(); }
        });
        //用户输入自动获取焦点
        $(_misSelector.LoginUserName).focus();
        //初始化验证码
        _securityCode.on("click", function () {
            $(this).html(CreateCode());
        }).html(CreateCode());

        //用户输出处理
        $(_misSelector.LoginUserName).focus(function () {
            if ($(this).val() == "请输入您的用户名") { $(this).val(""); }
            $(this).css("color", "#000");
        }).blur(function () {
            if ($(this).val() == "") {
                $(this).val("请输入您的用户名");
                $(this).css("color", "#d7d7d7");
            }
        });

        //$(_misSelector.LoginPassWord).focus(function () {
        //    if ($(this).val() == "请输入您的密码") {
        //        $(this).val("");
        //    }
        //    else { $(this).attr("type", "password"); }
        //    $(this).css("color", "#000");
        //}).blur(function () {
        //    if ($(this).val() == "") {
        //        $(this).attr("type", "text");
        //        $(this).val("请输入您的密码");
        //        $(this).css("color", "#d7d7d7");
        //    }
        //});
        //验证码的处理
        $("input" + _misSelector.LoginSecurityCode).focus(function () {
            if ($(this).val() == "验证码") {  $(this).val("");  }
            $(this).css("color", "#000000");
        });

        $("input" + _misSelector.LoginSecurityCode).blur(function () {
            if ($(this).val() == "") {
                $(this).val("验证码");
                $(this).css("color", "#d7d7d7");
            }
        });
   
        CmnMis.Frame.InitLogin(InterfaceUrl, function (param) {
            if (param.UserName == "") { Cmn.alert("用户名不能为空！"); return; }
            if (param.PassWord == "") { Cmn.alert("密码不能为空！"); return; }
        }, function (data) {
            if ($("input" + _misSelector.LoginSecurityCode).val() == _securityCode.html()) {
                if (data.IsSuccess == "1") {
                    //系统切换的差异暂时通过系统名称取处理 这里只是暂时的 后续要调整方案
                    if (_sysName == "Wct") {
                        window.location.href = "/Wct/Manage/AccountManage.html";
                    }
                    else {
                        window.location.href = "Default.html?SysName=" + _sysName;
                    }
                }
                else {
                    //
                    _securityCode.html(CreateCode());
                    alert("账号或密码错误！");
                }
            }
            else {
                Cmn.alert("验证码错误");
                _securityCode.html(CreateCode());
            }
        });


    };

    //兼容下
    var showtime = ShowTime;
    //显示时间
    ShowTime();

    $(".cmn-ModuleList").live("click", function () {
        $(".cmn-ModuleList").each(function (i) {
            $(this).find("div").removeClass("Currenttab" + (i + 1)).addClass("tab" + (i + 1));
            $(this).find("div").find("a").removeClass("CurrentHoverTab" + (i + 1));
        });

        $(this).find("div").removeClass("tab" + $(this).index()).addClass("Currenttab" + ($(this).index()));
        $(this).find("div").find("a").addClass("CurrentHoverTab" + ($(this).index()));
    });

    // 处理当前系统时间
    function ShowTime() {
        var today, year, month, day, hour, minute, second, weekday, strDate;
        today = new Date();
        weekday = today.getDay();
        switch (weekday) {
            case 0: {
                strDate = "星期日";
            } break;
            case 1: {
                strDate = "星期一";
            } break;
            case 2: {
                strDate = "星期二";
            } break;
            case 3: {
                strDate = "星期三";
            } break;
            case 4: {
                strDate = "星期四";
            } break;
            case 5: {
                strDate = "星期五";
            } break;
            case 6: {
                strDate = "星期六";
            } break;
        }
        year = today.getYear();
        month = today.getMonth() + 1;
        day = today.getDate();
        hour = today.getHours();
        minute = today.getMinutes();
        second = today.getSeconds();
        if (month.toString().length < 2) month = "0" + month;
        if (day.toString().length < 2) day = "0" + day;
        if (hour.toString().length < 2) hour = "0" + hour;
        if (minute.toString().length < 2) minute = "0" + minute;
        if (second.toString().length < 2) second = "0" + second;
        $(".cmn-CurrentSysDate").text((year < 2000 ? year + 1900 : year) + "-" + month + "-" + day + " " + hour + ":" + minute + ":" + second + " " + strDate);

        setTimeout(showtime, 1000);
    }
  
    //生成码
    function CreateCode(len) {
        if (!len) { len = 4;}
        var str = "qwertyuiopasdfghjklmnbvcxz1234567890"
        var t = "";
        for (var i = 0; i < len; i++) {
            t += str[Cmn.Math.Random(0, str.length-1)];
        }
        return t;
    }

});
 


/// <reference path="../../../Js/Cmn.js" />
/// <reference path="../../Js/CmnMis/CmnMis.js" />
/// <reference path="../../Js/CmnMis/CmnMisUserForm.js" />

CmnMis.CurUserForm.BeforeFillRecList.Add(function (data) {
    for (var _i = 0; _i < data.length; _i++) {
        if ("用户字段配置" != data[_i]["formdesc"]) {
            data[_i]["formdesc"] ="<b>" +data[_i]["formdesc"] +
                "</b><div class='jscToUserFormCfgBtn' style='margin-top:3px;padding:3px;cursor:pointer;color:blue;'>→字段配置</div>"+
                "<div class='btnSqlTest' style='padding:3px;cursor:pointer;color:red;'><a href='" +
                "Tools/UserFormSqlTest.htm?SysName=" + Cmn.Func.GetParamFromUrl("SysName") +
                "&userformid=" + data[_i]["userformid"] + "' target='_blank'>→SelectSql调试</a></div>";
        }

        //一键隐藏和一键显示功能
        data[_i]["isshowinmenudisp"] = data[_i]["isshowinmenudisp"]+
            "<div class='btnIsShowInMenu' style='margin-top:3px;padding:3px;cursor:pointer;color:blue;'>→"+(
            data[_i]["isshowinmenudisp"]=="是"?"隐藏":"显示") + "</div>";
    }

});

CmnMis.CurUserForm.AfterRecListLoad.Add(function () {
    $(".jscToUserFormCfgBtn").off("click").on("click", function () {
        var _recid = $(this).parents(".cmn-Rec").find("input").val();

        CmnMis.Func.ShowSetUserFormCol(_recid, CmnMis.CurUserForm.UserFormID);
    });

    $(CmnMis.CurUserForm.GetSelector(CmnMis.CurUserForm.Selector.RecContainer)).undelegate();
    $(CmnMis.CurUserForm.GetSelector(CmnMis.CurUserForm.Selector.RecContainer)).delegate(".btnIsShowInMenu", "click", function () {
        var _clickDom = this;

        if ($(_clickDom).html() == "→隐藏") {
            CmnMis.TableOpt.UpdateRec(CmnMis.CurUserForm, CmnMis.CurUserForm.GetCurRecKeyVal(this), 
                {"isshowinmenu":"0"}, function () {
                    $(_clickDom).parent().html(
                        "否<div class='btnIsShowInMenu' style='margin-top:3px;padding:3px;cursor:pointer;color:blue;'>→显示</div>");
                }, function () {
                    alert("隐藏失败！请重新尝试。");
                });
        }
        else { //显示
            CmnMis.TableOpt.UpdateRec(CmnMis.CurUserForm, CmnMis.CurUserForm.GetCurRecKeyVal(this),
                { "isshowinmenu": "1" }, function () {
                    $(_clickDom).parent().html(
                        "是<div class='btnIsShowInMenu' style='margin-top:3px;padding:3px;cursor:pointer;color:blue;'>→隐藏</div>");
                }, function () {
                    alert("显示失败！请重新尝试。");
                });
        }
    });
});

CmnMis.CurUserForm.OnAddInitComplete.Add(function () {
    CmnMis.CurUserForm.OnUpdateInitComplete.Trigger("UserForm_OnUpdateInitComplete_EventHandle");
});

CmnMis.CurUserForm.OnUpdateInitComplete.Add(function () {

    var _userFormExtCtl = CmnMis.CurUserForm.GetControlByName("form_exd_cfg"),
        _curFormDom = CmnMis.CurUserForm.GetDom();

    //调整控件排版
    _userFormExtCtl.ControlDom.css({
        "width": "100%",
        "height": "auto"
    });
    _userFormExtCtl.ControlDom.find(CmnMis.UI.Control.Selector.CtlContent).css({
        "line-height": "normal",
        "border": "none",
        "width": "auto",
        "height": "auto"
    }).empty();
   

    //OutputDataSql
    var _cfgPanelDOM = '<span style=display: inline-block; width: 100%;">{</span>'+
                       '<span class="jscNode" style="display: inline-block; width: 100%;">	' +
                            '<span style=" display: inline-block; padding:10px; text-align: right;">' +
                                '导出数据的sql语句:' +
                            '</span>' +
                            '<span nodename="OutputDataSql" style=" display: inline-block;padding:10px;text-align: center;">' +
                                '<textarea style="border: 1px #ccc solid;width: 250px; height: 80px;"></textarea>' +
                            '</span>' +
                       '</span>' +
                       '<span style=" display: inline-block; width: 100%;">}</span>';
  
    _userFormExtCtl.ControlDom.find(CmnMis.UI.Control.Selector.CtlContent).append(_cfgPanelDOM);
 
    _userFormExtCtl.SetValue = function (value) {
        if (value) { value = $.parseJSON(value); }
        if (Cmn.IsType(value, "object")) {
            $.each(value, function (key, val) {
                _curFormDom.find(".jscNode").find("span [nodename=" + key + "]").val(val);
            });
        }
    };

    _userFormExtCtl.GetValue = function () {
        var _formExtCfg = "{";

        _curFormDom.find(".jscNode").each(function () {
            _formExtCfg += ("\"" +
                $(this).find("span").eq(1).attr("nodename") + "\":\"" +
                $(this).find("span").eq(1).find("textarea").val() + "\",");
        });

        if (_curFormDom.find(".jscNode").length > 0) { _formExtCfg = _formExtCfg.slice(0, _formExtCfg.length - 1); }
      
        _formExtCfg += "}";

        return _formExtCfg;
    };

}, "UserForm_OnUpdateInitComplete_EventHandle");

CmnMis.CurUserForm.OnAddClick.Add(function () {

});
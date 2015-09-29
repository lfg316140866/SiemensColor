/// <reference path="../ThirdLib/jquery.js" />
/// <reference path="../Cmn.js" />
/// <reference path="../CmnAjax.js" />
/// <reference path="../CmnFuncExd.js" />
$(function () {
    CmnAjax.DataStepLoad(".Js_ColorMomentList", "Itf/Php/AjaxItf.php?method=GetSqlData&ItfName=get_moment_list", "", ".smg-box", 24, 12, function () {
    })
});
/// <reference path="../ThirdLib/jquery.js" />
/// <reference path="../Cmn.js" />
/// <reference path="../CmnAjax.js" />
/// <reference path="../CmnFuncExd.js" />
function BindDataStepLoad(method) {
    _Y = new CmnAjax.DataStepLoad([".Js_SmgBox1", ".Js_SmgBox2", ".Js_SmgBox3", ".Js_SmgBox4", ".Js_SmgBox5", ".Js_SmgBox6"], "Itf/Php/AjaxItf.php?method=GetSqlData&ItfName="+method+"", "", [".Js_InfoBox"], 16, 30, function (dat) {
        _Y.BindScrollLoadData(".Js_InfoBox", 100);
    })
}
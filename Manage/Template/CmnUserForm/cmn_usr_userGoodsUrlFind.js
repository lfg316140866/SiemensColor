/// <reference path="../../../Js/CmnMis/CmnMis.js" />
/// <reference path="../../../Js/md5.js" />
/// <reference path="../../../Js/CmnMis/CmnMisUserForm.js" />


CmnMis.CurUserForm.BeforeFillRecList.Add(function (data) {
    for (var _i = 0; _i < data.length; _i++) {
        data[_i]["ProductUrl"] = "<a href='" + data[_i]["ProductUrl"] + "' target=\"_blank\" >" + data[_i]["ProductUrl"] + "</a>";
    }

});


CmnMis.CurUserForm.BeforeGetRecList.Add(function () {
    var _userID = Cmn.Func.Cookie.Get("cmn_UserID");
    var _condition = "";
    if (_userID != "67" && _userID != "68") {
        CmnAjax.PostData("/Itf/CSharp/CmnMisItf.aspx?method=GetSqlData&ItfName=FindTask", "", function (data) {
            for (var _i = 0; _i < data.data.length; _i++) {
                data.data[_i]["TaskID"];
                _condition = data.data[_i]["TaskID"] + ",";
            }
            if (_condition != "") {
                _condition = _condition.substring(0, _condition.length - 1);
            } else {
                _condition = 0;
            }
            CmnMis.CurUserForm.AddCondition("a.TaskID in (" + _condition + " )");
        });
    }

});

CmnMis.CurUserForm.AfterRecListLoad.Add(function () {
    var _curFormDom = $(CmnMis.CurUserForm.GetUserFormSelector());

    _curFormDom.find(".jscRightCfgBtn").off("click").on("click", function () {

        var _recid = CmnMis.CurUserForm.GetCurRecKeyVal(this);

        CmnMis.Frame.ShowUserForm(399,
					{ ViewState: "List", Condition: "[a.TaskID=" + _recid + "]", data: { "a.TaskID": _recid } });
    });

    //_curFormDom.find(".cmn-btnSave").off("click").on("click", function () {
    //    if ($(".cmn_dateSelect_input").val == "") {
    //        alert("请填写定时启动时间");
    //    };
    //});

});



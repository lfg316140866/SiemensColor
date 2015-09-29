/// <reference path="../../../Js/CmnMis/CmnMis.js" />
/// <reference path="../../../Js/md5.js" />
/// <reference path="../../../Js/CmnMis/CmnMisUserForm.js" />




CmnMis.CurUserForm.BeforeFillRecList.Add(function (data) {
    for (var _i = 0; _i < data.length; _i++) {
        data[_i]["TaskCount"] = 
            "<b style='cursor: pointer; color: #00f;margin-left:2px;' TaskID = " + data[_i]["TaskID"] + " AdvSearchConditionID=" + data[_i]["AdvSearchConditionID"] + " class='jscRightCfgBtn'>[详细查询]</b>";
            
        var _hightTask = "HightTask" + data[_i]["AdvSearchConditionID"];

        var _taskAdvConditionRefID = data[_i]["TaskAdvConditionRefID"];
        var _taskID = data[_i]["TaskID"];
        IsHightTaskComplete(_hightTask, _taskID, _taskAdvConditionRefID);
           
    }



});

function IsHightTaskComplete(HightTask, TaskID, TaskAdvConditionRefID) {
    CmnAjax.PostData("/Itf/CSharp/CmnMisItf.aspx?method=GetSqlData&ItfName=IsHightTaskComplete&HightTask1=" + HightTask + "&TaskID=" + TaskID + "", "", function (dat) {
        if (dat.data[0]["Column1"] == 0) {
            CmnAjax.PostData("/Itf/CSharp/CmnMisItf.aspx?method=GetSqlData&ItfName=UpdateHightTaskStart&TaskAdvConditionRefID=" + TaskAdvConditionRefID + "", "", function () {

            });
        }
    });
};

CmnMis.CurUserForm.BeforeGetRecList.Add(function () {
    var _userID = Cmn.Func.Cookie.Get("cmn_UserID");
    if (_userID != "67" && _userID != "68") {
        CmnMis.CurUserForm.AddCondition("a.cmn_CreateUserID=" + _userID);
    }
});



CmnMis.CurUserForm.AfterRecListLoad.Add(function () {
    

    //for (var _i = 0; _i < $(".dat-EstimatedTime").length; _i++) {
    //    (function (_i) {
    //        var _taskID = $(".dat-TaskID").eq(_i).text();
    //        var _key = false;
    //        setTimeout(function () {
    //            CmnMis.Itf.GetData("GetTaskToQty", { "TaskID": _taskID }, false, function (data) {
    //                var _totalQty = $(".dat-TotalQty").eq(_i).text();
    //                var _grabQty = $(".dat-GrabQty").eq(_i).text();
    //                _totalQtys = data.data[0]["TotalQty"];
    //                if (_totalQtys - _totalQty == 0) {

    //                    if (_totalQtys != 0) {
    //                        if ($(".dat-TaskDtlRate").eq(_i).text() == "100.00%") {
    //                            $(".dat-EstimatedTime").eq(_i).text("已完成");
    //                        } else {
    //                            $(".dat-EstimatedTime").eq(_i).text("未启动");
    //                        }
    //                    } else {
    //                        $(".dat-EstimatedTime").eq(_i).text("没有数据");
    //                    }
    //                } else {
    //                    var _estimatedTime = (_grabQty - _totalQtys) / (_totalQtys - _totalQty) / 60;
    //                    $(".dat-EstimatedTime").eq(_i).text(_estimatedTime.toFixed(2) * 100 + "Hour");
    //                }
    //            });
    //            clearTimeout(this);
    //        }, 60000);
    //    })(_i);
    //};


    //for (var _i = 0; _i < $(".dat-EstimatedTime").length; _i++) {
    //    var _taskID = $(".dat-TaskID").eq(_i).text();
    //    var _key = false;
    //    CmnMis.Itf.GetData("GetTaskToQty", { "TaskID": _taskID }, false, function (data) {
    //        var _totalQty = $(".dat-TotalQty").eq(_i).text();
    //        var _grabQty = $(".dat-GrabQty").eq(_i).text();
    //        _totalQtys = data.data[0]["TotalQty"];
    //        var _estimatedTime = (_grabQty - _totalQtys) / (_totalQtys - _totalQty);
    //        setTimeout(function () {
    //            $(".dat-EstimatedTime").eq(_i).text(_estimatedTime.toFixed(2) * 100 + "%");
    //            _key = true;
    //            clearTimeout(this);
    //        }, 1000);
    //    });
    //}


    var _curFormDom = $(CmnMis.CurUserForm.GetUserFormSelector());

    _curFormDom.find(".jscRightCfgBtn").off("click").on("click", function () {
        var _t = "";
        if ($(this).attr("AdvSearchConditionID") == 1) {
            _t = "a.HightTask1"; 
        } else if ($(this).attr("AdvSearchConditionID") == 2) {
            _t = "a.HightTask2 ";
        }
        var _recid = CmnMis.CurUserForm.GetCurRecKeyVal(this);

        CmnMis.Frame.ShowUserForm(399,
					{ ViewState: "List", Condition: "a.TaskID=" + $(this).attr("TaskID") + " and " + _t + " = 2", data: { "a.TaskID": _recid, "a.HightTask1": $(this).attr("AdvSearchConditionID") } });
    });
});

CmnMis.CurUserForm.OnAddInitComplete.Add(function () {

});





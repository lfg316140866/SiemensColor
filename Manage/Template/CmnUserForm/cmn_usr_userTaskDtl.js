/// <reference path="../../../Js/CmnMis/CmnMis.js" />
/// <reference path="../../../Js/md5.js" />
/// <reference path="../../../Js/CmnMis/CmnMisUserForm.js" />
/// <reference path="../../../Js/ThirdLib/jquery1_9_1.js" />
CmnMis.CurUserForm.BeforeGetRecList.Add(function () {
    var _userID = Cmn.Func.Cookie.Get("cmn_UserID");
    if (_userID != "67" && _userID != "68") {
        CmnMis.CurUserForm.AddCondition("a.cmn_CreateUserID=" + _userID);
    }
});


CmnMis.CurUserForm.BeforeFillRecList.Add(function (data) {
    for (var _i = 0; _i < data.length; _i++) {
        data[_i]["TaskDesc"] = data[_i]["TaskDesc"] + "</br>" +
            "<b style='cursor: pointer; color: #00f;margin-left:2px;' class='jscRightCfgBtn'>[详细查询]</b>";
        data[_i]["EstimatedTime"] = "<b style='cursor: pointer; color: #00f;margin-left:2px;' taskID='" + data[_i]["TaskID"] + "'  class='jscOnHighBtn'>[绑定高级搜索条件]</b>";
        if (data[_i]["TaskStatusdisp"] == "处理完毕" || data[_i]["TaskStatusdisp"] == "异常" || data[_i]["TaskStatusdisp"]=="已暂停") {
            data[_i]["TaskRate"] = data[_i]["TaskRate"] + "</br> <b style='cursor: pointer; color: #00f;margin-left:2px;' taskID='" + data[_i]["TaskID"] + "'  class='jscTaskRateBtn'>[手动重连]</b>";
        }
        if (data[_i]["TaskStatusdisp"] != "处理完毕" && data[_i]["TaskStatusdisp"] != "已暂停" && data[_i]["TaskStatusdisp"] != "未处理") {
            data[_i]["TaskStatusdisp"] = data[_i]["TaskStatusdisp"] + "</br> <b style='cursor: pointer; color: #00f;margin-left:2px;' taskID='" + data[_i]["TaskID"] + "'  class='jscTaskTimeOut'>[暂停任务]</b>";
        }

    }
});




CmnMis.CurUserForm.AfterRecListLoad.Add(function () {
    $(".jscTaskRateBtn").off("click").on("click", function () {
        CmnAjax.PostData("/Itf/CSharp/CmnMisItf.aspx?method=GetSqlData&ItfName=RestartGrabTask&TaskID=" + $(this).attr("taskID") + "", "", function (data) {
            alert("已经将任务重新连接。对任务结果产生怀疑的时候，可使用手动重连功能测试。");
            CmnMis.CurUserForm.RefreshData();
        });
    });
    //暂停任务
    $(".jscTaskTimeOut").off("click").on("click", function () {
        CmnAjax.PostData("/Itf/CSharp/CmnMisItf.aspx?method=GetSqlData&ItfName=Grb_TimeOut&TaskID=" + $(this).attr("taskID") + "", "", function (data) {
            CmnMis.CurUserForm.RefreshData();
        });
    });

    $(".jscOnHighBtn").off("click").on("click", function () {
        var _recid = CmnMis.CurUserForm.GetCurRecKeyVal(this);

        CmnMis.Frame.ShowUserForm(404,
					{ ViewState: "Add", Condition: "[a.TaskID=" + $(this).attr("taskID") + "]", data: { "a.TaskID": $(this).attr("taskID") } });
    });

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

        var _recid = CmnMis.CurUserForm.GetCurRecKeyVal(this);

        CmnMis.Frame.ShowUserForm(399,
					{ ViewState: "List", Condition: "[a.TaskID=" + _recid + "]", data: { "a.TaskID": _recid } });
    });

});





CmnMis.CurUserForm.OnAddInitComplete.Add(function () {
    $("[name=ProductTypeID]").hide();
    $("[name=ProductTypedesc2]").find("select").html("");
    $("[name=ProductTypedesc3]").find("select").html("");
    liandong();
});


CmnMis.CurUserForm.BeforeAddSave.Add(function () {
    if ($("[name=ProductTypedesc3]").find("select").val() == null) {
        if ($("[name=ProductTypedesc2]").find("select").val() == null) {
        } else {
            $("[name=ProductTypeID]").find("select").val($("[name=ProductTypedesc2]").find("select").val());
        }
    } else {
        $("[name=ProductTypeID]").find("select").val($("[name=ProductTypedesc3]").find("select").val());
    }
    $("[name=ProductTypedesc2]").find("select").html("");
    $("[name=ProductTypedesc3]").find("select").html("");

});


CmnMis.CurUserForm.BeforeUpdateSave.Add(function (recID) {
    if ($("[name=ProductTypedesc3]").find("select").val() == null || $("[name=ProductTypedesc3]").find("select").val() == "") {
        if ($("[name=ProductTypedesc2]").find("select").val() == null || $("[name=ProductTypedesc2]").find("select").val() == "") {
            $("[name=ProductTypeID]").find("select").val($("[name=ProductTypedesc1]").find("select").val());
        } else {
            $("[name=ProductTypeID]").find("select").val($("[name=ProductTypedesc2]").find("select").val());
        }
    } else {
        $("[name=ProductTypeID]").find("select").val($("[name=ProductTypedesc3]").find("select").val());
    }
});



CmnMis.CurUserForm.OnUpdateInitComplete.Add(function (recID) {

    //liandong();


    setTimeout(function () {
        var _siteID = CmnMis.CurUserForm.GetControlByName("SiteID").GetValue();

        CmnAjax.PostData("/Itf/CSharp/CmnMisItf.aspx?method=GetSqlData&ItfName=grb_ClassifySearchOne&SiteID=" + _siteID + "&ParentProductTypeID=99999", "", function (data) {
            if (data.data.length > 0) {
                var _option = "";
                for (var _i = 0 ; _i < data.data.length; _i++) {
                    _option += "<option value='" + data.data[_i]["ProductTypeID"] + "' >" + data.data[_i]["ProductTypeDesc"] + "</option>"
                }
                $("[name=ProductTypedesc1]").find("select").html(_option);

                CmnAjax.PostData("/Itf/CSharp/CmnMisItf.aspx?method=GetSqlData&ItfName=AddFenLei&TaskID=" + recID + "", "", function (dat) {
                    if (dat.data[0]["ProductTypedesc1"] != "") {
                        $("[name=ProductTypedesc1]").find("select").val(dat.data[0]["ProductTypedesc1"]);
                        if (dat.data[0]["ProductTypedesc2"] != "") {
                            CmnAjax.PostData("/Itf/CSharp/CmnMisItf.aspx?method=GetSqlData&ItfName=grb_ClassifySearchOne&SiteID=" + _siteID + "&ParentProductTypeID=" + dat.data[0]["ProductTypedesc1"] + "",
                            "", function (datSecon) {
                                if (datSecon.data.length > 0) {
                                    var _option = "";
                                    for (var _i = 0 ; _i < datSecon.data.length; _i++) {
                                        _option += "<option value='" + datSecon.data[_i]["ProductTypeID"] + "' >" + datSecon.data[_i]["ProductTypeDesc"] + "</option>"
                                    }
                                    $("[name=ProductTypedesc2]").find("select").html(_option);
                                    $("[name=ProductTypedesc2]").find("select").val(dat.data[0]["ProductTypedesc2"]);
                                    if (dat.data[0]["ProductTypedesc3"] != "") {
                                        CmnAjax.PostData("/Itf/CSharp/CmnMisItf.aspx?method=GetSqlData&ItfName=grb_ClassifySearchOne&SiteID=" + _siteID + "&ParentProductTypeID=" + dat.data[0]["ProductTypedesc2"] + "",
                                        "", function (datThree) {
                                            if (datThree.data.length > 0) {
                                                var _option = "";
                                                for (var _i = 0 ; _i < datThree.data.length; _i++) {
                                                    _option += "<option value='" + datThree.data[_i]["ProductTypeID"] + "' >" + datThree.data[_i]["ProductTypeDesc"] + "</option>"
                                                }
                                                $("[name=ProductTypedesc3]").find("select").html(_option);
                                                $("[name=ProductTypedesc3]").find("select").val(dat.data[0]["ProductTypedesc3"]);
                                            }
                                        });
                                    }
                                }
                            });
                        }
                    }
                });

            }
        });
    }, 1000);

    var _productTypeID = CmnMis.CurUserForm.GetControlByName("SiteID");

    _productTypeID.OnChange.Add(function () {
        FillOneProductTypeList();
    }, "ProductTypeID");


    var _productTypeID1 = CmnMis.CurUserForm.GetControlByName("ProductTypedesc1");

    _productTypeID1.OnChange.Add(function () {
        FillSecondProductTypeList();
    }, "ProductTypeID");

    var _productTypeID2 = CmnMis.CurUserForm.GetControlByName("ProductTypedesc2");

    _productTypeID2.OnChange.Add(function () {
        FillThreeProductTypeList();
    }, "ProductTypedesc2");




    //liandong();

    //$("[name=ProductTypeID]").hide();
    //CmnAjax.PostData("/Itf/CSharp/CmnMisItf.aspx?method=GetSqlData&ItfName=JudgeType&ProductTypeID=" + $("[name=ProductTypeID]").find("select").val() + "", "", function (data) {
    //    if (data.data != null && data.data.length > 0) {
    //        var _parentProductTypeID = data.data[0]["ParentProductTypeID"];
    //        if (_parentProductTypeID == 99999) {
    //            $("[name=ProductTypedesc1]").find("select").val($("[name=ProductTypeID]").find("select").val());
    //        } else if (_parentProductTypeID <= 33) {
    //            $("[name=ProductTypedesc1]").find("select").val(_parentProductTypeID);
    //            FillSecondProductTypeList(function () {
    //                $("[name=ProductTypedesc2]").find("select").val($("[name=ProductTypeID]").find("select").val());
    //            });
    //        } else {
    //            CmnAjax.PostData("/Itf/CSharp/CmnMisItf.aspx?method=GetSqlData&ItfName=JudgeType&ProductTypeID=" + _parentProductTypeID + "", "", function (dat) {
    //                //CmnAjax.PostData("/Itf/CSharp/CmnMisItf.aspx?method=GetSqlData&ItfName=liandong&ParentProductTypeID=" + $("[name=ProductTypedesc1]").find("select").val() + "", "", function (data) {
    //                //    if (data.data != null && data.data.length > 0) {
    //                //        var _option = "";
    //                //        for (var _i = 0; _i < data.data.length; _i++) {
    //                //            _option += "<option cmn-fieldname='ProductTypeDesc' value='" + data.data[_i]["ProductTypeID"] + "'>" + data.data[_i]["ProductTypeDesc"] + "</option>";
    //                //            if (_i == data.data.length) {
    //                //                $("[name=ProductTypedesc2]").find("select").html(_option);
    //                //                $("[name=ProductTypedesc2]").find("select").val(_parentProductTypeID);
    //                //                CmnAjax.PostData("/Itf/CSharp/CmnMisItf.aspx?method=GetSqlData&ItfName=liandong&ParentProductTypeID=" + $("[name=ProductTypedesc2]").find("select").val() + "", "", function (data) {
    //                //                    if (data.data != null && data.data.length > 0) {
    //                //                        var _option = "";
    //                //                        for (var _i = 0; _i < data.data.length; _i++) {
    //                //                            _option += "<option cmn-fieldname='ProductTypeDesc' value='" + data.data[_i]["ProductTypeID"] + "'>" + data.data[_i]["ProductTypeDesc"] + "</option>";
    //                //                        }
    //                //                        $("[name=ProductTypedesc3]").find("select").html(_option);
    //                //                        if (callback != null) {
    //                //                            callback();
    //                //                        }
    //                //                    }
    //                //                });
    //                //            }
    //                //        }
    //                //    }
    //                //});

    //                if (dat.data != null && dat.data.length > 0) {
    //                    $("[name=ProductTypedesc1]").find("select").val(dat.data[0]["ParentProductTypeID"]);
    //                    FillSecondProductTypeList(function () {
    //                        $("[name=ProductTypedesc2]").find("select").val(_parentProductTypeID);
    //                        FillThreeProductTypeList(function () {
    //                            $("[name=ProductTypedesc3]").find("select").val($("[name=ProductTypeID]").find("select").val());
    //                        });
    //                    });
    //                }
    //            })
    //        }
    //    }
    //});


    //if ($("[name=ProductTypedesc1]").find("select").val() == "" || $("[name=ProductTypedesc1]").find("select").val() == null) {
    //    $("[name=ProductTypedesc1]").find("select").val("");
    //}
});


function liandong() {
    var _productTypeID = CmnMis.CurUserForm.GetControlByName("SiteID");

    _productTypeID.OnChange.Add(function () {
        FillOneProductTypeList();
    }, "ProductTypeID");


    var _productTypeID = CmnMis.CurUserForm.GetControlByName("ProductTypedesc1");

    _productTypeID.OnChange.Add(function () {
        FillSecondProductTypeList();
    }, "ProductTypeID");

    var _productTypeID = CmnMis.CurUserForm.GetControlByName("ProductTypedesc2");

    _productTypeID.OnChange.Add(function () {
        FillThreeProductTypeList();
    }, "ProductTypedesc2");

};

function FillOneProductTypeList(callback) {
    $("[name=ProductTypedesc1]").find("select").html("");
    $("[name=ProductTypedesc2]").find("select").html("");
    $("[name=ProductTypedesc3]").find("select").html("");

    CmnAjax.PostData("/Itf/CSharp/CmnMisItf.aspx?method=GetSqlData&ItfName=GetProductTypeDesc&SiteID=" + CmnMis.CurUserForm.GetControlByName("SiteID").GetValue() + "&ParentProductTypeID=99999", "", function (data) {
        if (data.data != null && data.data.length > 0) {
            var _option = "";
            for (var _i = 0; _i < data.data.length; _i++) {
                _option += "<option cmn-fieldname='ProductTypeDesc' value='" + data.data[_i]["ProductTypeID"] + "'>" + data.data[_i]["ProductTypeDesc"] + "</option>";
            }
            $("[name=ProductTypedesc1]").find("select").html(_option + "<option></option>");
            if (callback != null) {
                callback();
            }
        }
    });
};




function FillSecondProductTypeList(callback) {
    $("[name=ProductTypedesc2]").find("select").html("");
    $("[name=ProductTypedesc3]").find("select").html("");
    CmnAjax.PostData("/Itf/CSharp/CmnMisItf.aspx?method=GetSqlData&ItfName=liandong&ParentProductTypeID=" + $("[name=ProductTypedesc1]").find("select").val() + "", "", function (data) {
        if (data.data != null && data.data.length > 0) {
            var _option = "";
            for (var _i = 0; _i < data.data.length; _i++) {
                _option += "<option cmn-fieldname='ProductTypeDesc' value='" + data.data[_i]["ProductTypeID"] + "'>" + data.data[_i]["ProductTypeDesc"] + "</option>";
            }
            $("[name=ProductTypedesc2]").find("select").html(_option + "<option></option>");
            if (callback != null) {
                callback();
            }
        }
    });
};



function FillThreeProductTypeList(callback) {
    $("[name=ProductTypedesc3]").find("select").html("");
    CmnAjax.PostData("/Itf/CSharp/CmnMisItf.aspx?method=GetSqlData&ItfName=liandong&ParentProductTypeID=" + $("[name=ProductTypedesc2]").find("select").val() + "", "", function (data) {
        if (data.data != null && data.data.length > 0) {
            var _option = "";
            for (var _i = 0; _i < data.data.length; _i++) {
                _option += "<option cmn-fieldname='ProductTypeDesc' value='" + data.data[_i]["ProductTypeID"] + "'>" + data.data[_i]["ProductTypeDesc"] + "</option>";
            }
            $("[name=ProductTypedesc3]").find("select").html(_option + "<option></option>");
            if (callback != null) {
                callback();
            }
        }
    });
};



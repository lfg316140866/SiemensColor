﻿CmnMis_UI_Control_ItfSelect_Version = "2.1", function () { var a = CmnMis.UI.Control; a.ItfSelect = function (b, c) { var d, e; a.NewControl("SelectBySort", b, c), d = this, Cmn.Object.Inherit(d, a.SelectBySort, [b, c]), d.Type = "SelectBySort", d.IsExistHtmlTemp = !1, d.SetCfgDescCfg({ SortsPanelWidth: { Type: "Text", Desc: "分类面板的宽度", Val: "155" }, FillSql: { Type: "SqlInput", Desc: "sql或者接口名称", Val: "select itfname,itfdesc from cmn_itf_interface order by itfname desc" } }), d.ControlCfg = d.InitControlConfig(Cmn.Extend(d.ControlCfg, c)), d.ControlCfg.Sorts = [{ Desc: "接口分类", FillSql: "select interface_sort_id,interface_sort_desc from cmn_itf_interface_sort", ForeignKeyFieldName: "interface_sort_id", ParentIDFieldName: "parent_interface_sort_id" }], e = {}, c["FillSql"] || (e = CmnAjax.GetData(InterfaceUrl + "?method=EncryptStr", { Str: d.ControlCfg["FillSql"] }), e && e.Str && (d.ControlCfg["FillSql"] = e.Str)), (!c["Sorts"] || c["Sorts"] && 0 == c["Sorts"].length) && (e = CmnAjax.GetData(InterfaceUrl + "?method=EncryptStr", { Str: d.ControlCfg.Sorts[0]["FillSql"] }), e && e.Str && (d.ControlCfg.Sorts[0]["FillSql"] = e.Str)) } }();
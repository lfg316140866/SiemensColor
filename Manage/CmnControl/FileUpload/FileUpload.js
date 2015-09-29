/// <reference path="../../../Js/CmnUI/CmnUI.js" />
/// <reference path="../../../Js/CmnUI/Upload/Upload.js" />
/// <reference path="../../../Js/Cmn.js" />
/// <reference path="../../../Js/CmnAjax.js" />
/// <reference path="../../../Js/ThirdLib/jquery.js" />
/// <reference path="../../Js/CmnMis/CmnMisControl.js" />

//--------------------------------FileUpload控件类----------------------
CmnMis_UI_Control_FileUpload_Version = "2.2.2";
(function () {

    //控件对象的命名空间
    var _Control = CmnMis.UI.Control;

    _Control.FileUpload = function (colName, controlCfg) {
        /// <summary>FileUpload控件类</summary>
        /// <param name="colName" type="String">字段名称</param> 
        /// <param name="controlCfg" type="String">控件配置</param>
        Cmn.Object.Inherit(this, _Control.BasControl, [colName, controlCfg]);

        //指向当前控件对象
        var _Self = this,
            _IsUploadComplete = null,
            _Enabled = true;
        //控件类型
        _Self.Type = "FileUpload";
        //加载模板
        this.IsExistHtmlTemp = true;
        //值
        this.Val = "";
        //删除的文件路径列表
        this.DelFilePaths = [];

        //设置控件配置描述配置 
        _Self.SetCfgDescCfg({
            "SavePath": { "Type": "Text", "Desc": "上传保存的路径", "Val": "/Upload" },
            "FileSize": { "Type": "Text", "Desc": "上传文件的大小", "Val": "20" },
            "FileType": { "Type": "RadioButton", "Desc": "上传控件类型", "Val": [{ "key": "image", "val": "图片上传" }, { "key": "file", "val": "文件上传" }] },
            "FileExt": { "Type": "TextArea", "Desc": "上传文件限制后缀", "Val": "jpg,jpeg,png,gif,bmp,mp4,mp3,avi,3gp,rmvb,wmv,mkv,mpg,vob,mov,flv" },
            "IsSaveRealFileName": { "Type": "RadioButton", "Desc": "是否保存真实文件名称", "Val": [{ "key": "0", "val": "否" }, { "key": "1", "val": "是" }] }
        });

        //初始化控件配置
        _Self.ControlCfg = _Self.InitControlConfig(Cmn.Extend(_Self.ControlCfg, controlCfg));

        //点击删除按钮的事件
        _Self.OnClickDelBtn = new Cmn.Event(this);
        _Self.OnFilter = new Cmn.Event(this);
        _Self.OnComplete = new Cmn.Event(this);

        //初始化控件配置
        _Self.InitControl = function (controlCfg) {

            //加载所需js文件
            if (!Cmn.UI || !Cmn.UI.BasPlugin) {
                CmnAjax.Func.LoadJs(Cmn.Func.GetRoot() + "Js/CmnUI/CmnUI.js");
            }
            if (!Cmn.UI || !Cmn.UI.Upload) {
                CmnAjax.Func.LoadJs(Cmn.Func.GetRoot() + "Js/CmnUI/Upload/Upload.js");
            }

            //创建上传控件
            _Self.Upload = Cmn.UI.Upload(_Self.ControlDom.find(".cg-Ctl-SelectFileBtn"));

            //设置允许上传的文件后缀
            _Self.Upload.LimitFileSuffix = !!_Self.ControlCfg.FileExt ?
                _Self.ControlCfg.FileExt : _Self.Upload.LimitFileSuffix;

            //限制上传的文件大小 默认3m
            _Self.Upload.LimitSize = (parseInt(_Self.ControlCfg.FileSize) || 3);

            //保存的目录
            if (_Self.ControlCfg.SavePath) {
                _Self.Upload.SaveRootPath = _Self.ControlCfg.SavePath;
            }

            _Self.Upload.OnFilter.Add(function (e) {

                if (e.State) {
                    _IsUploadComplete = false;
                    _Self.SetValue(e.Path);
                    _Self.ControlDom.find(".cg-Ctl-ProgressContainer").show();
                    //开始上传
                    _Self.Upload.Upload();
                }
                else { Cmn.alert(e.Msg); }

                _Self.OnFilter.Trigger([e]);
            });
            _Self.Upload.OnProgress.Add(function (e) {
                _Self.ControlDom.find(".cg-Ctl-Progress").css({ width: e.Progress + "%" });
            });
            _Self.Upload.OnComplete.Add(function (e) {

                if (e.State) {  _Self.SetValue(e.Path);  }
                else {
                    _Self.SetValue("");
                    alert("上传失败，" + e.Msg);
                }

                _IsUploadComplete = true;

                _Self.OnComplete.Trigger([e]);
            });

            //删除文件
            _Self.ControlDom.find(".cg-Ctl-DelFileItemBtn").click(function () {
                //添加删除文件列表
                _Self.DelFilePaths.push(_Self.Val);
                //清空
                _Self.SetValue("");

                //触发点击删除按钮事件
                _Self.OnClickDelBtn.Trigger();

                //删除文件需要在保存的时候删除 所以监听了这个事件
                CmnMis.CurUserForm.AfterSave.Add(function () {

                    var _delLen = this.DelFilePaths.length,
                        _delImagePath = "";

                    if (!!window["InterfaceUrl"]) {

                        for (var _i = 0; _i < _delLen; _i++) {
                            //删除的文件路径
                            _delImagePath = this.DelFilePaths.shift();
                            //请求接口删除去
                            CmnAjax.PostData(window["InterfaceUrl"] + "?method=DelFile",
                               { FilePath: _delImagePath }, function (data) {
                                   if (data["IsSuccess"] && data.IsSuccess == "1") { }
                                   else {
                                       Cmn.Log("文件删除失败！");
                                   }
                               });
                        }
                    }

                }, "cmn-Ctl-FileUploadDelFile", _Self);

            });

        }

        //控件验证
        _Self.VerifyInput = function (tipSelector, errCallback) {
            /// <summary>数据验证</summary>
            /// <param name="tipSelector" type="String">提示容器选择器</param>
            /// <param name="errCallback" type="function">错误信息的回调</param>
            var _self = this,
                _regex = "",                //正则表达式
                _val = Cmn.Func.GetNoHTMLFormatStr(_self.GetValue()),
                _isVerify = true,
                _msg = "";

            //是否上传完成
            if (_IsUploadComplete == false) {
                errCallback && errCallback.call(_self, $.extend(new Cmn.ErrMsg("正在上传中....请稍后保存!"), { control: _self }));
                return false;
            };

            //必填
            if (_self.ControlCfg.IsRequired == "1") {
                _regex = /\S/;
                if (!_val) { _val = ""; }
                if (!_regex.test($.trim(_val))) {
                    _msg = _self.ControlCfg.ColTitle + " : " + "为必填项！";
                    _isVerify = false;
                }
            }

            _regex = _self.ControlCfg.RegexContent;

            if (!!_regex && _isVerify) {

                if (Cmn.IsType(_regex, "string")) {
                    if (_regex[0] == "/") { _regex = _regex.substr(1, _regex.length - 2); }
                    _regex = new RegExp(_regex);
                }
                else {
                    console.error(_regex.toString() + "正则表达式类型不对！");

                    return true;
                }

                if (!_val) { _val = ""; }
                if (!_regex.test($.trim(_val))) {
                    _isVerify = false;
                    _msg = _self.ControlCfg.RegexErrorMsg;
                }

            }

            //验证通过
            if (_isVerify) {
                _self.ControlDom.find(_Control.Selector.CtlVerifyRight).show();
                _self.ControlDom.find(_Control.Selector.CtlVerifyError).hide();
                $(tipSelector).hide().html("");
                return true;
            }
            else {
                _self.ControlDom.find(_Control.Selector.CtlVerifyError).show();
                _self.ControlDom.find(_Control.Selector.CtlVerifyRight).hide();
                _self.ControlDom.find(_Control.Selector.CtlErrTipDesc).html(_msg);
                _self.ControlDom.find(_Control.Selector.CtlErrTipDesc).show();
                _self.ControlDom.find(_Control.Selector.CtlTipDesc).hide();

                $(tipSelector).show().html(_msg);

                errCallback && errCallback.call(_self, $.extend(new Cmn.ErrMsg(_msg), { control: _self }));
                return false;
            }

            _self.ControlDom.find(_Control.Selector.CtlVerifyContainer).show();
        }

        //获取控件的值
        _Self.GetValue = function () { return _Self.Val;  }

        //设置控件的值
        _Self.SetValue = function (value) {
            if (!!value) {
                //显示文件容器
                _Self.ControlDom.find(".cg-Ctl-FileViewContainer").show();
                //上传成功隐藏进度条容器
                _Self.ControlDom.find(".cg-Ctl-ProgressContainer").hide();
                //进度条重置
                _Self.ControlDom.find(".cg-Ctl-Progress").css({ width: "0%" });
                //隐藏上传框
                _Self.ControlDom.find(".cg-Ctl-SelectFileBtn").hide();
                //设置文件名称
                _Self.ControlDom.find(".cg-Ctl-FilePreview").html(_Self.Upload.GetFileName(value));
                //值
                _Self.Val = value;
            }
            else{
                //显示上传按钮
                _Self.ControlDom.find(".cg-Ctl-SelectFileBtn").show();
                //隐藏上传文件容器
                _Self.ControlDom.find(".cg-Ctl-FileViewContainer").hide();
                //进度条重置
                _Self.ControlDom.find(".cg-Ctl-Progress").css({ width: "0%" });
                //清空预览容器
                _Self.ControlDom.find(".cg-Ctl-FilePreview").empty();
                _Self.Val = "";
            }
        }

        //控件初始化
        _Self.Init = function () {
            _Self.SetValue("");
        }

        //设置是否可用
        this.SetEnabled = function (isEnabled) {
            /// <summary>设置是否可用</summary>
            /// <param name="isEnabled" type="bool">是否可用，true:可用；false:不可用</param>

            //保存当前Enabled状态
            _Enabled = isEnabled;
             
            if (_Enabled) {
                _Self.ControlDom.find(_Control.Selector.CtlContent).find("input").attr("disabled", false);
                //值不为空的时候要显示删除按钮
                if (_Self.Val != "") { _Self.ControlDom.find(".cg-Ctl-DelImgItemBtn").show(); }
            }
            else {
                _Self.ControlDom.find(_Control.Selector.CtlContent).find("input").attr("disabled", true);
                _Self.ControlDom.find(".cg-Ctl-DelImgItemBtn").hide();
            }
          
        }
    }

})();

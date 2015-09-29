/// <reference path="jquery-1.9.1.min.js" />
/// <reference path="Cmn.js" />
/// <reference path="CmnAjax.js" />
/// <reference path="CmnFuncExd.js" />
/// <reference path="animate/AnimateFrame.js" />
/// <reference path="animate/Scenes.js" />
/// <reference path="animate/ScenesSwitch.js" />
$(document).ready(function () {

    Cmn.Func.ImageLazyLoading("body", function (pro) {
        $("#counter").html(pro + "%");
    }, function () {
        $(".wrap-load").delay(500).fadeOut(800);
        if (typeof(LoadAnimate)!="undefined") {
            LoadAnimate && LoadAnimate();
        }
        //clearInterval(_TitleTs);
    });

    //二维码
    $(".joins-btn a").hover(function (e) {
        $(this).parent().next(".two-code").show();
        event.stopPropagation();
    },function(e){
        $(this).parent().next(".two-code").hide();
        event.stopPropagation();
    });

    var _TitleNum = 0;
    _TitleTs = setInterval(function () {
        $(".load-title img").fadeOut(800);
        $(".load-tip img").fadeOut(800);
        $(".load-tip img").eq(_TitleNum).stop(true).fadeIn(800);
        $(".load-title img").eq(_TitleNum).stop(true).fadeIn(800);
        _TitleNum++;
        //  console.log(_Num);
        if (_TitleNum >2) { _TitleNum =0}
    }, 1000);

    ////二维码浮层弹出
    $(".JsJoinBtn").click(function () {
        $(".JsPopFloat").show();
    });

    $(".JsCloseBtn").click(function () {
        $(".JsPopFloat").hide();
    });
});

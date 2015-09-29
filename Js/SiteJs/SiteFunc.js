
//公共方法库
(SiteFunc = new function () {
    /// <summary>公共方法库</summary>
    this.Share = function () {
        var _sharePic = "http://" + Cmn.Func.GetMainDomain(location.href) + "/images/ShareImg.jpg";
        var _shareOnlyUrl = "http://" + Cmn.Func.GetMainDomain(location.href) + "/";
        var _title = "测试分析内容" + _shareOnlyUrl;
        var _SinaShare = 'http://service.weibo.com/share/share.php?title=' + encodeURIComponent(_title) + '&url=' + _shareOnlyUrl + '&source=&appkey=&pic=' + _sharePic;
        $("#sinaShare").attr("href", _SinaShare);
        var _renrenShare = 'http://s.jiathis.com/?webid=renren&title=&summary=' + encodeURIComponent(_title) + '&url=' + _shareOnlyUrl + '&pic=' + _sharePic;
        $("#renrenShare").attr("href", _renrenShare);
        var _tenxunShare = "http://share.v.t.qq.com/index.php?c=share&a=index&title=" + encodeURIComponent(_title) + "&url=" + _shareOnlyUrl + "&site=&pic=" + _sharePic;
        $("#tenxunShare").attr("href", _tenxunShare);
        var _qzoneShare = "http://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey?url="+encodeURIComponent(_shareOnlyUrl) + "&title=" + "&pics=" + encodeURIComponent(_sharePic) + "&summary="+encodeURIComponent(_title);
        $("#qzoneShare").attr("href", _qzoneShare);
        var _doubanShare = "http://s.jiathis.com/?webid=douban&summary="+"&title="+ encodeURIComponent(_title) +"&pic="+ encodeURIComponent(_sharePic) + "&url=" + encodeURIComponent(_shareOnlyUrl);
        $("#doubanShare").attr("href", _doubanShare);
    }
});


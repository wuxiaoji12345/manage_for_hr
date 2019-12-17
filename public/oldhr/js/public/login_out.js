$(function () {
    // 退出登录功能
    $(".b-login").click(function(){
        window.location.replace("/index.html");
        window.sessionStorage.clear();
    })

});
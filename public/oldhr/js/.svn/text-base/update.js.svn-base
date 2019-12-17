$(function () {

    // 判断是否登录
    if (window.sessionStorage.getItem("is_login") != 1) {

        window.location.href = "/index.html";
        return false;


    }


    // 调用左边菜单栏
    admin()


    //点击修改密码刷新整个页面
    $(".u-pwd").click(function () {
        window.location.reload()
    })


    var old_password = window.localStorage.getItem('password')
    var project_code = window.localStorage.getItem('mask');
    var hr_code = window.localStorage.getItem('id');
    var token = window.localStorage.getItem('taken');

    $("#oldPWD").focus(function () {
        $("#opwd").hide()
        $("#npwd").hide()
        $("#spwd").hide()
    })

    $("#newPWD").focus(function () {
        $("#opwd").hide()
        $("#npwd").hide()
        $("#spwd").hide()

    })
    $("#confirmPWD").focus(function () {
        $("#opwd").hide()
        $("#npwd").hide()
        $("#spwd").hide()
    })

    $('#cancel').click(function () {

        $("#oldPWD").val("")
        $("#newPWD").val("")
        $("#confirmPWD").val("")
        $("#opwd").hide()
        $("#npwd").hide()
        $("#spwd").hide()
        window.history.go(-1)

    });
    $('#confirm').click(function () {
        var oldPWD = document.getElementById('oldPWD').value;
        var newPWD = document.getElementById('newPWD').value;
        var confirmPWD = document.getElementById('confirmPWD').value;

        if (oldPWD == "") {

            $("#opwd").show().text("原密码不能为空,请输入")

            return
        }


        if (oldPWD != old_password) {
            $("#opwd").show().text("输入的密码与原密码不符")

            return
        }

        if (newPWD.length == "") {
            $("#npwd").show().text("新密码不能为空,请输入")

            return
        }
        if (newPWD == oldPWD) {
            $("#npwd").show().text("新密码与原密码重复")
            return;
        }
        var reg = /^[a-zA-Z\d]{6,16}$/

        if (!reg.test(newPWD)) {
            $("#npwd").show().text("输入的密码格式不正确")
            return
        }
        if (confirmPWD == "") {
            $("#spwd").show().text("请再次输入新密码")
            return
        }
        if (confirmPWD != newPWD) {
            $("#spwd").show().text("你输入的密码与新密码不一致")
            return
        }

        $.ajax({
            type: 'post',
            url: getUrl('update'),
            data: {
                project_code: project_code,
                hr_code: hr_code,
                token: token,
                old_password: oldPWD,
                new_password: newPWD
            },
            dataType: "json",
            success: function (res) {
                //  console.log(res)
                //  console.log(res.status)
                if (res.status == 1) {
                    // console.log(2)
                    window.location.href = "/index.html";
                    window.localStorage.clear()
                    window.sessionStorage.clear()
                }


            },
            error: function (XMLHttpRequest) {
                //请求过程中发生了错误，记录下错误的代码 例如 : 404 => page not found
                alert("请求遇到了错误 , 错误代码 : " + XMLHttpRequest.status);
            }
        })
    });


});

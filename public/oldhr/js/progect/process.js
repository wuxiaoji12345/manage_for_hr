$(document).ready(function () {

    // 判断是否登录
    if (window.sessionStorage.getItem("is_login") != 1) {

        window.location.href = "/index.html";
        return false;

    }



    //调用左边菜单栏   
    admin()

    // 调用请求套餐信息功能
    getCan()

    var project_code = window.localStorage.getItem('mask');
    var token = window.localStorage.getItem('taken');

    // 好医通项目标识
    var gs = window.localStorage.getItem('e_name');
    // console.log(gs)
    $(".yu-date").find("span").text(gs)

    // 页面渲染
    var str = '';
    $.ajax({
        url: getUrl('sum'),
        method: "GET",
        dataType: "json",
        data: {
            // project_code: project_code,
            // token: token
        },
        success: function (res) {
            // console.log(res)
            if (res.status == 1) {
                var result = res.data;
                // console.log(result);       

                var sum1 = 0;
                var sum2 = 0;
                var sum3 = 0;
                var sum4 = 0;
                var str3 = '';
                $.each(result, function (key, val) {

                    sum1 += parseFloat(val.append_total_num);
                    sum2 += Number(val.append_arrive_num);
                    sum3 += Number(val.append_report_num);
                    sum4 = sum2 / sum1
                    str += '<tr align="center" class="master-' + key + '">' +
                        '<td id="' + val.area_code + '">' + val.area_full_name + '</td>' +
                        '<td class="set-meal">套餐总计<i class="set-add"></i><i class="set-sub"></i></td>' +
                        '<td>' + val.append_total_num + '</td>' +
                        '<td>' + Number(val.append_arrive_num) + '</td>' +
                        '<td>' + Number(val.append_report_num) + '</td>' +
                        '<td>' + val.check_rate + '</td>' +
                        '</tr>'
                    str3 += '<option  id="' + val.area_code + '">' + val.area_full_name + '</option>'
                    // console.log(str3)


                });


                var str2 = '';
                str2 += '<td>总计</td>' +
                    '<td class="set-all">套餐总计<i class="set-add"></i><i class="set-sub"></i></td>' +
                    '<td>' + sum1 + '</td>' +
                    '<td>' + sum2 + '</td>' +
                    '<td>' + sum3 + '</td>' +
                    '<td>' + (sum4 * 100).toFixed(2) + '%' + '</td>';

                $(".tbint").html(str);
                $(".ss").append(str2);
                $(".yu-city").append(str3);
            } else {

                var str9 = '';
                str9 += '<td colspan="6" align="center">暂无数据</td>'
                $(".ss").append(str9);

            }


        },
        error: function (XMLHttpRequest) {

            //请求过程中发生了错误，记录下错误的代码 例如 : 404 => page not found
            alert("请求遇到了错误 , 错误代码 : " + XMLHttpRequest.status);
        }
    })





    //  点击查询功能
    function serach(areap_code, p_code, a_type, l_time, r_time) {

        $.ajax({
            url: getUrl('sum_search'),
            method: "GET",
            dataType: "json",
            data: {
                // project_code:project_code,
                // token:token,
                area: areap_code,
                p_code: p_code,
                a_type: a_type,
                l_time: l_time,
                r_time: r_time
            },
            success: function (res) {
                // console.log(res)
                if (res.status == 1) {
                    var sum1 = 0;
                    var sum2 = 0;
                    var sum3 = 0;
                    var sum4 = 0;
                    // var str3 = '';

                    var result = res.data;
                    var strc = '';
                    $.each(result, function (key, val) {
                        sum1 += parseFloat(val.append_total_num);
                        sum2 += Number(val.append_arrive_num);
                        sum3 += Number(val.append_report_num);

                        sum4 = ((sum2 / sum1) * 100).toFixed(2)
                        strc += '<tr style="text-align:center;">' +
                            '<td>' + val.area_full_name + '</td>' +
                            '<td>' + val.p_name + '</td>' +
                            '<td>' + val.append_total_num + '</td>' +
                            '<td>' + Number(val.append_arrive_num) + '</td>' +
                            '<td>' + Number(val.append_report_num) + '</td>' +
                            '<td>' + val.check_rate + '</td>' +
                            '</tr>';
                    });

                    if (sum4.slice(sum4.indexOf(".") + 1) == 0 || sum4.slice(sum4.indexOf(".") + 1) == 00) {
                        // console.log(sum4.slice(sum4.indexOf(".") + 1))
                        sum4 = parseInt(sum4)
                    }
                    var str5 = '';
                    str5 += '<td>总计</td>' +
                        '<td id="tc">套餐总计<i class="set-add"></i><i class="set-sub"></i></td>' +
                        '<td>' + sum1 + '</td>' +
                        '<td>' + sum2 + '</td>' +
                        '<td>' + sum3 + '</td>' +
                        '<td>' + sum4 + '%' + '</td>';

                    $(".tbint").html(strc);
                    $(".tbint").hide();
                    $(".ss").html(str5);

                } else {

                    $(".ss").html(" ")
                    $(".tbint").html(" ")
                    var str10 = '';
                    str10 += '<td colspan="6" align="center">暂无数据</td>'
                    $(".ss").append(str10);

                }

            },
            error: function (XMLHttpRequest) {

                //请求过程中发生了错误，记录下错误的代码 例如 : 404 => page not found
                alert("请求遇到了错误 , 错误代码 : " + XMLHttpRequest.status);
            }
        })

    }


    $('body').on('click', '#tc', function () {

        if ($(this).hasClass("flag")) {
            $(this).removeClass("flag");
            $(this).children(".set-add").css("display", "inline-block");
            $(this).children(".set-sub").hide();
            $(".tbint").hide();
        } else {
            $(this).addClass("flag");
            $(this).children(".set-add").hide();
            $(this).children(".set-sub").css("display", "inline-block");
            $(".tbint").show().css("background-color", "#ddd");
        }
        //  console.log(4)
    })



    //  按条件查询
    $("#serach-result").on("click", function () {

        //   alert(token)
        var opt1 = $(".yu-city").children("option:selected").attr("id")
        //    console.log(opt1)
        var opt2 = $(".tjian-can").children("option:selected").attr("id")

        var opt3 = $(".yu-type").children("option:selected").attr("id")

        var opt4 = $(".tjian-start #d12").val()

        var opt5 = $(".tjian-start #d13").val()


        if (opt1 == "0" && opt2 == "0" && opt3 == "0" && opt4 == "" && opt5 == "") {

            window.location.reload();
            //  return;

        } else {
            var areap_code = $(".yu-city").children("option:selected").attr("id");
            // console.log(typeof areap_code)
            var p_code = $(".tjian-can").children("option:selected").attr("id");

            var a_type = $(".yu-type").children("option:selected").attr("id");

            if ($(".yu-type").children("option:selected").text() == "员工预约") {
                a_type = 1
            }
            if ($(".yu-type").children("option:selected").text() == "家属预约") {
                a_type = 2
            }

            // console.log(a_type)
            if (areap_code == 0) {
                areap_code = ''
            }

            if (p_code == 0) {
                p_code = ''
            }

            if (a_type == 0) {
                a_type = ''
            }
            // console.log(areap_code)
            // console.log(p_code)
            // console.log(a_type)
            var l_time = $(".tjian-start #d12").val();
            // console.log(l_time)
            var r_time = $(".tjian-start #d13").val();
            // console.log(r_time)
            serach(areap_code, p_code, a_type, l_time, r_time)

        }
    })




    // 点击全国套餐总计功能
    function getAll(project_code, token, _this, master) {
        $.ajax({
            url: getUrl('sum_all'),
            methods: "GET",
            dataType: "json",
            data: {},
            success: function (res) {
                // console.log(res)
                if (res.status == 1) {
                    var result = res.data;
                    var str12 = ''
                    $.each(result, function (key, val) {
                        str12 += '<tr style="text-align:center;" bgcolor="#ddd" class=' + master + ' >' +
                            '<td>' + '全国' + '</td>' +
                            '<td>' + val.p_name + '</td>' +
                            '<td>' + val.append_total_num + '</td>' +
                            '<td>' + Number(val.append_arrive_num) + '</td>' +
                            '<td>' + Number(val.append_report_num) + '</td>' +
                            '<td>' + val.check_rate + '</td>' +
                            '</tr>';
                    })

                    _this.parent().after(str12);
                }
            }
        })
    }

    // 点击全国套餐总计获取数据展示

    $("body").on("click", ".set-all", function () {
        var areap_code = $(this).prev().attr("id");
        var master = $(this).parent().attr("class");
        var _this = $(this);

        if ($(this).hasClass("flag")) {
            var slave = $(this).parent().siblings();

            for (i = 0; i < slave.length; i++) {
                if (slave.eq(i).hasClass(master)) {
                    slave.eq(i).remove();
                }
            }

            $(this).removeClass("flag");
            $(this).children(".set-add").css("display", "inline-block");
            $(this).children(".set-sub").hide();

        } else {
            getAll(project_code, token, _this, master)
            $(this).addClass("flag");
            $(this).children(".set-add").hide();
            $(this).children(".set-sub").css("display", "inline-block");

        }


    })




    // 点击各个城市套餐总计功能
    function getSum(project_code, token, areap_code, _this, master) {
        $.ajax({
            url: getUrl('sum_search'),
            method: "GET",
            dataType: "json",
            data: {
                // project_code:project_code,
                // token:token,
                area: areap_code
            },
            success: function (res) {

                //   console.log(res)
                if (res.status == 1) {
                    var result = res.data;
                    //  console.log(result)
                    var strd = '';
                    $.each(result, function (key, val) {
                        strd += '<tr style="text-align:center;" bgcolor="#ddd" class=' + master + ' >' +
                            '<td>' + val.area_full_name + '</td>' +
                            '<td>' + val.p_name + '</td>' +
                            '<td>' + val.append_total_num + '</td>' +
                            '<td>' + Number(val.append_arrive_num) + '</td>' +
                            '<td>' + Number(val.append_report_num) + '</td>' +
                            '<td>' + val.check_rate + '</td>' +
                            '</tr>';
                    });

                    _this.parent().after(strd);

                }


                // if (res.status == 0) {
                //     alert(res.info)
                // }
            },

            error: function (XMLHttpRequest) {

                //请求过程中发生了错误，记录下错误的代码 例如 : 404 => page not found
                alert("请求遇到了错误 , 错误代码 : " + XMLHttpRequest.status);
            }

        })


    }






    // 点击各个城市套餐总计获取数据展示

    $("body").on("click", ".set-meal", function () {


        var areap_code = $(this).prev().attr("id");
        var master = $(this).parent().attr("class");
        var _this = $(this);

        if ($(this).hasClass("flag")) {
            var slave = $(this).parent().siblings();
            // console.log(slave) 
            for (i = 0; i < slave.length; i++) {
                if (slave.eq(i).hasClass(master)) {
                    slave.eq(i).remove();
                }
            }

            $(this).removeClass("flag");
            $(this).children(".set-add").css("display", "inline-block");
            $(this).children(".set-sub").hide();

        } else {
            getSum(project_code, token, areap_code, _this, master)
            $(this).addClass("flag");
            $(this).children(".set-add").hide();
            $(this).children(".set-sub").css("display", "inline-block");

        }



    })




    // 获取请求套餐信息功能
    function getCan() {
        var str4 = '';
        $.ajax({
            url: getUrl('project_package'),
            methods: "GET",
            dataType: "json",
            data: {
                // project_code: project_code,
                // token: token
            },
            success: function (res) {
                if (res.status == 1) {
                    var result = res.data;
                    // console.log(result);

                    $.each(result, function (key, val) {
                        str4 += '<option id="' + val.p_code + '">' + val.p_name + '</option>'
                        // console.log(str4) 
                    })
                    $(".tjian-can").append(str4)

                } else {

                }





            },
            error: function (XMLHttpRequest) {

                //请求过程中发生了错误，记录下错误的代码 例如 : 404 => page not found
                alert("请求遇到了错误 , 错误代码 : " + XMLHttpRequest.status);
            }
        })
    }


})
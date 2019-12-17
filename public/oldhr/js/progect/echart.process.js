$(function () {


    // ajax 请求柱状图数据的方法
    function getData() {
        //通过Ajax获取数据
        var myChart = echarts.init(document.getElementById("l-yu-date"));

        option = {
            title: {
                text: '前10名城市预约情况'
            },
            tooltip: {
                trigger: 'axis',
                axisPointer: {            // 坐标轴指示器，坐标轴触发有效
                    type: 'shadow'        // 默认为直线，可选为：'line' | 'shadow'
                },
                formatter: '{b}<br /><span style="display:none;margin-right:5px;border-radius:10px;width:9px;height:9px;background-color:rgba(0,0,0,0);"></span>{a0}:{c0}',



            },
            legend: {

                // orient:'vertical',
                // left:"left",
                // borderWidth:1,
                data: ["已预约人数"],
                x: 'right'

            },
            grid: {
                left: '8%',
                right: '100',
                bottom: '5%',
                containLabel: true
            },
            xAxis: [{
                type: 'category',
                "axisLabel": {
                    interval: 0
                },
                axisLabel: {
                    interval: 0,
                    rotate: 50
                },

                data: [],
                axisTick: {
                    alignWithLabel: true
                }
            }],

            yAxis: [{
                type: 'value'
            }],
            series: [{
                name: '已预约人数',
                data: [],
                type: 'bar',
                itemStyle: {
                    normal: {

                        color: '#01b2a9',  //柱状图的颜色
                    }
                },
                barWidth: "50%",
            }]
        };


        //   myChart.setOption(option);

        $.ajax({

            type: "GET",

            async: false, //同步执行

            url: '/process/process/sum',

            dataType: "json", //返回数据形式为json

            success: function (result) {
                if (result.code == 1 && result.data.length != 0) {
                    var result = result.data;
                    // var arr = new Array();
                    // console.log(result)
                    //将返回的category和series对象赋值给options对象内的category和series

                    //因为xAxis是一个数组 这里需要是xAxis[i]的形式
                    if (result.length < 10) {
                        $.each(result, function (kay, val) {
                            //截取字符串
                            var index = val.area_full_name.indexOf('市')
                            var r2 = val.area_full_name.slice(0, index)
                            option.xAxis[0].data.push(r2);
                            option.series[0].data.push(val.append_total_num)
                        })

                    } else {
                        $.each(result, function (kay, val) {
                            //截取字符串
                            var index = val.area_full_name.indexOf('市')
                            var r2 = val.area_full_name.slice(0, index)
                            option.xAxis[0].data.push(r2);
                            option.series[0].data.push(val.append_total_num)

                        })

                        option.xAxis[0].data = option.xAxis[0].data.slice(0, 10)
                        option.series[0].data = option.series[0].data.slice(0, 10)

                    }



                    myChart.hideLoading();

                    myChart.setOption(option);
                } else {

                    option = {
                        title: {
                            text: '前10名城市预约情况'
                        },
                        tooltip: {
                            trigger: 'axis',
                            axisPointer: {            // 坐标轴指示器，坐标轴触发有效
                                type: 'shadow'        // 默认为直线，可选为：'line' | 'shadow'
                            },
                            formatter: '{b}<br /><span style="display:none;margin-right:5px;border-radius:10px;width:9px;height:9px;background-color:rgba(0,0,0,0);"></span>{a0}:{c0}',

                        },
                        legend: {

                            // orient:'vertical',
                            // left:"left",
                            // borderWidth:1,
                            // data: ["已预约人数"],
                            // x: 'right'

                        },
                        grid: {
                            left: '8%',
                            right: '100',
                            bottom: '5%',
                            containLabel: true
                        },
                        xAxis: [{
                            type: 'category',
                            "axisLabel": {
                                interval: 0
                            },
                            axisLabel: {
                                interval: 0,
                                rotate: 50
                            },

                            data: [],
                            axisTick: {
                                alignWithLabel: true
                            }
                        }],

                        yAxis: [{
                            type: 'value',
                            minInterval: 50
                        }],
                        series: [{
                            name: '已预约人数',
                            data: [0],
                            type: 'bar',
                            itemStyle: {
                                normal: {
                                    color: '#01b2a9',  //柱状图的颜色
                                }
                            },
                            barWidth: "50%",
                        }]
                    };

                    myChart.setOption(option);

                }




            },

            error: function (errorMsg) {

                alert("不好意思，图表请求数据失败啦!");

            }

        });
    }


    // 调用柱状图数据方法
    getData()

    // 到检率的方法

    function getArrive() {
        var num1 = 0;
        var num2 = 0;
        var num3 = 0;
        var num4 = 0

        var myCharte = echarts.init(document.getElementById("r-yu-date"));
        //通过Ajax获取数据

        $.ajax({

            type: "GET",

            async: false, //同步执行

            url:'/process/process/reservation',

            dataType: "json", //返回数据形式为json
            data: {
            },
            success: function (res) {
                if (res.code == 1 && res.data.length != 0) {

                    var result = res.data;


                    //请求成功时执行该函数内容，result即为服务器返回的json对象

                    var n1 = 0;
                    var n2 = 0;
                    var n3 = 0;

                    // $.each(result, function (key, val) {

                    //     n1 += Number(val.append_total_num);
                    //     n2 += Number(val.append_arrive_num)
                    // })
                    n1 = Number(result.reservedNum)   //员工已预约人数
                    n2 = Number(result.checkNum)      //员工已到检人数
                    n3 = result.unReservedNum

                    num1 = n1
                    num2 = n2
                    num3 = n1 - n2
                    num4 = n3


                    optione = {
                        title: {
                            text: '到检率情况',

                            x: 'left'
                        },
                        tooltip: {
                            trigger: 'item',
                            formatter: "{a} <br/>{b} : {c} ({d}%)"
                        },
                        legend: {
                            orient: 'vertical',
                            x: 'right',
                            data: ['已到检人数', '已预约未到检人数','未预约人数']
                        },
                        color: ['#8db143', '#f99f97','#EF5D50'],
                        series: [
                            {
                                name: '到检率情况',
                                type: 'pie',
                                radius: '55%',
                                center: ['50%', '60%'],
                                data: [

                                    { value: num2, name: '已到检人数' },
                                    { value: num3, name: '已预约未到检人数' },
                                    { value: num4, name: '未预约人数'}

                                ],
                                itemStyle: {
                                    emphasis: {
                                        shadowBlur: 10,
                                        shadowOffsetX: 0,
                                        shadowColor: 'rgba(0, 0, 0, 0.5)'
                                    }
                                }
                            }
                        ]
                    };


                    //  myCharte.setOption(optione);
                    //  myCharte.showLoading();    //数据加载完之前先显示一段简单的loading动画



                    myCharte.setOption(optione);

                } else {

                    optione = {
                        title: {
                            text: '到检率情况',

                            x: 'left'
                        },
                        tooltip: {
                            trigger: 'item',
                            formatter: "{a} <br/>{b} : {c} ({d}%)"
                        },
                        legend: {
                            orient: 'vertical',
                            x: 'right',
                            data: ['已预约人数']
                        },
                        color: ["#6ccac9"],
                        series: [
                            {
                                name: '到检率情况',
                                type: 'pie',
                                radius: '55%',
                                center: ['50%', '60%'],
                                data: [


                                    { value: 0, name: '已预约人数' }

                                ],
                                itemStyle: {
                                    emphasis: {
                                        shadowBlur: 10,
                                        shadowOffsetX: 0,
                                        shadowColor: 'rgba(0, 0, 0, 0.5)'
                                    }
                                }
                            }
                        ]
                    };


                    //  myCharte.setOption(optione);
                    // myCharte.showLoading();    //数据加载完之前先显示一段简单的loading动画



                    myCharte.setOption(optione);






                }


            },
            error: function (errorMsg) {

                alert("不好意思，图表请求数据失败啦!");

            }


        });


    }

    getArrive()



})
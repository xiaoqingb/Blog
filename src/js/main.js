import $ from "jquery";
// import layui from 'layui-src';
// 进入页面获取昵称
let load=()=>{
    // 下面两个函数是用来解析时间戳的
    let timestampToTime=(timestamp)=>{
        let change=(t)=>{
            if (t < 10) {
                return "0" + t;
            } else {
                return t;
            }
        }
        let date = new Date(timestamp * 1000); //时间戳为10位需*1000，时间戳为13位的话不需乘1000
        let Y = date.getFullYear() + '-';
        let M = (date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1) + '-';
        let D = change(date.getDate()) + ' ';
        let h = change(date.getHours()) + ':';
        let m = change(date.getMinutes()) + ':';
        let s = change(date.getSeconds());
        return Y + M + D + h + m + s;

    }

    //绑定了导航栏和搜索栏监听事件，传入关键词,后台进行模糊搜索
    let search=(keyword)=>{
        $.ajax({
            url: "/blog/public/index/Content/get_article_list",
            type: "get",
            data: {
                'keyword': keyword,
            },
            success: (response) => {
                response = JSON.parse(response);
                if(response.code!=="0000")
                {
                    layer.msg('呜呜呜，博客里找不到客官想要的内容!');
                    return;
                }
                let time;
                $('#left-container').empty();
                $('#left-container').append(`<div id="article-list"></div>`);
                for (let i = 0; i < response.msg[0].length; i++) {
                    time = timestampToTime(response.msg[7][i])
                    $('#article-list').append(`<div class="article-list-container">
                                    <div class="article-item">
                                        <img class="article-pic" src="${response.msg[2][i]}" alt="">
                                        <div class="article-msg" >
                                            <p class="article-title article-choice" >${response.msg[0][i]}</p>
                                            <p class="name-time-clickNum">
                                                <a href="">
                                                    <img class="head" src="https://ss1.bdstatic.com/70cFuXSh_Q1YnxGkpoWK1HF6hhy/it/u=2884107401,3797902000&fm=26&gp=0.jpg" alt="" srcset="">
                                                </a>
                                                <span class="account-name-time">${response.msg[6][i]} ${time}
                                                </span>
                                                <span class="discuss-read">
                                                    <i class="layui-icon layui-icon-dialogue">${response.msg[4][i]}</i>
                                                    <i class="layui-icon layui-icon-read">99</i>
                                                </span>
                                            </p>
                                            <p class="article-content">${response.msg[1][i]}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>`)


                }
                bindArticleListener();
            }
        });
    }

    // 绑定搜索按钮
    $('#search-btn').click(()=>{
        search($("#search-content").val());
    })
    // 绑定打赏按钮
    $('#reward-btn').click(()=>{
        layer.open({
            type: 1,
            title: false,
            closeBtn: 0,
            area: ['auto', 'auto'],
            skin: 'layui-layer-nobg', //没有背景色
            shadeClose: true,
            content: '<img  src="https://i.loli.net/2019/09/28/KTjfQbFnRZzioMc.png">'
        });
    })
    // 绑定登出按钮
    $('#logout-btn').click(function() {
        $.ajax({
            url:'/blog/public/index/member/logout',
            type:'post',
            success: ()=> {
                location.href="login"
            }
        })
    })
    // 绑定投稿按钮
    $('#contribute-btn').click(function() {
        location.href="submit"
    })

    // 绑定文章的标题跳转
    function bindArticleListener() {
        $(".article-choice").click(function() {
            location.href="article?name="+escape($(this).html());
        })
    }
    bindArticleListener();

    function ajaxDemo() {
        // 加载导航栏
        return $.ajax({
            url: "/blog/public/index/Nav/get_nav",
            type: "get",
            success: (response) => {
                response = JSON.parse(response);
                // i是下拉列表的表头
                for (let i in response.msg) {
                    if (isNaN(i)) {
                        if (response.msg[i].length === 0) {
                            // 如果没有多余选项就只生成单独一个li
                            $("#nav-header-left").append(`<li class="layui-nav-item"><a href="">${i}</a></li>`);
                        } else {
                            // 有多余选项则生成下拉列表
                            let xx = "";
                            // 这里我是把下拉列表的选项都合并在一起了，方便下面字符串连接
                            for (let j = 0; j < response.msg[i].length; j++) {
                                xx += `<dd><a class="article-name-group" >${response.msg[i][j]}</a></dd>`;
                            }
                            $("#nav-header-left").append(` <li class="layui-nav-item">
                                                <a >${i}<span class="layui-nav-more"></span></a>
                                                <dl class="layui-nav-child "  >
                                                    ${xx}
                                                </dl>
                                            </li> `);
                        }
                    }
                }
                $(".article-name-group").click(function() {
                    search($(this).html())
                })
            }
        });
    }
    // 加载轮播图
    ajaxDemo().then((response) => {
        $.ajax({
            url: "/blog/public/index/Content/get_carousel_pic",
            type: "get",
            data:{
                picNum:5
            },
            success: (response) => {
                response = JSON.parse(response);
                for (let i = 0; i < response.msg.length; i++) {
                    $("#carousel-frame").append(`<div>
                                    <img class="carousel-pic" src="${response.msg[i]['pic']}" data-title="${response.msg[i]['title']}" alt="">
                                </div>`);
                }
                $('.carousel-pic').click(function() {
                    location.href='article?name='+escape($(this).data('title'));
                })
            }
        });
        //加载会员列表
    }).then((response) => {
        $.ajax({
            url: "/blog/public/index/Member/get_Member_list",
            type: "get",
            success: (response) => {
                response = JSON.parse(response);
                for (let i = 0; i < response.msg[0].length; i++) {
                    $('#user-list').append(`<li>
                                    <img class="account-pic" src="${response.msg[1][i]}" alt="">
                                    <p class="member-name">${response.msg[0][i]}</p>
                                </li>`);
                }
                $(".member-name").click(function() {
                    let name = escape($(this).html())
                    location.href="member?name="+name;
                })

            }
        });
        // 加载文章列表
    }).then((response) => {
        $.ajax({
            url: "/blog/public/index/Content/get_article_list",
            type: "get",
            data:{
                num:4
            },
            success: (response) => {
                response = JSON.parse(response);
                let time;
                for (let i = 0; i < response.msg[0].length; i++) {
                    time = timestampToTime(response.msg[7][i])
                    $('#article-list').append(`<div class="article-list-container">
                                                            <div class="article-item">
                                                                <img class="article-pic" src="${response.msg[2][i]}" alt="">
                                                                <div class="article-msg" >
                                                                    <p class="article-title article-choice">${response.msg[0][i]}</p>
                                                                    <p class="name-time-clickNum">
                                                                        <a href="">
                                                                            <img class="head" src="https://ss1.bdstatic.com/70cFuXSh_Q1YnxGkpoWK1HF6hhy/it/u=2884107401,3797902000&fm=26&gp=0.jpg" alt="" srcset="">
                                                                        </a>
                                                                        <span class="account-name-time">${response.msg[6][i]} ${time}
                                                                        </span>
                                                                        <span class="discuss-read">
                                                                            <i class="layui-icon layui-icon-dialogue">${response.msg[4][i]}</i>
                                                                            <i class="layui-icon layui-icon-read">${response.msg[3][i]}</i>
                                                                        </span>
                                                                    </p>
                                                                    <p class="article-content">${response.msg[1][i]}</p>
                                                                 </div>
                                                                </div>
                                                            </div>
                                                    </div>`)
                }
                bindArticleListener();
            }
        });
        // 获取置顶
    }).then((response) => {
        $.ajax({
            url: "/blog/public/index/Content/get_settop_commmend",
            type: "get",
            success: (response) => {
                response = JSON.parse(response);
                let time;
                for (let i = 0; i < response.msg.length; i++) {
                    $('#recommend-settop').append(` <li><img class="pic-settop"
                             src="${response.msg[i]['pic']}"
                             alt="" srcset="" data-title="${response.msg[i]['title']}"></li>`)
                }
                $('.pic-settop').click(function() {
                    location.href="article?name="+escape($(this).data('title'));

                });
            }
        });
        // 获取会员数，回复数，文章数
    }).then((response) => {
        $.ajax({
            url: "/blog/public/index/Content/get_article_menber_reply_count",
            type: "get",
            success: (response) => {
                response = JSON.parse(response);
                $("#member-count").append(response.msg[1]);
                $("#article-count").append(response.msg[0]);
                $("#reply-count").append(response.msg[2]);

                return response;
            }
        });
        // 获取热门推荐
    }).then((response) => {
        $.ajax({
            url: "/blog/public/index/Content/get_hot_command",
            type: "get",
            success: (response) => {
                response = JSON.parse(response);
                let time;
                for (let i=0;i<response.msg.length;i++){
                    time=timestampToTime(response.msg[i]['time'])
                    $('.recommend-container').append(`<div class="recommend-item">
                                                    <a class="hot-article" >${response.msg[i]['title']}</a>
                                                    <div>${time}</div>
                                                </div>`)
                }
                $('.hot-article').click(function() {
                    location.href="article?name="+escape($(this).html());
                })
                layui.use(['layer', 'form', 'carousel', 'element', 'jquery'], function () {
                    let layer = layui.layer
                        , element = layui.element
                        , carousel = layui.carousel
                        , $ = layui.$;
                    carousel.render({
                        interval: '2000',
                        elem: '#carousel'
                        , width: '100%' //设置容器宽度
                        , arrow: 'hover' //始终显示箭头
                        , anim: 'fade' //切换动画方式
                    });
                });
                return response;
            }
        });

    });

}
$(window).on("load",function(){
    $.ajax({
        url: "/blog/public/index/member/getName",
        type: "get",
        success:  (response)=>{
            response = JSON.parse(response);
            // 获取失败则跳转dao登录页面
            if (response.code!== "0000") {
                location.href = "login.html";
                return;
            }
            // 获取成功则输出昵称
            $("#user-name").html(response.msg);
            load();
        }
    });
})

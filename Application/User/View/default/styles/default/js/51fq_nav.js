    
   // 商品分类
    $("#qfq-aside-nav").hover(function(){
        $(this).find(".qfq-aside-navbody").stop().filter(':not(:animated)').slideToggle();
    });

    $(".qfq-category-nav li").hover(function(){
        $(this).children('h4').addClass("on");
        $(this).children('div').show();
    },function(){
        $(this).children('h4').removeClass("on");
        $(this).children('div').hide();
    });

    // 头部二维码
    $("#qfq-mobile").hover(function(){
        $(".qfq-mobile-pic").stop().slideToggle();
    });

    // 关闭头部二维码
    $(".qufenqi-search-right i").click(function(){
        $(".qufenqi-search-right").hide();
    });
	//右侧栏二维码
	$("#app").hover(function(){
			$(".code_right").stop().toggle();
		})
    // 返回顶部
    $(function(){
        $(".fix-top").on("click",function(){
            $('html,body').animate({'scrollTop':0},400); //滚回顶部的时间，越小滚的速度越快~
        });
        if ($(".qfq-detail-record").length > 0) {
            $.get("/get_history_records", function(data){
                $(".qfq-detail-record").empty();
                var records_string='';
                for(var index in data){
                    records_string += '<li>' +
                    '<a href="'+data[index].link+'"><img alt="'+data[index].name+'" title="'+data[index].name+'" src="'+data[index].image+'@0e_60w_60h_0c_1i_1o_95Q_1x.jpg"></a>' +
                    '<p><a title="'+data[index].name+'" href="'+data[index].link+'">'+data[index].name+'</a></p>' +
                    '</li>';
                }
                $(".qfq-detail-record").append(records_string);
            });
        }
    });

    // 登陆用户
    (function(){
        var name = 'userInfo';
        var arr = document.cookie.match(new RegExp("(^| )"+name+"=([^;]*)(;|$)"));
        if (!arr) {
            return $('#clientGuestInfo').css('display', '');;
        }
        var userInfo = decodeURIComponent(arr[2]).split('|');
        if (typeof userInfo[1] !== 'undefined' && userInfo[1].length > 0) {
            $('#clientUserInfo').css('display', '');
            $('#clientUserInfo .username').html(userInfo[1]);
        } else {
            $('#clientGuestInfo').css('display', '');;
        }
    })();

    // $(".qfq-aside-nav ul.qfq-category-nav li:last").css({"height":"26px","border":"none"});
    // $(".qfq-aside-nav ul.qfq-category-nav li:last").find("p").hide();

    // 客服QQ
    // var initQQ = false;
    // $('#BizQQ').add('#BizQQ2').click(function(){
    //     if (initQQ == true) {
    //         return true;
    //     }
    //     var layerId = $.layer({
    //         closeBtn: false,
    //         shadeClose: true,
    //         title: false,
    //         time: 3,
    //         shade: [0.1, '#000'],
    //         dialog: {msg: '正在连接客服QQ，请稍候。如果长时间无反应请重试。', type:1},
    //     });
    //     var thisObj = this;
    //     $.getScript('http://wpa.b.qq.com/cgi/wpa.php').done(function(){
    //         initQQ = true;
    //         BizQQWPA.addCustom([{
    //             aty: '0', //指定工号类型，0为自动分流，1为指定工号，2为指定分组
    //             a: '0', //指定接入者 aty=0时无效
    //             nameAccount: '4006989197', //营销qq号码
    //             selector: 'BizQQ' //要成为wpa元素的id
    //         },{
    //             aty: '0',
    //             a: '0',
    //             nameAccount: '4000990707',
    //             selector: 'BizQQ2'
    //           }
    //         ]);
    //         var intervalId = setInterval(function(){
    //             clearInterval(intervalId);
    //             thisObj.click();
    //         }, 3000);
    //     }).fail(function(){
    //     });
    // });

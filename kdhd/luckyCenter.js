
/*
 * @content:红包中心页面逻辑
 * @Date:2016-04-26
 * @author:Sure
 */

// var get_lucky = function (){
  var use_code = function (){
      $(".use-code").tap(function(){
      var get_code = $(".get_code").attr("value");
      // console.log(get_code);
      if (get_code == "") {
        alert("抱歉，您还未输入兑换码!");
      } else{
        $.ajax({
          type:'POST',
          url:'http://bd.koudaigongshe.com/mobile/coupon/checkCode',
          data:{"coupon":$(".get_code").attr("value")},         
          dataType:'json',
          success:function (msg){
            // console.log('请求兑换');
            if (msg == 1) {
                alert("恭喜你，兑换成功！");
                // $("#lucky-wrap").remove();
                var tpls = '<div id="lucky-wrap">' +
                            '<div id="append-data"></div>' +
                            '<h3 class="click-more">点击加载更多</h3>'+
                           '</div>';
                $(".lucky-lists").html(tpls);
                get_data();
            } else if (msg == 2){
                alert("输入的优惠码不符合要求");
            } else if(msg == 3){
                alert("好吧，你已领过同类优惠码...");
            } else{
                alert("信号不好，请换个地方再试。");
            };
          },
          error:function(msg){
                alert("同学，请输入正确的兑换码...");
          }
        });
      };
    });
  };
  use_code();
  var get_data = function(){
    $.ajax(
      {
        type: "GET",
        url: "http://bd.koudaigongshe.com/mobile/bonus/list",
        dataType: "json",
        success: function(data){
          // console.log("获取json数据成功！");
          // console.log(data);
          if (data == null) {
               $(".worn-info").append('<h6>啊偶~你还未领取任何红包，兑换一个吧！</h6>');
               // $(".click-more").css({
               //  "display":"none"
               // });
               $("#lucky-wrap h6").css({
                "font-weight":"normal",
                "font-size":".8em",
                "color":"#ff6550",
                "text-align":"center",
                "line-height":"50px",
                "margin":"0",
                "padding-top":"10px"
               });
               $(".click-more").tap(function(){
                 alert("你还未领取任何红包~");
               });
          } else{
            var html = ''; 
            var tpl_special="";
            var data_len = data.length;
            $(".click-more").tap(function(){
              $(".special").css({
                "display":"block"
              }); 
              $(".click-more").css({
                "display":"none"
              });
            });
              // console.log(data.length);
              $.each(data, function(i, val){
                  // console.log(val);
                  var cheaper = val["money"]/100;
                  var if_used = val['status'];
                  var dead_line = val['end_time'];
                  var container_id = val['id'];
                  // console.log(dead_line);
                  if (if_used == 1) {
                    x = "未使用";
                    y = "list-container";
                    z = "";
                  } else if(if_used == 2){
                    x = "已使用";
                    y = "list-container special"
                  } else{
                    x = '已过期';
                    y = "list-container special"
                  };
                  html +='<div onclick="get_id(' + container_id + ',' + cheaper + ')" class="'+ y +'" id="' + container_id + '" data-val="' + cheaper + '">'+
                        '<div class="list-header"></div>'+
                        '<div class="lucky-list">'+
                          '<div class="list-left">'+
                            '<h3>订餐红包</h3>'+
                            '<h5>有效至' + dead_line+ '(' + x + ')</h5>'+
                          '</div>'+
                          '<div class="list-right">'+
                            '<span>￥' + cheaper+'</span><span>.00</span>'+
                          '</div>'+
                          '<h4><span>美好时刻，必点一下</span><span><i></i></span></h4>'+
                        '</div>'+
                      '</div>'

              });
              $('#append-data').append(html);
              // console.log(data[0].status);
                if (data[0].status > 1) {
                  // console.log("你拥有的红包已过期");
                  $(".worn-info").append('<h6>你拥有的红包已过期，赶快用优惠码兑换吧~</h6>');
                  $("#lucky-wrap h6").css({
                   "font-weight":"normal",
                   "font-size":".8em",
                   "color":"#ff6550",
                   "text-align":"center",
                   "line-height":"50px",
                   "margin":"0",
                   "padding-top":"10px"
                  });
                  $(".click-more").tap(function(){
                    alert("你拥有的红包已过期，赶快输入优惠码兑换吧~");
                  });
                  // $(".click-more").tap(function(){
                  // alert("很遗憾，您还未获得红包！");
                  //   $(".click-more").css({
                  //     "display":"none"
                  //   });
                  // });
                } else{
                  // console.log("有可用红包");
                  $("worn-info").css({
                    "display":"none"
                  });
                };
          };
         },
        error:function (data) {
            // console.log(data);
            alert("哎呀，一定是旁边的朋友挡着信号啦。");
        }
    });
  };
  get_data();
  var get_id = function (container_id,cheaper){
  $("#vae").attr("value",container_id);
  var vae = $("#vae").attr("value");
  // console.log(vae);
  var changing = function (){
      var  tt = $("#total_price").attr("data-price");
      // var total_price = 870;
      // alert("若未失效，使用此优惠券可优惠" + cheaper + "元！");
      // console.log(tt);
      s =  tt - cheaper*100;
      v= s/100;
      html = "";
      console.log(s);
      
      var htmls = '';
      htmls +=  '<li class="packet" style="color:red">'+
                  '<div><p>红包抵现</p></div><span>-￥'+
                  cheaper + '</span>'
                '</li>';
      $(".packet").remove();
      $(".detail-body ul").append( htmls );

      htmls = '';
      htmls = '<p>总计<span>￥</span><span id="total_price"  data-price="' + tt + '">'+ v + '</span></p>';
      $(".order-submit-money").html(htmls); 
  };
  changing();
  $(".window-alert").css({
    "display":"none"
  });
};

// else if (data[0].status !== 1) {
//                 alert("执行至此！");
//                 console.log('暂无可用红包，输入优惠码兑换一下吧！');
//                 $("lucky-wrap").append("<h6 class='nothing'>暂无可用红包，输入优惠码兑换一下吧！</h6>");
//                 console.log(data[0].status);
//           } 
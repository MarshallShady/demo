    
    (function(){

        var WINDOW_WINTH = window.innerWidth,
            WINDOW_HEIGHT = window.innerHeight;
        var newCanvas = document.createElement("canvas");

        document.body.appendChild(newCanvas);        

        newCanvas.style.cssText = "position: absolute;left: 0;top: 0; z-index: 9999; background : rgba(0,0,0,0.5)";
        newCanvas.id = "loading";
        
        var loading = document.getElementById('loading');

        loading.width = WINDOW_WINTH;
        loading.height = WINDOW_HEIGHT;


        var ctx = loading.getContext('2d');
        //定时器
            var timeClear;
            //定义一些数字
            var arcNum = 0.02,
                    textNum = 1;
     
            function load1(){
     
                if(textNum >= 96){
                    clearInterval(timeClear);
                    return 0;
                }

                ctx.clearRect( 0,0,1024,720 );

                ctx.lineWidth = 2;
                ctx.strokeStyle = '#176785';
     
                ctx.beginPath();
                ctx.arc(WINDOW_WINTH/2, WINDOW_HEIGHT/2, Math.min( WINDOW_WINTH/5, 100 ) , 0.5 * Math.PI, (0.5 + arcNum) * Math.PI);
                ctx.stroke();
                ctx.closePath();

                ctx.beginPath();
                ctx.fillStyle = "rgba(0,0,0,0.8)";
                ctx.arc(WINDOW_WINTH/2, WINDOW_HEIGHT/2, Math.min( WINDOW_WINTH/5, 100 ) - 1, (0.5 - 0/2)* Math.PI, (0.5 + 4/2) * Math.PI);
                ctx.fill();
                ctx.closePath();
     
                //绘制里面的渐变颜色
                var color = ctx.createLinearGradient(WINDOW_WINTH/2, WINDOW_HEIGHT/2 + Math.min( WINDOW_WINTH/5, 100 ), WINDOW_WINTH/2, WINDOW_HEIGHT/2 - Math.min( WINDOW_WINTH/5, 100 ));
                color.addColorStop(0, '#499989');
                color.addColorStop(1, '#176785');
     
                ctx.beginPath();
                ctx.fillStyle = color;
                ctx.arc(WINDOW_WINTH/2, WINDOW_HEIGHT/2, Math.min( WINDOW_WINTH/5, 100 ) - 1, (0.5 - arcNum/2)* Math.PI, (0.5 + arcNum/2) * Math.PI);
                ctx.fill();
                ctx.closePath();
     
                //绘制文本
                ctx.beginPath();
                ctx.font= Math.min( WINDOW_WINTH/5, 100 )*0.8 + "px Arial";
                ctx.fillStyle = "#fff";
                ctx.textAlign = "center";
                
                ctx.fillText(textNum, WINDOW_WINTH/2, WINDOW_HEIGHT/2 + Math.min( WINDOW_WINTH/5, 100 )/3 , 100);
                ctx.closePath();

                ctx.beginPath();
                ctx.font= Math.min( WINDOW_WINTH/5, 100 )*0.4 + "px Microsoft YaHei";
                ctx.fillStyle = "#fff";
                ctx.textAlign = "center";
                
                ctx.fillText('正在加载...', WINDOW_WINTH/2 , WINDOW_HEIGHT/2 + Math.min( WINDOW_WINTH/5, 100 ) * 1.8 , 500);
                ctx.closePath();
     
     
                arcNum = arcNum + 0.02;
                textNum++;
     
            }
     
            timeClear = setInterval(load1, 60);

            window.loading = function()
            {
                WINDOW_WINTH = window.innerWidth;
                WINDOW_HEIGHT = window.innerHeight;
                
                loading.width = WINDOW_WINTH;
                loading.height = WINDOW_HEIGHT;

                clearInterval(timeClear);
                textNum = 1;
                arcNum = 0.02;
                loading.style.display = 'block';
                timeClear = setInterval(load1, 100);
            };
    }());
/**
 * Created by gwiro on 2016/4/12.
*/

var app = angular.module('myApp',[])
    .controller('myBaby', function ($scope,$injector) {
        console.log($scope)
        $scope.books = [
            'smark : 聪明',
            'coap : 肥皂',
            'camp : 帐砰',
            'temp : 帐篷',
            'bungalow : 平屋',
            'template : 模板',
            'fruit : 水果',
            'photo : 照片',
            'motor : 机动的',
            'tire : 轮胎',
            'arbitrary : 任意的',
            'skyscraper : 摩天大楼',
            'telescope : 望远镜、压缩',
            'homeland : 祖国',
            ''
        ];
    })
    .directive('hello', function(){
        return {
            restrict : 'AE',
            template : '<div>我这么可爱</div>',
            compile : function (ele, attr) {

                var tep = ele.children();
                console.log(ele);
                for ( var i = 0; i < 5; i++ ) {
                    ele.append( tep.clone() );
                }

                return function ( scope, ele, attr ){
                    console.log('in before');
                    console.log('ok');
                }
            },
            link : function ( scope, ele, attr ){
                console.log('in before');
                console.log('ok');
            }
        }
    });

/**
 * Created by gwiro on 2016/4/12.
 */
var app = angular.module("myApp",[]);

app.controller("myCtrl",[ "$scope" ,function($scope){
    $scope.name = "这是一个测试";
}]);
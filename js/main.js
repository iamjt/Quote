var app = angular.module('ticapp',[ "ngSanitize","ngAnimate","ui.router","ui.bootstrap",'angularFileUpload']);

server = "http://10.116.132.20:8080/veeterbee/";
// server = "http://localhost:8080/veeterbee/";
// server = "http://192.168.1.86:8080/veeterbee/";

moment().format("D, MMM YYYY");
app.constant("moment", moment);

app.config(["$locationProvider", '$httpProvider', function($locationProvider, $httpProvider) {
	$locationProvider.html5Mode(
		{
			enabled: true,
			requireBase: false
		});
	
	$httpProvider.defaults.useXDomain = true;
    delete $httpProvider.defaults.headers.common['X-Requested-With'];
    $httpProvider.defaults.headers.common["Accept"] = "application/json";
    $httpProvider.defaults.headers.common["Content-Type"] = "application/json";	
}]);
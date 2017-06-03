var app = angular.module('ticapp',[ "ngSanitize","ngAnimate","ngCookies","ui.router","ui.bootstrap",'angularFileUpload']);

server = "../quote-api/public/";

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

app.service("UserAndAuthentication", ["http", function(http){
	
}])
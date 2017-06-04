app.controller('directorycontroller',["$scope", "directoryservice", function($scope, directory){

	$scope.airlineList = directory.airlineList();


	directory.getAirlines();

}])

app.service("directoryservice", ["$http",function($http){

	var airlines = [];

	this.airlineList = function()
	{
		return airlines;
	}

	this.getAirlines = function(){

		return $http.get(server+"airlines").then(function(response){

			if(response.status == 200)
			{	
				angular.copy([],airlines)
				angular.copy(response.data, airlines);
			}

			console.log(airlines);
		})
	}

}])
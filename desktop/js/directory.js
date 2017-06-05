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

		$http.get(server+"airlines").then(function(response){

			if(response.status == 200)
			{	
				angular.copy([],airlines)
				angular.copy(response.data, airlines);
			}

			console.log(airlines);
		})
	}

	//Return all airline data related to this airline
	//Surcharges
	//Rates for each region
	this.getAirlineData = function(IATADesignator)
	{

	}

}])
app.controller('directorycontroller',["$scope", "directoryservice", function($scope, directory){

	$scope.listLoading = true;
	$scope.contentLoading = false;

	$scope.airlineList = directory.airlineList();
	$scope.selectedAirline;
	$scope.selectedAirlineAgent;
	$scope.selectedOriginName;
	$scope.selectedOrigin;

	$scope.airlineInformation = directory.selectedAirlineInformation();

	$scope.selectAirline = function(targetAirline){

		$scope.selectedOrigin = null;

		if($scope.selectedAirline == targetAirline)
			return;

		$scope.selectedAirline = targetAirline;
		$scope.contentLoading = true;

		var promise = directory.getAirlineData(targetAirline.IATADesignator);

		promise.then(function(response){
			$scope.contentLoading = false;

			if($scope.airlineInformation.agents)
			{
				$scope.selectedAirlineAgent = $scope.airlineInformation.agents[0];

				for(var originName in $scope.selectedAirlineAgent.routes)
				{
					$scope.selectedOriginName = originName;
					$scope.selectedOrigin = $scope.selectedAirlineAgent.routes[$scope.selectedOriginName];

					break;
				}
			}
		})
	}

	$scope.isAirlineSelected = function(airline)
	{
		return (airline == $scope.selectedAirline);
	}

	directory.getAirlines().then(function(response){
		$scope.listLoading = false;
	});

}])

app.service("directoryservice", ["$http","$q",function($http, $q){

	var airlines = [];
	var selectedAirline = {};

	this.selectedAirlineInformation = function()
	{
		return selectedAirline;
	}

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
		});
	}

	//Return all airline data related to this airline
	//Surcharges
	//Rates for each region
	this.getAirlineData = function(IATADesignator)
	{
		angular.copy({},selectedAirline)

		var deferred = $q.defer();

		$http.get(server+"airline-detailed/"+IATADesignator).then(function(response){

			if(response.status == 200)
			{	
				var data = response.data;
				var agentIndex, agent, route, routes, i;
				var originCountry, destinationCountry, originCity, destinationCity, toSort;
				
				for(agentIndex = 0; agentIndex < data.agents.length; agentIndex++)
				{
					agent = data.agents[agentIndex];
					routes = agent.routes;
					agent.routes = {};

					for(i=0; i<routes.length; i++)
					{	
						route = routes[i];

						if(route.origin)
						{
							originCountry = route.origin.Country;
							originCity = route.origin.City;
						}

						if(route.destination)
						{
							destinationCountry = route.destination.Country;
							destintationCity = route.destination.City;

							if(!agent.routes[originCountry])
								agent.routes[originCountry] = {};

							// if(!agent.routes[originCountry][originCity])
							// 	agent.routes[originCountry][originCity] = {};

							if(!agent.routes[originCountry][destinationCountry])
								agent.routes[originCountry][destinationCountry] = {};

							if(!agent.routes[originCountry][destinationCountry][destintationCity])
								agent.routes[originCountry][destinationCountry][destintationCity] = [];

							agent.routes[originCountry][destinationCountry][destintationCity].push(route);
						}
					}

					toSort = agent.routes[originCountry][destinationCountry][destintationCity]
				}

				angular.copy(data, selectedAirline);
				deferred.resolve();
			}
		});

		return deferred.promise;
	}

}])
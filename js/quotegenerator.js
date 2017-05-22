app.controller("QuoteGeneratorController",["$scope", "Airports", "QuoteService", function($scope, Airports, QuoteService){

	$scope.loading = true;

	$scope.origin = QuoteService.currentOriginObject();
	$scope.destination = QuoteService.currentDestinationObject();
	$scope.quotelist = QuoteService.currentQuoteList();

	//Resources
	$scope.originAirportList = Airports.originAirportList();
	$scope.destinationAirportList = Airports.destinationAirportList();
	$scope.airlineList = QuoteService.airlineList();
	$scope.agentList = QuoteService.agentList();
	$scope.services = QuoteService.services();

	$scope.originAirport = "";
	$scope.destinationAirport = "";

	$scope.dimensions = {
		length:"",
		width:"",
		height:""
	};
	$scope.rawWeight = 0;
	$scope.quantity = 0;
	$scope.dimensionUnit = "cm";
	$scope.weightUnit = "kg";

	//Test Values
	$scope.originAirport;
	$scope.destinationAirport;

	$scope.dimensions = {
		length:0,
		width:0,
		height:0
	};
	$scope.rawWeight = 0;
	$scope.quantity = 0;
	$scope.dimensionUnit = "cm";
	$scope.weightUnit = "kg";

	//Returns volume in cm3
	$scope.volume = function()
	{
		if($scope.dimensionUnit == "cm")
			return Math.round($scope.dimensions.length * $scope.dimensions.width * $scope.dimensions.height *100)/100;
		else
			return Math.round($scope.dimensions.length * $scope.dimensions.width * $scope.dimensions.height * 16.3871 * 100)/100;
	}

	//works based on cm3
	$scope.volumeWeight = function()
	{
		return $scope.volume()/5000;
	}

	$scope.weight = function()
	{
		if($scope.weightUnit == "kg")
			return $scope.rawWeight;
		else
			return Math.round($scope.rawWeight*0.453592*100)/100;
	}

	$scope.originValid = function()
	{
		if($scope.originAirport)
			return $scope.originAirport.IATACode != null;
		else
			return null;
	}

	$scope.destinationValid = function()
	{
		if($scope.destinationAirport)
			return $scope.destinationAirport.IATACode != null;
		else
			return false;
	}

	$scope.canGetQuote = function()
	{
		return $scope.originValid()&&$scope.destinationValid()&&($scope.volume()>0)&&($scope.weight()>0)&&(parseInt($scope.quantity)>0);
	}

	//Init Default View Settings
	$scope.formView = true;
	$scope.quoteView = false;
	$scope.filterView = false;

	$scope.showView = function(view)
	{
		switch(view)
		{
			case "form":
				$scope.formView = true;
				$scope.quoteView = false;
				$scope.filterView = false;
				break;
			case "quotes":
				$scope.formView = false;
				$scope.quoteView = true;
				$scope.filterView = false;
				break;
			case "filter":
				$scope.formView = false;
				$scope.quoteView = false;
				$scope.filterView = true;
				break;
		}
	}

	$scope.newQuote = function()
	{
		$scope.originAirport = "";
		$scope.destinationAirport = "";

		$scope.dimensions = {
			length:"",
			width:"",
			height:""
		};
		$scope.rawWeight = 0;
		$scope.quantity = 0;
		$scope.dimensionUnit = "cm";
		$scope.weightUnit = "kg";

		$scope.showView("form");
	}

	$scope.getQuotes = function()
	{
		var load = {
			quantity: $scope.quantity,
			volume: $scope.volume(),
			volumeWeight: $scope.volumeWeight(),
			weight: $scope.weight()
		}

		$scope.loading = true;

		var promise = QuoteService.getQuotes($scope.originAirport.IATACode, $scope.destinationAirport.IATACode, load);

		promise.then(function(response){
			$scope.showView("quotes");
			$scope.loading = false;
		});
	}

	$scope.previousQuotes = function()
	{
		
	}

	$scope.editEnabled = function()
	{
		return false;
	}

	$scope.setDimensionsUnit = function(unit)
	{
		$scope.dimensionUnit = unit;
	}

	$scope.setWeightUnit = function(unit)
	{
		$scope.weightUnit = unit;
	}

	$scope.filterByCountry = function(countries, viewValue){
		return countries.filter(function(country){
			viewValue = viewValue.toUpperCase();
			return (country.countryCode.toUpperCase().startsWith(viewValue))||(country.countryName.toUpperCase().indexOf(viewValue) != -1);
		});
	}

	$scope.filterByAirport = function(airports, viewValue, isOrigin){
		return airports.filter(function(airport){

			viewValue = viewValue.toUpperCase();
			return ((airport.IATACode.toUpperCase().startsWith(viewValue))||(airport.AirportName.toUpperCase().indexOf(viewValue) != -1));
		});
	}

	$scope.orderByAlphabets = function(myString)
	{
		return myString;
	}

	Airports.initService().then(function(){
		$scope.loading = false;
	})
}]);

app.service("QuoteService",["Airports","$http", "$q", function(Airports, $http, $q){

	var origin = {}
	var destination = {}
	var quotelist = [];
	var airlineList = [];
	var agentList = [];
	var services = ["GEN", "LIV", "PER", "DGR", "XPS", "SXPS"];

	this.currentQuoteList = function()
	{
		return quotelist;
	}

	this.currentOriginObject = function()
	{
		return origin;
	}

	this.currentDestinationObject = function()
	{
		return destination;
	}

	this.airlineList = function()
	{
		return airlineList;
	}

	this.agentList = function()
	{
		return agentList;
	}

	this.services = function()
	{
		return services;
	}

	//Assign The objects here, these objects should be assigned by the server itself
	this.getQuotes = function(originCode, destinationCode, loadParameters)
	{
		var data = {
			origin: originCode, 
			destination: destinationCode,
			load:loadParameters
		}

		var airlineListPromise = $http.post(server+"route-operators/", JSON.stringify(data));

		airlineListPromise.then(function(response){

			angular.copy({}, origin);
			angular.copy({}, destination);
			angular.copy([], quotelist);
			angular.copy([], agentList);
			angular.copy([], airlineList);
			
			angular.copy(response.data.origin, origin);
			angular.copy(response.data.destination, destination);

			for(var key in response.data.operators)
			{	
				var operator = response.data.operators[key];
				operator.code = key;

				if(operator.AirlineName)
				{
					quotelist.push(operator);

					if(!airlineList.includes(operator.AirlineName))
						airlineList.push(operator.AirlineName);

					if(!agentList.includes(operator.agent))
						agentList.push(operator.agent);
				}
			}
		})

		return airlineListPromise;
	}

	function saveQuote(originCode, destinationCode, loadParameters,quotesarray){

		var quoteCopy = [];
		angular.copy(quotes, quoteCopy);

		savedQuote = {
			origin: originCode, 
			destination: destinationCode,
			load:loadParameters
		}

		var localStorageArray;

		if(localStorage.getItem('tic_quote_app'))
		{
			localStorageArray = JSON.parse(localStorage.getItem('tic_quote_app'));
		}
		else
		{
			localStorageArray = {};
		}

		var today = new Date();
		var dd = today.getDate();
		var mm = today.getMonth()+1; //January is 0!
		var yyyy = today.getFullYear();

		if(dd<10) {
			dd='0'+dd
		} 

		if(mm<10) {
			mm='0'+mm
		} 

		today = dd+'-'+mm+'-'+yyyy;

		localStorageArray["originCode"+"-"+"destinationCode"+today] = {
			origin:originCode, 
			destination:destinationCode,
			load:loadParameters,
			date:today,
			quotes:quotesarray
		}
		
		localStorage.setItem('tic_quote_app',JSON.stringify(localStorageArray));
	}

	function localQuoteHistory(){

	}

}])


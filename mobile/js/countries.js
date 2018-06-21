app.service("Airports", ["$http",function($http){

	//Lists of unique countries
	var originCountries = [];
	var destinationCountries = [];

	//Lists of unique airports
	var originAirports = [];
	var destinationAirports = [];

	var totalOrigins = 0;
	var currentOrigins = 0;
	var totalDestinations = 0;
	var currentDestinations = 0;

	this.originCountryList = function(){
		return originCountries;
	}

	this.destinationCountryList = function(){
		return destinationCountries;
	};

	this.originAirportList = function(){
		return originAirports;
	}

	this.destinationAirportList = function(){
		return destinationAirports;
	};

	this.getLocation = function(airportCode){

		return http.get(server+"airports/"+airportCode);
	}

	this.originBuffer = function()
	{
		if(totalOrigins > 0)
			return Math.round(currentOrigins/totalOrigins*100);
		else
			return 0;
	}

	this.destinationBuffer = function()
	{	
		if(totalDestinations > 0)
			return Math.round(currentDestinations/totalDestinations*100);
		else
			return 0;
	}

	function uniqueCountryList(myList)
	{
		var tempCode = [];
		var tempCountry = [];
		var tempItem;

		for(var i=0; i<myList.length; i++)
		{	
			tempItem = myList[i];
			tempCode.push(tempItem.ISOCode);
			tempCountry.push(tempItem.Country);
		}

		var finalCodeList = Array.from(new Set(tempCode));
		var finalCountryList = Array.from(new Set(tempCountry));
		var finalList = [];

		for(var i=0; i<finalCodeList.length; i++)
		{
			finalList.push({
				countryCode: finalCodeList[i],
				countryName: finalCountryList[i],
				displayName: finalCountryList[i]+", "+finalCodeList[i]
			});
		}

		return finalList;
	}

	this.initService = function()
	{

		return $http.get(server+"airports").then(function(response){

			if(response.status == 200)
			{
				console.log(response.data)

				angular.copy([],originCountries);
				angular.copy(uniqueCountryList(response.data.originAirports), originCountries);

				angular.copy([],originAirports);
				angular.copy(response.data.originAirports, originAirports);

				angular.copy([],destinationCountries);
				angular.copy(uniqueCountryList(response.data.destinationAirports), destinationCountries);

				angular.copy([],destinationAirports);
				angular.copy(response.data.destinationAirports, destinationAirports);
			}
			else{}
		});
	}

	this.getAirportBuffer = function()
	{
		return $http.get(server+"airports-loading-status").then(function(response){
			currentDestinations = response.data.currentDestinations;

			totalOrigins = response.data.totalOrigins;
			currentOrigins = response.data.currentOrigins;
			totalDestinations = response.data.totalDestinations;
			currentDestinations = response.data.currentDestinations;
		})
	}
}])

app.filter("CountryFilter", function(){
	return function(country, searchText){

		return result;
	}
})
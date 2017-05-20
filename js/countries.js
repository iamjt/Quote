app.service("Airports", ["$http", function($http){

	//Lists of unique countries
	var originCountries = [];
	var destinationCountries = [];

	//Lists of unique airports
	var originAirports = [];
	var destinationAirports = [];

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

	function uniqueCountryList(myList)
	{
		var tempCode = [];
		var tempCountry = [];
		var tempItem;

		for(var i=0; i<myList.length; i++)
		{	
			tempItem = myList[i];
			tempCode.push(tempItem.countryCode);
			tempCountry.push(tempItem.countryName);
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

	$http.get(server+"airports").then(function(response){

		if(response.status == 200)
		{	
			console.log(response);

			// angular.copy([],originCountries);
			// angular.copy(uniqueCountryList(response.data), originCountries);

			// angular.copy([],originAirports);
			// angular.copy(response.data, originAirports);
		}
		else{}
	});
}])

app.filter("CountryFilter", function(){
	return function(country, searchText){

		return result;
	}
})
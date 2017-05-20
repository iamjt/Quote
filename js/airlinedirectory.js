app.controller("AirlineDirectoryController", ["$scope", "$interval", "moment", "Airports","Airlines", function($scope, $interval, moment, Airports, Airlines){

	$scope.loadingVendorData = false;
	$scope.selectedVendor = null;
	$scope.editingRoute = {};
	$scope.editingVendor = {};

	var loadingPromise = null;

	$scope.vendorList = Airlines.list();
	$scope.countryList = Airports.list();

	$scope.vendorList = [];
	$scope.countryList = [];

	$scope.currentView = "Quotes";

	$scope.showView = function(view)
	{	
		if($scope.currentView != view)
			$scope.currentView = view;
	}

	$scope.displayDate = function(date)
	{
		return moment().format("D MMM YYYY")
	}

	$scope.isViewVisible = function(view)
	{	
		if(!$scope.selectedVendor)
			return false;

		return $scope.currentView == view;

	}

	$scope.isVendorSelected = function(vendor)
	{
		if($scope.selectedVendor)
			if(vendor == $scope.selectedVendor)
				return "list-group-item-active";
	}

	$scope.selectVendor = function(vendor)
	{	
		if($scope.selectedVendor != vendor)
		{
			if(loadingPromise)
			{
				$interval.cancel(loadingPromise);
				$scope.selectedVendor = null;
			}

			$scope.selectedVendor = false;
			$scope.loadingVendorData = true;

			loadingPromise = $interval(function(){
				$scope.selectedVendor = vendor;
				$scope.loadingVendorData = false;
				$scope.showView('Quotes'); //Default to profile
				$interval.cancel(loadingPromise);
			},100)
		}
	}

	$scope.displayCost = function(unformattedCost)
	{
		return unformattedCost.toFixed(2);
	}

	$scope.editVendor = function(vendor)
	{
		$scope.editingVendor = angular.copy(vendor, $scope.editVendor);
		vendor.editing = true;
	}

	$scope.discardVendorChanegs = function(vendor)
	{
		vendor.editing = false;
	}

	$scope.saveVendorChanges = function(vendor)
	{
		$scope.editingVendor.editing = false;
		angular.copy($scope.editVendor, vendor);
		angular.copy({},$scope.editingRoute);		
	}


	$scope.editRoute = function(route)
	{
		$scope.editingRoute = angular.copy(route,$scope.editingRoute);
		route.editing = true;		
	}

	$scope.discardRouteChanges = function(route)
	{
		route.editing = false;
		// $scope.editingRoute = {};
	}

	$scope.saveRouteChanges  = function(route)
	{
		$scope.editingRoute.editing = false;
		angular.copy($scope.editingRoute, route);
		angular.copy({},$scope.editingRoute);
	}

	$scope.deleteRoute = function(route)
	{
		$scope.selectedVendor.deleteRoute(route);
	}

	$scope.vendorLink = function(website)
	{
		return website;
	}

	$scope.vendorFilter = function(filter)
	{

	}

	// $scope.selectVendor($scope.vendorList[0]);
}]);

app.filter("AirlineFilter", function(){

	return function(airlines, airlineFilterInput)
		{	
			var result = [];
			angular.forEach(airlines, function(airline) {  

				if(airlineFilterInput)
				{
					var airlineCheck = !airlineFilterInput

					if(!airlineCheck)
					{
						var pattern = RegExp(airlineFilterInput,"i")
						airlineCheck = pattern.test(airline.name) || pattern.test(airline.country);
					}

					if(airlineCheck)
						result.push(airline);
				}
				else
					result.push(airline);
			});

			return result;
		}
})
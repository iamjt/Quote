app.service("Airlines", ["$http", function($http){

	var airlineList = [];

	this.list = function(){
		return airlineList;
	}

	$http.get(server+"airlines").then(function(response){

		if(response.status == 200)
		{	
			angular.copy([],airlineList)
			angular.copy(response.data, airlineList);
		}
		else{}
	})
}]);
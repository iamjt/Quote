<?php
// Routes

$app->get('/', function ($request, $response, $args) {

	// Render index view
	return $this->renderer->render($response, '403.phtml', $args);
});

$app->get('/login', function($request, $response, $args) {

});

$app->get('/new-user', function($request, $response, $args) {

	$connection = connectToDB();
	if(newUser($connection, "jt.admin", "jt123456"))
	{
		echo "nice";
	}
	else
	{
		echo "failed";
	}

});

$app->get('/authenticate', function($request, $response, $args) {

	$output = array();
	$output['user'] = authenticate();

	$response
	->withStatus(200)
	->withHeader('Content-Type', 'application/json')
	->write(json_encode($output));
});

$app->get('/airport/{airport}', function($request, $response, $args) {

	$connection = connectToDB();
	
	$output = getAirportDetailsByCode($connection, $args['airport']);

	$response
	->withStatus(200)
	->withHeader('Content-Type', 'application/json')
	->write(json_encode($output));
});

$app->get('/airports', function($request, $response, $args) {

	$connection = connectToDB();
	
	$output = array();
	$output ["originAirports"]  = getAirports($connection, true);
	$output ["destinationAirports"] = getAirports($connection, false);

	if(isset($connection))
	{
		$connection -> close();
	}

	$response
	->withStatus(200)
	->withHeader('Content-Type', 'application/json')
	->write(json_encode($output));
});

//Should be a post function
//Route operators for given origin-destination route
//Requires the load object defined as
//{origin airport code, destination airport code, quantity, weight, length, width, height}
//Produces a json array
$app->post('/route-operators/', function($request, $response, $args) {
	
	$origin = $request->getParsedBody()['origin'];
	$destination = $request->getParsedBody()['destination'];

	$load = $request->getParsedBody()['load'];
	$weight = $load['volumeWeight'] > $load['weight']?$load['volumeWeight']:$load['weight'];

	$connection = connectToDB();

	$output = array();

	$output ["originAirport"] = getAirportDetailsByCode($connection, $origin);
	$output ["destinationAirport"] = getAirportDetailsByCode($connection, $destination);
	$output ["operators"] = getRouteOperators($connection, $origin, $destination, $weight);

	$response
	->withStatus(200)
	->withHeader('Content-Type', 'application/json')
	->write(json_encode($output));
});

//Given a route id
//produces a download link via json
$app->post('/get-quote/', function($request, $response, $args) {
	
	$routeID = $request->getParsedBody()['routeID'];
	$parameters = explode("-", $routeID);
		
	$connection = connectToDB();

	//Get airline details
	$airlineCode = $parameters[0];
	$airlineName = getAirlineByCode($connection, $airlineCode)['AirlineName'];

	//Get origin details
	$originCode = $parameters[1];
	$origin = getAirportDetailsByCode($connection, $originCode);

	//Get Destination details
	$destinationCode = $parameters[2];
	$destination = getAirportDetailsByCode($connection, $destinationCode);

	$quotes = getRouteQuotes($connection, $routeID);	

	$output = array();
	$output["AirlineName"] = $airlineName;
	$output["origin"] = $origin;
	$output["destination"] = $destination;
	$output["quotes"] = $quotes;

	$response
	->withStatus(200)
	->withHeader('Content-Type', 'application/json')
	->write(json_encode($output));
});
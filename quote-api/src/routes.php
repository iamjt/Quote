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

$app->get('/airports', function($request, $response, $args) {

	$connection = connectToDB();
	
	$originAirportCodes = getAirport($connection, true);
	$destinationAirportCodes = getAirport($connection, false);

	$output = array();
	$output ["originAirports"] = getAirportListByCodes($connection, $originAirportCodes);
	$output ["destinationAirports"] = getAirportListByCodes($connection, $destinationAirportCodes);

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
$app->get('/get-quote/[{routeid}]', function($request, $response, $args) {
	
	if(!isset($args['routeid']))
	{
		return $this->renderer->render($response, '403.phtml', $args);
	};
});
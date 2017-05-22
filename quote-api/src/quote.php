<?php
	
	function getRouteOperators ($connection, $origin, $destination, $weight){
		
		$stmt = $connection -> prepare("SELECT `RouteID`, `Airline`, `TransitAirportCode`, `AgentCode` FROM `routes` WHERE `OriginAirportCode` = ? AND `DestinationAirportCode` = ? ORDER BY `Airline` ASC");

		$quoteStmt = $connection -> prepare("SELECT * FROM `quotes` WHERE `RouteID` = ? ORDER BY `Serial`");
		$airlineStmt = $connection -> prepare("SELECT `AirlineName` FROM `airlines` WHERE `IATADesignator` = ?");

		if(!$stmt)
		{
			printf("Error: Unable to prepare statement");
		    exit();
		}

		$stmt->bind_param("ss", $origin, $destination);
		$stmt->execute();

		$result = $stmt->get_result();

		$output = array();

		if($result)
		{
			while($route = $result->fetch_assoc())
			{	
				//Build a hash our of all the route objects
				//If the route does not exist, make the route object container
				//Every route object should be specific to 
				if(!isset($output[$route['RouteID']]))
				{
					//Create the route object
					//Route object holds the different prices
					$routeObject = array ();
					$routeObject['RouteID'] = $route['RouteID'];

					//Get the airline Name
					$airlineStmt -> bind_param("s", $route['Airline']);
					$airlineStmt->execute();
					$airlineResult = $airlineStmt -> get_result();
					while($airlineQuery = $airlineResult -> fetch_assoc())
					{
						$routeObject['AirlineName'] = $airlineQuery['AirlineName'];
					}

					$routeObject['agent'] = $route['AgentCode'];
					$routeObject['transitAirportCode'] = $route['TransitAirportCode'];
					
					if($routeObject['transitAirportCode'] != '0')
					{
						$transitObject = getAirportDetailsByCode($connection, $routeObject['transitAirportCode']);
						$routeObject['transitCountry'] = $transitObject['Country'];
						$routeObject['transitCity'] = $transitObject['City'];
						$routeObject['TransitAirportName'] = $transitObject['AirportName'];
					}

					$routeQuotes = array();
					$routeQuotes['GEN'] = array();
					$routeQuotes['LIV'] = array();
					$routeQuotes['PER'] = array();
					$routeQuotes['DGR'] = array();
					$routeQuotes['XPS'] = array();
					$routeQuotes['SXPS'] = array();

					$quoteStmt -> bind_param("s", $route['RouteID']);
					$quoteStmt -> execute();

					$quoteResult = $quoteStmt -> get_result();

					while($quoteTable = $quoteResult -> fetch_assoc())
					{
						$routeQuotes[$quoteTable['Commodity']][] = getQuote($quoteTable,$weight);
						asort($routeQuotes[$quoteTable['Commodity']]);
					}

					$quoteStmt -> free_result();

					$routeObject["quotes"] = $routeQuotes;
					$output[$route['RouteID']] = $routeObject;
				}
			}

			$stmt -> free_result();
		}

		return $output;
	}

	function getQuote($quoteTable, $weight)
	{	

		$minimum = (float)$quoteTable['Minimum'];
		$bpn45 = (float)$quoteTable['BPN45'];
		$bp45 = (float)$quoteTable['BP45'];
		$bp100 = (float)$quoteTable['BP100'];
		$bp250 = (float)$quoteTable['BP250'];
		$bp300 = (float)$quoteTable['BP300'];
		$bp500 = (float)$quoteTable['BP500'];
		$bp1000 = (float)$quoteTable['BP1000'];

		$selectedRate = 0;

		if($weight < 45 && ($bpn45 > 0))
		{	
			$breakWeight = 0;
			
			if($bpn45 > 0)
				$breakWeight = $minimum/$bpn45;
			
			if($breakWeight < $weight)
				$selectedRate = $bpn45;
		}
		else if($weight < 100 && ($bp45 > 0))
		{
			$selectedRate = $bp45;
		}
		else if($weight < 250 && ($bp100 > 0))
		{
			$selectedRate = $bp100;
		}
		else if($weight < 300 && ($bp250 > 0))
		{
			$selectedRate = $bp250;
		}
		else if($weight < 500 && ($bp300 > 0))
		{
			$selectedRate = $bp300;
		}
		else if($weight < 1000 && ($bp500 > 0))
		{
			$selectedRate = $bp500;
		}
		else
		{
			$selectedRate = $bp1000;
		}
		
		$cost = $selectedRate * $weight;
		
		if($cost < $minimum)
			$cost = $minimum;
							
		return $cost;
	}

	function getAgentList($connection, $origin, $destination)
	{
		$stmt = $connection -> prepare("SELECT DISTINCT `AgentCode` FROM `routes` WHERE `OriginAirportCode` = ? AND `DestinationAirportCode` = ? ORDER BY `Airline` ASC");

		if(!$stmt)
		{
			printf("Error: Unable to prepare statement");
		    exit();
		}

		$stmt->bind_param("ss", $origin, $destination);
		$stmt->execute();
		$result = $stmt->get_result();

		$output = array();

		if($result)
		{
			while($agent = $result->fetch_assoc())
			{
				$output[] = $agent;
			}
		}

		return $output;
	}
?>
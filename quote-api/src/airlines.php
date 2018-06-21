<?php
	
	function getAllAirlines($connection)
	{	
		$stmt = $connection -> prepare("SELECT * FROM `airlines` where `IATADesignator` in (SELECT DISTINCT `Airline` from `routes`)");
		$stmt2 = $connection -> prepare("SELECT `RegionCode` FROM `countries` where `CountryName` = ?");
		$stmt->execute();

		$result = $stmt->get_result();
		
		$output = array();

		while($airline = $result->fetch_assoc())
		{
			$stmt2 -> bind_param("s", $airline["Country"]);
			$stmt2 -> execute();
			$result2 = $stmt2 -> get_result();

			$airline["RegionCode"] = $result2 -> fetch_assoc()["RegionCode"];
			$output[] = $airline;
		}

		$stmt -> close();

		return $output;
	}

	function getAirlineByCode($connection, $airlineID)
	{	
		$stmt = $connection -> prepare("SELECT * FROM `airlines` WHERE `IATADesignator` = ?");

		if(!$stmt)
		{
			printf("Error: Unable to prepare statement");
		    exit();
		}

		$stmt->bind_param("s", $airlineID);
		$stmt->execute();

		$result = $stmt->get_result();
		$airline = $result->fetch_assoc();

		$stmt -> close();

		if($airline)
			return $airline;
		else
			return null;
	}

	function getAirlineAgentRoutes($connection, $airlineID)
	{
		$output = array();

		$stmt = $connection -> prepare("SELECT * FROM `routes` WHERE `Airline` = ?");
		$stmt2 = $connection -> prepare("SELECT * FROM `airport` WHERE `IATACode` = ? OR `ICAOCode` = ?");


		$stmt->bind_param("s", $airlineID);
		$stmt->execute();

		$result = $stmt->get_result();

		$output = array();
		$temp = array();

		while($route = $result->fetch_assoc())
		{
			$stmt2->bind_param("ss", $route['OriginAirportCode'],$route['OriginAirportCode']);
			$stmt2->execute();
			$result2 = $stmt2->get_result();
			$route["origin"] = $result2->fetch_assoc();

			$stmt2->bind_param("ss", $route['DestinationAirportCode'],$route['DestinationAirportCode']);
			$stmt2->execute();
			$result2 = $stmt2->get_result();
			$route["destination"] = $result2->fetch_assoc();

			if($route['TransitAirportCode'] != "0")
			{
				$stmt2->bind_param("ss", $route['TransitAirportCode'],$route['TransitAirportCode']);
				$stmt2->execute();
				$result2 = $stmt2->get_result();
				$route["transition"] = $result2->fetch_assoc();
			}

			if($route["AgentCode"] == 0)
			{
				if(!isset($temp["TIC"]))
					$temp["TIC"] = array();
				$temp["TIC"][] = $route;
			}
			else
			{
				if(!isset($temp[$route["AgentCode"]]))
					$temp[$route["AgentCode"]] = array();
				$temp[$route["AgentCode"]][] = $route;
			}
		}

		$stmt -> close();

		foreach($temp as $key => $value)
		{
			$tempArray = array();
			$tempArray["name"] = $key;
			$tempArray["routes"] = $temp[$key];
			$output[] =$tempArray;
		}

		return $output;
	}
?>
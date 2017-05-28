<?php
	
	function setLoadingCookie($key, $value)
	{
		setcookie( $key, $value, time() + 120, './', $_SERVER['HTTP_HOST'], isset ( $_SERVER["HTTPS"] ) , false );
	}

	function getAirportLoadStatus()
	{
		$output = array();

		if(isset($_COOKIE['totalOrigins']))
			$output['totalOrigins'] = $_COOKIE['totalOrigins'];
		else
			$output['totalOrigins'] = 0;

		if(isset($_COOKIE['currentOrigins']))
			$output['currentOrigins'] = $_COOKIE['currentOrigins'];
		else
			$output['currentOrigins'] = 0;
		
		if(isset($_COOKIE['totalDestinations']))
			$output['totalDestinations'] = $_COOKIE['totalDestinations'];
		else
			$output['totalDestinations'] = 0;

		if(isset($_COOKIE['currentDestinations']))
			$output['currentDestinations'] = $_COOKIE['currentDestinations'];
		else
			$output['currentDestinations'] = 0;

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

	function getAirports($connection, $isOrigin)
	{
		if($isOrigin)
			$stmt = $connection -> prepare("SELECT * FROM `airport` WHERE `IATACode` IN (SELECT DISTINCT `OriginAirportCode` FROM `routes`)");
		else
			$stmt = $connection -> prepare("SELECT * FROM `airport` WHERE `IATACode` IN (SELECT DISTINCT `DestinationAirportCode` FROM `routes`)");

		if(!$stmt)
		{
			printf("Error: Unable to prepare statement");
		    exit();
		}

		$stmt->execute();

		$result = $stmt->get_result();

		if($isOrigin)
		{
			setLoadingCookie("totalOrigins", $stmt -> affected_rows);
			setLoadingCookie("currentOrigins", 0);
		}
		else
		{
			setLoadingCookie("totalDestinations", $stmt -> affected_rows);
			setLoadingCookie("currentDestinations", 0);
		}

		$output = array();

		$airportCount = 0;

		while($airport = $result->fetch_assoc())
		{
			if($airport["City"] == "Singapore")
			{
				$airport["DisplayName"] = "Singapore - ".$airport["IATACode"];
			}
			else if($airport["City"] == $airport["Country"])
			{
				$airport["DisplayName"] = $airport["City"]." (".$airport["AirportName"]." - ".$airport["IATACode"].")";	
			}
			else
			{
				$airport["DisplayName"] = $airport["City"].", ".$airport["Country"]." (".$airport["AirportName"]." - ".$airport["IATACode"].")";	
			}

			$output [] = $airport;
			$airportCount++;

			if($isOrigin)
				setLoadingCookie("currentOrigins", $airportCount);
			else
				setLoadingCookie('currentDestinations', $airportCount);
		}

		return $output;
	}

	function getAirportDetailsByCode($connection, $airportID)
	{
		$stmt = $connection -> prepare("SELECT * from `airport` WHERE `IATACode` = ?");

		if(!$stmt)
		{
			printf("Error: Unable to prepare statement");
		    exit();
		}

		$stmt->bind_param("s", $airportID);
		$stmt->execute();

		$result = $stmt->get_result();
		$airport = $result->fetch_assoc();

		if($airport)
		{
			if($airport["City"] == "Singapore")
			{
				$airport["DisplayName"] = "Singapore - ".$airport["IATACode"];
			}
			else if($airport["City"] == $airport["Country"])
			{
				$airport["DisplayName"] = $airport["City"]." (".$airport["AirportName"]." - ".$airport["IATACode"].")";	
			}
			else
			{
				$airport["DisplayName"] = $airport["City"].", ".$airport["Country"]." (".$airport["AirportName"]." - ".$airport["IATACode"].")";	
			}

			return $airport;
		}
		else
			return null;

		$stmt -> close();

	}

	function getAirportListByCodes($connection, $airportIDList)
	{
		$stmt = $connection -> prepare("SELECT * from `airport` WHERE `IATACode` = ?");

		if(!$stmt)
		{
			printf("Error: Unable to prepare statement");
		    exit();
		}

		$output = array();

		$count = 0;

		foreach($airportIDList as $airportID)
		{
			$stmt->bind_param("s", $airportID);
			$stmt->execute();

			$result = $stmt->get_result();

			if($result)
			{
				$airport = $result->fetch_assoc();

				if($airport)
				{
					if($airport["City"] == "Singapore")
					{
						$airport["DisplayName"] = "Singapore - ".$airport["IATACode"];
					}
					else if($airport["City"] == $airport["Country"])
					{
						$airport["DisplayName"] = $airport["City"]." (".$airport["AirportName"]." - ".$airport["IATACode"].")";	
					}
					else
					{
						$airport["DisplayName"] = $airport["City"].", ".$airport["Country"]." (".$airport["AirportName"]." - ".$airport["IATACode"].")";	
					}

					$output [] = $airport;
				}

				$stmt -> free_result();
			}

			$time_end = microtime(true);
			$time = $time_end - $time_start;
			echo $time+"<br/>";
		}

		$stmt -> close();

		return $output;
	}


?>
<?php

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
			$_COOKIE["totalOrigins"] = $stmt -> affected_rows;
			$_COOKIE["currentOrigins"] = 0;
		}
		else
		{
			$_COOKIE["totalDestinations"] = $stmt -> affected_rows;
			$_COOKIE["currentDestinations"] = 0;
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
				$_COOKIE["currentOrigins"] = $airportCount;
			else
				$_COOKIE['currentDestinations'] = $airportCount;
		}

		return $output;

		$stmt -> close();
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
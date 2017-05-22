<?php
	
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
		$stmt->store_result();
		$stmt -> bind_result($AirportName, $City, $Country, $ISOCode, $IATACode, $ICAOCode, $Region, $IATAZone, $LastUpdated);

		$output = array();

		while($stmt->fetch())
		{	
			$airport = array();
			$airport['City'] = $City;
			$airport['IATACode'] = $IATACode;
			$airport['Country'] = $Country;
			$airport['AirportName'] = $AirportName;

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

		$stmt -> close();

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
		}

		$stmt -> close();

		return $output;
	}


?>
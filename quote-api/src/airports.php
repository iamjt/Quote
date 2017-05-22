<?php
	
	function getAirport($connection, $isOrigin)
	{
		if($isOrigin)
			$stmt = $connection -> prepare("SELECT DISTINCT `OriginAirportCode` FROM `routes`");
		else
			$stmt = $connection -> prepare("SELECT DISTINCT `DestinationAirportCode` FROM `routes`");

		if(!$stmt)
		{
			printf("Error: Unable to prepare statement");
		    exit();
		}

		$stmt->execute();
		$stmt->store_result();
		$stmt->bind_result($airportCode);

		$output = array();

		while($stmt->fetch())
		{
			$output [] = $airportCode;
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
		$stmt = $connection -> prepare("SELECT `AirportName`, `City`, `Country` from `airport` WHERE `IATACode` = ?");

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
						$airport["DisplayName"] = "Singapore - ".$airportID;
					}
					else if($airport["City"] == $airport["Country"])
					{
						$airport["DisplayName"] = $airport["City"]." (".$airport["AirportName"]." - ".$airportID.")";	
					}
					else
					{
						$airport["DisplayName"] = $airport["City"].", ".$airport["Country"]." (".$airport["AirportName"]." - ".$airportID.")";	
					}

					$output [] = $airport;
				}

				$stmt -> free_result();
			}
		}

		$stmt -> close();

		return $output;
	}


?>
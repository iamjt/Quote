<?php
	
	function getAllAirlines($connection)
	{	
		$stmt = $connection -> prepare("SELECT * FROM `airlines` where `IATADesignator` in (SELECT DISTINCT `IATADesignator` from `airlines`)");
		$stmt->execute();

		$result = $stmt->get_result();
		
		$output = array();

		while($airline = $result->fetch_assoc())
		{
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

?>
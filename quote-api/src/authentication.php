<?php 
	
	function authenticate()
	{
		//If cookie exist, huat ah
		return true;
	}

	function login($connection, $userName, $password)
	{

	}

	function newUser($connection, $userName, $password)
	{	
		if (preg_match('/^[a-zA-Z0-9._]+$/', $userName) == 0)
			return false;

		$userID = "";

		while(strlen($userID) < 16)
			$userID .= rand(0,10);

		$stmt = $connection -> prepare("INSERT INTO `users`(`UserID`, `UserName`, `Password`) VALUES (?,?,?)");		

		if(!$stmt)
		{
			printf("Error: Unable to prepare statement");
			exit();
		}

		$stmt->bind_param("sss", $userID, $userName, $password);
		$stmt->execute();

		return true;
	}
?>
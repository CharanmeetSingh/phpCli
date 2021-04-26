<?php 

function connection() {  		// creates connection
	$config = json_decode(file_get_contents('config.php'), TRUE); //config[] includes db info: uname, pwd, host and dbname.
	if (is_null($config)) {
		echo "Please first specify username, password and host for DB connection.";
	}else {
		$conn = new mysqli($config[0], $config[1], $config[2], $config[3]);
		// Check connection
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		}
		return $conn;
	}
}


if (isset($argv[1])) {
	switch ($argv[1]) {
		case "--help":
			echo "--file: The name of the CSV file you want to parse.
			\r\n--create_table: Creates MySQL users table.
			\r\n--dry_run: This will run with --file directive in case you only want to run the script without making any entry in DB. Ex. --file [fileName] --dry_run.
			\r\n-u: MySQL username.
			\r\n-p: MySQL password.
			\r\n-h: MySQL host
			\r\n-db: MySQL DB";
			break;
		
		case "-u":
		case "-p":
		case "-h":
		case "-db":
			foreach ($argv as $key => $val) {
				if ($val == "-u") { 
					$uname = $argv[$key + 1];
				}
				if ($val == "-p") {
					$pwd = $argv[$key + 1];
				}
				if ($val == "-h") {
					$host = $argv[$key + 1];
				}
				if ($val == "-db") {
					$db = $argv[$key + 1];
				}
			}
			
			// Saving db configurations
			if (isset($uname) && isset($pwd) && isset($host) && isset($db)) {
				$config = [$host, $uname, $pwd, $db];
				$content = json_encode($config);
				file_put_contents('config.php', $content);
				echo "configurations saved successfully.";
			}else{
				echo "Please specify username, password, host and DB for connection, Ex. -u [uname] -p [pwd] and so forth.";
			}	
			break;
			
		case "--create_table":
			$conn = connection(); // Creating connection
			// sql to create table
			$sql = "CREATE TABLE users (
			name VARCHAR(30) NOT NULL,
			surname VARCHAR(30) NOT NULL,
			email VARCHAR(50) PRIMARY KEY)";
			
			if ($conn->query($sql) === TRUE) {
				echo "Table users created successfully";
			} else {
				echo "Error creating table: " . $conn->error;
			}
			break;
			
		case "--file":
			if (isset($argv[2]) && !isset($argv[3])) {
				$conn = connection(); // Creating connection
				$file = fopen($argv[2], 'r');
				while ($row = fgetcsv($file)) {
					if ($row[0] != "name" && $row[1] != "surname" && $row[2] != "email") {  // Omitting column names
						// Insertion into table
						if (filter_var(trim($row[2]), FILTER_VALIDATE_EMAIL)) { // Validating emails
							$name = ucfirst(strtolower($row[0]));
							$name = trim(trim($name), '!'); // First trim for whitespace then second for !
							$name = $conn->real_escape_string($name); // Escaping name for special char.
							$surname = trim(ucfirst(strtolower($row[1])));
							$surname = $conn->real_escape_string($surname); // Escaping surname for special char.
							$email = trim(strtolower($row[2]));
							$email = $conn->real_escape_string($email); // Escaping email for special char.
							$sql = "INSERT INTO users (name, surname, email)
							VALUES ('$name', '$surname', '$email')";
							
							if ($conn->query($sql) === TRUE) {
							echo "\r\n New record created successfully";
							} else {
								echo "\r\n Error: " . $conn->error;
							}
						}else {
							echo "\r\n Insertion failed due to invalid email.";
						}
					}
				}
				fclose($file);
			}else {
				if (!isset($argv[3])) {
					echo "Please specify the name of the file to be parsed.";
				}
			}
			
			if (isset($argv[3]) && $argv[3] == "--dry_run") {
				$file = fopen($argv[2], 'r');
				while ($row = fgetcsv($file)) {
					if ($row[0] != "name" && $row[1] != "surname" && $row[2] != "email") {  // Omitting column names
						// Insertion into table
						if (filter_var(trim($row[2]), FILTER_VALIDATE_EMAIL)) { // Validating emails
							$name = ucfirst(strtolower($row[0]));
							$name = trim(trim($name), '!'); // First trim for whitespace then second for !
							$name = $conn->real_escape_string($name); // Escaping name for special char.
							$surname = trim(ucfirst(strtolower($row[1])));
							$surname = $conn->real_escape_string($surname); // Escaping surname for special char.
							$email = trim(strtolower($row[2]));
							$email = $conn->real_escape_string($email); // Escaping email for special char.
							/* $sql = "INSERT INTO users (name, surname, email)
							VALUES ('$name', '$surname', '$email')";
							
							if ($conn->query($sql) === TRUE) {
							echo "\r\n New record created successfully";
							} else {
								echo "\r\n Error: " . $conn->error;
							} */
						}else {
							echo "\r\n Insertion failed due to invalid email.";
						}
					}
				}
				fclose($file);
			}else {
				echo "type --help to see options.";
			}
			break;
		
		default:
			echo "type --help to see options.";
	}
}else {
	echo "Welcome to the PHP CLI application, type --help to see options.";
}
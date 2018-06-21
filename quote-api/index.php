<?php
if (PHP_SAPI == 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $url  = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    if (is_file($file)) {
        return false;
    }
}

require __DIR__ . '/vendor/autoload.php';

session_start();

// Instantiate the app
$settings = require __DIR__ . '/src/settings.php';
$app = new \Slim\App($settings);

function connectToDB (){

    $db = require __DIR__ . '/db-config.php';

    $connection = mysqli_connect($db["host"],$db["user"],$db["password"],$db["name"]);

    if(!$connection)
    {
        echo "Error: Unable to connect to MySQL." . PHP_EOL;
        echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
        echo "Debugging error: " . mysqli_connect_error() . PHP_EOL;
        exit;
    }

    return $connection;
}

// Set up dependencies
require __DIR__ . '/src/dependencies.php';

// Register middleware
require __DIR__ . '/src/middleware.php';

// Register routes
require __DIR__ . '/src/routes.php';

//Helper functions
require __DIR__ . '/src/airlines.php';
require __DIR__ . '/src/airports.php';
require __DIR__ . '/src/quote.php';
require __DIR__ . '/src/operators.php';
require __DIR__ . '/src/authentication.php';

// Run app
$app->run();
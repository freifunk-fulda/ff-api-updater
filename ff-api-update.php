<?php

/* 
 * refresh the number of active nodes in the freifunk fulda community.
 * the node information is gathered from the ffmap nodes.json file and
 * written to the freifunk.net API file.
 */

# Autoloader funktion (needed for 3rd-party validator)
function __autoload($className)
{
    $className = ltrim($className, '\\');
    $fileName  = '';
    $namespace = '';
    if ($lastNsPos = strrpos($className, '\\')) {
        $namespace = substr($className, 0, $lastNsPos);
        $className = substr($className, $lastNsPos + 1);
        $fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
    }
    $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
    if (stream_resolve_include_path($fileName)) {
        require_once $fileName;
    }
}

# json validation
function validateJson($json) {
	$json_test = json_decode($json);
	if (json_last_error() !== JSON_ERROR_NONE || $json_test === null) {
	  die;
	}
	return $json_test;
}

# include configs
require (__DIR__ . '/config.php');
set_include_path(__DIR__ . '/validator/src' . PATH_SEPARATOR . get_include_path());

# commandline options
$arOptions = array();
$arArgs = array();
array_shift($argv);//script itself
foreach ($argv as $arg) {
    if ($arg{0} == '-') {
        $arOptions[$arg] = true;
    } else {
        $arArgs[] = $arg;
    }
}
if (isset($arOptions['--help']) || isset($arOptions['-h'])) {
    echo <<<HLP
Update and validate API
Usage: php ff-api-updater
   or: php ff-api-updater newapi.json 
Options:
   -h --help            Show this help
HLP;
    exit(1);
}

if (count($arArgs) == 1) {
    $command_apifile   = $arArgs[0];
} else if (count($arArgs) > 1){
    echo "Too many arguments...";
    exit(2);
}


# Download, verify and decode directory.json
$list_json = file_get_contents($APILISTFILE);
$list_php = validateJson($list_json);

# Select community API link from directory.json or use command line argument
if (isset($command_apifile))
    $apifile = $command_apifile;
else
    $apifile = $list_php->{$COMMUNITY};

# Download, verify and decode nodes list
$map_json = file_get_contents($NODELIST);
$map_php = validateJson($map_json);

# Count all nodes
$nodes = 0;
foreach($map_php->{'nodes'} as $node) {
    if (!($node->{'flags'}->{'gateway'}) && ($node->{'flags'}->{'online'}))
		$nodes++;
}

# Download, verify and decode API json file
$api_json = file_get_contents ($apifile);
$json_php = validateJson($api_json);

# Update nodes number
$json_php->{'state'}->{'nodes'} = $nodes;

# Update timestamp
$json_php->{'state'}->{'lastchange'} = date("c");

# Validate output data
if (!isset($json_php->{'api'})) {
	echo "No API version set. Impossible to validate.\n";
	die;
}
$SCHEMALINK="https://github.com/freifunk/api.freifunk.net/raw/master/specs/" . $json_php->{'api'} . ".json";
$schema_json = file_get_contents ($SCHEMALINK);
$schema_php = validateJson($schema_json);
if (!isset($schema_php->{'schema'})) {
	echo "No API schema detected. Impossible to validate.\n";
	die;
}

$validator = new JsonSchema\Validator();
$validator->check($json_php, $schema_php->{'schema'});
if (!($validator->isValid())) {
	echo "JSON does not validate. Violations:\n";
        foreach ($validator->getErrors() as $error) {
            echo sprintf("[%s] %s\n", $error['property'], $error['message']);
        }
	die;
}

# Generate human readable json
$api_json = json_encode($json_php, JSON_PRETTY_PRINT);

# Write human readable json file
$json_string = file_put_contents ($APIOUTPUTFILE, $api_json);

# Generate and write minimized json file
$api_json = json_encode($json_php);
$json_string = file_put_contents ($APIMINOUTPUTFILE, $api_json);

?>

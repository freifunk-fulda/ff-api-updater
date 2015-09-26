<?php

/* 
 * refresh the number of active nodes in the freifunk fulda community.
 * the node information is gathered from the ffmap nodes.json file and
 * written to the freifunk.net API file.
 */

# disable SSL verification to prevent errors
$arrContextOptions=array(
    "ssl"=>array(
        "verify_peer"=>false,
        "verify_peer_name"=>false,
    ),
);

# json validation
function validateJson($json) {
	$json_test = json_decode($json);
	if (json_last_error() !== JSON_ERROR_NONE || $json_test === null) {
	  die "Contains invalid json data";
	}
}

# include configs
require (__DIR__ . '/config.php');

# Download, verify and decode directory.json
$list_json = file_get_contents($APILISTFILE, false, stream_context_create($arrContextOptions));
validateJson($list_json);
$list_php = json_decode($list_json);

# Select community API link from directory.json
$apifile = $list_php->{$COMMUNITY};

# Download, verify and decode nodes list
$map_json = file_get_contents($NODELIST, false, stream_context_create($arrContextOptions));
validateJson($map_json);
$map_php = json_decode($map_json);

# Count all nodes
$nodes = count((array)($map_php->{'nodes'}));

# Remove gateways and offline nodes
foreach($map_php->{'nodes'} as $node) {
    if ($node->{'flags'}->{'gateway'} == true)
        $nodes = $nodes -1;
    else if ($node->{'flags'}->{'online'} == false)
        $nodes = $nodes -1;
}

# Download, verify and decode API json file
$api_json = file_get_contents ($apifile, false, stream_context_create($arrContextOptions));
validateJson($map_json);
$json_php = json_decode($api_json);

# Update nodes number
$json_php->{'state'}->{'nodes'} = $nodes;

# Generate human readable json
$api_json = json_encode($json_php, JSON_PRETTY_PRINT);

# Validate output data
validateJson($api_json);

# Write human readable json file
$json_string = file_put_contents ($APIOUTPUTFILE, $api_json);

# Generate and write minimized json file
$api_json = json_encode($json_php);
$json_string = file_put_contents ($APIMINOUTPUTFILE, $api_json);

?>

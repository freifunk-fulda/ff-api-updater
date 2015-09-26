<?php
 
/* 
 * refresh the number of active nodes in the freifunk fulda community.
 * the node information is gathered from the ffmap nodes.json file and
 * written to the freifunk.net API file.
 */
 
$arrContextOptions=array(
    "ssl"=>array(
        "verify_peer"=>false,
        "verify_peer_name"=>false,
    ),
);

require (__DIR__ . '/config.php');

$list_json = file_get_contents($APILISTFILE, false, stream_context_create($arrContextOptions));
$list_php = json_decode($list_json);

$apifile = $list_php->{$COMMUNITY};

$map_json = file_get_contents($NODELIST, false, stream_context_create($arrContextOptions));
$map_php = json_decode($map_json);

$nodes = count((array)($map_php->{'nodes'}));

foreach($map_php->{'nodes'} as $node) {
    if ($node->{'flags'}->{'gateway'} == true)
        $nodes = $nodes -1;
    else if ($node->{'flags'}->{'online'} == false)
        $nodes = $nodes -1;
}

$api_json = file_get_contents ($apifile, false, stream_context_create($arrContextOptions));
$json_php = json_decode($api_json);

$json_php->{'state'}->{'nodes'} = $nodes;

$api_json = json_encode($json_php, JSON_PRETTY_PRINT);
$json_string = file_put_contents ($APIOUTPUTFILE, $api_json);

$api_json = json_encode($json_php);
$json_string = file_put_contents ($APIMINOUTPUTFILE, $api_json);

?>

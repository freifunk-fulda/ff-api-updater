<?php
 
/* 
 * refresh the number of active nodes in the freifunk fulda community.
 * the node information is gathered from the ffmap nodes.json file and
 * written to the freifunk.net API file.
 */
 
$NODELIST = 'http://map.freifunk-fulda.de/nodes.json';
$APIFILE = '/path/to/FreifunkFulda-api.json';
 
$map_json = file_get_contents($NODELIST);
$map_php = json_decode($map_json);
 
$nodes = count($map_php->{'nodes'});
 
foreach($map_php->{'nodes'} as $node) {
    if ($node->{'flags'}->{'gateway'} == true)
        $nodes = $nodes -1;
    else if ($node->{'flags'}->{'online'} == false)
        $nodes = $nodes -1;
}
 
$api_json = file_get_contents ($APIFILE);
$json_php = json_decode($api_json);
 
$json_php->{'state'}->{'nodes'} = $nodes;
 
$api_json = json_encode($json_php);
$json_string = file_put_contents ($APIFILE, $api_json);
 
?>

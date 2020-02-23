<?php

/*
	Parse HLDS status output
*/
function parse_hlds_status($StatusString)
{
	// parse everything up to the player list
	$StatusArray = preg_split("/(\r\n|\n|\r)/", $StatusString);
	
	$out['hostname'] = array_shift($StatusArray);
	$out['version'] = array_shift($StatusArray);
	$out['tcp/ip'] = array_shift($StatusArray);
	$out['map'] = array_shift($StatusArray);
	$out['maxplayers'] = array_shift($StatusArray);
	
	array_shift($StatusArray); // Get rid of blank line
	
	// Parse player list
	$PlayerListString = $StatusArray; // Including header row
	array_pop($PlayerListString);
	
	// Parse headers
	$header = explode_by_whitespace(array_shift($PlayerListString));
	
	// Parse players
	$PlayerList = array_map('parse_player_line', $PlayerListString);
	array_walk($PlayerList, '_combine_array', $header);
	
	$out['Players'] = $PlayerList;
	
	return $out;
}

// Define private functions
function _combine_array(&$row, $key, $header)
{
	$row = array_combine($header, $row);
}

function parse_player_line($string)
{
	$temp = array();
	$temp[0] = trim(substr($string, 1, 2)); // Get the fixed-width player slot number
	$temp = array_merge($temp, explode_by_whitespace(substr($string, 4))); // The reset is whitespace delimited (not space delimited)
	return $temp;
}

function explode_by_whitespace($string)
{
	$pattern = '/[^\s"\']+|"([^"]*)"|\'([^\']*)\'/';
	$matches = array();
	$i = 0;
	$out = array();
	while( preg_match($pattern, $string, $matches, PREG_OFFSET_CAPTURE, $i) )
	{
		array_push($out, $matches[0][0]);
		$i = $matches[0][1] + strlen($matches[0][0]);
	}
	return $out;
}

?>

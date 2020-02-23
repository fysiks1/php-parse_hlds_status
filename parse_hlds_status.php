<?php

/*
	Parse HLDS status output
*/
function parse_hlds_status($StatusString)
{
	// Convert to an array
	$StatusArray = preg_split("/(\r\n|\n|\r)/", $StatusString);
	
	// Parse server parameters
	$matches = array();
	preg_match("/hostname\s*:\s*(.*)$/", trim(array_shift($StatusArray)), $matches);
	$out['hostname'] = $matches[1];
	preg_match("/version\s*:\s*(.*)$/", trim(array_shift($StatusArray)), $matches);
	$out['version'] = $matches[1];
	preg_match("/tcp\/ip\s*:\s*(.*)$/", trim(array_shift($StatusArray)), $matches);
	$out['tcp/ip'] = $matches[1];
	preg_match("/map\s*:\s*([\w-]+).*$/", trim(array_shift($StatusArray)), $matches);
	$out['map'] = $matches[1];
	preg_match("/players\s*:\s*(\d+).*\((\d+).*\)$/", trim(array_shift($StatusArray)), $matches);
	$PlayerCount = $matches[1];
	$out['maxplayers'] = $matches[2];
	
	array_shift($StatusArray);
	
	// Parse header
	$header = explode_by_whitespace(array_shift($StatusArray));

	// Parse player table
	$PlayerListString = array_slice($StatusArray, 0, $PlayerCount);
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
	$temp = array_merge($temp, explode_by_whitespace(substr($string, 4))); // The rest is whitespace delimited (not space delimited)
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

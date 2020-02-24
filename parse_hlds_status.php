<?php

/*
*	Parse HLDS status output
*/
function parse_hlds_status($StatusString)
{
	/*
	*	Parse server parameters
	*/
	
	$matches = array();
	preg_match("/^hostname\s*:\s*(.*)$/im", $StatusString, $matches);
	$out['hostname'] = $matches[1];
	preg_match("/version\s*:\s*(.*)$/im", $StatusString, $matches);
	$out['version'] = $matches[1];
	preg_match("/tcp\/ip\s*:\s*(.*)/im", $StatusString, $matches);
	$out['tcp/ip'] = $matches[1];
	preg_match("/map\s*:\s*([\w-]+).*$/im", $StatusString, $matches);
	$out['map'] = $matches[1];
	preg_match("/players\s*:\s*(\d+).*\((\d+).*\)$/im", $StatusString, $matches);
	$PlayerCount = $matches[1];
	$out['maxplayers'] = $matches[2];
	
	
	/*
	*	Parse player data
	*	- locate # at begining of line to signify start of table
	*	- locate second player count "\d+ users" which is the end of the table
	*	- parse the table
	*/
	
	// Isolate player data table
	preg_match("/^#/im", $StatusString, $matches,  PREG_OFFSET_CAPTURE);
	$iStart = $matches[0][1];
	preg_match("/^(\d+)\s+users\s*$/im", $StatusString, $matches, PREG_OFFSET_CAPTURE);
	$iEnd = $matches[0][1];
	$PlayerDataString = trim(substr($StatusString, $iStart, $iEnd - $iStart));
	
	// Convert to an array
	$PlayerDataArray = preg_split("/(\r\n|\n|\r)/", $PlayerDataString);
	
	// Parse header
	$header = explode_by_whitespace(array_shift($PlayerDataArray));

	// Parse player data
	$PlayerListString = array_slice($PlayerDataArray, 0, $PlayerCount);
	$PlayerList = array_map('parse_player_line', $PlayerListString);
	array_walk($PlayerList, '_combine_array', $header);
	$out['players'] = $PlayerList;
	
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
	$pattern = "/[\s,]*\\\"([^\\\"]+)\\\"[\s,]*|" . "[\s,]*'([^']+)'[\s,]*|" . "[\s,]+/";
	return preg_split($pattern, $string, 0, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
}

?>

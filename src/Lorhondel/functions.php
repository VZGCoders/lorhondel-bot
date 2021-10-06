<?php

/*
 * This file is a part of the Lorhondel project.
 *
 * Copyright (c) 2021-present Valithor Obsidion <valzargaming@gmail.com>
 */
 
namespace Lorhondel;
 
use Discord\Parts\Channel\Channel;
use Discord\Parts\Channel\Message;
use Discord\Parts\Guild\Role;
use Lorhondel\Parts\Part;
use Lorhondel\Parts\Player\Player;
use Lorhondel\Parts\Party\Party;
use Discord\Parts\User\Member;
use Discord\Parts\User\User;
use Symfony\Component\OptionsResolver\Options;

 /**
 * The Lorhondel EPOCH, the first second of 2021 (GMT -4).
 *
 * @int Lorhondel EPOCH.
 */
const EPOCH = 1609473600000; //Milliseconds
 
 /**
 * The HTML Color Table.
 *
 * @array HTML Color Table.
 */
 
 
/*
*********************
*********************
DiscordPHP
*********************
*********************
*/
 
const COLORTABLE = [
    'indianred' => 0xcd5c5c, 'lightcoral' => 0xf08080, 'salmon' => 0xfa8072, 'darksalmon' => 0xe9967a,
    'lightsalmon' => 0xffa07a, 'crimson' => 0xdc143c, 'red' => 0xff0000, 'firebrick' => 0xb22222,
    'darkred' => 0x8b0000, 'pink' => 0xffc0cb, 'lightpink' => 0xffb6c1, 'hotpink' => 0xff69b4,
    'deeppink' => 0xff1493, 'mediumvioletred' => 0xc71585, 'palevioletred' => 0xdb7093,
    'lightsalmon' => 0xffa07a, 'coral' => 0xff7f50, 'tomato' => 0xff6347, 'orangered' => 0xff4500,
    'darkorange' => 0xff8c00, 'orange' => 0xffa500, 'gold' => 0xffd700, 'yellow' => 0xffff00,
    'lightyellow' => 0xffffe0, 'lemonchiffon' => 0xfffacd, 'lightgoldenrodyellow' => 0xfafad2,
    'papayawhip' => 0xffefd5, 'moccasin' => 0xffe4b5, 'peachpuff' => 0xffdab9, 'palegoldenrod' => 0xeee8aa,
    'khaki' => 0xf0e68c, 'darkkhaki' => 0xbdb76b, 'lavender' => 0xe6e6fa, 'thistle' => 0xd8bfd8,
    'plum' => 0xdda0dd, 'violet' => 0xee82ee, 'orchid' => 0xda70d6, 'fuchsia' => 0xff00ff,
    'magenta' => 0xff00ff, 'mediumorchid' => 0xba55d3, 'mediumpurple' => 0x9370db, 'rebeccapurple' => 0x663399,
    'blueviolet' => 0x8a2be2, 'darkviolet' => 0x9400d3, 'darkorchid' => 0x9932cc, 'darkmagenta' => 0x8b008b,
    'purple' => 0x800080, 'indigo' => 0x4b0082, 'slateblue' => 0x6a5acd, 'darkslateblue' => 0x483d8b,
    'mediumslateblue' => 0x7b68ee, 'greenyellow' => 0xadff2f, 'chartreuse' => 0x7fff00, 'lawngreen' => 0x7cfc00,
    'lime' => 0x00ff00, 'limegreen' => 0x32cd32, 'palegreen' => 0x98fb98, 'lightgreen' => 0x90ee90,
    'mediumspringgreen' => 0x00fa9a, 'springgreen' => 0x00ff7f, 'mediumseagreen' => 0x3cb371,
    'seagreen' => 0x2e8b57, 'forestgreen' => 0x228b22, 'green' => 0x008000, 'darkgreen' => 0x006400,
    'yellowgreen' => 0x9acd32, 'olivedrab' => 0x6b8e23, 'olive' => 0x808000, 'darkolivegreen' => 0x556b2f,
    'mediumaquamarine' => 0x66cdaa, 'darkseagreen' => 0x8fbc8b, 'lightseagreen' => 0x20b2aa,
    'darkcyan' => 0x008b8b, 'teal' => 0x008080, 'aqua' => 0x00ffff, 'cyan' => 0x00ffff, 'lightcyan' => 0xe0ffff,
    'paleturquoise' => 0xafeeee, 'aquamarine' => 0x7fffd4, 'turquoise' => 0x40e0d0, 'mediumturquoise' => 0x48d1cc,
    'darkturquoise' => 0x00ced1, 'cadetblue' => 0x5f9ea0, 'steelblue' => 0x4682b4, 'lightsteelblue' => 0xb0c4de,
    'powderblue' => 0xb0e0e6, 'lightblue' => 0xadd8e6, 'skyblue' => 0x87ceeb, 'lightskyblue' => 0x87cefa,
    'deepskyblue' => 0x00bfff, 'dodgerblue' => 0x1e90ff, 'cornflowerblue' => 0x6495ed,
    'mediumslateblue' => 0x7b68ee, 'royalblue' => 0x4169e1, 'blue' => 0x0000ff, 'mediumblue' => 0x0000cd,
    'darkblue' => 0x00008b, 'navy' => 0x000080, 'midnightblue' => 0x191970, 'cornsilk' => 0xfff8dc,
    'blanchedalmond' => 0xffebcd, 'bisque' => 0xffe4c4, 'navajowhite' => 0xffdead, 'wheat' => 0xf5deb3,
    'burlywood' => 0xdeb887, 'tan' => 0xd2b48c, 'rosybrown' => 0xbc8f8f, 'sandybrown' => 0xf4a460,
    'goldenrod' => 0xdaa520, 'darkgoldenrod' => 0xb8860b, 'peru' => 0xcd853f, 'chocolate' => 0xd2691e,
    'saddlebrown' => 0x8b4513, 'sienna' => 0xa0522d, 'brown' => 0xa52a2a, 'maroon' => 0x800000,
    'white' => 0xffffff, 'snow' => 0xfffafa, 'honeydew' => 0xf0fff0, 'mintcream' => 0xf5fffa, 'azure' => 0xf0ffff,
    'aliceblue' => 0xf0f8ff, 'ghostwhite' => 0xf8f8ff, 'whitesmoke' => 0xf5f5f5, 'seashell' => 0xfff5ee,
    'beige' => 0xf5f5dc, 'oldlace' => 0xfdf5e6, 'floralwhite' => 0xfffaf0, 'ivory' => 0xfffff0,
    'antiquewhite' => 0xfaebd7, 'linen' => 0xfaf0e6, 'lavenderblush' => 0xfff0f5, 'mistyrose' => 0xffe4e1,
    'gainsboro' => 0xdcdcdc, 'lightgray' => 0xd3d3d3, 'silver' => 0xc0c0c0, 'darkgray' => 0xa9a9a9,
    'gray' => 0x808080, 'dimgray' => 0x696969, 'lightslategray' => 0x778899, 'slategray' => 0x708090,
    'darkslategray' => 0x2f4f4f, 'black' => 0x000000,
];

/**
 * Checks to see if a part has been mentioned.
 *
 * @param Part|string $part    The part or mention to look for.
 * @param Message     $message The message to check.
 *
 * @return bool Whether the part was mentioned.
 */
function mentioned($part, Message $message): bool
{
    if ($part instanceof User || $part instanceof Member) {
        return $message->mentions->has($part->id);
    } elseif ($part instanceof Player) {
		return ($message->mentions->has($part->user->id) || strpos($message->content, "<@${$part->id}>") !== false);
	} elseif ($part instanceof Role) {
        return $message->mention_roles->has($part->id);
    } elseif ($part instanceof Channel) {
        return strpos($message->content, "<#{$part->id}>") !== false;
    }

    return strpos($message->content, $part) !== false;
}

/**
 * Get int value for color.
 *
 * @param int|string $color The color's int, hexcode or htmlname.
 *
 * @return int color
 */
function getColor($color = 0): int
{
    if (is_integer($color)) {
        return $color;
    }

    if (preg_match('/^([a-z]+)$/ui', $color, $match)) {
        $colorName = strtolower($match[1]);
        if (isset(COLORTABLE[$colorName])) {
            return COLORTABLE[$colorName];
        }
    }

    if (preg_match('/^(#|0x|)([0-9a-f]{6})$/ui', $color, $match)) {
        return hexdec($match[2]);
    }

    return 0;
}

/**
 * Checks if a string contains an array of phrases.
 *
 * @param string $string  The string to check.
 * @param array  $matches Array containing one or more phrases to match.
 *
 * @return bool
 */
function contains(string $string, array $matches): bool
{
    foreach ($matches as $match) {
        if (strpos($string, $match) !== false) {
            return true;
        }
    }

    return false;
}

/**
 * Converts a string to studlyCase.
 *
 * @param string $string The string to convert.
 *
 * @return string
 */
function studly(string $string): string
{
    $ret = '';
    preg_match_all('/([a-z0-9]+)/ui', $string, $matches);

    foreach ($matches[0] as $match) {
        $ret .= ucfirst(strtolower($match));
    }

    return $ret;
}

/**
 * Polyfill to check if mbstring is installed.
 *
 * @param string $str
 *
 * @return int
 */
function poly_strlen($str)
{
    // If mbstring is installed, use it.
    if (function_exists('mb_strlen')) {
        return mb_strlen($str);
    }

    return strlen($str);
}

/**
 * Converts a file to base64 representation.
 *
 * @param string $filepath
 *
 * @return string
 */
function imageToBase64(string $filepath): string
{
    if (! file_exists($filepath)) {
        throw new \InvalidArgumentException('The given filepath does not exist.');
    }

    $mimetype = \mime_content_type($filepath);

    if (array_search($mimetype, ['image/jpeg', 'image/png', 'image/gif']) === false) {
        throw new \InvalidArgumentException('The given filepath is not one of jpeg, png or gif.');
    }

    $contents = file_get_contents($filepath);

    return "data:{$mimetype};base64,".base64_encode($contents);
}

 /**
 * Takes a snowflake and calculates the time that the snowflake
 * was generated.
 *
 * @param string|int $snowflake
 *
 * @return int
 */
function getSnowflakeTimestamp(string $snowflake)
{
    if (\PHP_INT_SIZE === 4) { //x86
        $binary = \str_pad(\base_convert($snowflake, 10, 2), 64, 0, \STR_PAD_LEFT);
        $time = \base_convert(\substr($binary, 0, 42), 2, 10);
        $timestamp = (float) ((((int) \substr($time, 0, -3)) + EPOCH).'.'.\substr($time, -3));
        $workerID = (int) \base_convert(\substr($binary, 42, 5), 2, 10);
        $processID = (int) \base_convert(\substr($binary, 47, 5), 2, 10);
        $increment = (int) \base_convert(\substr($binary, 52, 12), 2, 10);
    } else { //x64
        $snowflake = (int) $snowflake;
        $time = (string) ($snowflake >> 22);
        $timestamp = (float) ((((int) \substr($time, 0, -3)) + EPOCH).'.'.\substr($time, -3));
        $workerID = ($snowflake & 0x3E0000) >> 17;
        $processID = ($snowflake & 0x1F000) >> 12;
        $increment = ($snowflake & 0xFFF);
    }
    if ($timestamp < EPOCH || $workerID < 0 || $workerID >= 32 || $processID < 0 || $processID >= 32 || $increment < 0 || $increment >= 4096) {
        return null;
    }

    return $timestamp;
}

/**
 * Generates a snowflake
 *
 * @param string|int $snowflake
 *
 * @return int
 */
function generateSnowflake(Lorhondel $lorhondel, $time = null, $workerID = null, $processID = null, $increment = null)
{
	$processID = $processID ?? $lorhondel->processID;
	$workerID = $workerID ?? $lorhondel->workerID;
	$increment = $increment ?? $lorhondel->increment;
	$lorhondel->increment++;
	if (! $time) $timeSinceEpoch = (time() . '000') - EPOCH;
	else $timeSinceEpoch = ($time . '000') - EPOCH;
	$snowflake = ($timeSinceEpoch << 22) | (($workerID & 0x1F) << 17) | (($processID & 0x1F) << 12) | ($increment & 0xFFF);
	return (int) $snowflake;
}

/**
 * For use with the Symfony options resolver.
 * For an option that takes a snowflake or part,
 * returns the snowflake or the value of `id_field`
 * on the part.
 *
 * @param string $id_field
 *
 * @internal
 */
function normalizePartId($id_field = 'id')
{
    return static function (Options $options, $part) use ($id_field) {
        if ($part instanceof Part) {
            return $part->{$id_field};
        }

        return $part;
    };
}

/**
 * Escape various Discord formatting and markdown into a plain text:
 * _Italics_, **Bold**, __Underline__, ~~Strikethrough~~, ||spoiler||
 * `Code`, ```Code block```, > Quotes, >>> Block quotes
 * #Channel @User
 * A backslash will be added before the each formatting symbol
 * 
 * @return string the escaped string unformatted as plain text
 */
function escapeMarkdown(string $text): string
{
    return addcslashes($text, '#*:>@_`|~');
}

function snowflake_timestamp($snowflake)
{
    if (\PHP_INT_SIZE === 4) { //x86
        $binary = \str_pad(\base_convert($snowflake, 10, 2), 64, 0, \STR_PAD_LEFT);
        $time = \base_convert(\substr($binary, 0, 42), 2, 10);
        $timestamp = (float) ((((int) \substr($time, 0, -3)) + 1420070400).'.'.\substr($time, -3));
        $workerID = (int) \base_convert(\substr($binary, 42, 5), 2, 10);
        $processID = (int) \base_convert(\substr($binary, 47, 5), 2, 10);
        $increment = (int) \base_convert(\substr($binary, 52, 12), 2, 10);
    } else { //x64
        $snowflake = (int) $snowflake;
        $time = (string) ($snowflake >> 22);
        $timestamp = (float) ((((int) \substr($time, 0, -3)) + 1420070400).'.'.\substr($time, -3));
        $workerID = ($snowflake & 0x3E0000) >> 17;
        $processID = ($snowflake & 0x1F000) >> 12;
        $increment = ($snowflake & 0xFFF);
    }
    if ($timestamp < 1420070400 || $workerID < 0 || $workerID >= 32 || $processID < 0 || $processID >= 32 || $increment < 0 || $increment >= 4096) {
        return null;
    }
    return $timestamp;
}

/*
*********************
*********************
SQL 
*********************
*********************
*/

function json_validate($data)
{
	if (is_array($data) || is_object($data))
		$data = json_encode($data);
    // decode the JSON data
		
    $result = json_decode($data);

    // switch and check possible JSON errors
    switch (json_last_error()) {
        case JSON_ERROR_NONE:
            $error = ''; // JSON is valid // No error has occurred
            break;
        case JSON_ERROR_DEPTH:
            $error = 'The maximum stack depth has been exceeded.';
            break;
        case JSON_ERROR_STATE_MISMATCH:
            $error = 'Invalid or malformed JSON.';
            break;
        case JSON_ERROR_CTRL_CHAR:
            $error = 'Control character error, possibly incorrectly encoded.';
            break;
        case JSON_ERROR_SYNTAX:
            $error = 'Syntax error, malformed JSON.';
            break;
        // PHP >= 5.3.3
        case JSON_ERROR_UTF8:
            $error = 'Malformed UTF-8 characters, possibly incorrectly encoded.';
            break;
        // PHP >= 5.5.0
        case JSON_ERROR_RECURSION:
            $error = 'One or more recursive references in the value to be encoded.';
            break;
        // PHP >= 5.5.0
        case JSON_ERROR_INF_OR_NAN:
            $error = 'One or more NAN or INF values in the value to be encoded.';
            break;
        case JSON_ERROR_UNSUPPORTED_TYPE:
            $error = 'A value of a type that cannot be encoded was given.';
            break;
        default:
            $error = 'Unknown JSON error occured.';
            break;
    }

    if ($error !== '') {
        echo '[JSON ERROR] '. $error . PHP_EOL;
    }
    // everything is OK
	//echo '[JSON OKAY]' . PHP_EOL; //var_dump ($result);
    return $result;
}
function sqlGet(array $columns = [], string $table = '', string $wherecolumn = '', array $values = [], string $order = '', $limit = ''): array
{
	//sqlGet(['*'], $repository, '', [], '', 500); //get all
	if (empty($columns)) return [];
	if (! $table) return [];
	include 'connect.php'; //$mysqli and $pdo
	$array = array();
	
	$sql = "SELECT ";
	for($x=0;$x<count($columns);$x++)
		if ($x<count($columns)-1) $sql .= $columns[$x] . ', ';
		else $sql .= $columns[$x] . ' ';
	$sql .= "FROM $table";
	if ($wherecolumn && !empty($values)) {
		$sql .= " WHERE $wherecolumn = ?";
	}
	if ($order) $sql .= " ORDER BY $order";
	if ($limit) $sql .= " LIMIT $limit";
	echo '[SQL] ' . $sql . PHP_EOL;
	$value_string = '(';
	foreach ($values as $value) {
		//if ($value !== null) {
			//if ($value == false) $value = '0';
			$value_string .= "$value, ";
		//}
	}
	$value_string = substr($value_string, 0, strlen($value_string)-2) . ')';
	echo $value_string . PHP_EOL;
	
	if (! $wherecolumn) {
		$stmt = mysqli_prepare($mysqli, $sql); //Select all values in the column
		$stmt->execute();
		if ($result = $stmt->get_result()) {
			while ($rows = $result->fetch_all(MYSQLI_ASSOC)) {
				foreach ($rows as $row) {
					foreach ($row as $r => $v) {
						$array[$row['id']][$r] = $v;
					}
				}
			}
			
		} else {
			var_dump (mysqli_stmt_error($stmt));
			return [];
		}
	}
	elseif ($wherecolumn && !empty($values)) {
		if ($stmt = mysqli_prepare($mysqli, $sql)) {
			$stmt->bind_param("s", $value);
			foreach ($values as $value) {
				$stmt->execute();
				if ($result = $stmt->get_result()) {
					while ($rows = $result->fetch_all(MYSQLI_ASSOC)) {
						foreach ($rows as $row) {
							foreach ($row as $r => $v) {
								$array[$row['id']][$r] = $v;
							}
						}
					}
				} else {
					var_dump(mysqli_stmt_error($stmt));
					return [];
				}
			}
		} 
	}
	echo '[GET ARRAY]'; var_dump($array);
	return $array;
}
function sqlCreate(string $table, $data)
{
	include 'connect.php';
	if (is_object($data))
		$string = json_encode($data);
	echo '[DATA]' . PHP_EOL;
	var_dump ($data);
	//$data = json_decode(json_encode($data), true); //var_dump($data);
	$types = '';
	$values_clean = array();
	if (!empty($data)) {
		$sql = "INSERT INTO $table (";
		foreach ($data as $key => $value) {
			$sql .= $key . ', ';
		}
		$sql = substr($sql, 0, strlen($sql)-2) . ') VALUES (';
		foreach ($data as $key => $value) {
			$sql .= '?, ';
			//$types .= 's';
			$value = $value; //Remove any _ from variable names
			$values_clean[] = $value;
			//$sql .= "$value, ";
		}
		$sql = substr($sql, 0, strlen($sql)-2) . ')';
	} else return false;
	echo '[SQL] ' . $sql . PHP_EOL;
	echo '[VALUES_CLEAN] '; var_dump($values_clean);
	
	if ($stmt = $PDO->prepare($sql)) {
		if ($stmt->execute($values_clean)) return true;
		else echo mysqli_stmt_error($stmt);
	} else echo mysqli_stmt_error($stmt);
	return false;
}
function sqlUpdate(array $columns = [], array $values = [], string $table, string $wherecolumn = '', $target = '')
{
	echo '[UPDATE COLUMNS]'; var_dump($columns);
	echo '[UPDATE VALUES]'; var_dump($values);
	
	if (empty($columns)) return false;
	if (! $table) return false;
	if (count($columns) != count($values)) return false;
	include 'connect.php';
	
	$sql = "UPDATE $table SET ";
	for($x=0;$x<count($columns);$x++)
	{
		if ($x<count($columns)-1) $sql .= "{$columns[$x]} = ?, "; // {$values[$x]}
		else $sql .= "{$columns[$x]} = ?"; //{$values[$x]}
	}
	if ($wherecolumn && $target) {
		$sql .= " WHERE $wherecolumn = '$target'";
	}
	echo '[SQL] ' . $sql . PHP_EOL;
	$value_string = '(';
	foreach ($values as $value) {
		$value_string .= "$value, ";
	}
	$value_string = substr($value_string, 0, strlen($value_string)-2) . ')';
	echo $value_string . PHP_EOL;

	if ($stmt = $PDO->prepare($sql)) {
		if ($stmt->execute($values)) return true;
		else echo mysqli_stmt_error($stmt);
	} else echo mysqli_stmt_error($stmt);
	return false;
}
function sqlDelete(string $table, string $wherecolumn = '', array $values = [], string $order = '', int|string $limit = '')
{
	include 'connect.php';
	$array = array();
	
	$sql = "DELETE FROM $table";
	if ($wherecolumn && !empty($values)) {
		$sql .= " WHERE $wherecolumn = ?";
	}
	if ($order) $sql .= " ORDER BY $order";
	if ($limit) $sql .= " LIMIT $limit";
	echo '[SQL] ' . $sql . PHP_EOL;
	$value_string = '(';
	foreach ($values as $value) {
		$value_string .= "$value, ";
	}
	$value_string = substr($value_string, 0, strlen($value_string)-2) . ')';
	echo $value_string . PHP_EOL;
	
	if ($stmt = $PDO->prepare($sql)) {
		if ($stmt->execute($values))	 return true;
		else echo mysqli_stmt_error($stmt);
	} else echo mysqli_stmt_error($stmt);
	return false;
}

/**
 * Creates a part from an array of data and add it to the relevant repository.
 * Intended to be used in conjunction with another function that alters data in SQL.
 *
 * @return Part
 */
function partPusher($lorhondel, $repository, $part_name, $array)
{
	$part = null;
	foreach ($array as $data) { //Create all into parts and push
		if ($attributes = json_decode(json_encode(json_validate($data)), true)) {
			if ($part = $lorhondel->factory($part_name, $attributes)) {
				if ($lorhondel->$repository->offsetGet($part->id))
					$lorhondel->$repository->pull($part->id);
				$lorhondel->$repository->push($part);
			}
		}
	}
	return $part;
}
function getCurrentPlayer($lorhondel, $user_id)
{
	if (count($collection = $lorhondel->players->filter(fn($p) => $p->user_id == $user_id && $p->active == 1 )) > 0) {
		//echo '[FOUND ACTIVE CACHED PLAYER]'; //var_dump($collection);
		foreach ($collection as $player) //There should only be one
			return $player;
	}
	
	//No active Player part was found, so check SQL to make sure
	include 'connect.php';
	$sql = "SELECT * FROM players WHERE user_id = ? AND active = 1";
	$get = array();
	$part = null;
	if ($stmt = $PDO->prepare($sql))
		if ($stmt->execute([$user_id]))
			if ($result = $stmt->fetchAll())
				$get = $result;
	echo '[getCurrentPlayer]'; var_dump($get);
	if (! empty($get)) {
		$part = partPusher($lorhondel, 'players', '\Lorhondel\Parts\Player\Player', $get);
		echo '[getCurrentPlayer2]'; var_dump($part);
		return $part;
	} else return null;
}
function getCurrentParty($lorhondel, $id)
{
	if (count($collection = $lorhondel->parties->filter(fn($p) => $p->player1 == $id || $p->player2 == $id || $p->player3 == $id || $p->player4 == $id || $p->player5 == $id))>0) {
		foreach ($collection as $party) { //There should only be one
			if ($player = $lorhondel->players->offsetGet($id)) {
				if ($player->party_id === null) {
					$player->party_id = $party->id;
					$lorhondel->players->save($player);
				}
			}
			return $party;
		}
	}
	
	//No Party part for the Player was found, so check SQL to make sure
	include 'connect.php';
	$part = null;
	$sql = "SELECT * FROM parties WHERE ? in (player1, player2, player3, player4, player5)";
	if ($stmt = $PDO->prepare($sql))
		if ($stmt->execute([$id]))
			if ($result = $stmt->fetchAll())
				$part = partPusher($lorhondel, 'parties', '\Lorhondel\Parts\Party\Party', $result);
	return $part ?? false;
}
function getPlayerLocation($lorhondel)
{
	//
}
/*
Returns true if Party exists and is not full
Returns null if Party is not found or an invalid parameter was passed
Returns false if Party is full
*/
function isPartyJoinable($part, $lorhondel = null): bool
{
	if ($part instanceof Party) {
		$party = $part;
		$id = $part->id;
	}
	elseif ($part instanceof Player)
		if ($player->party_id !== null)
			$id = $player->party_id;
	elseif (is_numeric($part)) $id = $part;
	//else return null; //Internal function should not allow passing of invalid parameter
	
	if ($party = $party ?? $lorhondel->parties->offsetGet($id)) {
		if (! $party->player1 || ! $party->player2 || ! $party->player3 || ! $party->player4 || ! $party->player5)
			return true;
		else return false;
	}// else return null;  //Internal function should not allow passing of invalid parameter
	return false;
}

function playerEmbed($lorhondel, $player)
{
	$embed = $lorhondel->discord->factory(\Discord\Parts\Embed\Embed::class);
	$embed->setColor(0xe1452d)
	//	->setDescription('$author_guild_name') // Set a description (below title, above fields)
	//	->setImage('https://avatars1.githubusercontent.com/u/4529744?s=460&v=4') // Set an image (below everything except footer)
		->setTimestamp()
		->setFooter('Lonhondel by ArtsyAxolotl#5128')
		->setURL('');
	if ($player->name) $embed->addFieldValues('Name', $player->name, true);
	$embed->addFieldValues('Species', $player->species, true);
	if ($player->party_id) $embed->addFieldValues('Party ID', $player->party_id, true);
	if ($party = $lorhondel->parties->offsetGet($player->party_id))
		if ($party->name) $embed->addFieldValues('Party Name', $party->name, true);
	$embed->addFieldValues('ID', $player->id, false);
	$embed	
		->addFieldValues('Health', $player->health, true)
		->addFieldValues('Attack', $player->attack, true)
		->addFieldValues('Defense', $player->defense, true)
		->addFieldValues('Speed', $player->speed, true)
		->addFieldValues('Skill Points', $player->skillpoints, true);
	if ($user = $lorhondel->discord->users->offsetGet($player->user_id)) {
		$embed->setAuthor("{$user->username} ({$user->id})", $user->avatar); // Set an author with icon
		$embed->setThumbnail("{$user->avatar}"); // Set a thumbnail (the image in the top right corner)
	}
	return $embed;
}
function partyEmbed($lorhondel, $party)
{
	echo '[CLASS]' . get_class($lorhondel->discord) . PHP_EOL;;
	$players = array();
	$players[] = $player1 = $lorhondel->players->offsetGet($party->player1);
	$players[] = $player2 = $lorhondel->players->offsetGet($party->player2);
	$players[] = $player3 = $lorhondel->players->offsetGet($party->player3);
	$players[] = $player4 = $lorhondel->players->offsetGet($party->player4);
	$players[] = $player5 = $lorhondel->players->offsetGet($party->player5);
	
	$embed = $lorhondel->discord->factory(\Discord\Parts\Embed\Embed::class);
	$embed->setColor(0xe1452d)
	//	->setDescription('$author_guild_name') // Set a description (below title, above fields)
	//	->setImage('https://avatars1.githubusercontent.com/u/4529744?s=460&v=4') // Set an image (below everything except footer)
		->setTimestamp()
		->setFooter('Lorhondel by ArtsyAxolotl#5128')                             					// Set a footer without icon
		->setURL('');                             												// Set the URL
	if ($party->name) $embed->addFieldValues('Name', $party->name, true);
	$embed->addFieldValues('ID', $party->id, true);
	foreach ($players as $player) {
		if ($player && $user = $lorhondel->discord->users->offsetGet($player->user_id)) {
			$embed->setAuthor("{$user->username} ({$user->id})", $user->avatar); // Set an author with icon
			if ($player->id == $party->{$party->leader}) {
			if ($player->name) $leader_string = "{$player->name} ({$player->id})";
			else $leader_string = "{$player->id}";
				$embed->addFieldValues('Leader', $leader_string, true);
				$embed->setThumbnail("{$user->avatar}"); // Set a thumbnail (the image in the top right corner)
			}
		}
	}
	$inline = false;
	for ($x=0; $x<count($players); $x++) {
		if ($players[$x]) {
			if ($players[$x]->name) $player_string = "{$players[$x]->name} ({$players[$x]->id})";
			else $player_string = "{$players[$x]->id}";
			$embed->addFieldValues('Player ' . $x+1, $player_string, $inline);
			$inline = true;
		}
	}
	return $embed;
}

/*
*********************
*********************
Filesystem 
*********************
*********************
*/

function getvar($array, $var)
{ //gamerbanner stuff
    if (array_key_exists($var, $array)) {
        return $array[$var];
    }
    return null;
}

//Checks if a directory contains any files
function is_dir_empty($dir)
{
    foreach (new DirectoryIterator($dir) as $fileInfo) {
        if ($fileInfo->isDot()) {
            continue;
        }
        return false;
    }
    return true;
}

//Checks if a folder exists and creates one if it doesn't
function CheckDir($foldername)
{
    //echo "CheckDir" . PHP_EOL;
    
    $path = __DIR__ . "/" .$foldername."/";
    $exist = false;
    //Create folder if it doesn't already exist
    if (!file_exists($path)) {
        mkdir($path, 0777, true);
        echo "NEW DIR CREATED: $path" . PHP_EOL;
    } else {
        $exist = true;
    }
    return $exist;
}

//Checks if a file exists
function CheckFile($foldername, $filename)
{
    if ($foldername !== null) {
        $folder_symbol = "/";
    } else $folder_symbol = "";
    //echo "CheckDir" . PHP_EOL;
    
    $path = __DIR__ . "/" .$foldername.$folder_symbol.$filename;
    //Create folder if it doesn't already exist
    if (file_exists($path))
		return true;
	return false;
}

//Saves a variable to a file
//Target is a full path, IE __DIR__ . "/" .target.php
function VarSave($foldername, $filename, $variable)
{
    if ($foldername !== null) {
        $folder_symbol = "/";
    } else $folder_symbol = "";
    //echo "VarSave" . PHP_EOL;
    
    $path = __DIR__ . "/" .$foldername.$folder_symbol; //echo "PATH: $path" . PHP_EOL;
    //Create folder if it doesn't already exist
    if (!file_exists($path)) {
        mkdir($path, 0777, true);
        echo "NEW DIR CREATED: $path" . PHP_EOL;
    }
    //Save variable to a file
    $serialized_variable = serialize($variable);
    file_put_contents($path.$filename, $serialized_variable);
}

//Loads a variable from a file
//Target is a full path, IE __DIR__ . "/" .target.php
function VarLoad($foldername, $filename)
{
    if ($foldername !== null) {
        $folder_symbol = "/";
    }else $folder_symbol = "";
    //echo "[VarLoad]" . PHP_EOL;
    
    $path = __DIR__ . "/" .$foldername.$folder_symbol; //echo "PATH: $path" . PHP_EOL;
    //Make sure the file exists
    if (!file_exists($path.$filename)) {
        return null;
    }
    //Load a variable from a file
    $loadedvar = file_get_contents($path.$filename); //echo "FULL PATH: $path$filename" . PHP_EOL;
    $unserialized = unserialize($loadedvar);
    return $unserialized;
}

function VarDelete($foldername, $filename)
{
    if ($foldername !== null) {
        $folder_symbol = "/";
    }else $folder_symbol = "";
    echo "VarDelete" . PHP_EOL;
    
    $path = __DIR__ . "/" .$foldername.$folder_symbol.$filename; //echo "PATH: $path" . PHP_EOL;
    //Make sure the file exists first
    if (CheckFile($foldername, $filename)) {
        //Delete the file
        unlink($path);
        clearstatcache();
    } else {
        echo "NO FILE TO DELETE" . PHP_EOL;
    }
}

/*
*********************
*********************
Timers and Cooldowns
*********************
*********************
*/

function TimeCompare($foldername, $filename)
{ //echo "foldername, filename: $foldername, $filename" . PHP_EOL;
    
    $then = VarLoad($foldername, $filename); //instance of now;
    //echo "then: " . PHP_EOL; var_dump ($then) . PHP_EOL;
    //check if file exists
    if ($then) {
        $sincetime = date_diff($now, $then);
        $timecompare['y'] = $sinceYear 		= $sincetime->y;
        $timecompare['m'] = $sinceMonth 	= $sincetime->m;
        $timecompare['d'] = $sinceDay 		= $sincetime->d;
        $timecompare['h'] = $sinceHour 		= $sincetime->h;
        $timecompare['i'] = $sinceMinute 	= $sincetime->i;
        $timecompare['s'] = $sinceSecond 	= $sincetime->s;
        echo 'Timer found to compare!' . PHP_EOL;
        return $timecompare;
    } else {
        //File not found, so return 0's
        $sincetime = date_diff($now, $now);
        $timecompare['y'] = $sinceYear 		= ($sincetime->y)+1; //Assume one year has passed, enough time to avoid any cooldown
        $timecompare['m'] = $sinceMonth 	= $sincetime->m;
        $timecompare['d'] = $sinceDay 		= $sincetime->d;
        $timecompare['h'] = $sinceHour 		= $sincetime->h;
        $timecompare['i'] = $sinceMinute 	= $sincetime->i;
        $timecompare['s'] = $sinceSecond 	= $sincetime->s;
        echo 'Timer not found to compare!' . PHP_EOL;
        //echo "timecompare: " . PHP_EOL; var_dump($timecompare) . PHP_EOL;
        return $timecompare;
    }
    //echo 'Timer not found to compare!' . PHP_EOL;
}

function TimeCompareMem($author_id, $variable)
{ //echo "foldername, filename: $foldername, $filename" . PHP_EOL;
    
    //$then = VarLoad($foldername, $filename); //instance of now;
    $varname = $author_id . $variable . "_cooldown"; //Check this
    $then = $GLOBALS["$varname"];
    //echo "then: " . PHP_EOL; var_dump ($then) . PHP_EOL;
    //check if file exists
    if ($then) {
        $sincetime = date_diff($now, $then);
        $timecompare['y'] = $sinceYear 		= $sincetime->y;
        $timecompare['m'] = $sinceMonth 	= $sincetime->m;
        $timecompare['d'] = $sinceDay 		= $sincetime->d;
        $timecompare['h'] = $sinceHour 		= $sincetime->h;
        $timecompare['i'] = $sinceMinute 	= $sincetime->i;
        $timecompare['s'] = $sinceSecond 	= $sincetime->s;
        echo 'Timer found to compare!' . PHP_EOL;
        return $timecompare;
    } else {
        //File not found, so return 0's
        $sincetime = date_diff($now, $now);
        $timecompare['y'] = $sinceYear 		= ($sincetime->y)+1; //Assume one year has passed, enough time to avoid any cooldown
        $timecompare['m'] = $sinceMonth 	= $sincetime->m;
        $timecompare['d'] = $sinceDay 		= $sincetime->d;
        $timecompare['h'] = $sinceHour 		= $sincetime->h;
        $timecompare['i'] = $sinceMinute 	= $sincetime->i;
        $timecompare['s'] = $sinceSecond 	= $sincetime->s;
        echo 'Timer not found to compare!' . PHP_EOL;
        //echo "timecompare: " . PHP_EOL; var_dump($timecompare) . PHP_EOL;
        return $timecompare;
    }
    //echo 'Timer not found to compare!' . PHP_EOL;
}

function TimeLimitCheck($time, $y, $m, $d, $h, $i, $s)
{
    //echo "time['s']: " . $time['s'] . PHP_EOL;
    if (! $time) {
        return true;
    } //Nothing to check, assume true
    if (! $y) {
        $y = 0;
    }//echo '$y: ' . $s . PHP_EOL;
    if (! $m) {
        $m = 0;
    }//echo '$m: ' . $s . PHP_EOL;
    if (! $d) {
        $d = 0;
    }//echo '$d: ' . $s . PHP_EOL;
    if (! $h) {
        $h = 0;
    }//echo '$h: ' . $s . PHP_EOL;
    if (! $i) {
        $i = 0;
    }//echo '$i: ' . $s . PHP_EOL;
    if (! $s) {
        $s = 0;
    }//echo '$s: ' . $s . PHP_EOL;
    //echo "time['y'] " . $time['y'] . PHP_EOL;
    //echo "time['m'] " . $time['m'] . PHP_EOL;
    //echo "time['d'] " . $time['d'] . PHP_EOL;
    //echo "time['h'] " . $time['h'] . PHP_EOL;
    //echo "time['i'] " . $time['i'] . PHP_EOL;
    //echo "time['s'] " . $time['s'] . PHP_EOL;
    //Calculate total number of seconds needed to continue.
    $required_time =
    ($s) +
    ($i * 60) +
    ($h * 3600) +
    ($d * 86400) +
    ($m * 2629746) +
    ($y * 31556952);
    //echo 'required_time: ' . $required_time . PHP_EOL;
    //Calculate total number of seconds passed.
    $passed_time =
    ($time['s']) +
    ($time['i'] * 60) +
    ($time['h'] * 3600) +
    ($time['d'] * 86400) +
    ($time['m'] * 2629746) +
    ($time['y'] * 31556952);
    //echo 'passed_time: ' . $passed_time . PHP_EOL;
    $return_array = array();
    if ($passed_time > $required_time) {
        $return_array[0] = true;
    } else {
        $return_array[0] = false;
    }
    $return_array[1] = $passed_time;
    return $return_array;
}

function PassedTimeCheck($y, $m, $d, $h, $i, $s)
{
    if (! $y) {
        $y = 0;
    }//echo '$y: ' . $s . PHP_EOL;
    if (! $m) {
        $m = 0;
    }//echo '$m: ' . $s . PHP_EOL;
    if (! $d) {
        $d = 0;
    }//echo '$d: ' . $s . PHP_EOL;
    if (! $h) {
        $h = 0;
    }//echo '$h: ' . $s . PHP_EOL;
    if (! $i) {
        $i = 0;
    }//echo '$i: ' . $s . PHP_EOL;
    if (! $s) {
        $s = 0;
    }//echo '$s: ' . $s . PHP_EOL;
    //Calculate total number of seconds passed.
    $passed_time =
    ($s) +
    ($i * 60) +
    ($h * 3600) +
    ($d * 86400) +
    ($m * 2629746) +
    ($y * 31556952);
    //echo 'passed_time: ' . $passed_time . PHP_EOL;
    if ($passed_time != 0) {
        return $passed_time;
    }
}

function CheckCooldown($foldername, $filename, $limit_array)
{
    echo "CHECK COOLDOWN" . PHP_EOL;
    //echo "limit_array: " . PHP_EOL; var_dump ($limit_array) . PHP_EOL;
    $TimeCompare = TimeCompare($foldername, $filename);
    //echo "TimeCompare: " . PHP_EOL; var_dump ($TimeCompare) . PHP_EOL;
    //$timetopass = $timelimitcheck[0]; //True/False, whether enough time has passed
    //$timetopass = $timelimitcheck[1]; //total # of seconds
    if ($TimeCompare) {
        $TimeLimitCheck = TimeLimitCheck($TimeCompare, $limit_array['year'], $limit_array['month'], $limit_array['day'], $limit_array['hour'], $limit_array['min'], $limit_array['sec']);
        //echo "TimeLimitCheck: " . PHP_EOL; var_dump ($TimeLimitCheck) . PHP_EOL;
        return $TimeLimitCheck;
    } else { //File was not found, so assume the check passes because they haven't used it before
        $TimeLimitCheck = array();
        $TimeLimitCheck[] = true;
        $TimeLimitCheck[] = 0;
        return $TimeLimitCheck;
    }
}

function CheckCooldownMem($author_id, $variable, $limit_array)
{
    echo "[CHECK COOLDOWN]" . PHP_EOL;
    //echo "limit_array: " . PHP_EOL; var_dump ($limit_array) . PHP_EOL;
    $TimeCompare = TimeCompareMem($author_id, $variable);
    //echo "TimeCompare: " . PHP_EOL; var_dump ($TimeCompare) . PHP_EOL;
    //$timetopass = $timelimitcheck[0]; //True/False, whether enough time has passed
    //$timetopass = $timelimitcheck[1]; //total # of seconds
    if ($TimeCompare) {
        $TimeLimitCheck = TimeLimitCheck($TimeCompare, $limit_array['year'], $limit_array['month'], $limit_array['day'], $limit_array['hour'], $limit_array['min'], $limit_array['sec']);
        //echo "TimeLimitCheck: " . PHP_EOL; var_dump ($TimeLimitCheck) . PHP_EOL;
        return $TimeLimitCheck;
    } else { //File was not found, so assume the check passes because they haven't used it before
        $TimeLimitCheck = array();
        $TimeLimitCheck[] = true;
        $TimeLimitCheck[] = 0;
        return $TimeLimitCheck;
    }
}

function SetCooldown($foldername, $filename)
{
    echo "SET COOLDOWN" . PHP_EOL;
    if ($foldername !== null) {
        $folder_symbol = "/";
    }else $folder_symbol = "";
    
    $path = __DIR__ . "/" .$foldername.$folder_symbol; //echo "PATH: $path" . PHP_EOL;
    $now = new DateTime();
    VarSave($foldername, $filename, $now);
}

function SetCooldownMem($author_id, $variable)
{
    echo "[SET COOLDOWN]" . PHP_EOL;
    $now = new DateTime();
    $varname = $author_id . $variable . "_cooldown";
    $GLOBALS["$varname"] = $now;
}

function FormatTime($seconds)
{
    //compare time
    $dtF = new \DateTime('@0');
    $dtT = new \DateTime("@$seconds");
    //ymdhis
    $formatted = $dtF->diff($dtT)->format(' %y years, %m months, %d days, %h hours, %i minutes and %s seconds');
    //echo "formatted: " . $formatted . PHP_EOL;
    //remove 0 values
    $formatted = str_replace(" 0 years,", "", $formatted);
    $formatted = str_replace(" 0 months,", "", $formatted);
    $formatted = str_replace(" 0 days,", "", $formatted);
    $formatted = str_replace(" 0 hours,", "", $formatted);
    $formatted = str_replace(" 0 minutes and", "", $formatted);
    $formatted = str_replace(" 0 seconds,", "", $formatted);
    $formatted = trim($formatted);
    //echo "new formatted: " . $formatted . PHP_EOL;
    return $formatted;
}

function TimeArrayToSeconds($array)
{
    $y = $array['year'];
    $m = $array['month'];
    $d = $array['day'];
    $h = $array['hour'];
    $i = $array['min'];
    $s = $array['sec'];
    $seconds =
    ($s) +
    ($i * 60) +
    ($h * 3600) +
    ($d * 86400) +
    ($m * 2629746) +
    ($y * 31556952);
    return $seconds;
}

/*
*********************
*********************
Miscellaneous
*********************
*********************
*/

//Returns a random result from an array
function GetRandomArrayIndex(array $array)
{
	return rand(0, count($array)-1);
}

//Removes a value from an array
function array_value_remove($value, $array)
{
    return array_diff($array, [$value]);
}

function appendImages($array)
{
    if (!is_array($array) || empty($array)) return false;
    
    /* Create new imagick object */
    $img = new Imagick();
    foreach ($array as $url) {
        /* retrieve image content */
        $webimage = file_get_contents($url);
        $img->readImageBlob($webimage);
    }
    /* Append the images into one */
    $img->resetIterator();
    $combined = $img->appendImages(true);
    /* Output the image */
    $combined->setImageFormat("png");
    
    /* Define pathing */
    $cache_folder = "C:/WinNMP/WWW/vzg.project/cache/";
    $img_rand = rand(0, 99999999999) . "cachedimage.png"; //Some big number to make the URLs unique because Discord caches image links
    $path =  $cache_folder . $img_rand;
    
    /* Delete old images before creating the new one */
    $files = glob($cache_folder . "*"); //Get all file names
    foreach ($files as $file) {
        if (is_file($file)) {
            unlink($file);
        } //Delete file
    }
    clearstatcache();
        
    /* Save the file */
	try {
		$combined->writeImage($path);
	}catch(Exception $e) {
		return null;
	}
    //imagepng($combined, $path); //Only works for resources, but imagick is an object
    
    /* Return the URL where the image can be accessed by Discord */
    $webpath = "https://www.valzargaming.com/cache/" . $img_rand;
    return $webpath;
}
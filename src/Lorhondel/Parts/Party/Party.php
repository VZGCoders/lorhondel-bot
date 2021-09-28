<?php

/*
 * This file is a part of the Lorhondel project.
 *
 * Copyright (c) 2021-present Valithor Obsidion <valzargaming@gmail.com>
 */

namespace Lorhondel\Parts\Party;

use Lorhondel\Endpoint;
use Lorhondel\Parts\Part;
use Lorhondel\Parts\Player\Player;

/**
 * A party is a reference to a group of players.
 *

 * @property int    $id            The unique identifier of the player.
 * @property string $name          Name of the party.
 * @property string $leader        Plaintext property name.
 *
 * @property int    $player1       Party creator snowflake (usually).
 * @property int    $player2       Party member snowflake.
 * @property int    $player3       Party member snowflake.
 * @property int    $player4       Party member snowflake.
 * @property int    $player5       Party member snowflake.
 *
 * @property bool   $looking       Whether the party is looking for players.
 *
 * @property int    $battle        The unique identifier of the active battle.
 
 */
class Party extends Part
{

    /**
     * @inheritdoc
     */
    protected static $fillable = ['id', 'name', 'leader', 'player1', 'player2', 'player3', 'player4', 'player5'];

	/**
     * Returns the fillable attributes.
     *
     * @return array
     */
    public static function getFillableAttributes($context = '')
	{
		$fillable = array();
		foreach (self::$fillable as $attr) {
			if (! $context || in_array($context, self::$fillable)) {
				$fillable[] = $attr;
			}
		}
		return $fillable;
	}

	/**
     * @inheritdoc
     */
    public function getCreatableAttributes(): array
    {
        return [
			'id'      => $this->id,
			'leader'  => $this->leader,
			'player1' => $this->player1,
			'player2' => $this->player2,
			'player3' => $this->player3,
			'player4' => $this->player4,
			'player5' => $this->player5,
        ];
    }

	/**
     * @inheritdoc
     */
    public function getRepositoryAttributes(): array
    {
        return [
            'party_id' => $this->id,
        ];
    }
	
	/*
	
	*/
	public function join($lorhondel, $player): int
	{
		if ($player instanceof Player)
			$id = $player->id;
		elseif (is_numeric($player)) {
			$id = $player;
			$player = $lorhondel->players->offsetGet($id);
		} else return 0; //$message->reply('Invalid parameter! Expects Player or Player ID.');
		if ($player->party_id) return 0; //$message->reply('Player is already in a party!');
		
		//if (isPartyJoinable(null, $this) === false) return 0; //$message->reply('Party is full!');
		//if ($isPartyJoinable === null) return null; //$message->reply('Unable to locate party!');	
		
		if ($this->player1 === null) {
			$this->player1 = $id;
			return 1;
		} elseif ($this->player2 === null) {
			$this->player2 = $id;
			return 2;
		} elseif ($this->player3 === null) {
			$this->player3 = $id;
			return 3;
		} elseif ($this->player4 === null) {
			$this->player4 = $id;
			return 4;
		} elseif ($this->player5 === null) {
			$this->player5 = $id;
			return 5;
		} else return 0; //This could have been caught by isPartyJoinable
	}
}

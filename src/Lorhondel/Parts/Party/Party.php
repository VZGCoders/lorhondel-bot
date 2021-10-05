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
 * @property array  $invites       Array of player IDs that have been invited to join the party.
 * @property int    $battle        The unique identifier of the active battle.
 
 */
class Party extends Part
{

    /**
     * @inheritdoc
     */
    protected static $fillable = ['id', 'name', 'leader', 'player1', 'player2', 'player3', 'player4', 'player5'];

	public $invites = [];

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

	public function rename($lorhondel, $name)
	{
		if ($name) {
			if (strlen($name) > 64) return 'Party name cannot exceed 64 characters!';
			$return = 'Changed name of Party `' . ($this->name ?? $this->id) . "` to `$name`!";
			$this->name = $name;
		} else {
			$return = 'Party `' . ($this->name ?? $this->id) . '` has had its name removed! It is now known as Party `' . ($this->name ?? $this->id) . '`!';
			$this->name = null;
		}
		if ($lorhondel) $lorhondel->parties->save($this);
		return $return;
	}

	public function invite($lorhondel, $player)
	{
		if ($player instanceof Player)
			$id = $player->id;
		elseif (is_numeric($player)) {
			$id = $player;
			$player = $lorhondel->players->offsetGet($id);
		} else return 'Invalid parameter! Expects Player or Player ID.';
		
		if ($this->player1 == $id || $this->player2 == $id || $this->player3 == $id || $this->player4 == $id || $this->player5 == $id)
			return 'Cannot invite a player that is already a member of the party!';
		
		if (in_array($id, $this->invites))
			return 'This player has already been invited to the party!';
		
		$this->invites[] = $id;
		return ($player->name ?? $player->id) . ' has been invited to the party!';
	}

	public function uninvite($lorhondel, $player)
	{
		if ($player instanceof Player)
			$id = $player->id;
		elseif (is_numeric($player)) {
			$id = $player;
			$player = $lorhondel->players->offsetGet($id);
		} else return 'Invalid parameter! Expects Player or Player ID.';
		
		if (!in_array($id, $this->invites))
			return 'This player has not been invited to the party!';
		
		unset($this->invites[$id]);
		return ($player->name ?? $player->id) . ' has been uninvited to the party!';
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
		
		if (in_array($id, $this->invites))
			unset($this->invites[$id]);
			
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

	public function leave($lorhondel, $player)
	{
		if ($player instanceof Player)
			$id = $player->id;
		elseif (is_numeric($player)) {
			$id = $player;
			$player = $lorhondel->players->offsetGet($id);
		} else return 'Invalid parameter! Expects Player or Player ID.'; //$message->reply('Invalid parameter! Expects Player or Player ID.');
		if ($player->party_id != $this->id) return 'Player `' . ($player->name ?? $player->id) . '` is not a member of Party `' . ($this->name ?? $this->id) . '`! '; //$message->reply('Player is not a member of this party!');
		
		if ($this->player1 == $id) {
			$position = 1;
			$this->player1 = null;
		} elseif ($this->player2 == $id) {
			$position = 2;
			$this->player2 = null;
		} elseif ($this->player3 == $id) {
			$position = 3;
			$this->player3 = null;
		} elseif ($this->player4 == $id) {
			$position = 4;
			$this->player4 = null;
		} elseif ($this->player5 == $id) {
			$position = 5;
			$this->player5 = null;
		}
		
		$return = 'Player `' . ($player->name ?? $player->id) . "` is no longer Player `$position` of Party `" . ($this->name ?? $this->id) . '`! ';
		
		if ($this->{$this->leader} == $this->{'player' . $position}) {
			$this->leader = null;
			if ($succession = $this->succession($lorhondel))
				$return .= $succession;
		}
		
		$player->party_id = null;
		$lorhondel->players->save($player);
		
		return $return;
	}
	
	/**
	 *
	 * Disbands the party if no players remain
	 * Assign a new party leader if no leader exists
	 *
     */
    public function succession($lorhondel = null)
    {
		if (! $this->leader) {
			if ($this->player1 && ($this->player1 != $this->leader))
				$this->leader = $this->player1;
			elseif ($this->player2 && ($this->player2 != $this->leader))
				$this->leader = $this->player2;
			elseif ($this->player3 && ($this->player3 != $this->leader))
				$this->leader = $this->player3;
			elseif ($this->player4 && ($this->player4 != $this->leader))
				$this->leader = $this->player4;
			elseif ($this->player5 && ($this->player5 != $this->leader))
				$this->leader = $this->player5;
			else return $this->disband($lorhondel);
			$lorhondel->parties->save($party);
			if ($lorhondel && $player = $lorhondel->players->offsetGet($id)) {
				$leader = $player->name ?? $player->id;
			} else $leader = $this->leader;
			return 'Player `' . $leader . '` is the new leader of `' . ($this->name ?? $this->party) . '`! `';
		} else return false;
    }
	
	public function disband($lorhondel = null)
	{
		$array = array();
		if ($this->player1) {
			$array[] = $this->player1;
		}
		if ($this->player2) {
			$array[] = $this->player2;
		}
		if ($this->player3) {
			$array[] = $this->player3;
		}
		if ($this->player4) {
			$array[] = $this->player4;
		}
		if ($this->player5) {
			$array[] = $this->player5;
		}
		
		foreach ($array as $id) {
			echo "[REMOVING ID FROM PARTY] $id/{$this->id}";
			if ($lorhondel && $player = $lorhondel->players->offsetGet($id)) {
				$player->party_id = null;
				$lorhondel->players->save($player);
			} else return false;
		}
		if ($lorhondel) $lorhondel->parties->delete($this->id);
		return 'Party ' . ($this->name ?? $this->id) . ' has been disbanded! ';
	}
}

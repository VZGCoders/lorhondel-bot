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
 * A Party is a reference to a group of Players.
 *

 * @property int    $id            The unique identifier of the Player.
 * @property string $name          Name of the Party.
 * @property string $leader        Plaintext property name.
 *
 * @property int    $player1       Party creator snowflake (usually).
 * @property int    $player2       Party member snowflake.
 * @property int    $player3       Party member snowflake.
 * @property int    $player4       Party member snowflake.
 * @property int    $player5       Party member snowflake.
 *
 * @property bool   $looking       Whether the Party is looking for players.
 *
 * @property array  $invites       Array of Player IDs that have been invited to join the Party.
 * @property int    $battle        The unique identifier of the active battle.
 
 */
class Party extends Part
{

    /**
     * @inheritdoc
     */
    protected static $fillable = ['id', 'name', 'leader', 'player1', 'player2', 'player3', 'player4', 'player5', 'looking'];

	public $invites = [];

	/**
     * Returns the fillable attributes.
     *
     * @return array
     */
    public static function getFillableAttributes($context = ''): array
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

	public function isLeader($player): bool
	{
		if ($player instanceof Player) {
			$id = $player->id;
		} elseif (is_numeric($player)) {
			$id = $player;
		} else return false;
		
		if ($this->{$this->leader} == $id) return true;
		return false;
	}

	public function help(): string
	{
		return '';
	}
	
	public function looking($lorhondel = null, $player = null): string
	{
		if ($player instanceof Player) {
			if (! $this->isLeader($player)) return 'Player `' . ($player->name ?? $player->id) . '` is not the Party leader!';
		}
		if (\Lorhondel\isPartyFull($this, null)) $return = 'Party cannot be full!';
		switch ($this->looking) {
			case null:
			case false:
				$this->looking = true;
				$return = 'Party `' . ($this->name ?? $this->id) . '` is now looking for Players!';
				break;
			case true:
				$this->looking = false;
				$return = 'Party `' . ($this->name ?? $this->id) . '` is no longer looking for Players!';
				break;
			default:
				break;
		}
		$lorhondel->parties->save($this);
		return $return;
	}

	public function rename($lorhondel = null, $player = null, $name = null): string
	{
		if ($player instanceof Player) {
			if (! $this->isLeader($player)) return 'Player `' . ($player->name ?? $player->id) . '` is not the Party leader!';
		}
		if ($name) {
			if (strlen($name) > 64) return 'Party name cannot exceed 64 characters!';
			$return = 'Changed name of Party `' . ($this->name ?? $this->id) . "` to `$name`!";
			$this->name = $name;
		} else {
			$return = 'Party `' . ($this->name ?? $this->id) . '` has had its name removed! It is now known as Party `' . $this->id . '`!';
			$this->name = null;
		}
		if ($lorhondel) $lorhondel->parties->save($this);
		return $return;
	}

	public function invite($lorhondel = null, $player = null, $id = null): string
	{
		if ($player instanceof Player) {
			if (! $this->isLeader($player)) return 'Player `' . ($player->name ?? $player->id) . '` is not the Party leader!';
		}
		if ($id instanceof Player) {
			$id = $id->id;
		}
		elseif (! is_numeric($id)) return 'Invalid parameters! Expects Player or Player ID.';
		$target_player = $lorhondel->players->offsetGet($id);
		
		if ($this->player1 == $id || $this->player2 == $id || $this->player3 == $id || $this->player4 == $id || $this->player5 == $id)
			return 'Cannot invite a Player that is already a member of the Party!';
		
		if (in_array($id, $this->invites))
			return 'This Player has already been invited to the Party!';
		
		$this->invites[] = $id;
		return ($target_player->name ?? $target_player->id) . ' has been invited to the Party!';
	}

	public function uninvite($lorhondel = null, $player = null, $id = null): string
	{
		if ($player instanceof Player) {
			if (! $this->isLeader($player)) return 'Player `' . ($player->name ?? $player->id) . '` is not the Party leader!';
		}
		if ($id instanceof Player) {
			$target_player = $id;
			$id = $id->id;
		} elseif (is_numeric($id)) {
			if (! $target_player = $lorhondel->players->offsetGet($id)) return "Unable to locate a Player with ID `$id`!";
		} else return 'Invalid parameter! Expects Player or Player ID.';
		
		if (! in_array($id, $this->invites)) return 'This Player has not been invited to the Party!';
		
		foreach ($this->invites as $key => $value) {
			if ($value == $id) unset($this->invites[$key]);
		}
		return ($target_player->name ?? $target_player->id) . ' has been uninvited to the Party!';
	}

	/*
	* Add a Player to the Party.
	*/
	public function join($lorhondel, $player): string
	{
		if ($player instanceof Player)
			$id = $player->id;
		elseif (is_numeric($player)) {
			$id = $player;
			$player = $lorhondel->players->offsetGet($id);
		} else return 'Invalid parameter! Expects Player or Player ID.';
		if ($player->party_id) return 'Player is already in a Party!';
		if (\Lorhondel\isPartyFull($this, null) === false) return 'Party is full!';
		
		if (in_array($id, $this->invites))
			unset($this->invites[$id]);
		
		$position = null;
		if ($this->player1 === null) {
			$this->player1 = $id;
			$position = 1;
		} elseif ($this->player2 === null) {
			$this->player2 = $id;
			$position = 2;
		} elseif ($this->player3 === null) {
			$this->player3 = $id;
			$position = 3;
		} elseif ($this->player4 === null) {
			$this->player4 = $id;
			$position = 4;
		} elseif ($this->player5 === null) {
			$this->player5 = $id;
			$position = 5;
		}
		if (! \Lorhondel\isPartyFull($this, null)) $this->looking = false;
		$lorhondel->parties->save($this);
		$player->looking = false;
		$lorhondel->players->save($player);
		
		return 'Player `' . ($player->name ?? $player->id) . '` has joined Party `' . ($this->name ?? $this->id) . "` as Player `$position`!";
	}

	/*
	* Remove a Player from the Party.
	*/
	public function leave($lorhondel, $player): string
	{
		if ($player instanceof Player)
			$id = $player->id;
		elseif (is_numeric($player)) {
			$id = $player;
			$player = $lorhondel->players->offsetGet($id);
		} else return 'Invalid parameter! Expects Player or Player ID.';
		if ($player->party_id != $this->id) return 'Player `' . ($player->name ?? $player->id) . '` is not a member of Party `' . ($this->name ?? $this->id) . '`! '; //$message->reply('Player is not a member of this Party!');
		
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
		$lorhondel->parties->save($this)->done(
			function ($result) use ($player, $lorhondel) {
				$player->party_id = null;
				$lorhondel->players->save($player);
			}
		);
		return $return;
	}
	
	/*
	* Forcefully remove a Player from the Party.
	*/
	public function kick($lorhondel, $player = null, $id = null): string
	{
		if ($player instanceof Player) {
			if (! $this->isLeader($player)) return 'Player `' . ($player->name ?? $player->id) . '` is not the Party leader!';
		}
		if ($id instanceof Player) {
			$target_player = $id;
			$id = $id->id;
		} elseif (! is_numeric($id)) return 'Invalid parameters! Expects Player or Player ID.';
		if ($target_player != $lorhondel->players->offsetGet($id)) return "Unable to locate a Player with ID `$id`!";
		
		if ($this->player1 != $id && $this->player2 != $id && $this->player3 != $id && $this->player4 != $id && $this->player5 != $id)
			return 'Cannot kick a Player that is not already a member of the Party!';
		if ($id == $this->{$this->leader}) return 'The Party leader cannot be kicked from their own Party!';
		return $this->leave($target_player);
	}

	/*
	* Transfer ownership of the Party to a Player.
	*/
	public function transfer($lorhondel = null, $player = null, $id = null): string
	{
		if ($player instanceof Player) {
			if (! $this->isLeader($player)) return 'Player `' . ($player->name ?? $player->id) . '` is not the Party leader!';
		}
		if ($id instanceof Player) {
			$id = $id->id;
		} elseif (! is_numeric($id)) return 'Invalid parameters! Expects Player or Player ID.';
		
		if ($this->player1 == $id) $this->leader = 'player1';
		elseif ($this->player2 == $id) $this->leader = 'player2';
		elseif ($this->player3 == $id) $this->leader = 'player3';
		elseif ($this->player4 == $id) $this->leader = 'player4';
		elseif ($this->player5 == $id) $this->leader = 'player5';
		else return 'Player is not already in the Party!';
		$lorhondel->parties->save($this);
		$leader = $lorhondel->players->offsetGet($this->{$this->leader});
		return 'Player `' . ($leader->name ?? $leader->id) . '` is the new leader of `' . ($this->name ?? $this->id) . '`!';;
	}

	/**
	 * Disbands the Party if no players remain
	 * Assign a new Party leader if no leader exists
     */
    public function succession($lorhondel = null, ?bool $force = false): string|bool
    {
		if ($force || ! $this->leader) {
			if ($this->player1 && ($this->player1 != $this->leader))
				$this->leader = 'player1';
			elseif ($this->player2 && ($this->player2 != $this->leader))
				$this->leader = 'player2';
			elseif ($this->player3 && ($this->player3 != $this->leader))
				$this->leader = 'player3';
			elseif ($this->player4 && ($this->player4 != $this->leader))
				$this->leader = 'player4';
			elseif ($this->player5 && ($this->player5 != $this->leader))
				$this->leader = 'player5';
			else return $this->disband($lorhondel);
			$lorhondel->parties->save($party);
			if ($lorhondel && $player = $lorhondel->players->offsetGet($id)) {
				$leader = $player->name ?? $player->id;
			} else $leader = $this->{$leader};
			return 'Player `' . $leader . '` is the new leader of `' . ($this->name ?? $this->id) . '`! `';
		} else return false;
    }
	
	public function disband($lorhondel = null, $player = null): string
	{
		if ($player instanceof Player) {
			if (! $this->isLeader($player)) return 'Player `' . ($player->name ?? $player->id) . '` is not the Party leader!';
		}
		
		$player_ids = array();
		if ($this->player1) $player_ids[] = $this->player1;
		if ($this->player2) $player_ids[] = $this->player2;
		if ($this->player3) $player_ids[] = $this->player3;
		if ($this->player4) $player_ids[] = $this->player4;
		if ($this->player5) $player_ids[] = $this->player5;
		
		$players = array();
		foreach ($player_ids as $id) {
			if ($player = $lorhondel->players->offsetGet($id)) {
				$player->party_id = null;
				$players[] = $player;
			}
		}
		
		$lorhondel->parties->delete($this)->done(
			function ($result) use ($lorhondel, $players) {
				if (count($players) == 0) return;
				$promise = null;
				$string = '';
				$string1 = '$promise = $lorhondel->players->save(array_shift($players))->done(function () use ($lorhondel, $players, $i) {';
				$string2 = '});';
				for ($i = 0; $i < count($players); $i++) {
				  $string .= $string1;
				}
				for ($i = 0; $i < count($players); $i++) {
				  $string .= $string2;
				}
				eval($string); //I really hate this language sometimes
			}
		);
		return 'Party `' . ($this->name ?? $this->id) . '` has been disbanded! ';
	}
}

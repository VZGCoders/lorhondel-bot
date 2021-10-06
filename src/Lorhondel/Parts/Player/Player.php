<?php

/*
 * This file is a part of the Lorhondel project.
 *
 * Copyright (c) 2021-present Valithor Obsidion <valzargaming@gmail.com>
 */

namespace Lorhondel\Parts\Player;

use Lorhondel\Endpoint;
use Lorhondel\Parts\Part;

/**
 * A Player is a general Player that is not attached to a group.
 *

 * @property int    $id            The unique identifier of the Player.
 * @property int    $user_id       Discord user id.
 * @property int    $party_id      Current Party id.
 * @property bool   $active        Whether the Player is active.
 * @property bool   $looking       Whether the Player is looking for a Party.
 *
 * @property string $name          The name of the Player.
 * @property string $species       The species of the Player.
 * @property int    $health        Health, obviously.
 * @property int    $attack        How much damage you output.
 * @property int    $defense       How much damage you block.
 * @property int    $speed         Evasiveness; Higher speed means a higher chance to evade an attack.
 * @property int    $skillpoints   Skill Points.

 */
class Player extends Part
{

    /**
     * @inheritdoc
     */
    protected static $fillable = ['id', 'user_id', 'party_id', 'active', 'looking', 'name', 'species', 'health', 'attack', 'defense', 'speed', 'skillpoints'];
	
	protected static $species_list = ['Elarian', 'Manthean', 'Noldarus', 'Veias', 'Jedoa'];

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
     * Returns the fillable species attributes.
     *
     * @return array
     */
    public static function getFillableSpeciesAttributes($context = '')
	{
		$species_list = array();
		foreach (self::$species_list as $attr) {
			if (! $context || in_array($context, self::$species_list)) {
				$species_list[] = $attr;
			}
		}
		return $species_list;
	}

	/**
     * @inheritdoc
     */
    public function getCreatableAttributes(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
			'party_id' => $this->party_id,
			'active' => $this->active,
			'name' => $this->name,
            'species' => $this->species,
            'health' => $this->health,
            'attack' => $this->attack,
            'defense' => $this->defense,
            'speed' => $this->speed,
            'skillpoints' => $this->skillpoints,
        ];
    }

    /**
     * Returns a timestamp for when a Player's account was created.
     *
     * @return float
     */
    public function createdTimestamp()
    {
        return \Lorhondel\getSnowflakeTimestamp($this->id);
    }
	

    /**
     * @inheritdoc
     */
    public function getRepositoryAttributes(): array
    {
        return [
            'player_id' => $this->id,
        ];
    }
	
	public function help(): string
	{
		return '';
	}
	
	public function activate($lorhondel): string
	{
		$collection = $lorhondel->players->filter(fn($p) => $p->user_id == $this->user_id && $p->active == 1);
		
		$players = [];
		foreach ($collection as $player) {
			$player->active = 0;
			$players[] = $player;
		}
		
		$this->active = 1;
		$lorhondel->players->save($this)->done(
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
		return 'Player `' . ($this->name ?? $this->id) . '` is now your active Player! ';
	}

	public function deactivate($lorhondel): string
	{
		$collection = $lorhondel->players->filter(fn($p) => $p->user_id == $this->user_id && $p->active == 1 && $p->id != $this->id);
		
		$players = [];
		foreach ($collection as $player) {
			$player->active = 0;
			$players[] = $player;
		}
		
		$this->active = 0;
		$lorhondel->players->save($this)->done(
			function ($result) use ($lorhondel, $players) {
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
		return 'Player `' . ($this->name ?? $this->id) . '` is no longer your active Player! ';
	}

	public function looking($lorhondel): string
	{
		if ($this->party_id === null) {
			switch ($this->looking) {
				case null:
				case false:
					$this->looking = true;
					$return = 'Player ' . ($this->name ?? $this->id) . ' is now looking for a Party!';
					break;
				case true:
					$this->looking = false;
					$return = 'Player ' . ($this->name ?? $this->id) . ' is no longer looking for a Party!';
					break;
				default:
					break;
			}
			$lorhondel->players->save($this);
		} else $return = 'Please leave your current Party before listing yourself as looking for a new one!';
		return $return;
	}

	public function rename($lorhondel, $name): string
	{
		if ($name) {
			if (strlen($name) > 64) return 'Player name cannot exceed 64 characters!';
			$return = 'Changed name of Player `' . ($this->name ?? $this->id) . "` to `$name`!";
			$this->name = $name;
		} else {
			$return = 'Player `' . ($this->name ?? $this->id) . '` has had its name removed! It is now known as Player `' . ($this->id) . '`!';
			$this->name = null;
		}
		if ($lorhondel) $lorhondel->players->save($this);
		return $return;
	}

    /**
     * Returns a formatted mention.
     *
     * @return string A formatted mention.
     */
    public function __toString()
    {
        return "<@{$this->user_id}>";
    }
}

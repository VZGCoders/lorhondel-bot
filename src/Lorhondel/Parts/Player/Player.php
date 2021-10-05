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
 * A player is a general player that is not attached to a group.
 *

 * @property int    $id            The unique identifier of the player.
 * @property int    $user_id       Discord user id.
 * @property int    $party_id      Current party id.
 * @property bool   $active        Whether the player is active.
 * @property bool   $looking       Whether the player is looking for a party.
 *
 * @property string $name          The name of the player.
 * @property string $species       The species of the player.
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
    protected static $fillable = ['id', 'user_id', 'party_id', 'active', 'name', 'species', 'health', 'attack', 'defense', 'speed', 'skillpoints'];
	
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
     * Returns a timestamp for when a player's account was created.
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
	
	public function rename($lorhondel, $name)
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

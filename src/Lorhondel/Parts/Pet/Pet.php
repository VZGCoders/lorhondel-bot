<?php

/*
 * This file is a part of the Lorhondel project.
 *
 * Copyright (c) 2021-present Valithor Obsidion <valzargaming@gmail.com>
 */

namespace Lorhondel\Parts\Pet;

use Lorhondel\Endpoint;
use Lorhondel\Parts\Part;

/**
 * Pets are creatures that you can give to your character as a cosmetic companion
 * While pets do not directly aid in exploration, quests, or battles,
 * they can grow up to other stages that will be catalogued in the field guide and are necessary for completing the guide. 
 *

 * @property int    $id            The unique identifier of the Pet.
 * @property int    $user_id       Owner's Discord user id.
 *
 * @property string $name          The name of the Pet.
 * @property string $species       The species of the Pet.
 * @property int    $variant       Variants have an alternate color pallet and a sparkly effect.
 * @property int    $affection     Feeding your Pet or playing with it will increase it’s affection level.

 */
class Pet extends Part
{
    /**
     * @inheritdoc
     */
    protected static $fillable = ['id', 'user_id', 'name', 'species', 'variant', 'affection'];

	protected static $species_list = ['']; //TODO

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
        return [ //'id', 'user_id', 'name', 'species', 'variant', 'affection'];
            'id' => $this->id,
            'user_id' => $this->user_id,
			'name' => $this->name,
            'species' => $this->species,
            'variant' => $this->variant,
			'affection' => $this->affection',
        ];
    }

    /**
     * Returns a timestamp for when a Pet's account was created.
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
            'pet_id' => $this->id,
        ];
    }

	public function help(): string
	{
		return '';
	}

	public function rename($lorhondel, $name): string
	{
		if ($name) {
			if (strlen($name) > 64) return 'Pet name cannot exceed 64 characters!';
			$return = 'Changed name of Pet `' . ($this->name ?? $this->id) . "` to `$name`!";
			$this->name = $name;
		} else {
			$return = 'Pet `' . ($this->name ?? $this->id) . '` has had its name removed! It is now known as Pet `' . ($this->id) . '`!';
			$this->name = null;
		}
		if ($lorhondel) $lorhondel->pets->save($this);
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

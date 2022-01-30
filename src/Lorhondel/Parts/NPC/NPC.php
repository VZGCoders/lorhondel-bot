<?php

/*
 * This file is a part of the Lorhondel project.
 *
 * Copyright (c) 2021-present Valithor Obsidion <valzargaming@gmail.com>
 */

namespace Lorhondel\Parts\NPC;

use Lorhondel\Endpoint;
use Lorhondel\Parts\Part;

/**
 * NPCs can offer quests.
 * Fulfilling their needs will increase your friendship with them, as well as award reputation points.
 * Having a higher friendship with an NPC means cheaper or unique items.
 *

 * @property int    $id            The unique identifier of the NPC.
 * @property string $name          The name of the NPC.
 * @property string $species       The species of the NPC.
 * @property int    $quests        Number of quests available from this NPC.

 */
class NPC extends Part
{
    /**
     * @inheritdoc
     */
    protected static $fillable = ['id', 'name', 'species', 'quests'];

    protected static $species_list = ['']; //TODO

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
            'name' => $this->name,
            'quests' => $this->quests,
        ];
    }

    /**
     * Returns a timestamp for when a NPC's account was created.
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
            'npc_id' => $this->id,
        ];
    }

    public function help(): string
    {
        return '';
    }

    public function rename($lorhondel, $name): string
    {
        if ($name) {
            if (strlen($name) > 64) return 'NPC name cannot exceed 64 characters!';
            $return = 'Changed name of NPC `' . ($this->name ?? $this->id) . "` to `$name`!";
            $this->name = $name;
        } else {
            $return = 'NPC `' . ($this->name ?? $this->id) . '` has had its name removed! It is now known as NPC `' . ($this->id) . '`!';
            $this->name = null;
        }
        if ($lorhondel) $lorhondel->npcs->save($this);
        return $return;
    }

    /**
     * Returns a formatted mention.
     *
     * @return string A formatted mention.
     */
    public function __toString()
    {
        return "<@{$this->id}>";
    }
}

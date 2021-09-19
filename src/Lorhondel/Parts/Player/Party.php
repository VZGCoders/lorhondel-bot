<?php

/*
 * This file is a part of the Lorhondel project.
 *
 * Copyright (c) 2021-present Valithor Obsidion <valzargaming@gmail.com>
 */

namespace Lorhondel\Parts\Player;

use Lorhondel\Builders\MessageBuilder;
use Lorhondel\Endpoint;
use Lorhondel\Parts\Channel\Channel;
use Lorhondel\Parts\Part;
use Lorhondel\Parts\Channel\Message;
use React\Promise\ExtendedPromiseInterface;

/**
 * A party is a reference to a group of players.
 *

 * @property int    $id            The unique identifier of the player.
 * @property string $leader        Plaintext property name.
 *
 * @property int    $player1       Party creator snowflake (usually).
 * @property int    $player2       Party member snowflake.
 * @property int    $player3       Party member snowflake.
 * @property int    $player4       Party member snowflake.
 * @property int    $player5       Party member snowflake.

 */
class Player extends Part
{

    /**
     * @inheritdoc
     */
    protected static $fillable = ['id', 'leader', 'player1', 'player2', 'player3', 'player4', 'player5'];

	/**
     * Returns the fillable attributes.
     *
     * @return array
     */
    public static function getFillableAttributes($context = '')
	{
		$fillable = array();
		foreach (self::$fillable as $attr) {
			if (!$context || in_array($context, $attrContexts)) {
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
     * Leaves the party.
     *
     * @return ExtendedPromiseInterface
     */
    public function leave(): ExtendedPromiseInterface
    {
        return $this->lorhondel->parties->leave($this->id);
    }

    /**
     * Transfers ownership of the party to
     * another player.
     *
     * @param Player|int $player The member to transfer ownership to.
     *
     * @return ExtendedPromiseInterface
     */
    public function transferOwnership($player): ExtendedPromiseInterface
    {
        if ($player instanceof Player) {
            $player = $player->id;
        }
		
		if(in_array($player, (array) $this));

		/*
        return $this->http->patch(Endpoint::bind(Endpoint::PARTY_PATCH), ['leader' => $player])->then(function ($response) use ($player) {
            if ($response->leader != $player) {
                throw new Exception('Ownership was not transferred correctly.');
            }

            return $this;
        });
		*/
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
}

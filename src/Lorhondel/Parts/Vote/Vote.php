<?php

/*
 * This file is a part of the Lorhondel project.
 *
 * Copyright (c) 2021-present Valithor Obsidion <valzargaming@gmail.com>
 */

namespace Lorhondel\Parts\Vote;

use Lorhondel\Endpoint;
use Lorhondel\Parts\Part;
use React\Promise\ExtendedPromiseInterface;

/**
 * A vote is a general vote that is not attached to a group.
 *

 * @property int    $id            The unique identifier of the vote.
 * @property bool   $active        Whether the vote is active.
 * @property int    $duration      Vote duration in seconds.
 * @property bool   $result        True if succeeded, False if failed.
 *
 * @property array  $voters        Array of unique identifiers who are allowed to vote.
 * @property array  $votes         Associative array containing voter unique identifiers and their vote.
 *
 * @property TimerInterface $timer Controls the flow of vote. //Declared with $this->timer=addPeriodicTimer($int, function ($timer) ...) and nulled with cancelTimer($this->timer).
 */
class Vote extends Part
{

    /**
     * @inheritdoc
     */
    protected static $fillable = ['id', 'active', 'duration', 'result'];

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
            'id',
			'active',
			'duration',
			'result'
        ];
    }	

    /**
     * @inheritdoc
     */
    public function getRepositoryAttributes(): array
    {
        return [
            'vote_id' => $this->id,
        ];
    }
}

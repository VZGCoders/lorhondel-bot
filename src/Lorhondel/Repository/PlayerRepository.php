<?php

/*
 * This file is a part of the Lorhondel project.
 *
 * Copyright (c) 2021-present Valithor Obsidion <valzargaming@gmail.com>
 */

namespace Lorhondel\Repository;

use Lorhondel\Endpoint;
use Lorhondel\Parts\Player\Player;

/**
 * Contains users that the user shares guilds with.
 *
 * @see \Lorhondel\Parts\Player\Player
 *
 * @method Player|null get(string $discrim, $key)  Gets an item from the collection.
 * @method Player|null first()                     Returns the first element of the collection.
 * @method Player|null pull($key, $default = null) Pulls an item from the repository, removing and returning the item.
 * @method Player|null find(callable $callback)    Runs a filter callback over the repository.
 */
class PlayerRepository extends AbstractRepository
{
    /**
     * @inheritdoc
     */
    protected $endpoints = [
        'get' => Endpoint::PLAYER,
    ];

    /**
     * @inheritdoc
     */
    protected $class = Player::class;
}

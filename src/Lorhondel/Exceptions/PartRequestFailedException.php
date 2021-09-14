<?php

/*
 * This file is a part of the Lorhondel project.
 *
 * Copyright (c) 2021-present Valithor Obsidion <valzargaming@gmail.com>
 */

namespace Lorhondel\Exceptions;

/**
 * Thrown when a request that was executed from a part failed.
 *
 * @see \Lorhondel\Parts\Part::save() Can be thrown when being saved.
 * @see \Lorhondel\Parts\Part::delete() Can be thrown when being deleted.
 */
class PartRequestFailedException extends \Exception
{
}

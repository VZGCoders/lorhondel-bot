<?php

/*
 * This file is a part of the Lorhondel project.
 *
 * Copyright (c) 2021-present Valithor Obsidion <valzargaming@gmail.com>
 */

namespace Lorhondel\Exceptions;

/**
 * Thrown when the Lorhondel servers return `content longer than 2000 characters` after
 * a REST request. The user must use WebSockets to obtain this data if they need it.
 *
 * @author David Cole <david.cole1340@gmail.com>
 * @author Valithor Obsidion <valzargaming@gmail.com>
 */
class ContentTooLongException extends RequestFailedException
{
}

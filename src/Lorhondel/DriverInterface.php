<?php

/*
 * This file is a part of the Lorhondel project.
 *
 * Copyright (c) 2021-present Valithor Obsidion <valzargaming@gmail.com>
 */

namespace Lorhondel;

use Psr\Http\Message\ResponseInterface;
use React\Promise\ExtendedPromiseInterface;

/**
 * Interface for an HTTP driver.
 *
 * @author David Cole <david.cole1340@gmail.com>
 * @author Valithor Obsidion <valzargaming@gmail.com>
 */
interface DriverInterface
{
    /**
     * Runs a request.
     *
     * Returns a promise resolved with a PSR response interface.
     *
     * @param Request $request
     *
     * @return ExtendedPromiseInterface<ResponseInterface>
     */
    public function runRequest(Request $request): ExtendedPromiseInterface;
}

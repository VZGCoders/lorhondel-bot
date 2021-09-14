<?php

/*
 * This file is a part of the LorhondelPHP project.
 *
 * Copyright (c) 2015-present David Cole <david.cole1340@gmail.com>
 *
 * This file is subject to the MIT license that is bundled
 * with this source code in the LICENSE.md file.
 */

namespace Lorhondel\WebSockets\Events;

use Lorhondel\Helpers\Deferred;
use Lorhondel\Parts\Interactions\Interaction;
use Lorhondel\WebSockets\Event;

class InteractionCreate extends Event
{
    /**
     * @inheritdoc
     */
    public function handle(Deferred &$deferred, $data): void
    {
        // do nothing with interactions - pass on to LorhondelPHP-Slash
        $deferred->resolve($this->factory->create(Interaction::class, $data, true));
    }
}

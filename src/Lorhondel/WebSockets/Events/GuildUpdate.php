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

use Lorhondel\WebSockets\Event;
use Lorhondel\Helpers\Deferred;

class GuildUpdate extends Event
{
    /**
     * @inheritdoc
     */
    public function handle(Deferred &$deferred, $data): void
    {
        /** @var \Lorhondel\Parts\Guild\Guild */
        $guild = $this->lorhondel->guilds->get('id', $data->id);
        $oldGuild = clone $guild;

        $guild->fill((array) $data);

        $deferred->resolve([$guild, $oldGuild]);
    }
}

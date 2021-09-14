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

use Lorhondel\Parts\User\Member;
use Lorhondel\WebSockets\Event;
use Lorhondel\Helpers\Deferred;

class GuildMemberUpdate extends Event
{
    /**
     * @inheritdoc
     */
    public function handle(Deferred &$deferred, $data): void
    {
        /** @var \Lorhondel\Parts\User\Member */
        $memberPart = $this->factory->create(Member::class, $data, true);
        $old = null;

        if ($guild = $this->lorhondel->guilds->get('id', $memberPart->guild_id)) {
            $old = $guild->members->get('id', $memberPart->id);
            $raw = (is_null($old)) ? [] : $old->getRawAttributes();
            $memberPart = $this->factory->create(Member::class, array_merge($raw, (array) $data), true);

            $guild->members->push($memberPart);
        }

        if ($user = $this->lorhondel->users->get('id', $data->user->id)) {
            $user->fill((array) $data->user);
        }

        $deferred->resolve([$memberPart, $old]);
    }
}

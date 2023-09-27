<?php

namespace Common\Channels;

use App\Channel;
use Illuminate\Support\Collection;

class LoadChannelMenuItems
{
    public function execute(): Collection
    {
        return Channel::limit(20)->get()
            ->map(fn(Channel $channel) => [
                'label' => $channel->name,
                'action' => '/' . $channel->slug,
                'type' => 'route',
                'model_id' => $channel->id,
                'id' => $channel->id,
            ]);
    }
}

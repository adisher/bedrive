<?php

namespace Common\Channels;

use App\Channel;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CrupdateChannel
{
    public function execute($params, $initialChannel = null): Channel
    {
        if (!$initialChannel) {
            $channel = app(Channel::class)->newInstance([
                'user_id' => Auth::id(),
            ]);
        } else {
            $channel = $initialChannel;
        }

        $attributes = [
            'name' => $params['name'],
            'tagline' => Arr::get($params, 'tagline'),
            'slug' => Arr::get($params, 'slug') ?? slugify($params['name']),
            // merge old config so config that is not in crupdate channel form is not lost
            'config' => array_merge(
                $initialChannel['config'] ?? [],
                $params['config'],
            ),
        ];

        $channel
            ->fill(
                array_merge($attributes, [
                    // make sure updated_at is always changed, event if model is
                    // not dirty otherwise channel cache will not be cleared
                    'updated_at' => now(),
                ]),
            )
            ->save();

        if (
            $channel->config['contentType'] === 'manual' &&
            ($channelContent = Arr::get($params, 'content.data'))
        ) {
            // detach old channelables
            DB::table('channelables')
                ->where('channel_id', $channel->id)
                ->delete();

            $pivots = collect($channelContent)
                ->map(function ($item, $i) use ($channel) {
                    return [
                        'channel_id' => $channel->id,
                        'channelable_id' => $item['id'],
                        'channelable_type' => modelTypeToNamespace(
                            $item['model_type'],
                        ),
                        'order' => $i,
                    ];
                })
                ->filter(function ($item) use ($channel) {
                    // channels should not be attached to themselves
                    return $item['channelable_type'] !== Channel::class ||
                        $item['channelable_id'] !== $channel->id;
                });
            DB::table('channelables')->insert($pivots->toArray());
        }

        return $channel;
    }
}

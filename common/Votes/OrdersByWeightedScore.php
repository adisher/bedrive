<?php

namespace Common\Votes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

trait OrdersByWeightedScore
{
    public function scopeOrderByWeightedScore(
        Builder $query,
        $direction = 'desc',
        string $positiveCol = 'upvotes',
        string $negativeCol = 'downvotes',
    ): Builder {
        return $query
            ->addSelect([
                DB::raw(
                    "(($positiveCol + 1.9208) / ($positiveCol + $negativeCol) -.96 * SQRT(($positiveCol * $negativeCol) / ($positiveCol + $negativeCol) + 0.9604) / ($positiveCol + $negativeCol)) / (1 + 3.8416 / ($positiveCol + $negativeCol)) AS weighted_score",
                ),
            ])
            ->orderBy('weighted_score', $direction);
    }
}

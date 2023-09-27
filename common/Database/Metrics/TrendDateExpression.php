<?php

namespace Common\Database\Metrics;

use Carbon\CarbonImmutable;
use DateTime;
use DateTimeZone;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Expression;

class TrendDateExpression extends Expression
{
    public function __construct(
        protected Builder $query,
        protected string $column,
        protected string $unit,
        protected string $timezone,
    ) {
    }

    public function getValue(): string
    {
        // dates in database are stored in UTC, and date_format will not return hour in many cases, so we need
        // to convert date_format result to specified timezone in mysql because it can use the full timestamp
        $offset = $this->offset();
        if ($offset > 0) {
            $interval = '+ INTERVAL ' . $offset . ' HOUR';
        } elseif ($offset === 0) {
            $interval = '';
        } else {
            $interval = '- INTERVAL ' . $offset * -1 . ' HOUR';
        }

        switch ($this->unit) {
            case 'year':
                return "date_format({$this->wrap(
                    $this->column,
                )} {$interval}, '%Y')";
            case 'month':
                return "date_format({$this->wrap(
                    $this->column,
                )} {$interval}, '%Y-%m')";
            case 'week':
                return "date_format({$this->wrap(
                    $this->column,
                )} {$interval}, '%x-%v')";
            case 'day':
                return "date_format({$this->wrap(
                    $this->column,
                )} {$interval}, '%Y-%m-%d')";
            case 'hour':
                return "date_format({$this->wrap(
                    $this->column,
                )} {$interval}, '%Y-%m-%d %H:00')";
            case 'minute':
                return "date_format({$this->wrap(
                    $this->column,
                )} {$interval}, '%Y-%m-%d %H:%i:00')";
        }
    }

    protected function wrap(string $value): string
    {
        return $this->query
            ->getQuery()
            ->getGrammar()
            ->wrap($value);
    }

    protected function offset(): int
    {
        $timezoneOffset = function ($timezone) {
            return (new DateTime(
                CarbonImmutable::now()->format('Y-m-d H:i:s'),
                new DateTimeZone($timezone),
            ))->getOffset() /
                60 /
                60;
        };

        $appOffset = $timezoneOffset(config('app.timezone'));
        $userOffset = $timezoneOffset($this->timezone);

        return $userOffset - $appOffset;
    }
}

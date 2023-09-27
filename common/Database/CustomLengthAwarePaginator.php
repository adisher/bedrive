<?php

namespace Common\Database;

use Illuminate\Pagination\LengthAwarePaginator;

class CustomLengthAwarePaginator extends LengthAwarePaginator
{
    public function toArray(): array
    {
        return [
            'current_page' => $this->currentPage(),
            'data' => $this->items->toArray(),
            'from' => $this->firstItem(),
            'last_page' => $this->lastPage(),
            'next_page' => $this->hasMorePages()
                ? $this->currentPage() + 1
                : null,
            'per_page' => $this->perPage(),
            'prev_page' =>
                $this->currentPage() > 1 ? $this->currentPage() - 1 : null,
            'to' => $this->lastItem(),
            'total' => $this->total(),
        ];
    }
}

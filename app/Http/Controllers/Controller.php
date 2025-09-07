<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Pagination\LengthAwarePaginator;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Return a standardised JSON structure for paginated data.
     *
     * The mobile applications expect collections to be wrapped in a
     * `data` key with pagination information provided separately.
     * Laravel's paginator returns metadata at the top level which broke
     * backward compatibility when pagination was introduced. This helper
     * normalises the structure across the API.
     */
    protected function formatPaginated(LengthAwarePaginator $paginator): array
    {
        return [
            'data' => $paginator->items(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ],
        ];
    }

    protected function paginatedResponse(LengthAwarePaginator $paginator)
    {
        return response()->json($this->formatPaginated($paginator));
    }
}

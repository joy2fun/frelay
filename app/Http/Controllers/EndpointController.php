<?php

namespace App\Http\Controllers;

use App\Models\Endpoint;
use App\Models\EndpointTarget;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Http;

class EndpointController extends Controller
{
    public function relay($slug)
    {
        /**@var Endpoint */
        $endpoint = Endpoint::where([
            'slug' => $slug,
            'enabled' => 1,
        ])->firstOrFail();

        $targets = $endpoint->targets->filter(fn (EndpointTarget $item) => $item->enabled && $item->passedRule());

        if ($targets->count() == 0) {
            abort(204);
        }

        $responses = Http::pool(function (Pool $pool) use ($targets) {
            return $targets->map(function ($item) use ($pool) {
                return $pool
                    ->withHeaders($item->buildHeaders())
                    ->{strtolower($item->method)}($item->uri, $item->buildBody());
            });
        });

        return $this->response(current($responses));
    }

    private function response($response)
    {
        if (!$response) abort(500);

        if (str($response->header('content-type'))->contains('json')) {
            return response()->json($response->body(), $response->status());
        }

        return response($response->body(), $response->status());
    }
}

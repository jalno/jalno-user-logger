<?php

namespace Jalno\UserLogger\Http\Resources;

use Jalno\AAA\Http\Resources\UserResource;
use Jalno\UserLogger\Http\Resources\Concerns\HasSummary;
use Illuminate\Http\Resources\Json\JsonResource;

class LogResource extends JsonResource
{
    use HasSummary;

    public function toArray($request)
    {
        $data = parent::toArray($request);
        if ($this->summary) {
            unset($data['properties']);
        } else {
            $data['user'] = UserResource::make($this->resource->user);
        }

        return $data;
    }
}

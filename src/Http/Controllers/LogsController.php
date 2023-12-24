<?php

namespace Jalno\UserLogger\Http\Controllers;

use Jalno\UserLogger\Contracts\ILogger;
use Jalno\UserLogger\Http\Requests\LogsSearchRequest;
use Jalno\UserLogger\Http\Resources\LogCollection;
use Jalno\UserLogger\Http\Resources\LogResource;
use Jalno\UserLogger\Models\Log;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class LogsController extends Controller
{
    use AuthorizesRequests;
    use ValidatesRequests;

    public function __construct(protected ILogger $logger)
    {
    }

    public function index(LogsSearchRequest $request): LogCollection
    {
        $data = $request->validated();
        if (isset($data['user_id'])) {
            $data['user'] = $data['user_id'];
            unset($data['user_id']);
        }
        $types = Log::query()
            ->filter($data)
            // ->userHasAccess(Auth::user())
            ->cursorPaginate();

        return LogCollection::make($types, true);
    }

    public function show(int $log): LogResource
    {
        $type = Log::query()->findOrFail($log);
        // dd($type->parameters);
        // $this->authorize('view', $type);

        return LogResource::make($type);
    }

    public function destroy(int $log): Response
    {
        $log = Log::query()->findOrFail($log);
        $this->authorize('destroy', $log);
        $log->delete();

        return response()->noContent();
    }
}

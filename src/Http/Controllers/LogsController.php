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
use Illuminate\Http\Response;
use dnj\AAA\Contracts\IUser;
use Jalno\UserLogger\Contracts\Permissions\Logs as LogsPermissions;

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
        /** @var IUser */
        $user = Auth::user();
        $types = Log::query()
            ->filter($data)
            ->userHasAccess($user)
            ->cursorPaginate();

        return LogCollection::make($types, true);
    }

    public function show(int $log): LogResource
    {
        $type = Log::query()->findOrFail($log);
        $this->authorize(LogsPermissions::View->value, $type);

        return LogResource::make($type);
    }

    public function destroy(int $log): Response
    {
        $log = Log::query()->findOrFail($log);
        $this->authorize(LogsPermissions::Delete->value, $log);
        $log->delete();

        return response()->noContent();
    }
}

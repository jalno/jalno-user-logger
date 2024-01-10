<?php

namespace Jalno\UserLogger\Http\Resources;

use dnj\AAA\Contracts\IUser;
use dnj\AAA\Contracts\IUserManager;
use dnj\AAA\Http\Resources\UserSummaryResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class LogCollection extends ResourceCollection
{
    public $collects = LogResource::class;

    public function __construct($resource, bool $summary = true)
    {
        if ($summary) {
            $this->collects = LogSummaryResource::class;
        }
        parent::__construct($resource);
    }

    public function paginationInformation(Request $request, array $paginated, array $default): array
    {
        $users = array_map(fn (LogResource $r) => $r->resource->getOwnerUserId(), $paginated['data']);
        $users = array_filter($users, fn (int|null $user) => !is_null($user));
        $users = array_unique($users);

        /**
         * @var IUserManager
         */
        $userManager = app(IUserManager::class);
        $users = iterator_to_array($userManager->search(['id' => $users]));
        $users = array_map(fn (IUser $u) => new UserSummaryResource($u), $users);
        $default['users'] = $users;

        return $default;
    }
}

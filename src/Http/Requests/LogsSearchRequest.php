<?php

namespace Jalno\UserLogger\Http\Requests;

use dnj\AAA\Rules\UserExists;
use dnj\UserLogger\Contracts\ILog;
use dnj\UserLogger\Rules\SubjectType;
use Illuminate\Foundation\Http\FormRequest;

use Jalno\UserLogger\Contracts\Permissions\Logs as LogsPermissions;

/**
 * @property int|string|null      $id
 * @property string|null          $title
 * @property bool|string|int|null $has_full_access
 * @method \dnj\AAA\Contracts\IUser user()
 */
class LogsSearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can(LogsPermissions::ViewAny->value, ILog::class);
    }

    public function rules(): array
    {
        /** @var \dnj\AAA\Contracts\IUser */
        $user = $this->user();
        return [
            'id' => ['sometimes', 'required', 'numeric'],
            'event' => ['sometimes', 'required', 'string'],
            'user_id' => ['sometimes', 'required', app(UserExists::class)->userHasAccess($user)],
            'subject.type' => ['sometimes', 'required', new SubjectType()],
            'subject.id' => ['required_with:subject.type', 'string'],
            'ip' => ['sometimes', 'required', 'ip'],
        ];
    }
}

<?php

namespace Jalno\UserLogger\Http\Requests;

use dnj\AAA\Rules\UserExists;
use dnj\UserLogger\Contracts\ILog;
use dnj\UserLogger\Rules\SubjectType;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @property int|string|null      $id
 * @property string|null          $title
 * @property bool|string|int|null $has_full_access
 */
class LogsSearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
        return $this->user()->can('viewAny', ILog::class);
    }

    public function rules(): array
    {
        return [
            'id' => ['sometimes', 'required', 'numeric'],
            'event' => ['sometimes', 'required', 'string'],
            'user_id' => ['sometimes', 'required', app(UserExists::class)->userHasAccess($this->user())],
            'subject.type' => ['sometimes', 'required', new SubjectType()],
            'subject.id' => ['required_with:subject.type', 'string'],
            'ip' => ['sometimes', 'required', 'ip'],
        ];
    }
}

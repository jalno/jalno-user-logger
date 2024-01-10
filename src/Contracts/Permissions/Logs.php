<?php

namespace Jalno\UserLogger\Contracts\Permissions;

enum Logs: string
{
    case ViewAny = 'userpanel_logs_search';
    case View = 'userpanel_logs_view';
    case Delete = 'userpanel_logs_delete';
}

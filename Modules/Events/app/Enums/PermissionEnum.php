<?php

namespace Modules\Events\Enums;

enum PermissionEnum: string
{
    case VIEW_EVENT = 'view event';
    case EDIT_EVENT = 'edit event';
    case CREATE_EVENT = 'create event';

    case API_VIEW_EVENT_LIST = 'api view event list';
    case API_VIEW_EVENT = 'api view event';
    case API_EDIT_EVENT = 'api edit event';
    case API_CREATE_EVENT = 'api create event';
}

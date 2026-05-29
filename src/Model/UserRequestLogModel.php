<?php

namespace Hexin\Library\Model;

class UserRequestLogModel extends Model
{
    protected $connection = 'DB_OLAP';

    protected $table = 'user_request_log';

    protected $fillable = [
        'route',
        'uuid',
        'uname',
        'module',
        'action',
        'create_time',
        'update_time',
    ];


}

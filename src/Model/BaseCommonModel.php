<?php

namespace Hexin\Library\Model;

use Hexin\Library\Model\Model;

class BaseCommonModel extends  Model
{

    /**
     * The name of the "created at" column.
     *
     * @var string
     */
    const CREATED_AT = 'created_at';

    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    const UPDATED_AT = 'updated_at';

    public function viewUpdatedAt()
    {
        return $this->updated_at->toDateTimeString();
    }
    public function viewCreatedAt()
    {
        return $this->created_at->toDateTimeString();
    }
}

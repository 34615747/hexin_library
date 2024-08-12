<?php

namespace Hexin\Library\Lib\SoftDelete;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope as EloquentSoftDeletingScope;

class SoftDeletingScope extends EloquentSoftDeletingScope
{
    public function extend(Builder $builder)
    {
        foreach ($this->extensions as $extension) {
            $this->{"add{$extension}"}($builder);
        }

        $builder->onDelete(function (Builder $builder) {
            $column = $this->getDeletedAtColumn($builder);

            return $builder->update([
                $column => $builder->getModel()->freshTimestamp(),//改了这句
            ]);
        });
    }
}
<?php

namespace Hexin\Library\Lib\Import;

use Maatwebsite\Excel\Concerns\SkipsUnknownSheets;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MultipleSheetImport implements WithMultipleSheets, SkipsUnknownSheets
{
    public function sheets(): array
    {
        return [];
    }

    public function onUnknownSheet($sheetName)
    {
        // TODO: Implement onUnknownSheet() method.
    }
}

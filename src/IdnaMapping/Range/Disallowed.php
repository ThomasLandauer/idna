<?php

namespace MLocati\IDNA\IdnaMapping\Range;

use MLocati\IDNA\IdnaMapping\TableRow;
use Exception;

/**
 * The code point is not allowed.
 */
class Disallowed extends Range
{
    /**
     * Initializes the instance.
     *
     * @param TableRow $row
     *
     * @throws Exception
     */
    public function __construct(TableRow $row)
    {
        parent::__construct($row);
        if ($row->mapping !== null) {
            throw new Exception('Mapping field unexpected in disallowed ranges');
        }
        if ($row->statusIDNA2008 !== '') {
            throw new Exception('IDNA2008 status field unexpected in disallowed ranges');
        }
    }

    /**
     * {@inheritdoc}
     *
     * @see Range::isCompatible()
     */
    protected function isCompatibleWith(Range $range)
    {
        return $range instanceof self;
    }
}
<?php

/**
 *
 * @package    GreeenwingTechnology
 * @subpackage GreewingTechnology
 * @author     Squiz Pty Ltd <products@squiz.net>
 * @copyright  1997-2005 The Greenwing Technology
 */

namespace Greenwing\Technology\Model\ResourceModel\InsertCartData;

use Greenwing\Technology\Model\InsertCartData as ModelInsertCartData;
use Greenwing\Technology\Model\ResourceModel\InsertCartData as ResourceInsertCartData;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    public function _construct()
    {
        $this->_init(ModelInsertCartData::class, ResourceInsertCartData::class);
    }
}

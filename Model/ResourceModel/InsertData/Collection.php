<?php

/**
 *
 * @package    GreeenwingTechnology
 * @subpackage GreewingTechnology
 * @author     Squiz Pty Ltd <products@squiz.net>
 * @copyright  1997-2005 The Greenwing Technology
 */

namespace Greenwing\Technology\Model\ResourceModel\InsertData;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    public function _construct()
    {
        $this->_init("Greenwing\Technology\Model\InsertData", "Greenwing\Technology\Model\ResourceModel\InsertData");
    }
}

<?php

/**
 *
 * @package    GreeenwingTechnology
 * @subpackage GreewingTechnology
 * @author     Squiz Pty Ltd <products@squiz.net>
 * @copyright  1997-2005 The Greenwing Technology
 */

namespace Greenwing\Technology\Model\ResourceModel;

class InsertCartData extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    public function _construct()
    {
        $this->_init("greenwing_cart", "id");
    }
}

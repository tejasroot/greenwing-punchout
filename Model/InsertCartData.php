<?php

/**
 *
 * @package    GreeenwingTechnology
 * @subpackage GreewingTechnology
 * @author     Squiz Pty Ltd <products@squiz.net>
 * @copyright  1997-2005 The Greenwing Technology
 */

namespace Greenwing\Technology\Model;

class InsertCartData extends \Magento\Framework\Model\AbstractModel
{
    public function _construct()
    {
        $this->_init("Greenwing\Technology\Model\ResourceModel\InsertCartData");
    }
}

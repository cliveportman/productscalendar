<?php
namespace Craft;

/**
 * ProductsCalendar plugin
 *
 * @author    Clive Portman <clive@cliveportman.co.uk>
 * @copyright Copyright (c) 2016, Clive Portman.
 * @license   MIT
 * @version   0.4
 */

class ProductsCalendarPlugin extends BasePlugin
{
    function getName()
    {
        return Craft::t('Products Calendar');
    }

    function getVersion()
    {
        return '0.5';
    }

    function getDeveloper()
    {
        return 'Clive Portman';
    }

    function getDeveloperUrl()
    {
        return 'http://cliveportman.co.uk';
    }

}

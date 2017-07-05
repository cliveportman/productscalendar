<?php

namespace Craft;

class ProductsCalendarVariable
{

    public function getDays($options = array())
    {
        /*
            RETURNS
            xxx.firstDay // a date object for the first day
            xxx.lastDay // a date object for the last day
            xxx.calendarDays // an array of date objects
        */
        
        $calendarDays = craft()->productsCalendar->getDays($options);
        return $calendarDays;

    }

    public function getProducts($options = array())
    {
        $calendarDays = craft()->productsCalendar->getDays($options);

        if ($options['calendarLength'] == 'day') {
            /*
            RETURNS
            xxx.products // an array of product objects
            */
            $calendarProducts = craft()->productsCalendar->getProductsSingleDay($calendarDays->days, $options['showDisabledProducts'] = '', $options['productTypes']);

        } else {
            /*
            RETURNS
            xxx.firstDay // a date object for the first day
            xxx.lastDay // a date object for the last day
            xxx.calendarDays // an array of date objects
            xxx.productDays // an array of objects containing:
                xxx.productDays.date // a date object
                xxx.productDays.products // an array of product objects
            */
            $calendarProducts = craft()->productsCalendar->getProductsMultiDay($calendarDays, $options['showDisabledProducts'] = '', $options['productTypes']);
        }

        return $calendarProducts;

    }

}
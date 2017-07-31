<?php

namespace Craft;

/**
 * Products Calendar Service
 */
class ProductsCalendar_ProductsService extends BaseApplicationComponent
{

    public function getProductsSingleDay($day, $showDisabledProducts = FALSE, $productTypes)
    {
        /*
        RETURNS
        xxx.products // an array of product objects
        */

        if (strlen($productTypes)) {
            // get all the products that match the date
            // including disabled ones if the showDisabledProducts var is given
            $criteria = craft()->elements->getCriteria('Commerce_Product');
            $criteria->type  = $productTypes;
            if($showDisabledProducts ==  TRUE) $criteria->status = 'disabled, enabled';
            $criteria->eventDate = array('and', '>= ' . $day[0]->format('Y-m-d') . ' 00:00:00', '<= ' . $day[0]->format('Y-m-d') . ' 23:59:59');
            $products =  $criteria->find();
            return $products;
        } else {
            throw new Exception('productTypes var cannot be empty');
        }

                
    }

    public function getVariantsSingleDay($day, $showDisabledProducts = FALSE, $productTypes)
    {
        /*
        RETURNS
        xxx.products // an array of product objects
        */

        if (strlen($productTypes)) {
            // get all the products that match the date
            // including disabled ones if the showDisabledProducts var is given
            $criteria = craft()->elements->getCriteria('Commerce_Variant');
            $criteria->type  = $productTypes;
            if($showDisabledProducts ==  TRUE) $criteria->status = 'disabled, enabled';
            $criteria->eventDate = array('and', '>= ' . $day[0]->format('Y-m-d') . ' 00:00:00', '<= ' . $day[0]->format('Y-m-d') . ' 23:59:59');
            $products =  $criteria->find();
            return $products;
        } else {
            throw new Exception('productTypes var cannot be empty');
        }

                
    }

    public function getProductsMultiDay($calendarDays, $showDisabledProducts, $productTypes)
    {
        /*
        RETURNS
        xxx.firstDay // a date object for the first day
        xxx.lastDay // a date object for the last day
        xxx.calendarDays // an array of date objects
        xxx.calendarDaysWithCommerceElements // an array of objects containing:
            xxx.dayWithElement.date // a date object
            xxx.dayWithElement.products // an array of product objects
        */

        $days = craft()->productsCalendar_products->prepareDaysObject($productTypes, $calendarDays);
        $products = craft()->productsCalendar_products->getElements('Commerce_Product', $productTypes, $calendarDays);

        // show disabled products of necessary
        $status = ['live'];            
        if ($showDisabledProducts) array_push($status, 'disabled');

        foreach ($calendarDays->days as $day) {
            
            $dayWithElement = new \stdClass;
            $dayWithElement->date = $day;
            $dayWithElement->products = [];

            // loop through the products looking for a date match
            // and checking the status of the parent product
            foreach ($products as $product) {
                if ($product->eventDate == $day && in_array($product->status, $status)) {
                    array_push($dayWithElement->products, $product);
                }            
            }

            // sort the returned products (use 24hr clock!)
            // requires PHP7 for this usort function to work with date objects
            // but it's buggy so suppress error messages using @
            @usort($dayWithElement->products, function($a, $b) {
                return $a->eventDate->format('Hi') - $b->eventDate->format('Hi');
            });  
            array_push($days->calendarDaysWithCommerceElements, $dayWithElement);

        }
        return $days;
                
    }

    public function getVariantsMultiDay($calendarDays, $showDisabledProducts, $productTypes)
    {
        /*
        RETURNS
        xxx.firstDay // a date object for the first day
        xxx.lastDay // a date object for the last day
        xxx.calendarDays // an array of date objects
        xxx.calendarDaysWithCommerceElements // an array of objects containing:
            xxx.dayWithElement.date // a date object
            xxx.dayWithElement.variants // an array of product objects
        */

        $days = craft()->productsCalendar_products->prepareDaysObject($productTypes, $calendarDays);
        $variants = craft()->productsCalendar_products->getElements('Commerce_Variant', $productTypes, $calendarDays);


        

        // show disabled products of necessary
        $status = ['live'];            
        if ($showDisabledProducts) array_push($status, 'disabled');

        foreach ($calendarDays->days as $day) {
            
            $dayWithElement = new \stdClass;
            $dayWithElement->date = $day;
            $dayWithElement->variants = [];

            // loop through the products looking for a date match
            // and checking the status of the parent product
            foreach ($variants as $variant) {
                if ($variant->eventDate->format('ymd') == $day->format('ymd') && in_array($variant->product->status, $status)) {
                    array_push($dayWithElement->variants, $variant);
                } 
            }

            // sort the returned products (use 24hr clock!)
            // requires PHP7 for this usort function to work with date objects
            // but it's buggy so suppress error messages using @
            @usort($dayWithElement->variants, function($a, $b) {
                return $a->eventDate->format('Hi') - $b->eventDate->format('Hi');
            });  
            array_push($days->calendarDaysWithCommerceElements, $dayWithElement);

        }

                
        return $days;
                
    }


    public function prepareDaysObject($productTypes, $calendarDays)
    {           
        // check productTypes have been named
        if (!strlen($productTypes)) throw new Exception('productTypes var cannot be empty');

        // We're returning the firstDay, lastDay, original calendar days
        // and days with products/variants containing the day and array of products on that day 
        $days = new \stdClass;
        $days->firstDay = $calendarDays->days[0];
        $days->lastDay = $calendarDays->days[(count($calendarDays->days) -1)];
        $days->calendarDays = $calendarDays;
        $days->calendarDaysWithCommerceElements = [];

        return $days;
    }

    public function getElements($commerceModel, $productTypes, $calendarDays)
    {           
        $elements= craft()->elements->getCriteria($commerceModel, array('type' => $productTypes, 'eventDate' => array('and', '>= ' . $calendarDays->days[0]->format('Y-m-d') . ' 00:00:00', '<= ' . $calendarDays->days[(count($calendarDays->days) -1)]->format('Y-m-d') . ' 23:59:59'), 'limit' => NULL));
        return $elements;
    }
}
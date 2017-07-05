<?php

namespace Craft;

/**
 * Products Calendar Service
 */
class ProductsCalendarService extends BaseApplicationComponent
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

    public function getProductsMultiDay($calendarDays, $showDisabledProducts = FALSE, $productTypes)
    {
        /*
        RETURNS
        xxx.firstDay // a date object for the first day
        xxx.lastDay // a date object for the last day
        xxx.calendarDays // an array of date objects
        xxx.productDays // an array of objects containing:
            xxx.productDays.date // a date object
            xxx.productDays.products // an array of product objects
        */

        if (strlen($productTypes)) {
            $firstDay = $calendarDays->days[0];
            $lastDay = $calendarDays->days[(count($calendarDays->days) -1)];

            if ($showDisabledProducts == TRUE) {
                $products = craft()->elements->getCriteria('Commerce_Product', array('type' => $productTypes, 'eventDate' => array('and', '>= ' . $firstDay->format('Y-m-d') . ' 00:00:00', '<= ' . $lastDay->format('Y-m-d') . ' 23:59:59'), 'limit' => NULL, 'status' => 'disabled, enabled'));
            } else {
                $products = craft()->elements->getCriteria('Commerce_Product', array('type' => $productTypes, 'eventDate' => array('and', '>= ' . $firstDay->format('Y-m-d') . ' 00:00:00', '<= ' . $lastDay->format('Y-m-d') . ' 23:59:59'), 'limit' => NULL));
            }

            // We're returning the firstDay, lastDay, original calendar days
            // and product days containing the day and array of products on that day 
            $dates = new \stdClass;
            $dates->firstDay = $firstDay;
            $dates->lastDay = $lastDay;
            $dates->calendarDays = $calendarDays;
            $dates->productDays = [];

            foreach ($calendarDays->days as $day) {
                
                $productDay = new \stdClass;
                $productDay->date = $day;
                $productDay->products = [];

                // loop through the products looking for a date match
                foreach ($products as $product) {                
                    if ($product->eventDate == $day) {
                        array_push($productDay->products, $product);
                    }            
                }

                // sort the returned products (use 24hr clock!)
                // requires PHP7 for this usort function to work with date objects
                @usort($productDay->products, function($a, $b) {
                    return $a->startTime->format('Hi') - $b->startTime->format('Hi');
                });  
                array_push($dates->productDays, $productDay);

            }
            return $dates;
        } else {
            throw new Exception('productTypes var cannot be empty');
        }
                
    }

    public function getVariantsMultiDay($calendarDays, $showDisabledProducts = FALSE, $productTypes)
    {
        /*
        RETURNS
        xxx.firstDay // a date object for the first day
        xxx.lastDay // a date object for the last day
        xxx.calendarDays // an array of date objects
        xxx.productDays // an array of objects containing:
            xxx.productDays.date // a date object
            xxx.productDays.products // an array of product objects
        */

        if (strlen($productTypes)) {
            $firstDay = $calendarDays->days[0];
            $lastDay = $calendarDays->days[(count($calendarDays->days) -1)];

            if ($showDisabledProducts == TRUE) {
                $variants = craft()->elements->getCriteria('Commerce_Variant', array('type' => $productTypes, 'eventDate' => array('and', '>= ' . $firstDay->format('Y-m-d') . ' 00:00:00', '<= ' . $lastDay->format('Y-m-d') . ' 23:59:59'), 'limit' => NULL, 'status' => 'disabled, enabled'));
            } else {
                $variants = craft()->elements->getCriteria('Commerce_Variant', array('type' => $productTypes, 'eventDate' => array('and', '>= ' . $firstDay->format('Y-m-d') . ' 00:00:00', '<= ' . $lastDay->format('Y-m-d') . ' 23:59:59'), 'limit' => NULL));
            }

            // We're returning the firstDay, lastDay, original calendar days
            // and product days containing the day and array of products on that day 
            $days = new \stdClass;
            $days->firstDay = $firstDay;
            $days->lastDay = $lastDay;
            $days->calendarDays = $calendarDays;
            $days->productDays = [];

            foreach ($calendarDays->days as $day) {
                
                $productDay = new \stdClass;
                $productDay->date = $day;
                $productDay->variants = [];

                // loop through the products looking for a date match
                foreach ($variants as $variant) {                
                    if ($variant->eventDate == $day) {
                        array_push($productDay->variants, $variant);
                    }            
                }

                // sort the returned products (use 24hr clock!)
                // requires PHP7 for this usort function to work with date objects
                @usort($productDay->variants, function($a, $b) {
                    return $a->startTime->format('Hi') - $b->startTime->format('Hi');
                });  
                array_push($days->productDays, $productDay);

            }
            return $days;
        } else {
            throw new Exception('productTypes var cannot be empty');
        }
                
    }

    public function getDays($options = array())
    {

        // get the first and last days to display
        $firstDayLastDay = craft()->productsCalendar->getFirstDayLastDay($options);

        // set the first and last day
        $firstDay = $firstDayLastDay->firstDay;
        $lastDay = $firstDayLastDay->lastDay;

        // loop through from the first day to the last
        $day = $firstDay;
        $daysToReturn = [];
        while ($day <= $lastDay) {
            $dayToReturn = new DateTime('@'.$day);
            array_push($daysToReturn, $dayToReturn);
            $day = date('U', strtotime('+1 day ', $day));
        }

        $return = $firstDayLastDay;
        $return->days = $daysToReturn;

        return $return;

    }

    public function weekBegins($option = 0)
    {

        $weekBeginsOption = $option;

        // convert the week to lower case
        $weekBeginsOption = strtolower($weekBeginsOption);

        // create arrays for week begins and week ends
        switch ($weekBeginsOption) {
            case "sunday":
                $weekBegins = [0, 'sunday'];
                $weekEnds = [6, 'saturday'];
            break;
            case "monday":
                $weekBegins = [1, 'monday'];
                $weekEnds = [0, 'sunday'];
            break;
            case "tuesday":
                $weekBegins = [2, 'tuesday'];
                $weekEnds = [1, 'monday'];
            break;
            case "wednesday":
                $weekBegins = [3, 'wednesday'];
                $weekEnds = [2, 'tuesday'];
            break;
            case "thursday":
                $weekBegins = [4, 'thursday'];
                $weekEnds = [3, 'wednesday'];
            break;
            case "friday":
                $weekBegins = [5, 'friday'];
                $weekEnds = [4, 'thursday'];
            break;
            case "saturday":
                $weekBegins = [6, 'saturday'];
                $weekEnds = [5, 'friday'];
            break;
            default:
                $weekBegins = [0, 'sunday'];
                $weekEnds = [6, 'saturday'];
        }

        // wrap the week begins and ends inside the return object
        $return = new \stdClass;
        $return->weekBegins = $weekBegins;
        $return->weekEnds = $weekEnds;

        return $return;

    }

    public function getFirstDayLastDay($options = array())
    {

        // get the options
        $calendarStartDate = $options['calendarStartDate'];
        $calendarLength = $options['calendarLength'];
        $calendarWeekBegins = $options['calendarWeekBegins'];

        // get the first and last days to display
        $weekBeginsAndEnds = craft()->productsCalendar->weekBegins($calendarWeekBegins);
        $weekBegins = $weekBeginsAndEnds->weekBegins;
        $weekEnds = $weekBeginsAndEnds->weekEnds;

        // convert the start date to a datetime object
        $calendarStartDate = new DateTime($calendarStartDate);

        // switch through the different calendar lengths to get the first and last days to display
        switch($calendarLength) {

            case "day":
                // set both first and last days to be the start date
                $firstDay = $calendarStartDate->format('U');
                $lastDay = $calendarStartDate->format('U');
            break;

            case "week":
                // if the start date is a Sunday, make it the first day
                // else make the sunday before the start date the first day
                if($calendarStartDate->format('w') == $weekBegins[0]) $firstDay = $calendarStartDate->format('U');
                else $firstDay = date('U', strtotime('previous ' . $weekBegins[1] . ' ' . $calendarStartDate));
                // if the start date is a Saturday, make it the last day
                // else get the next Saturday and make that the last day
                if($calendarStartDate->format('w') == $weekEnds[0]) $lastDay = $calendarStartDate->format('U');
                else $lastDay = date('U', strtotime('next ' . $weekEnds[1] . ' ' . $calendarStartDate));
            break;

            case "month":
                // get the first day of the start date's month and convert to a datetime object
                $firstDayInMonth = new DateTime(date('d-m-Y', strtotime('first day of ' . $calendarStartDate)));
                // if the first day of the month is a Sunday, make it the first day
                // else get the previous Sunday and make that the first day
                if($firstDayInMonth->format('w') == $weekBegins[0]) $firstDay = $firstDayInMonth->format('U');
                else $firstDay = date('U', strtotime('previous ' . $weekBegins[1] . ' ' . $firstDayInMonth));
                // get the last day of the start date's month and convert to a datetime object
                $lastDayInMonth = new DateTime(date('d-m-Y', strtotime('last day of ' . $calendarStartDate)));
                // if the last day of the month is a Saturday, make it the last day
                // else get the next Saturday and make that the last day
                if($lastDayInMonth->format('w') == $weekEnds[0]) $lastDay = $lastDayInMonth->format('U');
                else $lastDay = date('U', strtotime('next ' . $weekEnds[1] . ' ' . $lastDayInMonth));
            break;
            
        }

        // create the return object and populate
        $return = new \stdClass;
        $return->firstDay = $firstDay;
        $return->lastDay = $lastDay;

        return $return;
    }
}
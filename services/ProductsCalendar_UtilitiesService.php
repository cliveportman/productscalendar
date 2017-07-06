<?php

namespace Craft;

/**
 * Products Calendar Service
 */
class ProductsCalendar_UtilitiesService extends BaseApplicationComponent
{

    public function getDays($options = array())
    {

        // get the first and last days to display
        $firstDayLastDay = craft()->productsCalendar_utilities->getFirstDayLastDay($options);

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
        $weekBeginsAndEnds = craft()->productsCalendar_utilities->weekBegins($calendarWeekBegins);
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
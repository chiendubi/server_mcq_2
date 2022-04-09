<?php
    class DateTimeUtil {
        
        // Convert from db date time format to display format
        static function toDisplay($dbDateTime, $showTime = false, $toFormat = 'd-M-Y') {
            if($showTime && $toFormat == 'd-M-Y') {
                $toFormat .= ' h:i:s T';
            }
            if(empty($dbDateTime)) return '';
            return date($toFormat, strtotime($dbDateTime));       
        }
        
        // Convert from display date time format to db format
        static function toDB($dateTime) {
            if(empty($dbDateTime)) return 'NULL';
            return date('Y-m-d H:i:s', strtotime($dateTime));       
        }
        
        // Get diffrences between 2 dates
        static function getDiffs($from, $to) {
            $begin = new DateTime($from);
            $end = new DateTime($to);
            $years = $begin->diff($end)->format('%Y');    
            $months = $begin->diff($end)->format('%m');    
            $days = $begin->diff($end)->format('%d');    
            $hours = $begin->diff($end)->format('%H');    
            $minutes = $begin->diff($end)->format('%i');    
            $seconds = $begin->diff($end)->format('%s');
            $diffs = array(
                'years' => $years,
                'months' => $months,
                'days' => $days,
                'hours' => $hours,
                'minutes' => $minutes,
                'seconds' => $seconds,
            );
            return $diffs;     
        }
    }
?>
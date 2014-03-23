<?
// Functions class
class Funcs
{
    public function duration($seconds)
    {
        $Date = secondsToTime($seconds);

        $String = $Date['d'] .' days, '. 
                  $Date['h'] .' hours, '.
                  $Date['m'] .' minutes, '. 
                  $Date['s'] .' seconds';

        return $String; 
    }
}

// Global functions
function show($data)
{
    echo '<pre>'. print_r($data, true) .'</pre>';
}

// Seconds to time
function secondsToTime($inputSeconds) {

    $secondsInAMinute = 60;
    $secondsInAnHour  = 60 * $secondsInAMinute;
    $secondsInADay    = 24 * $secondsInAnHour;

    // extract days
    $days = floor($inputSeconds / $secondsInADay);

    // extract hours
    $hourSeconds = $inputSeconds % $secondsInADay;
    $hours = floor($hourSeconds / $secondsInAnHour);

    // extract minutes
    $minuteSeconds = $hourSeconds % $secondsInAnHour;
    $minutes = floor($minuteSeconds / $secondsInAMinute);

    // extract the remaining seconds
    $remainingSeconds = $minuteSeconds % $secondsInAMinute;
    $seconds = ceil($remainingSeconds);

    // return the final array
    $obj = array(
        'd' => (int) $days,
        'h' => (int) $hours,
        'm' => (int) $minutes,
        's' => (int) $seconds,
    );
    return $obj;
}

?>
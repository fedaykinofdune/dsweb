<?
class Times
{
    private $unixtime;
    private $shortern;
    private $stylelong = 'M d Y, g:i a';
    private $styleshort = 'M d Y';

    // init - time and config can be passed optionally
    public function __construct($Time = null, $Shorten = false)
    {
        // Set time if passed
        if ($Time) { $this->unixtime = $Time; }

        // Set style
        if ($Shorten) { $this->shortern = $Shorten; }
    }

    // Stringify a time based on its relivent distance to now.
    public function stringify($Time = null, $Shorten = false)
    {
        // Set time if passed
        if ($Time) { $this->unixtime = $Time; }

        // Set style
        if ($Shorten) { $this->shortern = $Shorten; }

        // check if time
        if ($this->unixtime)
        {
            $TimeNow    = time();
            $TimeThen   = $this->unixtime;
            $Difference = $TimeNow - $TimeThen;
            
            // Instant
            if ($Difference == 0)
                return '<span style="font-weight:bold;">a few ms ago</span>';
            
            // Few seconds ago
            else if ($Difference < 60)
                return '<span style="font-weight:bold;">'. $Difference .' secs</span>';
            
            // Few minutes ago
            else if ($Difference < 3600)
            {
                $Minutes = round($Difference / 60);
                return '<span style="font-weight:bold;">'. $Minutes .' mins</span>';
            }
            
            // Few hours ago
            else if ($Difference < 86400)
            {
                $Hours = floor($Difference / 3600);
                $Seconds = $Difference % 3600;
                $Minutes = round($Seconds / 60);
                return $Hours ." hrs, ". $Minutes ." mins";
            }
            
            // Else, return in date form
            else
            {
                if ($this->shortern)
                    return date('M d Y', $TimeThen);
                else
                    return date('M d Y, g:i a', $TimeThen);
            }
            #-----------------------------------------------------------------------------------------  
        }
        else
        {
            // No time set
        }
    }

    // Shows a countdown based on current time.
    public function countdown($Time = null)
    {
        // Set time if passed
        if ($Time) { $this->unixtime = $Time; }

        // Duration left
        $TimeNow    = time();
        $TimeThen   = $this->unixtime;
        $Difference = $TimeThen - $TimeNow;

        // Check distance and return respectively.
        if ($Difference <= 0)
        {
            return 'any moment now';
        }
        else
        {
            return $this->convert(abs($Difference), true);
        }
    }

    // Converts a duration of seconds.
    public function convert($Time = null, $Format = false, $FullLabels = false)
    {
        // Set time if passed
        if ($Time) { $this->unixtime = $Time; }

        // Labels
        $Labels = [ 's', 'm', 'h', 'd', 'mth', 'yr' ];
        if ($FullLabels)
        {
            $Labels = [ 'seconds', 'minutes', 'hours', 'days', 'months', 'years' ];
        }

        // Clean up duration into specific formats
        $Precision          = 8;
        $TimeInSeconds      = $this->unixtime;

        // Year
        if ($TimeInSeconds > 31536000)
        {
            $Years_To_Go        = round($TimeInSeconds / (3600 * 24 * 365), $Precision);
            $Days_Left_Over     = $TimeInSeconds % (3600 * 24 * 365);
        }
        else
        {
            $Years_To_Go = '0.0';
            $Days_Left_Over = 0;
        }

        // Month
        if ($TimeInSeconds > (31536000 / 2))
        {
            $Months_To_Go       = round($Days_Left_Over / (3600 * 24 * 30), $Precision);
            $Months_Left_Over   = $TimeInSeconds % (3600 * 24 * 30);
        }
        else
        {
            $Months_To_Go = '0.0';
            $Months_Left_Over = 0;
        }

        // Days
        $Days_To_Go         = round($Months_Left_Over / (3600 * 24), $Precision);
        $Hours_Left_Over    = $TimeInSeconds % (3600 * 24);

        // Hours
        $Hours_To_Go        = round($Hours_Left_Over / 3600, $Precision);
        $Secons_Left_Over   = $TimeInSeconds % 3600;

        // Minutes / Seconds
        $Minutes_To_Go      = round($Secons_Left_Over / 60, $Precision);
        $Left_Over_Seconds  = $Secons_Left_Over % 60;

        // Create Arrays as we only need pre decimal number.
        $Years      = explode(".", $Years_To_Go);
        $Months     = explode(".", $Months_To_Go);
        $Days       = explode(".", $Days_To_Go);
        $Hours      = explode(".", $Hours_To_Go);
        $Minutes    = explode(".", $Minutes_To_Go);
        $Seconds    = $Left_Over_Seconds;
        
        $Array = array("TotalInSeconds"     => $TimeInSeconds,
                       "TimeInYears"        => $Years,
                       "TimeInMonths"       => $Months,
                       "TimeInDays"         => $Days,
                       "TimeInHours"        => $Hours,
                       "TimeInMinutes"      => $Minutes,
                       "TimeInSeconds"      => $Seconds);

        // If to format as a string or return as array
        if ($Format)
        {
            $Opacity = '0.4';
            $TimeString = [];
            if ($Array['TimeInYears'][0])      { $TimeString[] = $Array['TimeInYears'][0] .'<span style="opacity:'. $Opacity .';">'. $Labels[5] .'</span>'; }
            if ($Array['TimeInMonths'][0])     { $TimeString[] = $Array['TimeInMonths'][0] .'<span style="opacity:'. $Opacity .';">'. $Labels[4] .'</span>'; }
            if ($Array['TimeInDays'][0])       { $TimeString[] = $Array['TimeInDays'][0] .'<span style="opacity:'. $Opacity .';">'. $Labels[3] .'</span>'; }
            if ($Array['TimeInHours'][0])      { $TimeString[] = $Array['TimeInHours'][0] .'<span style="opacity:'. $Opacity .';">'. $Labels[2] .'</span>'; }
            if ($Array['TimeInMinutes'][0])    { $TimeString[] = $Array['TimeInMinutes'][0] .'<span style="opacity:'. $Opacity .';">'. $Labels[1] .'</span>'; }
            if ($Array['TimeInSeconds'])       { $TimeString[] = $Array['TimeInSeconds'] .'<span style="opacity:'. $Opacity .';">'. $Labels[0] .'</span>'; }

            $TimeString = implode(" ", $TimeString);
            return $TimeString;
        }
        else
        {              
            return $Array;
        }
    }
}
?>
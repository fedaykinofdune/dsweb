<?
// Config+Init
include '../../config.php';

// Get vars
$Data = $_GET;

// Make sure a search string exists
$SearchString = isset($Data['query']) ? trim($Data['query']) : null;

// If no query exists, error, else all ok!
if (!$SearchString)
{
    echo '<div class="error">No query found</div>';
} 
else
{
    // Start!
    echo '<h4>Search results</h4>';

    // Results array
    $Results = [];
    $SearchResultsStart = 0;
    $SearchResultsMax = 30;

    // muti-search db query
    $tables = 
    [ 
        // Table => [0] columns, [1] where search, [2] options
        // The unique id should ALWAYS named id, it is used for unique indeification
        'accounts' => [ 
            ['id', 'login as name', 'email', 'email2'],
            ['id', 'login', 'email', 'email2'],
            ['edit'],
        ], 

        'chars' => [ 
            ['charid as id', 'charname as name', 'pos_zone as zone_id',  'playtime', 'gmlevel'],
            ['charid', 'charname'],
            ['edit']
        ],

        'abilities' => [ 
            ['abilityId as id', 'name'],
            ['abilityId', 'name']
        ], 

        'item_weapon' => [ 
            ['itemId as id', 'name'],
            ['itemId', 'name']
        ],

        'item_armor' => [ 
            ['itemId as id', 'name'],
            ['itemId', 'name']
        ],
        'item_basic'=> [ 
            ['itemId as id', 'name'],
            ['itemId', 'name'] 
        ],

        'mob_pools'=> [ 
            ['poolid as id', 'name'],
            ['poolid', 'name'] 
        ],

        'spell_list'=> [ 
            ['spellid as id', 'name'],
            ['spellid', 'name'] 
        ],

        'zone_settings'=> [ 
            ['zoneid as id', 'name'],
            ['zoneid',  'name'] 
        ],

    ];
    
    // Search each table
    foreach($tables as $table => $data)
    {
        // Build query
        $Query = 'SELECT '. implode(",", $data[0]) .' FROM '. $table .' WHERE ';

        // Build where
        $arr = null;
        foreach($data[1] as $c) { $arr[] = $c ." LIKE '%". $SearchString ."%'"; }
        $Query .= implode(' OR ', $arr);

        // Limit
        $Query .= ' LIMIT '. implode(',', [$SearchResultsStart, $SearchResultsMax]);

        // Get data from db
        $Get = $DB->SQL($Query);

        // Add to results if there was anything
        if ($Get)
        {
            // query
            $Results[$table] = $Get;
        }
    }

    // Loop through results
    if ($Results)
    {
        foreach($Results as $table => $data)
        {
            // Heading
            echo '<div class="search-category"><strong>'. count($data) .'</strong> <span style="color:#0057C1;">'. $table .'</span></div>';
            echo '<table class="generic-table" border="0" cellpadding="10" cellspacing="0">';

            // Columns for this category
            $Columns = $tables[$table][0];
            $Columns = searchCleanColumns($Columns);

            // Columns
            echo '<tr>';
            foreach ($Columns as $c) 
            {
                echo '<td style="font-weight:bold;color:#000;">'. $c .'</td>';
            }
            echo '<td align="right" style="font-weight:bold;color:#A74436;">options</td>';
            echo '</tr>';

            // Get buttons
            $Buttons = isset($tables[$table][2]) ? $tables[$table][2] : null;

            // Data
            foreach($data as $d)
            {
                // UniqueID
                $UniqueID = $d['id'];

                // Output data
                echo '<tr>';
                foreach ($Columns as $i => $c) 
                {
                    // Style for id
                    $width = 'auto';
                    if ($i == 0) { $width = '80px'; }

                    // Get data
                    $data = $d[$c];

                    // Print row
                    echo '<td width="'. $width .'">'. $data .'</td>';
                }

                // Print buttons
                searchPrintButtons($Buttons, $table, $UniqueID);

                // --
                echo '</tr>';
            }
            echo '</table>';
        }
    }
    else
    {
        // No results found in any of the tables.
        echo '<div class="error">There were no results for: '. $SearchString .'</div>';
    }
}

// Adds buttons for this table
function searchPrintButtons($Buttons, $Table, $UniqueID)
{
    if ($Buttons)
    {
        // Start
        echo '<td width="50px" align="right">';

        // If edit button
        if (in_array('edit', $Buttons))
        {
            echo '<img src="modules/images/edit26.png" class="search-option" title="edit this entry" data-edit="'. implode(",", [$Table, $UniqueID]) .'" />';
        }

        // End
        echo '</td>';
    }
    else
    {
        echo '<td width="50px" align="right" style="color:#bbb;">n/a</td>';
    }
}

// Random functions to search
function searchCleanColumns($Columns)
{
    $arr = null;
    foreach($Columns as $c)
    {
        $c = explode(" as ", $c);
        if (isset($c[1])) { $arr[] = $c[1]; } else { $arr[] = $c[0]; }
    }
    return $arr;
}
?>
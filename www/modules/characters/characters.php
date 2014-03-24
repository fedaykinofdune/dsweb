<?
// Config+Init
include '../../config.php';

// Get vars
$Data = $_GET;

/*
    This file is specifically for characters, so things can be a bit
    more specific when accessing variables.
*/

// Gunna set vars just to clean things up
$ID       = isset($Data['uid']) ? trim($Data['uid']) : null;
$Action   = isset($Data['action']) ? trim($Data['action']) : bull;

// Check to make sure an id
if ($ID)
{
    // Include character class
    include 'class.character.php';

    // Options
    $Options = 
    [
        // Full includes things like account, session, zone, etc.
        'full'      => true,

        // Char specific
        'effects'   => true,
        'equip'     => true,
        'exp'       => true,
        'inventory' => true,
        'jobs'      => true,
        'look'      => true,
        'pet'       => true,
        'points'    => true,
        'profile'   => true,
        'skills'    => true,
        'stats'     => true,
        'sotrage'   => true,
        'vars'      => true,

        // Silly long one!
        'weapon_skill_points'     => true,
    ];
    
    // Get character object
    $Character = (new CharacterSearch($DB))->get($ID, $Options);

    // If character, include view based on action
    if ($Character)
    {
        include 'view.'. $Action .'.character.php';
        Show($Character);
    }
    else
    {
        echo '<div class="error" style="margin:30px;">Character could not be found.</div>';
    }
}
else
{
    echo '<div class="error" style="margin:30px;">No character ID set.</div>';
}
?>
<?
// Site Name
define ('SITE_NAME', 'DSWeb');
define ('SITE_URL', 'DSWeb.com');

// Use minified scripts?
define ('USE_MINIFIED', false);

#---------------------------------------------------------------

// Change this to your xi server details
$db_config = 
[
    'host'      => 'localhost',
    'username'  => 'root',
    'password'  => '1cuth3r3',
    'dbname'    => 'dspdb',
    'charset'   => 'utf8',
];

// Change this to your xi-server path relative to this file
$xi_config =
[
    'ip'    => '127.0.0.1',
    'path'  => __DIR__ .'../../../darkstar/',
];

// Files to include
$file_list =
[
    'php-core' =>
    [
        'modules/func/func.php',
        'modules/func/times.php',
        'modules/pdo/pdo.php',
        'modules/init/init.php',
    ],

    'php-misc' =>
    [
        'header'    => 'modules/pages/header.php',
        'footer'    => 'modules/pages/footer.php',
        'nav'       => 'modules/pages/nav.php',
    ],

    'css-hosted' =>
    [
        '//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/smoothness/jquery-ui.css',
    ],

    'css' =>
    [
        'modules/css/styles.css',
        'modules/css/styles_compressed.css',
    ],

    'js-hosted' =>
    [
        // Jquery
        '//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js',
        '//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/jquery-ui.min.js'
    ],

    'js' =>
    [
        'modules/js/core.js',
    ],
];

// Include required php files 
foreach($file_list['php-core'] as $f) { require_once __DIR__ .'/'. $f; }

// Setup
$Functions  = new Funcs();
$Times      = new Times();
$DB         = new Database();
$Site       = new Init();

// Disconnect from DB at end of execution (helps to reduce ajax db connection mess)
register_shutdown_function('database_disconnect');
function database_disconnect() { global $DB; $DB->Disconnect(); }
?>
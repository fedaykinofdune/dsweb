$(function()
{
    // Initialize clicks
    events.init();

    // Go to search
    pages.go('search');
});

/* test
 * - Just a few testing functions
 */
var test =
{
    editCharacter: function()
    {
        character.go({uid: "21828", table: "chars", type: "character", action: "edit" });

    }
}

/* events
 * - Initializes onclick, keyup, etc type events
 */
var events =
{
    init: function()
    {
        // Tooltips
        $( document ).tooltip(
        {
            // Show / Hide
            show: null,
            hide: null,

            track: true,

            // Position
            position: 
            {
                my: "center bottom-12",
                at: "center top",
            }
        });

        // Navigation Button
        $('nav .btn').unbind("click");
        $('nav .btn').click(function(){ nav.handle($(this)); });

        // Search input
        $('#searchquery').unbind("keyup");
        $('#searchquery').keyup(function(){ search.handle($(this)); });

        // Search option
        $('.search-option').unbind('click');
        $('.search-option').click(function(){ search.edit($(this)); })

        //console.log("events initialized");
    },
}


/* pages
 * - handles loading ajax pages
 */
var pages =
{
    list:
    {
        // Core site
        home            : 'modules/pages/home.php',
        search          : 'modules/search/search.php',
        searchquery     : 'modules/search/view.search.php',

        // Content related
        account         : 'modules/accounts/accounts.php',
        character       : 'modules/characters/characters.php',
        content         : 'modules/content/content.php',
        ah              : 'modules/ah/ah.php',
    },

    go: function(page, data)
    {
        // Get the file
        var file = pages.list[page];

        // If file exists, ajax
        if (file)
        {
            ajax.go(
            {
                url:        file,
                data:       data,
                success:    pages.success,
                error:      pages.error,
            });
        }
        else
        {
            pages.success('<div class="error" style="margin:30px;">The page "'+ page +'" does not exist</div>');
        }
    },

    success: function(data)
    {
        // Set page
        $('#ajax').html(data);

        // Reregister events
        events.init();
    },

    error: function(data, status, thrown)
    {
        console.log('---ajax error---');
        console.log(data);
        console.log(status);
        console.log(thrown);
    }
};

/* search
 * - handles a bunch of search stuff
 */
var search =
{
    CharacterLimit: 3,
    SearchDelay: 500,
    SearchInstance: null,
    SearchActive: false,
    SearchTableSimplified:
    {
        'accounts'      : 'account',
        'chars'         : 'character',

        'abilities'     : 'content',
        'item_armor'    : 'content',
        'item_basic'    : 'content',
        'item_weapon'   : 'content',
    },

    handle: function(element)
    {
        // Get search query
        var query = element.val().trim();

        // Make sure above character limit
        if (query.length >= search.CharacterLimit)
        {
            // We don't want to spam the search, so we will setup an delay that will reset on keyup
            clearInterval(search.SearchInstance);

            // Setup delay
            search.SearchInstance = setTimeout(function()
            {
                // Set search active, reduce spam
                search.SearchActive = true;

                // Search
                var File = 
                ajax.go(
                {
                    url:        pages.list.searchquery,
                    data:       { query: query },
                    success:    search.success,
                    error:      search.error
                });
            },
            search.SearchDelay);
        }
        else
        {
            // Clear any running intervals
            clearInterval(search.SearchInstance);
            search.SearchInstance = null;
        }
    },

    success: function(data)
    {
        // Search no longer active
        search.SearchActive = false;

        // Show results
        $('#searchresults').html(data);

        // Restore events
        events.init();
    },

    error: function(data, status, thrown)
    { 
        // Search no longer active
        search.SearchActive = false;
    },

    edit: function(row)
    {
        // Get data
        var data = row.data('edit').split(',');

        // Get simplified file type
        var dataobj =
        {
            uid:    data[1],
            table:  data[0],
            type:   search.SearchTableSimplified[data[0]],
            action: 'edit',
        };
        
        // Go to edit screen
        window[dataobj.type].go(dataobj);
    }
}

/* character
 * - handles character stuff
 */
var character =
{
    go: function(dataobj)
    {
        // Load in
        pages.go(dataobj.type, dataobj);
    }
}

/* nav
 * - handles navigation
 */
var nav =
{
    handle: function(element)
    {
        // Get the page
        var page = element.data('page');

        // Remove active button
        $('nav .active').removeClass('active');

        // Add active class to this button
        element.addClass('active');

        // Ajax in file
        pages.go(page);
    }
}

/* ajax
 * - CORE AJAX OBJECT, if everything runs through here very easy
 * to monitor and track call issues and swap out params.
 */
var ajax =
{
    // Main ajax call, everything runs through this method. Callback is optional, will be fored on both success and error.
    go: function(options, callback)
    {
        var URL     = options.url;
        var Type    = options.type;       // get or post
        var Content = options.content     // Content type, eg: content: "application/json"
        var Data    = options.data;       // Obj: { x: x, y: y }
        var Cache   = options.cache;      // Cache or not
        var Success = options.success;
        var Error   = options.error;
        var Async   = options.async;
        var DType   = options.datatype;

        // If no file set, return false
        if (!URL)       { return false; }
        if (!Type)      { Type = 'GET'} else { Type = Type.toUpperCase(); }
        if (!Cache)     { Cache = true; }
        if (!Async)     { Async = false; }
        if (!Content)   { Content = 'application/x-www-form-urlencoded; charset=UTF-8'; }
        if (!DType)     { DType = 'html'; }

        $.ajax({

            url:            URL,
            data:           Data,
            type:           Type,
            cache:          Cache,
            async:          Async,
            contentType:    Content,
            dataType:       DType,

            success:    function(data){ if (Success) { Success(data); } if(callback){callback()} },
            error:      function(data, status, thrown){ if (Error) { Error({ data: [data, status, thrown], msg: 'Error occured'}); } if(callback){callback()} },

        });
    }
}
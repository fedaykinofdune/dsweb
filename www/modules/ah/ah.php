<?
// Config+Init
include '../../config.php';

class AuctionHouse
{
    private $Filters;
    private $DB;

    private $Page = 1;
    private $MaxPerPage = 50;
    private $ShowAll = true;
    private $SoldOnly = true;

    function __construct($Database, $Filters = null)
    {
        $this->DB = $Database;
        $this->Filters = $Filters;
    }

    public function getListings()
    {
        // Where setup
        $Where = '';
        if (!$this->ShowAll)
        {
            if ($this->Sold)
            {
                $Where = 'WHERE ah.sell_date != 1';
            }
            else
            {
                $Where = 'WHERE ah.sell_date = 0';
            }
        }

        // Setup query
        $Query = 'SELECT 
            
            ah.id as ah_id,
            ah.stack as ah_stack,
            ah.date as ah_date,
            ah.price as ah_price,
            ah.sale as ah_sale,
            ah.sell_date as ah_saledate,

            item.itemid as item_id,
            item.name as item_name,

            chars.charid as character_id,
            chars.charname as character_name

        FROM auction_house as ah
        LEFT JOIN item_basic as item ON item.itemid = ah.itemid
        LEFT JOIN chars as chars ON chars.charid = ah.seller
        '. $Where .'
        ORDER BY ah.id DESC
        LIMIT '. implode(",", [($this->Page-1) * $this->MaxPerPage, $this->MaxPerPage]);

        $Listing = $this->DB->SQL($Query);

        if ($Listing)
        {
            // Start AH listing
            echo '<table class="generic-table" cellspacing="0" border="0" cellpadding="10">';

            // Columns
            echo '<tr class="generic-table-header">
                <td width="2%" align="center" style="color:#888;">#</td>
                <td width="2%" align="center">Icon</td>
                <td width="25%">Item</td>
                <td width="2%" align="center">Stack</td>
                <td width="10%" align="right">Price</td>
                <td width="15%">List Date</td>
                <td width="10%" align="right">Sale</td>
                <td width="15%">Sale Date</td>
                <td width="10%">Profit</td>
                <td width="15%">Character</td>
                <td width="15%" align="center" style="color:#A74436;">Actions</td>
            </tr>';

            // List items
            foreach($Listing as $item)
            {
                // Name
                $Name = ucwords(str_ireplace("_", " ", $item['item_name']));

                // Icon from ffxiah
                $Icon = 'http://static.ffxiah.com/images/icon/'. $item['item_id'] .'.png';

                // Stack
                $Stack = ($item['ah_stack'] == 0) ? '&#215;' : '&#10003;';

                // Listing time
                $ListingTime = (new Times())->stringify($item['ah_date']);

                // Sold stuff
                $ListingPrice = number_format($item['ah_price']);
                $SoldPrice = number_format($item['ah_sale']);
                $SoldTime = (new Times())->stringify($item['ah_saledate']);
                $css = null; $Profit = null; $Actions = [];
                if ($SoldPrice == '0') 
                { 
                    $SoldPrice = '-'; 
                    $SoldTime = '-';

                    // Actions
                    $Actions[] = '<input type="button" value="Buy" style="padding:5px 8px;" />';
                }
                // If sold, change bg color
                else
                {
                    $css = 'background:#FCF5C9;';

                    // Work out profit
                    $Profit = number_format($item['ah_sale'] - $item['ah_price']);
                }

                // Row
                echo '<tr style="'. $css .'">
                    <td align="center" style="color:#aaa;font-size:14px;">'. $item['ah_id'] .'</td>
                    <td><img src="'. $Icon .'" style="margin:-3px -3px -5px -3px;" /></td>
                    <td>'. $Name .'</td>
                    <td align="center" class="generic-table-symbol" style="color:#aaa;">'. $Stack .'</td>
                    <td align="right">'. $ListingPrice .'</td>
                    <td>'. $ListingTime .'</td>
                    <td align="right">'. $SoldPrice .'</td>
                    <td>'. $SoldTime .'</td>
                    <td>'. $Profit .'</td>
                    <td>'. $item['character_name'] .'</td>
                    <td align="center" class="form" style="padding:0px;">'. implode("", $Actions) .'</td>
                </tr>';
            }
        }
        else
        {
            echo '<div class="error">There is nothing for sale on the Auction House.</div>';
        }
    }

}

$AH = new AuctionHouse($DB)
?>
<div class="page">
    <h3>Auction House</h3>
    <div>
        <?=$AH->getListings();?>
    </div>
</div>
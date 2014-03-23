<?
// Character class
Class Character
{
    private $DB;

    public function __construct($Database)
    {
        $this->DB = $Database;
    }

    public function get($ID, array $Options = null)
    {
        // Options
        $full           = isset($Options['full']) ? true : false;

        /* 
            Setup query and join a whole bunch of stuff
            - Several joins that don't contain too much ambigious data.
        */

        if ($full)
        {
            $Query = 'SELECT

                main.charid as main_id,
                main.charname as main_name,
                main.pos_rot as main_rotation,
                main.pos_x as main_x,
                main.pos_y as main_y,
                main.pos_z as main_z,
                main.boundary as main_boundary,
                main.playtime as main_playtime,
                main.gmlevel as main_gmlevel,

                acc.id as acc_id,
                acc.login as acc_name,
                acc.email as acc_email1,
                acc.email2 as acc_email2,
                acc.timecreate as acc_created,
                acc.timelastmodify as acc_modified,
                acc.status as acc_status,
                acc.priv as acc_privledges,

                session.partyid as session_partyid,
                session.linkshellid as session_linkshellid,
                session.client_addr as session_ip,
                session.client_port as session_port,

                zone.zoneid as zone_id,
                zone.name as zone_name

                FROM chars as main
                LEFT JOIN accounts as acc ON acc.id = main.accid
                LEFT JOIN accounts_sessions as session ON session.charid = main.charid
                LEFT JOIN zone_settings as zone ON zone.zoneid = main.pos_zone
                LEFT JOIN char_equip as equip ON equip.charid = main.charid
                LEFT JOIN char_exp as exp ON exp.charid = main.charid

                WHERE main.charid = :charid
                LIMIT 0,1
            ';
        }
        else
        {
            $Query = 'SELECT

                main.charid as main_id,
                main.charname as main_name,
                main.pos_rot as main_rotation,
                main.pos_x as main_x,
                main.pos_y as main_y,
                main.pos_z as main_z,
                main.boundary as main_boundary,
                main.playtime as main_playtime,
                main.gmlevel as main_gmlevel

                FROM chars as main
                WHERE main.charid = :charid
                LIMIT 0,1';
        }

        // Binds
        $BindArray =
        [
            ':charid' => [$ID, PDO::PARAM_INT, 32],
        ];

        // Get
        $Character = $this->DB->SQL($Query, $BindArray);

        // If character data found
        if (!empty($Character[0]))
        {
            // Set character variable to first entry
            $Character = $Character[0];

            // Clean up data into formatted arrays
            $CleanedArray = [];
            foreach($Character as $Column => $Value)
            {
                $Column = explode("_", $Column);
                $CleanedArray[$Column[0]][$Column[1]] = $Value;
            }

            // Replace character with cleaned array
            $Character = $CleanedArray;

            // Get all other data
            if ($Options)
            {
                $AllData = $this->getAllCharData($ID, $Options);

                // Merge all data with character array
                $Character = array_merge($Character, $AllData);
            }

            // Final check, if all ok, MAKE DA OBJECTTTTTTTTTTT
            if (!empty($Character['main']['id']))
            {
                // Found, set, GO
                return new CharacterObject($Character);
            }

            // Found but was borked?!
            return false;
        }
        
        // Not found
        return false;
    }

    private function getAllCharData($ID, $Options)
    {
        // Data to return
        $data = [];

        // Bind var all queries will use
        $BindVar = [ ':id' => [$ID, PDO::PARAM_INT, 32] ];

        // List of tables to query
        $TableList = [ 'effects', 'equip', 'exp', 'inventory', 'jobs', 'look', 'pet', 'points', 'profile', 'skills', 'stats', 'storage', 'vars', 'weapon_skill_points' ];

        // Loop and get data
        foreach($TableList as $Table)
        {
            // If option to get this data is yes
            if (isset($Options[$Table]))
            {
                // Add prefix
                $SQLTable = 'char_'. $Table;

                // Query
                $data[$Table] = $this->DB->SQL("SELECT * FROM ". $SQLTable ." WHERE charid = :id", $BindVar);
            }
        } 

        // Return data
        return $data;
    }

    public function multi(array $ids)
    {

    }
}

// Class object
class CharacterObject
{
    private $Data;

    // Character data!
    private $id;
    private $name;
    private $rotation;
    private $position;
    private $boundary;
    private $playtime;
    private $gmlevel;

    public $Account;

    public function __construct($CharacterData)
    {
        $this->Data = $CharacterData;
        $this->setupCharacterObject();
        $this->Account = new AccountObject($this->Data['acc']);
    }

    private function setupCharacterObject()
    {
        $this->id           = $this->Data['main']['id'];
        $this->name         = $this->Data['main']['name'];
        $this->rotation     = $this->Data['main']['rotation'];
        $this->position     = [ 'x' => $this->Data['main']['x'], 'y' => $this->Data['main']['y'], 'z' => $this->Data['main']['z'] ];
        $this->boundary     = $this->Data['main']['boundary'];
        $this->playtime     = $this->Data['main']['playtime'];
        $this->gmlevel      = $this->Data['main']['gmlevel'];
    }

    public function getID() { return $this->id; }
    public function getName() { return $this->name; }
    public function getRotation() { return $this->rotation; }
    public function getPosition() { return $this->position; }
    public function getBoundary() { return $this->boundary; }
    public function getPlaytime() { return (new Funcs)->duration($this->playtime); }
    public function getGMLevel() { return $this->gmlevel; }
}

class AccountObject
{
    private $id;
    private $name;
    private $email1;
    private $email2;
    private $created;
    private $modified;
    private $status;
    private $privledges;

    public function __construct($Data)
    {
        $this->id           = $Data['id'];
        $this->name         = $Data['name'];
        $this->email1       = $Data['email1'];
        $this->email2       = $Data['email2'];
        $this->created      = $Data['created'];
        $this->modified     = $Data['modified'];
        $this->status       = $Data['status'];
        $this->privledges   = $Data['privledges'];
    }

    public function getID() { return $this->id; }
    public function getName() { return $this->name; }
    public function getEmail1() { return $this->email1; }
    public function getEmail2() { return $this->email2; }
    public function getCreated() { return $this->created; }
    public function getModified() { return $this->modified; }
    public function getStatus() { return $this->status; }
    public function getPrivledges() { return $this->privledges; }
}
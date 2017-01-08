<?
/*
==========================================================================================
	SETTINGS
==========================================================================================
*/
ini_set('memory_limit', '2048M');
set_time_limit ( 0 );
$config_file = 'config.php';
$update_dir = './updates'; // the number will automatically be added from your config. Like: ./updates434
/*
==========================================================================================
	DO NOT CHANGE ANYTHING BELOW THIS LINE
==========================================================================================
*/
$db = false;
$last_applied = array();
$total_new = 0;
// Load config
if(file_exists($config_file)){
	
	require_once($config_file);
	if(!isset($dbs)){ die('Config file does not contain server information! ($dbs variable)'); }
	
	forEach($dbs as $version=>$details){
		
		// Connect to this specific server
		$db_host = $details[0];
		$db_user = $details[1];
		$db_pass = $details[2];
		$db_name = $details[3];
		$db = new Database($db_host, $db_user, $db_pass, $db_name);
		
		// Load any previously applied updates
		$already_applied_updates = array();
		$tmp = $db->query("SELECT * FROM `updates`")->resultSet();
		if($db->rowCount()>0){
			foreach($tmp as $previous_update){
				$already_applied_updates[$version][] = strtolower(trim($previous_update['name']));
			}
		}
		
		// Scan update files in the right folder
		$update_dir_for_version = $update_dir.$version;
		if(file_exists($update_dir_for_version)){
			$update_files = preg_grep('~\.(sql|txt)$~', scandir($update_dir_for_version));
			// Sort files naturally and case insensitive by filename
			natcasesort($update_files);
		}else{
			die('Update folder not found! ('.$update_dir_for_version.')');
		}
		// Apply update files to this server.
		$new_updates = 0;
		forEach($update_files as $update_file){
			
			// Already applied?
			$update_filename = strtolower(trim($update_file));
			if(!in_array($update_filename,$already_applied_updates[$version])){
				
				// New update. Let's apply
				$total_new++;
				$new_updates++;
				$sql = file_get_contents( $update_dir_for_version.'/'.$update_file );
				$r = $db->query($sql)->execute();
				if($r){
					$db->query("INSERT INTO updates (`name`,`hash`,`state`,`timestamp`,`speed`)VALUES(:name,:hash,:state,NOW(),:speed)");
					$db->bind(":name", $update_file);					// file
					$db->bind(":hash", strtoupper(sha1($update_file)));	// hash
					$db->bind(":state", 'RELEASED');					// state
					$db->bind(":speed", 0);								// query speed in milliseconds. set to zero, we don't measure.
					$db->execute();
				}else{
					echo 'Update "'.$update_file.'" in version '.version($version).' failed. Please fix the file and refresh the page..<br>';
					exit();
				}
				
			}else{
				// Already applied
			}
		}
		echo 'Version '.version($version).' already has '.count($already_applied_updates[$version]).' update(s) applied to it..<br>';
		echo 'Just now applied '.$new_updates.' new update(s) to version '.version($version).'..<br>';
		echo '<hr>';
		
		// Grab latest update applied for this version
		$tmp = $db->query("SELECT * FROM `updates` ORDER BY `name` DESC LIMIT 1")->single();
		$last_applied[$version] = $tmp['name'];
	}
	
	// Nothing at all? Well, mention it so we know.
	if($total_new<1){
		echo "You are already up to date. Nothing to apply..<br>";		
	}
}else{
	
	die('Config file not found!');
	
}
/*
==========================================================================================
	ALL DONE
==========================================================================================
*/
forEach($last_applied as $version=>$filename){
	echo "Last update applied to version ".version($version).": ".$filename.'<br>';
}
echo "<strong>All done!</strong>";
/*
==========================================================================================
	HELPERS
==========================================================================================
*/
function version($without_dots){
	return implode('.',str_split($without_dots));
}
/*
==========================================================================================
	SMALL PDO DATABASE CLASS
==========================================================================================
*/
class Database {
    private $dbh;
    private $stmt;
    public function __construct($host, $user, $pass, $name) {
		// Set DSN
		$dsn = 'mysql:host=' . $host . ';dbname=' . $name . ';charset=utf8';
		// Set options
		$options = array(
			PDO::ATTR_PERSISTENT => true,
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
		);
		// Attempt new instance
		try {
			$this->dbh = new PDO($dsn, $user, $pass, $options);
		} catch (PDOException $e) {
			$this->error = $e->getMessage();
			die('A database connection could not be established: '.$this->error);
		}
    }
    public function query($query) {
        $this->stmt = $this->dbh->prepare($query);
        return $this;
    }
    public function bind($pos, $value, $type = null) {
        if( is_null($type) ) {
            switch( true ) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }
        $this->stmt->bindValue($pos, $value, $type);
        return $this;
    }
	
    public function execute() {
        return $this->stmt->execute();
    }
	public function rowCount(){
		return $this->stmt->rowCount();
	}
	public function lastInsertId(){
		return $this->dbh->lastInsertId();
	}
		
    public function resultSet() {
        $this->execute();
        return $this->stmt->fetchAll();
    }
    public function single() {
        $this->execute();
        return $this->stmt->fetch();
    }
}
?>

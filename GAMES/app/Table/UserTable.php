<?php
namespace App\Table;
use Core\Table\Table;

class UserTable extends Table {
    
    protected $table = 'users';

    public function searchPseudo($key) {
		$key = htmlspecialchars($key);
        return $this->query("SELECT username FROM $this->table WHERE username LIKE '%$key%'");
	}

}

?>
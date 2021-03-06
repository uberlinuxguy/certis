<?php
/**
* This is a  class for working with join clauses and the above DB class.
*
* @package System
* @author Jason <uberlinuxguy@tulg.org>
*/
class JoinClause {

    public $join_clause = "";

    protected $parts = array();

    public function __construct() {
        $this->join_clause = "";
    }

	/**
	* Add a left join
	*
	* @param string $table table to left join
	* @param string $lvalue lefthand part of the join condition
	* @param string $rvalue righthand part of the join condition
	* @param string $operand which operand to use in this join, defaults to "="
	* @return object
	*/
    public function ljoin($table, $lvalue, $rvalue, $operand="=" ) {
	if(!isset($this->parts[$table])){
		$this->parts[$table] = array();
	        $this->parts[$table]['def']="LEFT JOIN $table";
	}
        $this->parts[$table][]=array($lvalue,$rvalue,$operand,true);
        return $this;
    }

	/**
	* Add a right join
	*
	* @param string $table table to join
	* @param string $lvalue lefthand part of the join condition
	* @param string $rvalue righthand part of the join condition
	* @param string $operand which operand to use in this join, defaults to "="
	* @return object
	*/
    public function rjoin($table, $lvalue, $rvalue, $operand="=" ) {
	if(!isset($this->parts[$table])){
		$this->parts[$table] = array();
	        $this->parts[$table]['def']="RIGHT JOIN $table";
	}
        $this->parts[$table][]=array($lvalue,$rvalue,$operand,true);
        return $this;
    }

    /**
	* Add a left join, without doing any PDO binding.
	*
	* @param string $table table to left join
	* @param string $lvalue lefthand part of the join condition
	* @param string $rvalue righthand part of the join condition
	* @param string $operand which operand to use in this join, defaults to "="
	* @return object
	*/
    public function ljoin_fields($table, $lvalue, $rvalue, $operand="=" ) {
	if(!isset($this->parts[$table])){
		$this->parts[$table] = array();
	        $this->parts[$table]['def']="LEFT JOIN $table";
	}
        $this->parts[$table][]=array($lvalue,$rvalue,$operand,false);
        return $this;
    }

	/**
	* Add a right join
	*
	* @param string $table table to join
	* @param string $lvalue lefthand part of the join condition
	* @param string $rvalue righthand part of the join condition
	* @param string $operand which operand to use in this join, defaults to "="
	* @return object
	*/
    public function rjoin_fields($table, $lvalue, $rvalue, $operand="=" ) {
	if(!isset($this->parts[$table])){
		$this->parts[$table] = array();
	        $this->parts[$table]['def']="RIGHT JOIN $table";
	}
        $this->parts[$table][]=array($lvalue,$rvalue,$operand,false);
        return $this;
    }
    

	/**
	* Build the join clause that will be used in a PDO object.
	*
	* @return string
	*/
    public function build_clause() {
        // Build our JOIN clause for the query
        //error_log(print_r($this->parts, true));
	$this->join_clause="";
        if (@count($this->parts) > 0) {
            foreach ($this->parts as $part) {
                // if it's an array, then it's a name=value pair.  So put in
                // the placeholders for it.
		$this->join_clause .= " " . $part['def'];
		unset($part['def']);
		$next=0;
		foreach($part as $key => $value) {
	                if(is_array($value)) {
        	        	// only put in the place holders if part[3] is true.
				$joinOp = ($next) ? " AND " : " ON (";
                		if($value[3] === TRUE) {
		               		$this->join_clause .= $joinOp . $value[0] . 
                        		$value[2] . " :cond_" . preg_replace('/[^a-z_]/i','_',$value[0]);
	                	} else {
        	        		// if part[3] is false, then this is a field based left join.
                			$this->join_clause .= $joinOp. $value[0] . "" . $value[2] . "" . $value[1] . ""; 
                		}
				$next=1;
               		} /*else {
	                    // otherwise, append it cuz it's part of the string.
        	            $this->join_clause .= " " . $part;
                	}*/
		}
		$this->join_clause .= ") ";

            }
        }

        // return the join_clause built.
        return $this->join_clause;

    }

	/**
	* Bind the values to the placeholders using a PDO Statement
	*
	* @param string $stmt the statment to bind everything to.
	* @return
	*/
    public function bind_values($stmt){


        if(@count($this->parts) > 0) {
        	foreach($this->parts as $part) {
        		// if it's array, bind it.
        		if(is_array($part)) {
        			if($part[3] === TRUE) {
						$stmt->bindValue(':cond_'.preg_replace('/[^a-z_]/i','_',$part[0]),$part[1]);
        			}
        		}
			}
		}
    }

}
?>

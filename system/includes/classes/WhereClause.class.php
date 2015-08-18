<?php

/**
* This is a  class for working with where clauses and the DB class.
*
* @package System
* @author Jason <uberlinuxguy@tulg.org>
*/
class WhereClause {

    public $where_clause = "";

    protected $parts = array();

    public function __construct($name, $value, $operand="=") {
        $this->where_clause = "";
        $this->parts[]=array($name, $value, $operand);

    }

	/**
	* Add an element to the where clause, using the AND operator.
	*
	* @param string $name column name to match on.
	* @param string $value the value you will be searching on
	* @param string $operand which operand to use in this element, defaults to "="
	* @return object
	*/
    public function w_and($name, $value, $operand="=" ) {
        $this->parts[]="AND";
        $this->parts[]=array($name,$value,$operand);
        return $this;
    }
	/**
	* Add an element to the where clause, using the OR operator.
	*
	* @param string $name column name to match on.
	* @param string $value the value you will be searching on
	* @param string $operand which operand to use in this element, defaults to "="
	* @return object
	*/
    public function w_or($name, $value, $operand="=" ) {
        $this->parts[]="OR";
        $this->parts[]=array($name,$value,$operand);
        return $this;
    }

	/**
	* Add in a grouped portion from another where clause using the AND operator
	*
	* @param string $where_grp the other WhereClause object to pull elements from
	* @return object
	*/
    public function w_g_and($where_grp) {
        if(get_class($where_grp) == "WhereClause") {
            $this->parts[] = "AND";
            $this->parts[] = "(";
            $this->parts = array_merge($this->parts, $where_grp->parts);
            $this->parts[] = ")";
        }
        return $this;
    }

	/**
	* Add in a grouped portion from another where clause using the OR operator
	*
	* @param string $where_grp the other WhereClause object to pull elements from
	* @return object
	*/
    public function w_g_or($where_grp) {
        if(get_class($where_grp) == "WhereClause") {
            $this->parts[] = "OR";
            $this->parts[] = "(";
            $this->parts = array_merge($this->parts, $where_grp->parts);
            $this->parts[] = ")";
        }
        return $this;
    }

	/**
	* Build the where clause that will be used in a PDO object.
	*
	* @return string
	*/
    public function build_clause() {
        // Build our WHERE clause for the query
        // clear out any previous where clause
        $this->where_clause	 = "";

        if (@count($this->parts) > 0) {
            foreach ($this->parts as $part) {
                // if it's an array, then it's a name=value pair.  So put in
                // the placeholders for it.
                if(is_array($part)) {
                    $this->where_clause .= " " . $part[0] . " " .
                        $part[2] . " :cond_" . preg_replace('/[^a-z_]/i','',$part[0]);
                } else {
                    // otherwise, append it cuz it's part of the string.
                    $this->where_clause .= " " . $part;
                }

            }
        }

        // return the where_clause built.
        return $this->where_clause . " ";

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
					$stmt->bindValue(':cond_'.preg_replace('/[^a-z_]/i','',$part[0]),$part[1]);
        		}
			}
		}
    }

}
?>
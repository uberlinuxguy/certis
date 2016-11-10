<?php
    class Hosts extends Model {
        protected $_table = 'hosts';

        /**
        * checks the input for validity according to what should be in the table
        * returning an array of errors or NULL if no errors occur.
        * NOTE: This function may modify it's argument.
        *
        *@access    public
        *@param     array   $data   the array of data to check
        *@return    array
        */
        public function check_input(&$data) {

            $errors=array();
            
            if(preg_match('/![a-zA-Z0-9.]/', $data['name'])) {
                $errors[] = "Invalid Host Name!";
            }
	
            // 'normalize' the MAC address to all upper case letters
            $data['primary_mac'] = strtoupper($data['primary_mac']);
            // then validate it... -ish
            if(!preg_match('/^([0-9A-F]{2}\:){5}[0-9A-F]{2}$/i',$data['primary_mac'])){
            	$errors[] = "Invalid MAC Address " . $data['primary_mac'] . ".";
            }

            if(count($errors) > 0 ) {
                return $errors;
            }
            else {
                return NULL;
            }

        }

        /**
        * sets the proper elements from $data into the fields on this instance of the model
        *
        *@access    public
        *@param     array   $data   the array of data to set
        *@param     bool    $insert Is this an insert or an update?
        */
        public function set_data($data, $insert=0){
            if($insert === TRUE) {
                $insert = array();
                $insert['name'] = $data['name'];
                $insert['alias'] = $data['alias'];
                $insert['primary_mac'] = $data['primary_mac'];
                $this->insert($insert);

            } else {
            	$this->id = $data['id'];
                $this->name = $data['name'];
                $this->alias = $data['alias'];
                $this->primary_mac = $data['primary_mac'];
                $this->save();
            }
        }
    }

<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of XML_Model
 *
 * @author Lancelei
 */
class XML_Model extends Memory_Model {
    
    /**
	 * Constructor.
	 * @param string $origin Filename of the CSV file
	 * @param string $keyfield  Name of the primary key field
	 * @param string $entity	Entity name meaningful to the persistence
	 */
	function __construct($origin = null, $keyfield = 'id', $entity = null)
	{
		parent::__construct();

		// guess at persistent name if not specified
		if ($origin == null)
			$this->_origin = get_class($this);
		else
			$this->_origin = $origin;

		// remember the other constructor fields
		$this->_keyfield = $keyfield;
		$this->_entity = $entity;

		// start with an empty collection
		$this->_data = array(); // an array of objects
		$this->fields = array(); // an array of strings
		// and populate the collection
		$this->load();
	}

	/**
	 * Load the collection state appropriately, depending on persistence choice.
	 * OVER-RIDE THIS METHOD in persistence choice implementations
	 */
	
    protected function load()
    {
        if(file_exists($this->_origin)){

            $tasks = simplexml_load_file($this->_origin);

            $tmp = array();
            $holdname = 'task';
            foreach($tasks->children()->children() as $child){


                array_push($tmp,(string)$child->getName());
                
            }
            $this->_fields = $tmp;


            foreach($tasks->children() as $childtask){
                $record = new stdClass();
                $i = 0;
                foreach($childtask->children() as $sechitask){
                    $record->{$this->_fields[$i]} = (string)$sechitask;
                    $i++;
                }
                $key = $record->{$this->_keyfield};
                $this->_data{$key} = $record;

            }



        }
        $this->reindex();

    }
    protected function store()
    {
        $xml = new SimpleXMLElement("<tasks></tasks>");
        if (file_exists($this->_origin)) {
            foreach ($this->_data as $key => $record) {
                $holdtask = $xml->addChild("task");
                for ($i = 0; $i < count($this->_fields); $i++) {

                    $holdtask->addChild($this->_fields[$i], $record->{$this->_fields[$i]});
                    

                }
            }
            $xml->asXml($this->_origin);
        }
    }
}

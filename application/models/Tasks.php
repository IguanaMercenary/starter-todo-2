<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
/**
 * Modified to use REST client to get port data from our server.
 */
define('REST_SERVER', 'http://backend.local');  // the REST server host
define('REST_PORT', $_SERVER['SERVER_PORT']);   // the port you are running the server on
/**
 * Tasks model to pull task data from the CSV file.
 *
 * @author Michael
 */

class Tasks extends CSV_Model {

    public function __construct() {
//        parent::__construct(APPPATH . '../data/tasks.xml', 'id');
        parent::__construct();
    }

    protected function load() {
        // load the $this->_data array
        // load our data from the REST backend
        $this->rest->initialize(array('server' => REST_SERVER));
        $this->rest->option(CURLOPT_PORT, REST_PORT);
        $this->_data = $this->rest->get('job');

        // rebuild the field names from the first object
        $one = array_values((array) $this->_data);
        $this->_fields = array_keys((array) $one[0]);

        // rebuild the keys table
        $this->reindex();
    }

    // Retrieve an existing DB record as an object
    function get($key, $key2 = null) {
        $this->rest->initialize(array('server' => REST_SERVER));
        $this->rest->option(CURLOPT_PORT, REST_PORT);
        return $this->rest->get('job' . $key);
    }

    // Delete a record from the DB
    function delete($key, $key2 = null) {
        $this->rest->initialize(array('server' => REST_SERVER));
        $this->rest->option(CURLOPT_PORT, REST_PORT);
        $this->rest->delete('job' . $key);
        $this->load(); // because the "database" might have changed
    }

    // Update a record in the DB
    function update($record) {
        $this->rest->initialize(array('server' => REST_SERVER));
        $this->rest->option(CURLOPT_PORT, REST_PORT);
        $key = $record->{$this->_keyfield};
        $retrieved = $this->rest->put('job' . $key, $record);
        $this->load(); // because the "database" might have changed
    }

    // Add a record to the DB
    function add($record) {
        $this->rest->initialize(array('server' => REST_SERVER));
        $this->rest->option(CURLOPT_PORT, REST_PORT);
        $key = $record->{$this->_keyfield};
        $retrieved = $this->rest->post('job' . $key, $record);
        $this->load(); // because the "database" might have changed
    }

    function getCategorizedTasks() {
        // extract the undone tasks
        foreach ($this->all() as $task) {
            if ($task->status != 2)
                $undone[] = $task;
        }

        // substitute the category name, for sorting
        foreach ($undone as $task)
            $task->group = $this->app->group($task->group);

        // order them by category
        usort($undone, "orderByCategory");

        // convert the array of task objects into an array of associative objects       
        foreach ($undone as $task)
            $converted[] = (array) $task;

        return $converted;
    }

    // provide form validation rules
    public function rules() {
        $config = array(
            ['field' => 'task', 'label' => 'TODO task', 'rules' => 'alpha_numeric_spaces|max_length[64]'],
            ['field' => 'priority', 'label' => 'Priority', 'rules' => 'integer|less_than[4]'],
            ['field' => 'size', 'label' => 'Task size', 'rules' => 'integer|less_than[4]'],
            ['field' => 'group', 'label' => 'Task group', 'rules' => 'integer|less_than[5]'],
        );
        return $config;
    }

}

// return -1, 0, or 1 of $a's category name is earlier, equal to, or later than $b's
function orderByCategory($a, $b) {
    if ($a->group < $b->group)
        return -1;
    elseif ($a->group > $b->group)
        return 1;
    else
        return 0;
}

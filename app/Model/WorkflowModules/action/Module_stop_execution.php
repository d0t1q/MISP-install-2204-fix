<?php
include_once APP . 'Model/WorkflowModules/WorkflowBaseModule.php';

class Module_stop_execution extends WorkflowBaseModule
{
    public $is_blocking = true;
    public $can_stop_workflow = true;
    public $id = 'stop-execution';
    public $name = 'Stop execution';
    public $description = 'Essentially stops the execution for blocking paths. Do nothing for non-blocking paths';
    public $icon = 'ban';
    public $inputs = 1;
    public $outputs = 0;
    public $params = [];

    public function __construct()
    {
        parent::__construct();
    }

    public function exec(array $node, WorkflowRoamingData $roamingData, array &$errors = []): bool
    {
        parent::exec($node, $roamingData, $errors);
        $errors[] = __('Execution stopped');
        return false;
    }
}
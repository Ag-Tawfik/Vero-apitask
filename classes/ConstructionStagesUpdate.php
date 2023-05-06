<?php

class ConstructionStagesUpdate
{
    public $id;
    public $name;
    public $startDate;
    public $endDate;
    public $duration;
    public $durationUnit;
    public $color;
    public $externalId;
    public $status;

    public function __construct($data)
    {

        if (is_object($data)) {

            $vars = get_object_vars($this);

            foreach ($vars as $name => $value) {

                if (isset($data->$name)) {

                    $this->$name = $data->$name;
                }
            }
        }
    }

    function validateInput($data)
    {
        if (isset($data->status)) {
            if (!in_array($data->status, ['NEW', 'PLANNED', 'DELETED'])) {
                throw new Exception('Invalid status');
            }
        } else {
            $data->status ??= 'NEW';
        }
    }
}

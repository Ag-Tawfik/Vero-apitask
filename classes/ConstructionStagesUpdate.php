<?php

class ConstructionStagesUpdate
{
    public $name;
    public $startDate;
    public $endDate = null;
    public $duration;
    public $durationUnit = 'DAYS';
    public $color = null;
    public $externalId = null;
    public $status = 'NEW';

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

    public function validateData(): array
    {
        $errors = [];

        if (strlen($this->name) > 255) {
            $errors[] = ['error' => ['code' => 422, 'message' => 'Name is too long, maximum 255 chars']];
        }

        $startDate = \DateTime::createFromFormat(\DateTime::ATOM, $this->startDate);
        if (!$startDate && !empty($this->startDate)) {
            $errors[] = ['error' => ['code' => 422, 'message' => 'Start date is not in the correct format']];
        }

        if ($this->durationUnit && !in_array($this->durationUnit, ['HOURS', 'DAYS', 'WEEKS'])) {
            $errors[] = ['error' => ['code' => 422, 'message' => 'Duration unit is not valid']];
        }

        if ($this->color && !preg_match('/^#([a-f0-9]{3}){1,2}$/i', $this->color)) {
            $errors[] = ['error' => ['code' => 422, 'message' => 'Color is not valid HEX color']];
        }

        if ($this->externalId && strlen($this->externalId) > 255) {
            $errors[] = ['error' => ['code' => 422, 'message' => 'External ID is too long, maximum 255 chars']];
        }

        if ($this->status && !in_array($this->status, ['NEW', 'PLANNED', 'DELETED'])) {
            $errors[] = ['error' => ['code' => 422, 'message' => 'Status is not valid']];
        }

        if (isset($this->startDate) && isset($this->endDate) && $this->endDate !== null) {
            $start_date = DateTime::createFromFormat(DateTime::ATOM, $this->startDate);
            $end_date = DateTime::createFromFormat(DateTime::ATOM, $this->endDate);
            $diff = $start_date->diff($end_date);
            switch ($this->durationUnit) {
                case 'HOURS':
                    $this->duration = $diff->h + ($diff->days * 24);
                    break;
                case 'DAYS':
                    $this->duration = $diff->days;
                    break;
                case 'WEEKS':
                    $this->duration = floor($diff->days / 7);
                    break;
            }
        }

        return $errors;
    }
}

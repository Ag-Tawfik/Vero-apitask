<?php

/**
 * Class ConstructionStagesUpdate
 * Handles the update of existing construction stages with validation
 */
class ConstructionStagesUpdate
{
    private const ALLOWED_STATUSES = ['NEW', 'PLANNED', 'DELETED'];
    private const ALLOWED_DURATION_UNITS = ['HOURS', 'DAYS', 'WEEKS'];

    private ?string $name = null;
    private ?string $startDate = null;
    private ?string $endDate = null;
    private ?string $durationUnit = null;
    private ?string $color = null;
    private ?string $externalId = null;
    private ?string $status = null;

    /**
     * Constructor
     * @param object $data The data to update a construction stage
     */
    public function __construct(object $data)
    {
        if (isset($data->name)) {
            $this->name = $data->name;
        }
        if (isset($data->startDate)) {
            $this->startDate = $data->startDate;
        }
        if (isset($data->endDate)) {
            $this->endDate = $data->endDate;
        }
        if (isset($data->durationUnit)) {
            $this->durationUnit = $data->durationUnit;
        }
        if (isset($data->color)) {
            $this->color = $data->color;
        }
        if (isset($data->externalId)) {
            $this->externalId = $data->externalId;
        }
        if (isset($data->status)) {
            $this->status = $data->status;
        }
    }

    /**
     * Validate the construction stage data
     * @return array Array of validation errors, empty if valid
     */
    public function validateData(): array
    {
        $errors = [];

        // Validate name if provided
        if ($this->name !== null) {
            if (empty($this->name)) {
                $errors[] = 'Name cannot be empty';
            } elseif (strlen($this->name) > 255) {
                $errors[] = 'Name must not exceed 255 characters';
            }
        }

        // Validate start date if provided
        if ($this->startDate !== null) {
            $startDate = DateTime::createFromFormat('Y-m-d\TH:i:s\Z', $this->startDate);
            if (!$startDate) {
                $errors[] = 'Start date must be in ISO 8601 format (YYYY-MM-DDThh:mm:ssZ)';
            }
        }

        // Validate end date if provided
        if ($this->endDate !== null) {
            $endDate = DateTime::createFromFormat('Y-m-d\TH:i:s\Z', $this->endDate);
            if (!$endDate) {
                $errors[] = 'End date must be in ISO 8601 format (YYYY-MM-DDThh:mm:ssZ)';
            } elseif ($this->startDate !== null) {
                $startDate = DateTime::createFromFormat('Y-m-d\TH:i:s\Z', $this->startDate);
                if ($endDate < $startDate) {
                    $errors[] = 'End date must be after start date';
                }
            }
        }

        // Validate duration unit if provided
        if ($this->durationUnit !== null && !in_array($this->durationUnit, self::ALLOWED_DURATION_UNITS)) {
            $errors[] = 'Duration unit must be one of: ' . implode(', ', self::ALLOWED_DURATION_UNITS);
        }

        // Validate color if provided
        if ($this->color !== null && !preg_match('/^#([a-f0-9]{3}){1,2}$/i', $this->color)) {
            $errors[] = 'Color must be a valid hex color code (e.g., #FF0000 or #F00)';
        }

        // Validate external ID if provided
        if ($this->externalId !== null && strlen($this->externalId) > 255) {
            $errors[] = 'External ID must not exceed 255 characters';
        }

        // Validate status if provided
        if ($this->status !== null && !in_array($this->status, self::ALLOWED_STATUSES)) {
            $errors[] = 'Status must be one of: ' . implode(', ', self::ALLOWED_STATUSES);
        }

        return $errors;
    }

    /**
     * Get the construction stage data as an array
     * @return array
     */
    public function toArray(): array
    {
        $data = [];
        if ($this->name !== null) {
            $data['name'] = $this->name;
        }
        if ($this->startDate !== null) {
            $data['startDate'] = $this->startDate;
        }
        if ($this->endDate !== null) {
            $data['endDate'] = $this->endDate;
        }
        if ($this->durationUnit !== null) {
            $data['durationUnit'] = $this->durationUnit;
        }
        if ($this->color !== null) {
            $data['color'] = $this->color;
        }
        if ($this->externalId !== null) {
            $data['externalId'] = $this->externalId;
        }
        if ($this->status !== null) {
            $data['status'] = $this->status;
        }
        return $data;
    }

    /**
     * Calculate duration based on start and end dates
     * @return float|null
     */
    public function calculateDuration(): ?float
    {
        if ($this->startDate === null || $this->endDate === null || $this->durationUnit === null) {
            return null;
        }

        $start = new DateTime($this->startDate);
        $end = new DateTime($this->endDate);
        $diff = $start->diff($end);

        return match ($this->durationUnit) {
            'HOURS' => $diff->days * 24 + $diff->h + $diff->i / 60,
            'DAYS' => $diff->days + $diff->h / 24,
            'WEEKS' => ($diff->days + $diff->h / 24) / 7,
            default => null
        };
    }
}

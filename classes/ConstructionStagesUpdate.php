<?php

/**
 * Class ConstructionStagesUpdate
 * Handles the update of construction stages with validation
 */
class ConstructionStagesUpdate
{
    public function __construct(
        private readonly ?string $name = null,
        private readonly ?string $startDate = null,
        private readonly ?string $endDate = null,
        private readonly ?DurationUnit $durationUnit = null,
        private readonly ?string $color = null,
        private readonly ?string $externalId = null,
        private readonly ?Status $status = null,
    ) {}

    /**
     * Create instance from raw data object
     */
    public static function fromObject(object $data): self
    {
        return new self(
            name: $data->name ?? null,
            startDate: $data->startDate ?? null,
            endDate: $data->endDate ?? null,
            durationUnit: isset($data->durationUnit) ? DurationUnit::from($data->durationUnit) : null,
            color: $data->color ?? null,
            externalId: $data->externalId ?? null,
            status: isset($data->status) ? Status::from($data->status) : null
        );
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

        // Validate color if provided
        if ($this->color !== null && !preg_match('/^#([a-f0-9]{3}){1,2}$/i', $this->color)) {
            $errors[] = 'Color must be a valid hex color code (e.g., #FF0000 or #F00)';
        }

        // Validate external ID if provided
        if ($this->externalId !== null && strlen($this->externalId) > 255) {
            $errors[] = 'External ID must not exceed 255 characters';
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
        
        if ($this->name !== null) $data['name'] = $this->name;
        if ($this->startDate !== null) $data['startDate'] = $this->startDate;
        if ($this->endDate !== null) $data['endDate'] = $this->endDate;
        if ($this->durationUnit !== null) $data['durationUnit'] = $this->durationUnit->value;
        if ($this->color !== null) $data['color'] = $this->color;
        if ($this->externalId !== null) $data['externalId'] = $this->externalId;
        if ($this->status !== null) $data['status'] = $this->status->value;

        return $data;
    }

    /**
     * Calculate duration based on start and end dates
     * @return float|null
     */
    public function calculateDuration(): ?float
    {
        if (!$this->startDate || !$this->endDate || !$this->durationUnit) {
            return null;
        }

        $start = new DateTime($this->startDate);
        $end = new DateTime($this->endDate);
        $diff = $start->diff($end);

        return match ($this->durationUnit) {
            DurationUnit::HOURS => $diff->days * 24 + $diff->h + $diff->i / 60,
            DurationUnit::DAYS => $diff->days + $diff->h / 24,
            DurationUnit::WEEKS => ($diff->days + $diff->h / 24) / 7,
        };
    }
}

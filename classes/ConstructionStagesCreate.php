<?php

enum DurationUnit: string {
	case HOURS = 'HOURS';
	case DAYS = 'DAYS';
	case WEEKS = 'WEEKS';
}

enum Status: string {
	case NEW = 'NEW';
	case PLANNED = 'PLANNED';
	case DELETED = 'DELETED';
}

/**
 * Class ConstructionStagesCreate
 * Handles the creation of new construction stages with validation
 */
class ConstructionStagesCreate
{
	public function __construct(
		private readonly string $name,
		private readonly string $startDate,
		private readonly ?string $endDate = null,
		private readonly ?DurationUnit $durationUnit = null,
		private readonly ?string $color = null,
		private readonly ?string $externalId = null,
		private readonly Status $status = Status::NEW,
	) {}

	/**
	 * Create instance from raw data object
	 */
	public static function fromObject(object $data): self
	{
		return new self(
			name: $data->name ?? '',
			startDate: $data->startDate ?? '',
			endDate: $data->endDate ?? null,
			durationUnit: isset($data->durationUnit) ? DurationUnit::from($data->durationUnit) : null,
			color: $data->color ?? null,
			externalId: $data->externalId ?? null,
			status: isset($data->status) ? Status::from($data->status) : Status::NEW
		);
	}

	/**
	 * Validate the construction stage data
	 * @return array Array of validation errors, empty if valid
	 */
	public function validateData(): array
	{
		$errors = [];

		// Validate name
		if (empty($this->name)) {
			$errors[] = 'Name is required';
		} elseif (strlen($this->name) > 255) {
			$errors[] = 'Name must not exceed 255 characters';
		}

		// Validate start date
		if (empty($this->startDate)) {
			$errors[] = 'Start date is required';
		} else {
			$startDate = DateTime::createFromFormat('Y-m-d\TH:i:s\Z', $this->startDate);
			if (!$startDate) {
				$errors[] = 'Start date must be in ISO 8601 format (YYYY-MM-DDThh:mm:ssZ)';
			}
		}

		// Validate end date if provided
		if (!empty($this->endDate)) {
			$endDate = DateTime::createFromFormat('Y-m-d\TH:i:s\Z', $this->endDate);
			if (!$endDate) {
				$errors[] = 'End date must be in ISO 8601 format (YYYY-MM-DDThh:mm:ssZ)';
			} elseif (isset($startDate) && $endDate < $startDate) {
				$errors[] = 'End date must be after start date';
			}
		}

		// Validate color
		if (!empty($this->color) && !preg_match('/^#([a-f0-9]{3}){1,2}$/i', $this->color)) {
			$errors[] = 'Color must be a valid hex color code (e.g., #FF0000 or #F00)';
		}

		// Validate external ID
		if (!empty($this->externalId) && strlen($this->externalId) > 255) {
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
		return [
			'name' => $this->name,
			'startDate' => $this->startDate,
			'endDate' => $this->endDate,
			'durationUnit' => $this->durationUnit?->value,
			'color' => $this->color,
			'externalId' => $this->externalId,
			'status' => $this->status->value
		];
	}

	/**
	 * Calculate duration based on start and end dates
	 * @return float|null
	 */
	public function calculateDuration(): ?float
	{
		if (empty($this->startDate) || empty($this->endDate) || !$this->durationUnit) {
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

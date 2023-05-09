<?php

class ConstructionStages
{
	private $db;

	public function __construct()
	{
		$this->db = Api::getDb();
	}

	public function getAll()
	{
		$stmt = $this->db->prepare("
			SELECT
				ID as id,
				name, 
				strftime('%Y-%m-%dT%H:%M:%SZ', start_date) as startDate,
				strftime('%Y-%m-%dT%H:%M:%SZ', end_date) as endDate,
				duration,
				durationUnit,
				color,
				externalId,
				status
			FROM construction_stages
		");
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public function getSingle($id)
	{
		$stmt = $this->db->prepare("
			SELECT
				ID as id,
				name, 
				strftime('%Y-%m-%dT%H:%M:%SZ', start_date) as startDate,
				strftime('%Y-%m-%dT%H:%M:%SZ', end_date) as endDate,
				duration,
				durationUnit,
				color,
				externalId,
				status
			FROM construction_stages
			WHERE ID = :id
		");
		$stmt->execute(['id' => $id]);
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public function post(ConstructionStagesCreate $data): array
	{
		$errorMsg = $data->validateData($data);

		if ($errorMsg) {
			return $errorMsg;
		}

		$stmt = $this->db->prepare("
			INSERT INTO construction_stages
			    (name, start_date, end_date, duration, durationUnit, color, externalId, status)
			    VALUES (:name, :start_date, :end_date, :duration, :durationUnit, :color, :externalId, :status)
			");
		$stmt->execute([
			'name' => $data->name,
			'start_date' => $data->startDate,
			'end_date' => $data->endDate,
			'duration' => $data->duration,
			'durationUnit' => $data->durationUnit,
			'color' => $data->color,
			'externalId' => $data->externalId,
			'status' => $data->status,
		]);
		return $this->getSingle($this->db->lastInsertId());
	}

	public function update(ConstructionStagesUpdate $data, int $id): array
	{
		$constructionStage = $this->getSingle($id);

		if (!$constructionStage) {
			return ['error' => ['code' => 404, 'message' => 'Record not found']];
		}

		$errorMsg = $data->validateData($data);
		if ($errorMsg) {
			return $errorMsg;
		}

		$startDate = $data->startDate ?? $constructionStage[0]['startDate'];
		$endDate = $data->endDate ?? $constructionStage[0]['endDate'];

		if ($startDate && $endDate && $endDate < $startDate) {
			return ['error' => ['code' => 422, 'message' => 'End date must be after start date']];
		}

		$stmt = $this->db->prepare("
        UPDATE construction_stages
        SET
            name = :name,
            start_date = :start_date,
            end_date = :end_date,
            duration = :duration,
            durationUnit = :durationUnit,
            color = :color,
            externalId = :externalId,
            status = :status
        WHERE ID = :id
    ");

		$stmt->execute([
			'id' => $id,
			'name' => $data->name ?? $constructionStage[0]['name'],
			'start_date' => $startDate,
			'end_date' => $endDate,
			'duration' => $data->calculateDuration($startDate, $endDate),
			'durationUnit' => $data->durationUnit,
			'color' => $data->color,
			'externalId' => $data->externalId,
			'status' => $data->status,
		]);

		return $this->getSingle($id);
	}

	public function delete(int $id): array
	{
		$stmt = $this->db->prepare("
			UPDATE construction_stages
			SET
				status = 'DELETED'
			WHERE ID = :id
		");

		$stmt->execute([
			'id' => $id
		]);

		return ['success' => ['code' => 204, 'message' => 'Record deleted successfully']];
	}
}

<?php

/**
 * Class ConstructionStages
 * Handles all operations related to construction stages
 */
class ConstructionStages
{
	public function __construct(
		private readonly PDO $db
	) {}

	/**
	 * Get all construction stages
	 * @return array
	 * @throws Exception
	 */
	public function getAll(): array
	{
		try {
			$stmt = $this->db->query("
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
				WHERE status != 'DELETED'
				ORDER BY start_date DESC
			");
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch (PDOException $e) {
			throw new Exception('Failed to fetch construction stages: ' . $e->getMessage(), 500);
		}
	}

	/**
	 * Get a single construction stage by ID
	 * @param int $id
	 * @return array
	 * @throws Exception
	 */
	public function getSingle(int $id): array
	{
		try {
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
				WHERE ID = :id AND status != 'DELETED'
			");
			$stmt->execute(['id' => $id]);
			$stage = $stmt->fetch(PDO::FETCH_ASSOC);

			if (!$stage) {
				throw new Exception('Construction stage not found', 404);
			}

			return $stage;
		} catch (PDOException $e) {
			throw new Exception('Failed to fetch construction stage: ' . $e->getMessage(), 500);
		}
	}

	/**
	 * Create a new construction stage
	 * @param ConstructionStagesCreate $data
	 * @return array
	 * @throws Exception
	 */
	public function post(ConstructionStagesCreate $data): array
	{
		try {
			$errors = $data->validateData();
			if (!empty($errors)) {
				return [
					'error' => [
						'code' => 400,
						'message' => 'Validation failed',
						'errors' => $errors
					]
				];
			}

			$stageData = $data->toArray();
			$duration = $data->calculateDuration();
			if ($duration !== null) {
				$stageData['duration'] = $duration;
			}

			$stmt = $this->db->prepare("
				INSERT INTO construction_stages (
					name, start_date, end_date, duration, durationUnit,
					color, externalId, status
				) VALUES (
					:name, :startDate, :endDate, :duration, :durationUnit,
					:color, :externalId, :status
				)
			");

			$params = [
				'name' => $stageData['name'],
				'startDate' => $stageData['startDate'],
				'endDate' => $stageData['endDate'],
				'duration' => $stageData['duration'] ?? null,
				'durationUnit' => $stageData['durationUnit'],
				'color' => $stageData['color'],
				'externalId' => $stageData['externalId'],
				'status' => $stageData['status']
			];

			$stmt->execute($params);
			$id = (int)$this->db->lastInsertId();

			return $this->getSingle($id);
		} catch (PDOException $e) {
			throw new Exception('Failed to create construction stage: ' . $e->getMessage(), 500);
		}
	}

	/**
	 * Update a construction stage
	 * @param int $id
	 * @param ConstructionStagesUpdate $data
	 * @return array
	 * @throws Exception
	 */
	public function update(int $id, ConstructionStagesUpdate $data): array
	{
		try {
			// Check if stage exists
			$this->getSingle($id);

			$errors = $data->validateData();
			if (!empty($errors)) {
				return [
					'error' => [
						'code' => 400,
						'message' => 'Validation failed',
						'errors' => $errors
					]
				];
			}

			$stageData = $data->toArray();
			$duration = $data->calculateDuration();
			if ($duration !== null) {
				$stageData['duration'] = $duration;
			}

			if (empty($stageData)) {
				return $this->getSingle($id);
			}

			$updates = [];
			$params = ['id' => $id];

			foreach ($stageData as $key => $value) {
				if ($value !== null) {
					$dbKey = match($key) {
						'startDate' => 'start_date',
						'endDate' => 'end_date',
						default => $key
					};
					$updates[] = "$dbKey = :$key";
					$params[$key] = $value;
				}
			}

			$stmt = $this->db->prepare('
				UPDATE construction_stages
				SET ' . implode(', ', $updates) . '
				WHERE id = :id
			');

			$stmt->execute($params);

			return $this->getSingle($id);
		} catch (PDOException $e) {
			throw new Exception('Failed to update construction stage: ' . $e->getMessage(), 500);
		}
	}

	/**
	 * Delete a construction stage (soft delete)
	 * @param int $id
	 * @return array
	 * @throws Exception
	 */
	public function delete(int $id): array
	{
		try {
			// Check if stage exists
			$this->getSingle($id);

			$stmt = $this->db->prepare('
				UPDATE construction_stages
				SET status = :status
				WHERE id = :id
			');

			$stmt->execute([
				'id' => $id,
				'status' => Status::DELETED->value
			]);

			return [
				'success' => [
					'code' => 200,
					'message' => 'Construction stage deleted successfully'
				]
			];
		} catch (PDOException $e) {
			throw new Exception('Failed to delete construction stage: ' . $e->getMessage(), 500);
		}
	}
}

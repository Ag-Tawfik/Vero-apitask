<?php

/**
 * Class ConstructionStages
 * Handles all operations related to construction stages
 */
class ConstructionStages
{
	private PDO $db;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->db = Api::getDb();
	}

	/**
	 * Get all construction stages
	 * @return array
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
		} catch (Exception $e) {
			throw new Exception('Failed to fetch construction stages: ' . $e->getMessage());
		}
	}

	/**
	 * Get a single construction stage by ID
	 * @param int $id
	 * @return array
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
		} catch (Exception $e) {
			throw new Exception('Failed to fetch construction stage: ' . $e->getMessage());
		}
	}

	/**
	 * Create a new construction stage
	 * @param ConstructionStagesCreate $data
	 * @return array
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
			$id = $this->db->lastInsertId();

			return $this->getSingle($id);
		} catch (Exception $e) {
			throw new Exception('Failed to create construction stage: ' . $e->getMessage());
		}
	}

	/**
	 * Update a construction stage
	 * @param int $id
	 * @param ConstructionStagesUpdate $data
	 * @return array
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

			$updates = [];
			$params = ['id' => $id];

			foreach ($stageData as $key => $value) {
				if ($value !== null) {
					$dbKey = $key === 'startDate' ? 'start_date' : ($key === 'endDate' ? 'end_date' : $key);
					$updates[] = "$dbKey = :$key";
					$params[$key] = $value;
				}
			}

			if (empty($updates)) {
				return $this->getSingle($id);
			}

			$stmt = $this->db->prepare('
				UPDATE construction_stages
				SET ' . implode(', ', $updates) . '
				WHERE id = :id
			');

			$stageData['id'] = $id;
			$stmt->execute($stageData);

			return $this->getSingle($id);
		} catch (Exception $e) {
			throw new Exception('Failed to update construction stage: ' . $e->getMessage());
		}
	}

	/**
	 * Delete a construction stage (soft delete)
	 * @param int $id
	 * @return array
	 */
	public function delete(int $id): array
	{
		try {
			// Check if stage exists
			$this->getSingle($id);

			$stmt = $this->db->prepare('
				UPDATE construction_stages
				SET status = "DELETED"
				WHERE id = ?
			');

			$stmt->execute([$id]);

			return [
				'success' => [
					'code' => 200,
					'message' => 'Construction stage deleted successfully'
				]
			];
		} catch (Exception $e) {
			throw new Exception('Failed to delete construction stage: ' . $e->getMessage());
		}
	}
}

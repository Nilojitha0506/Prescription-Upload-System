<?php
require_once 'Database.php';

class Prescription {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function create($user_id, $note, $delivery_address, $delivery_time_slot) {
        $stmt = $this->pdo->prepare('INSERT INTO prescriptions (user_id, note, delivery_address, delivery_time_slot) VALUES (?, ?, ?, ?)');
        if ($stmt->execute([$user_id, $note, $delivery_address, $delivery_time_slot])) {
            return $this->pdo->lastInsertId();
        }
        return false;
    }

    public function addImage($prescription_id, $image_path) {
        $stmt = $this->pdo->prepare('INSERT INTO prescription_images (prescription_id, image_path) VALUES (?, ?)');
        return $stmt->execute([$prescription_id, $image_path]);
    }

    public function getByUserId($user_id) {
        $stmt = $this->pdo->prepare('SELECT * FROM prescriptions WHERE user_id = ? ORDER BY created_at DESC');
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare('SELECT * FROM prescriptions WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllWithUser() {
        $stmt = $this->pdo->query('SELECT p.*, u.name as user_name FROM prescriptions p JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare('DELETE FROM prescriptions WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function getImages($prescription_id) {
        $stmt = $this->pdo->prepare('SELECT image_path FROM prescription_images WHERE prescription_id = ?');
        $stmt->execute([$prescription_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

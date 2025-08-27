<?php
require_once 'Database.php';

class Quotation {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function create($prescription_id, $pharmacy_id, $total_amount) {
        $stmt = $this->pdo->prepare('INSERT INTO quotations (prescription_id, pharmacy_id, total_amount) VALUES (?, ?, ?)');
        if ($stmt->execute([$prescription_id, $pharmacy_id, $total_amount])) {
            return $this->pdo->lastInsertId();
        }
        return false;
    }

    public function addItem($quotation_id, $drug_name, $quantity, $amount) {
        $stmt = $this->pdo->prepare('INSERT INTO quotation_items (quotation_id, drug_name, quantity, amount) VALUES (?, ?, ?, ?)');
        return $stmt->execute([$quotation_id, $drug_name, $quantity, $amount]);
    }

    public function getByUserId($user_id) {
        $stmt = $this->pdo->prepare('
            SELECT q.*, p.note 
            FROM quotations q 
            JOIN prescriptions p ON q.prescription_id = p.id 
            WHERE p.user_id = ? ORDER BY q.created_at DESC
        ');
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare('SELECT * FROM quotations WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getItems($quotation_id) {
        $stmt = $this->pdo->prepare('SELECT * FROM quotation_items WHERE quotation_id = ?');
        $stmt->execute([$quotation_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateStatus($id, $status) {
        $stmt = $this->pdo->prepare('UPDATE quotations SET status = ? WHERE id = ?');
        return $stmt->execute([$status, $id]);
    }

    public function getAllWithDetails() {
        $stmt = $this->pdo->query('
            SELECT q.*, u.name as pharmacy_name, p.note 
            FROM quotations q 
            JOIN users u ON q.pharmacy_id = u.id 
            JOIN prescriptions p ON q.prescription_id = p.id 
            ORDER BY q.created_at DESC
        ');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare('DELETE FROM quotations WHERE id = ?');
        return $stmt->execute([$id]);
    }
}

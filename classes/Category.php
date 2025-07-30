<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

class Category {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    public function create($userId, $name, $type, $color = '#007bff') {
        $stmt = $this->db->prepare("INSERT INTO categories (user_id, name, type, color) VALUES (?, ?, ?, ?)");
        
        try {
            $stmt->execute([$userId, $name, $type, $color]);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            throw $e;
        }
    }
    
    public function getByUser($userId, $type = null) {
        $sql = "SELECT * FROM categories WHERE user_id = ?";
        $params = [$userId];
        
        if ($type) {
            $sql .= " AND type = ?";
            $params[] = $type;
        }
        
        $sql .= " ORDER BY name";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function update($id, $name, $type, $color) {
        $stmt = $this->db->prepare("UPDATE categories SET name = ?, type = ?, color = ? WHERE id = ?");
        
        try {
            $stmt->execute([$name, $type, $color, $id]);
            return true;
        } catch (PDOException $e) {
            throw $e;
        }
    }
    
    public function delete($id) {
        // Verificar si la categoría tiene transacciones asociadas
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM transactions WHERE category_id = ?");
        $stmt->execute([$id]);
        $count = $stmt->fetchColumn();
        
        if ($count > 0) {
            return false; // No se puede eliminar si tiene transacciones
        }
        
        $stmt = $this->db->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }
    
    public function getCategoryUsage($userId) {
        $stmt = $this->db->prepare("
            SELECT 
                c.id,
                c.name,
                c.color,
                c.type,
                COUNT(t.id) as transaction_count,
                COALESCE(SUM(t.amount), 0) as total_amount
            FROM categories c
            LEFT JOIN transactions t ON c.id = t.category_id
            WHERE c.user_id = ?
            GROUP BY c.id, c.name, c.color, c.type
            ORDER BY c.name
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

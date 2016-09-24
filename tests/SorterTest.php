<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../sorter.php';

class SorterTest extends TestCase
{
    private $db;
    private $sorter;

    protected function setUp(): void
    {
        // Bellek içi (in-memory) SQLite veritabanı ve tablo kurulumu
        $this->db = new PDO('sqlite::memory:');
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->db->exec("CREATE TABLE categories (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT,
            order_no INTEGER,
            top_id INTEGER DEFAULT 0
        )");

        // Test verilerini ekleme
        $this->db->exec("INSERT INTO categories (id, title, order_no, top_id) VALUES (1, 'Kategori 1', 1, 5)");
        $this->db->exec("INSERT INTO categories (id, title, order_no, top_id) VALUES (2, 'Kategori 2', 2, 5)");
        $this->db->exec("INSERT INTO categories (id, title, order_no, top_id) VALUES (3, 'Kategori 3', 3, 5)");

        $this->sorter = new Sorter($this->db, 'categories', 'order_no', 'top_id = ?', array(5), 'en');
    }

    public function testGetFreeListNo()
    {
        $freeNo = $this->sorter->getFreeListNo();
        $this->assertEquals(4, $freeNo);
    }

    public function testMoveToFirst()
    {
        $result = $this->sorter->moveToFirst(3);
        $this->assertTrue($result);

        $order = $this->sorter->getOrderNum(3);
        $this->assertEquals(1, $order);
    }

    public function testMoveUp()
    {
        $result = $this->sorter->moveUp(2);
        $this->assertTrue($result);

        $order = $this->sorter->getOrderNum(2);
        $this->assertEquals(1, $order);
    }
}
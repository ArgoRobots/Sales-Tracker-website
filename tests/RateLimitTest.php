<?php

use PHPUnit\Framework\TestCase;

// Autoload classes if available
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

// Simple in-memory mock for MySQLi operations
global $fakeDb;

function get_db_connection() {
    global $fakeDb;
    return $fakeDb;
}

class FakeResult {
    private $count;
    public function __construct($count) {
        $this->count = $count;
    }
    public function fetch_assoc() {
        $now = date('Y-m-d H:i:s');
        return [
            'count' => $this->count,
            'period_start' => $now,
            'last_action_at' => $now,
        ];
    }
}

class FakeStatement {
    private $db;
    public function __construct($db) {
        $this->db = $db;
    }
    public function bind_param($types, &...$vars) {
        // no-op
    }
    public function execute() {
        // no-op
    }
    public function get_result() {
        return new FakeResult($this->db->nextCount());
    }
    public function close() {
        // no-op
    }
}

class FakeDB {
    private $postCount = 0;

    public function prepare($sql) {
        return new FakeStatement($this);
    }

    public function nextCount() {
        return $this->postCount;
    }

    public function incrementPostCount(): void {
        $this->postCount++;
    }
}

// Include the rate limit functions
require_once __DIR__ . '/../community/rate_limit.php';

class RateLimitTest extends TestCase
{
    protected function setUp(): void
    {
        global $fakeDb;
        $fakeDb = new FakeDB();
        $_SESSION = ['user_id' => 1, 'role' => 'user'];
    }

    public function testUnderThresholdReturnsFalse(): void
    {
        $this->assertFalse(check_rate_limit(1, 'post'));
    }

    public function testExceedingThresholdReturnsHtmlMessage(): void
    {
        global $fakeDb;
        // First call within limits
        $this->assertFalse(check_rate_limit(1, 'post'));
        // Simulate post being created
        $fakeDb->incrementPostCount();
        // Second call exceeds short-term limit
        $result = check_rate_limit(1, 'post');
        $this->assertIsString($result);
        $this->assertStringContainsString('rate-limit-message', $result);
    }
}

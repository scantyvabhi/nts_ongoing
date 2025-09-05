<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$csvFile = './data/employees.csv';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON data']);
        exit;
    }
    
    $action = $input['action'] ?? '';
    
    switch ($action) {
        case 'update':
            $employees = $input['employees'] ?? [];
            if (empty($employees)) {
                http_response_code(400);
                echo json_encode(['error' => 'No employee data provided']);
                exit;
            }
            
            // Write to CSV file
            $result = writeEmployeesToCSV($employees, $csvFile);
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Employees updated successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to update CSV file']);
            }
            break;
            
        case 'get':
            $employees = readEmployeesFromCSV($csvFile);
            echo json_encode(['success' => true, 'employees' => $employees]);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
            break;
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}

function writeEmployeesToCSV($employees, $filename) {
    if (empty($employees)) {
        return false;
    }
    
    // Get headers from first employee
    $headers = array_keys($employees[0]);
    
    // Create CSV content
    $csvContent = implode(',', $headers) . "\n";
    
    foreach ($employees as $employee) {
        $row = [];
        foreach ($headers as $header) {
            $value = $employee[$header] ?? '';
            // Escape commas and quotes
            if (strpos($value, ',') !== false || strpos($value, '"') !== false) {
                $value = '"' . str_replace('"', '""', $value) . '"';
            }
            $row[] = $value;
        }
        $csvContent .= implode(',', $row) . "\n";
    }
    
    // Write to file
    return file_put_contents($filename, $csvContent) !== false;
}

function readEmployeesFromCSV($filename) {
    if (!file_exists($filename)) {
        return [];
    }
    
    $content = file_get_contents($filename);
    $lines = explode("\n", trim($content));
    
    if (empty($lines)) {
        return [];
    }
    
    $headers = str_getcsv(array_shift($lines));
    $employees = [];
    
    foreach ($lines as $line) {
        if (empty(trim($line))) continue;
        
        $row = str_getcsv($line);
        if (count($row) === count($headers)) {
            $employee = array_combine($headers, $row);
            $employees[] = $employee;
        }
    }
    
    return $employees;
}
?> 
<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$csvFile = './data/contact_submissions.csv';

// Ensure the data directory exists
if (!file_exists('./data')) {
    mkdir('./data', 0755, true);
}

function generateUniqueId() {
    return 'CNT' . date('Ymd') . sprintf('%04d', rand(1, 9999));
}

function escapeCSVField($field) {
    if (strpos($field, ',') !== false || strpos($field, '"') !== false || strpos($field, "\n") !== false) {
        return '"' . str_replace('"', '""', $field) . '"';
    }
    return $field;
}

function addContactSubmission($data) {
    global $csvFile;
    
    $id = generateUniqueId();
    $submissionDate = date('Y-m-d H:i:s');
    $status = 'new';
    
    $csvData = [
        $id,
        escapeCSVField($data['name']),
        escapeCSVField($data['email']),
        escapeCSVField($data['message']),
        $submissionDate,
        $status
    ];
    
    $csvLine = implode(',', $csvData) . "\n";
    
    if (file_put_contents($csvFile, $csvLine, FILE_APPEND | LOCK_EX) !== false) {
        return [
            'success' => true,
            'message' => 'Contact submission saved successfully',
            'id' => $id
        ];
    } else {
        return [
            'success' => false,
            'error' => 'Failed to save contact submission'
        ];
    }
}

function getContactSubmissions() {
    global $csvFile;
    
    if (!file_exists($csvFile)) {
        return [
            'success' => true,
            'submissions' => []
        ];
    }
    
    $csvData = file_get_contents($csvFile);
    $lines = explode("\n", trim($csvData));
    $submissions = [];
    
    if (count($lines) <= 1) {
        return [
            'success' => true,
            'submissions' => []
        ];
    }
    
    $header = str_getcsv($lines[0]);
    
    for ($i = 1; $i < count($lines); $i++) {
        if (trim($lines[$i]) === '') continue;
        
        $row = str_getcsv($lines[$i]);
        if (count($row) === count($header)) {
            $submissions[] = array_combine($header, $row);
        }
    }
    
    return [
        'success' => true,
        'submissions' => array_reverse($submissions) // Latest first
    ];
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            throw new Exception('Invalid JSON input');
        }
        
        $action = $input['action'] ?? '';
        
        switch ($action) {
            case 'submit':
                // Validate required fields
                $requiredFields = ['name', 'email', 'message'];
                foreach ($requiredFields as $field) {
                    if (empty($input[$field])) {
                        throw new Exception("Field '$field' is required");
                    }
                }
                
                // Validate email
                if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
                    throw new Exception('Invalid email format');
                }
                
                $result = addContactSubmission($input);
                echo json_encode($result);
                break;
                
            case 'get':
                $result = getContactSubmissions();
                echo json_encode($result);
                break;
                
            default:
                throw new Exception('Invalid action');
        }
    } else {
        throw new Exception('Only POST method is allowed');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>

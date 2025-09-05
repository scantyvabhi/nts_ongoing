<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$serviceEnquiriesFile = './data/service_enquiries.csv';
$jobApplicationsFile = './data/job_applications.csv';
$uploadsDir = './uploads/resumes/';

// Ensure directories exist
if (!file_exists('./data')) {
    mkdir('./data', 0755, true);
}
if (!file_exists($uploadsDir)) {
    mkdir($uploadsDir, 0755, true);
}

function generateUniqueId($prefix) {
    return $prefix . date('Ymd') . sprintf('%04d', rand(1, 9999));
}

function escapeCSVField($field) {
    if (strpos($field, ',') !== false || strpos($field, '"') !== false || strpos($field, "\n") !== false) {
        return '"' . str_replace('"', '""', $field) . '"';
    }
    return $field;
}

function addServiceEnquiry($data) {
    global $serviceEnquiriesFile;
    
    $id = generateUniqueId('ENQ');
    $submissionDate = date('Y-m-d H:i:s');
    $status = 'new';
    
    $csvData = [
        $id,
        escapeCSVField($data['name']),
        escapeCSVField($data['phone']),
        escapeCSVField($data['email']),
        escapeCSVField($data['service_type']),
        escapeCSVField($data['message']),
        $submissionDate,
        $status
    ];
    
    $csvLine = implode(',', $csvData) . "\n";
    
    if (file_put_contents($serviceEnquiriesFile, $csvLine, FILE_APPEND | LOCK_EX) !== false) {
        return [
            'success' => true,
            'message' => 'Service enquiry saved successfully',
            'id' => $id
        ];
    } else {
        return [
            'success' => false,
            'error' => 'Failed to save service enquiry'
        ];
    }
}

function addJobApplication($data, $resumeFile = null) {
    global $jobApplicationsFile, $uploadsDir;
    
    $id = generateUniqueId('JOB');
    $submissionDate = date('Y-m-d H:i:s');
    $status = 'new';
    $resumeFilename = '';
    
    // Handle file upload if present
    if ($resumeFile && $resumeFile['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        $fileType = $resumeFile['type'];
        
        if (in_array($fileType, $allowedTypes)) {
            $extension = pathinfo($resumeFile['name'], PATHINFO_EXTENSION);
            $resumeFilename = $id . '_' . time() . '.' . $extension;
            $uploadPath = $uploadsDir . $resumeFilename;
            
            if (!move_uploaded_file($resumeFile['tmp_name'], $uploadPath)) {
                return [
                    'success' => false,
                    'error' => 'Failed to upload resume file'
                ];
            }
        } else {
            return [
                'success' => false,
                'error' => 'Invalid file type. Only PDF and DOC files are allowed.'
            ];
        }
    }
    
    $csvData = [
        $id,
        escapeCSVField($data['name']),
        escapeCSVField($data['email']),
        escapeCSVField($data['phone']),
        escapeCSVField($data['role']),
        escapeCSVField($resumeFilename),
        escapeCSVField($data['message']),
        $submissionDate,
        $status
    ];
    
    $csvLine = implode(',', $csvData) . "\n";
    
    if (file_put_contents($jobApplicationsFile, $csvLine, FILE_APPEND | LOCK_EX) !== false) {
        return [
            'success' => true,
            'message' => 'Job application saved successfully',
            'id' => $id
        ];
    } else {
        return [
            'success' => false,
            'error' => 'Failed to save job application'
        ];
    }
}

function getServiceEnquiries() {
    global $serviceEnquiriesFile;
    
    if (!file_exists($serviceEnquiriesFile)) {
        return [
            'success' => true,
            'enquiries' => []
        ];
    }
    
    $csvData = file_get_contents($serviceEnquiriesFile);
    $lines = explode("\n", trim($csvData));
    $enquiries = [];
    
    if (count($lines) <= 1) {
        return [
            'success' => true,
            'enquiries' => []
        ];
    }
    
    $header = str_getcsv($lines[0]);
    
    for ($i = 1; $i < count($lines); $i++) {
        if (trim($lines[$i]) === '') continue;
        
        $row = str_getcsv($lines[$i]);
        if (count($row) === count($header)) {
            $enquiries[] = array_combine($header, $row);
        }
    }
    
    return [
        'success' => true,
        'enquiries' => array_reverse($enquiries) // Latest first
    ];
}

function getJobApplications() {
    global $jobApplicationsFile;
    
    if (!file_exists($jobApplicationsFile)) {
        return [
            'success' => true,
            'applications' => []
        ];
    }
    
    $csvData = file_get_contents($jobApplicationsFile);
    $lines = explode("\n", trim($csvData));
    $applications = [];
    
    if (count($lines) <= 1) {
        return [
            'success' => true,
            'applications' => []
        ];
    }
    
    $header = str_getcsv($lines[0]);
    
    for ($i = 1; $i < count($lines); $i++) {
        if (trim($lines[$i]) === '') continue;
        
        $row = str_getcsv($lines[$i]);
        if (count($row) === count($header)) {
            $applications[] = array_combine($header, $row);
        }
    }
    
    return [
        'success' => true,
        'applications' => array_reverse($applications) // Latest first
    ];
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'submit_enquiry':
                // Validate required fields
                $requiredFields = ['name', 'phone', 'email', 'service_type', 'message'];
                foreach ($requiredFields as $field) {
                    if (empty($_POST[$field])) {
                        throw new Exception("Field '$field' is required");
                    }
                }
                
                // Validate email
                if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                    throw new Exception('Invalid email format');
                }
                
                $result = addServiceEnquiry($_POST);
                echo json_encode($result);
                break;
                
            case 'submit_application':
                // Validate required fields
                $requiredFields = ['name', 'email', 'phone', 'role', 'message'];
                foreach ($requiredFields as $field) {
                    if (empty($_POST[$field])) {
                        throw new Exception("Field '$field' is required");
                    }
                }
                
                // Validate email
                if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                    throw new Exception('Invalid email format');
                }
                
                $resumeFile = $_FILES['resume'] ?? null;
                $result = addJobApplication($_POST, $resumeFile);
                echo json_encode($result);
                break;
                
            case 'get_enquiries':
                $result = getServiceEnquiries();
                echo json_encode($result);
                break;
                
            case 'get_applications':
                $result = getJobApplications();
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

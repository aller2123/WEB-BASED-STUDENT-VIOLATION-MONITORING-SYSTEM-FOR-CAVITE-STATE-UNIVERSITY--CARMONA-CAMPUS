<?php
require 'dbconfig.php';
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to log messages
function logMessage($message) {
    file_put_contents('import_log.txt', date('[Y-m-d H:i:s] ') . $message . "\n", FILE_APPEND);
}

// Check if the file was uploaded
if (isset($_FILES['file'])) {
    logMessage("File upload detected");
    logMessage("File details: " . print_r($_FILES['file'], true));

    if ($_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['file']['tmp_name'];
        $fileExtension = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));

        logMessage("File successfully uploaded. Extension: $fileExtension");

        // Allowed file extensions
        $allowedExtensions = ['xlsx', 'xls'];

        if (in_array($fileExtension, $allowedExtensions)) {
            logMessage("File extension is valid");

            try {
                // Connect to the database
                $conn = new mysqli($host, $user, $password, $database);

                // Check connection
                if ($conn->connect_error) {
                    logMessage("Database connection error: " . $conn->connect_error);
                    exit;
                }

                logMessage("Database connection established");

                // Import students from the file
                $importedStudents = importStudentsFromFile($conn, $file);

                logMessage("Import process completed. Imported students: $importedStudents");

                if ($importedStudents > 0) {
                    logMessage("Successfully imported $importedStudents students.");
                } else {
                    logMessage("No students were imported. Check the import_log.txt file for details.");
                }
                
            } catch (Exception $e) {
                logMessage("Error importing students: " . $e->getMessage());
            }
        } else {
            logMessage("Invalid file extension");
        }
    } else {
        logMessage("File upload error. Error code: " . $_FILES['file']['error']);
        echo "File upload failed. Please check the log for details.";
    }
} else {
    logMessage("No file upload detected");
    echo "No file was uploaded. Please select a file and try again.";
}

function importStudentsFromFile($conn, $file) {
    require_once 'vendor/autoload.php';

    $reader = new Xlsx();
    $spreadsheet = $reader->load($file);
    $worksheet = $spreadsheet->getActiveSheet();
    $highestRow = $worksheet->getHighestRow();

    $importedStudents = 0;
    $importedStudentNumbers = [];

    for ($row = 2; $row <= $highestRow; $row++) {
        // Create a new array for each student to avoid overwriting
        $rowData = [];
        for ($col = 'A'; $col <= 'J'; $col++) { // Updated to include column J for email
            $rowData[] = $worksheet->getCell($col . $row)->getValue();
        }

        // Inside the importStudentsFromFile function, before mapping the data:
        $birthdate = $worksheet->getCell('H' . $row)->getValue();

        if (is_numeric($birthdate)) {
            // Convert Excel date number to PHP date
            $birthdate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($birthdate)->format('Y-m-d');
        } else {
            // Trim any whitespace and check if the date is in the expected format
            $birthdate = trim($birthdate);
          
            // Check if the date is in a recognized format
            $dateTime = DateTime::createFromFormat('d/m/Y', $birthdate);
            if ($dateTime) {
                $birthdate = $dateTime->format('Y-m-d');
            } else {
                // If parsing fails, log an error
                logMessage("Invalid date format for row $row: $birthdate");
                $birthdate = null; // or set a default value
            }
        }
        
        // Map the row data to the student data array
        $studentData = [
            'Student Number' => $rowData[0],
            'Surname' => $rowData[1],
            'First Name' => $rowData[2],
            'Middle Name' => $rowData[3],
            'Program' => $rowData[4],
            'Year Level' => $rowData[5],
            'Gender' => $rowData[6],
            'Birthdate' => $birthdate,
            'Phone Number' => $rowData[8],
            'Email' => !empty($rowData[9]) ? $rowData[9] : null, // Add email field, set to null if empty
        ];

        $importedStudentNumbers[] = $rowData[0];
        // Call the importStudent function for each student
        $importedStudents += importStudent($conn, $studentData);
    }

    // Update status of non-imported students
    updateNonImportedStudents($conn, $importedStudentNumbers);

    return $importedStudents;
}

// Add this after the importStudentsFromFile function
function updateNonImportedStudents($conn, $importedStudentNumbers) {
    // Get the selected status from POST
    $selectedStatus = $_POST['importStatus'] ?? 'Not Enrolled';
    
    // Only proceed with status updates if importing non-graduate students
    if ($selectedStatus !== 'Graduate' && !empty($importedStudentNumbers)) {
        // Create placeholders for the IN clause
        $placeholders = str_repeat('?,', count($importedStudentNumbers) - 1) . '?';
        
        $sql = "UPDATE students 
                SET status = 'Not Enrolled' 
                WHERE student_no NOT IN ($placeholders)
                AND status != 'Graduate'";  // Preserve graduate status
        
        $stmt = $conn->prepare($sql);
        
        // Bind parameters individually using bind_param
        $types = str_repeat('s', count($importedStudentNumbers));
        
        // Need to create references for bind_param to work correctly with call_user_func_array
        $params = array();
        $params[] = &$types;
        
        foreach($importedStudentNumbers as $key => $value) {
            $params[] = &$importedStudentNumbers[$key];
        }
        
        // Use call_user_func_array to pass the parameters by reference
        call_user_func_array(array($stmt, 'bind_param'), $params);
        
        // Execute without parameters
        $stmt->execute();
        $stmt->close();
    }
}

// Then update the importStudent function to include email in the column mapping
function importStudent($conn, $rowData) {
    // Check if student already exists
    $checkSql = "SELECT student_no FROM students WHERE student_no = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("s", $rowData['Student Number']);
    $checkStmt->execute();
    $checkStmt->store_result();

    // Get the selected status from POST
    $selectedStatus = $_POST['status'] ?? 'Not Enrolled';

    if ($checkStmt->num_rows > 0) {
        // Student exists - update the status, middle name, year level, program, phone number and email if changed
        $updateSql = "UPDATE students SET 
                        status = ?, 
                        middle_name = ?, 
                        year_level = ?, 
                        program_id = ?, 
                        phone_number = ?,
                        email = ? 
                      WHERE student_no = ?";
        
        $updateStmt = $conn->prepare($updateSql);
        $programId = getProgramId($conn, $rowData['Program']); // Get program ID based on the program name
        $updateStmt->bind_param("ssissss", $selectedStatus, $rowData['Middle Name'], $rowData['Year Level'], $programId, $rowData['Phone Number'], $rowData['Email'], $rowData['Student Number']);
        $updateStmt->execute();
        $updateStmt->close();
        return 1;
    } else {
        // New student - insert full record
        $columnMapping = [
            'Student Number' => 'student_no',
            'Surname' => 'surname',
            'First Name' => 'first_name',
            'Middle Name' => 'middle_name',
            'Program' => 'program_id',
            'Year Level' => 'year_level',
            'Gender' => 'gender',
            'Birthdate' => 'birthdate',
            'Phone Number' => 'phone_number',
            'Email' => 'email',
            'Status' => 'status'
        ];

        $columns = array_values($columnMapping);
        $placeholders = implode(", ", array_fill(0, count($columns), "?"));
        $sql = "INSERT INTO students (" . implode(", ", $columns) . ") VALUES ($placeholders)";

        $stmt = $conn->prepare($sql);
        $values = [];
        $types = "";

        foreach ($columnMapping as $excelColumn => $databaseColumn) {
            $value = isset($rowData[$excelColumn]) ? $rowData[$excelColumn] : null;

            if ($databaseColumn === 'status') {
                $value = $selectedStatus;
                $types .= "s";
            } elseif ($databaseColumn === 'program_id') {
                $value = getProgramId($conn, $value);
                $types .= "i";
            } else {
                $types .= "s";
            }
            $values[] = $value;
        }

        try {
            $stmt->bind_param($types, ...$values);
            $stmt->execute();
            $stmt->close();
            return 1;
        } catch (Exception $e) {
            logMessage("Error importing student: " . $e->getMessage());
            return 0;
        }
    }
}
function getProgramId($conn, $programName) {
    $sql = "SELECT program_id FROM program WHERE program_name = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $programName);
    $stmt->execute();
    $programId = null;
    $stmt->bind_result($programId);
    
    if ($stmt->fetch()) {
        $stmt->close();
        return $programId;
    } else {
        $stmt->close();
        logMessage("Program not found: $programName");
        return null;
    }
}

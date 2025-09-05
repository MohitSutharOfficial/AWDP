<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config/database.php';

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Initialize database connection
try {
    $db = new Database();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection error: ' . $e->getMessage()]);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$response = ['success' => false, 'message' => ''];

// Helper function for validation
function validateRequired($data, $required_fields) {
    $missing = [];
    foreach ($required_fields as $field) {
        if (empty(trim($data[$field] ?? ''))) {
            $missing[] = $field;
        }
    }
    return $missing;
}

// Helper function to sanitize input
function sanitizeInput($input) {
    return trim(htmlspecialchars($input, ENT_QUOTES, 'UTF-8'));
}

try {
    switch ($action) {
        case 'test_connection':
            $response = ['success' => true, 'message' => 'Database connection successful', 'timestamp' => time()];
            break;

        // Contact Management
        case 'get_contact':
            $id = intval($_GET['id'] ?? $_POST['id'] ?? 0);
            if ($id > 0) {
                $contact = $db->fetchOne("SELECT * FROM contacts WHERE id = ?", [$id]);
                if ($contact) {
                    $response = ['success' => true, 'data' => $contact];
                } else {
                    $response = ['success' => false, 'message' => 'Contact not found'];
                }
            } else {
                $response = ['success' => false, 'message' => 'Invalid contact ID'];
            }
            break;

        case 'update_contact_status':
            $id = intval($_POST['id'] ?? 0);
            $status = sanitizeInput($_POST['status'] ?? '');
            
            if ($id > 0 && in_array($status, ['new', 'read', 'replied'])) {
                $result = $db->execute("UPDATE contacts SET status = ? WHERE id = ?", [$status, $id]);
                if ($result) {
                    $response = ['success' => true, 'message' => 'Contact status updated successfully'];
                } else {
                    $response = ['success' => false, 'message' => 'Failed to update contact status'];
                }
            } else {
                $response = ['success' => false, 'message' => 'Invalid contact ID or status'];
            }
            break;

        case 'delete_contact':
            $id = intval($_POST['id'] ?? 0);
            if ($id > 0) {
                $result = $db->execute("DELETE FROM contacts WHERE id = ?", [$id]);
                if ($result) {
                    $response = ['success' => true, 'message' => 'Contact deleted successfully'];
                } else {
                    $response = ['success' => false, 'message' => 'Failed to delete contact'];
                }
            } else {
                $response = ['success' => false, 'message' => 'Invalid contact ID'];
            }
            break;

        // Testimonial Management
        case 'get_testimonial':
            $id = intval($_GET['id'] ?? $_POST['id'] ?? 0);
            if ($id > 0) {
                $testimonial = $db->fetchOne("SELECT * FROM testimonials WHERE id = ?", [$id]);
                if ($testimonial) {
                    $response = ['success' => true, 'data' => $testimonial];
                } else {
                    $response = ['success' => false, 'message' => 'Testimonial not found'];
                }
            } else {
                $response = ['success' => false, 'message' => 'Invalid testimonial ID'];
            }
            break;

        case 'add_testimonial':
            $data = [
                'name' => sanitizeInput($_POST['name'] ?? ''),
                'company' => sanitizeInput($_POST['company'] ?? ''),
                'message' => sanitizeInput($_POST['message'] ?? ''),
                'rating' => intval($_POST['rating'] ?? 5),
                'status' => sanitizeInput($_POST['status'] ?? 'pending')
            ];
            
            $missing = validateRequired($data, ['name', 'message']);
            if (!empty($missing)) {
                $response = ['success' => false, 'message' => 'Required fields missing: ' . implode(', ', $missing)];
            } else {
                // Map status to is_active
                $is_active = ($data['status'] === 'approved') ? 1 : 0;
                
                $result = $db->execute(
                    "INSERT INTO testimonials (name, company, testimonial, rating, is_active, created_at) VALUES (?, ?, ?, ?, ?, NOW())",
                    [$data['name'], $data['company'], $data['message'], $data['rating'], $is_active]
                );
                
                if ($result) {
                    $response = ['success' => true, 'message' => 'Testimonial added successfully'];
                } else {
                    $response = ['success' => false, 'message' => 'Failed to add testimonial'];
                }
            }
            break;

        case 'update_testimonial':
            $id = intval($_POST['id'] ?? 0);
            $data = [
                'name' => sanitizeInput($_POST['name'] ?? ''),
                'company' => sanitizeInput($_POST['company'] ?? ''),
                'message' => sanitizeInput($_POST['message'] ?? ''),
                'rating' => intval($_POST['rating'] ?? 5),
                'status' => sanitizeInput($_POST['status'] ?? 'pending')
            ];
            
            if ($id <= 0) {
                $response = ['success' => false, 'message' => 'Invalid testimonial ID'];
            } else {
                $missing = validateRequired($data, ['name', 'message']);
                if (!empty($missing)) {
                    $response = ['success' => false, 'message' => 'Required fields missing: ' . implode(', ', $missing)];
                } else {
                    // Map status to is_active
                    $is_active = ($data['status'] === 'approved') ? 1 : 0;
                    
                    $result = $db->execute(
                        "UPDATE testimonials SET name = ?, company = ?, testimonial = ?, rating = ?, is_active = ?, updated_at = NOW() WHERE id = ?",
                        [$data['name'], $data['company'], $data['message'], $data['rating'], $is_active, $id]
                    );
                    
                    if ($result) {
                        $response = ['success' => true, 'message' => 'Testimonial updated successfully'];
                    } else {
                        $response = ['success' => false, 'message' => 'Failed to update testimonial'];
                    }
                }
            }
            break;

        case 'delete_testimonial':
            $id = intval($_POST['id'] ?? 0);
            if ($id > 0) {
                $result = $db->execute("DELETE FROM testimonials WHERE id = ?", [$id]);
                if ($result) {
                    $response = ['success' => true, 'message' => 'Testimonial deleted successfully'];
                } else {
                    $response = ['success' => false, 'message' => 'Failed to delete testimonial'];
                }
            } else {
                $response = ['success' => false, 'message' => 'Invalid testimonial ID'];
            }
            break;

        case 'update_testimonial_status':
            $id = intval($_POST['id'] ?? 0);
            $status = sanitizeInput($_POST['status'] ?? '');
            
            if ($id > 0) {
                // Map status to is_active
                $is_active = ($status === 'approved') ? 1 : 0;
                
                $result = $db->execute("UPDATE testimonials SET is_active = ? WHERE id = ?", [$is_active, $id]);
                if ($result) {
                    $response = ['success' => true, 'message' => 'Testimonial status updated successfully'];
                } else {
                    $response = ['success' => false, 'message' => 'Failed to update testimonial status'];
                }
            } else {
                $response = ['success' => false, 'message' => 'Invalid testimonial ID'];
            }
            break;

        // Database Operations
        case 'optimize_table':
            $table = sanitizeInput($_POST['table'] ?? '');
            $allowed_tables = ['contacts', 'testimonials', 'all'];
            
            if (in_array($table, $allowed_tables)) {
                if ($table === 'all') {
                    $db->execute("OPTIMIZE TABLE contacts, testimonials");
                    $response = ['success' => true, 'message' => 'All tables optimized successfully'];
                } else {
                    $db->execute("OPTIMIZE TABLE " . $table);
                    $response = ['success' => true, 'message' => "Table {$table} optimized successfully"];
                }
            } else {
                $response = ['success' => false, 'message' => 'Invalid table name'];
            }
            break;

        case 'get_stats':
            // Get database statistics
            $totalContacts = $db->fetchOne("SELECT COUNT(*) as count FROM contacts")['count'] ?? 0;
            $newContacts = $db->fetchOne("SELECT COUNT(*) as count FROM contacts WHERE status = 'new'")['count'] ?? 0;
            $totalTestimonials = $db->fetchOne("SELECT COUNT(*) as count FROM testimonials")['count'] ?? 0;
            $activeTestimonials = $db->fetchOne("SELECT COUNT(*) as count FROM testimonials WHERE is_active = 1")['count'] ?? 0;
            
            $response = [
                'success' => true,
                'data' => [
                    'total_contacts' => intval($totalContacts),
                    'new_contacts' => intval($newContacts),
                    'total_testimonials' => intval($totalTestimonials),
                    'active_testimonials' => intval($activeTestimonials),
                    'last_updated' => date('Y-m-d H:i:s')
                ]
            ];
            break;

        default:
            $response = ['success' => false, 'message' => 'Unknown action: ' . $action];
            break;
    }

} catch (Exception $e) {
    error_log("API Error in admin-crud.php: " . $e->getMessage());
    $response = [
        'success' => false,
        'message' => 'Server error occurred. Please try again.',
        'debug' => $e->getMessage(), // Remove in production
        'action' => $action
    ];
}

// Output response
echo json_encode($response);
?>

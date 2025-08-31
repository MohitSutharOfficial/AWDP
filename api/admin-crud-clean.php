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

try {
    switch ($action) {
        case 'test':
            $response = ['success' => true, 'message' => 'API is working', 'timestamp' => time()];
            break;

        case 'get_stats':
            // Get database statistics
            $totalContacts = $db->fetchOne("SELECT COUNT(*) as count FROM contacts")['count'] ?? 0;
            $newContacts = $db->fetchOne("SELECT COUNT(*) as count FROM contacts WHERE status = 'new'")['count'] ?? 0;
            $totalTestimonials = $db->fetchOne("SELECT COUNT(*) as count FROM testimonials")['count'] ?? 0;
            
            $response = [
                'success' => true,
                'data' => [
                    'total_contacts' => intval($totalContacts),
                    'new_contacts' => intval($newContacts),
                    'total_testimonials' => intval($totalTestimonials),
                    'last_updated' => date('Y-m-d H:i:s')
                ]
            ];
            break;

        case 'get_contacts':
            // Get all contacts
            $contacts = $db->fetchAll("SELECT * FROM contacts ORDER BY created_at DESC");
            $response = [
                'success' => true,
                'data' => $contacts,
                'count' => count($contacts)
            ];
            break;

        case 'get_testimonials':
            // Get all testimonials
            $testimonials = $db->fetchAll("SELECT * FROM testimonials ORDER BY created_at DESC");
            $response = [
                'success' => true,
                'data' => $testimonials,
                'count' => count($testimonials)
            ];
            break;

        case 'mark_contact_read':
            $contactId = intval($_POST['contact_id'] ?? 0);
            if ($contactId > 0) {
                $result = $db->execute("UPDATE contacts SET status = 'read' WHERE id = ?", [$contactId]);
                if ($result) {
                    $response = ['success' => true, 'message' => 'Contact marked as read'];
                } else {
                    $response = ['success' => false, 'message' => 'Failed to update contact'];
                }
            } else {
                $response = ['success' => false, 'message' => 'Invalid contact ID'];
            }
            break;

        case 'delete_contact':
            $contactId = intval($_POST['contact_id'] ?? 0);
            if ($contactId > 0) {
                $result = $db->execute("DELETE FROM contacts WHERE id = ?", [$contactId]);
                if ($result) {
                    $response = ['success' => true, 'message' => 'Contact deleted successfully'];
                } else {
                    $response = ['success' => false, 'message' => 'Failed to delete contact'];
                }
            } else {
                $response = ['success' => false, 'message' => 'Invalid contact ID'];
            }
            break;

        case 'mark_all_read':
            $result = $db->execute("UPDATE contacts SET status = 'read' WHERE status = 'new'");
            if ($result) {
                $response = ['success' => true, 'message' => 'All contacts marked as read'];
            } else {
                $response = ['success' => false, 'message' => 'Failed to update contacts'];
            }
            break;

        case 'toggle_testimonial_status':
            $testimonialId = intval($_POST['testimonial_id'] ?? 0);
            if ($testimonialId > 0) {
                $current = $db->fetchOne("SELECT is_active FROM testimonials WHERE id = ?", [$testimonialId]);
                if ($current) {
                    $newStatus = $current['is_active'] ? 0 : 1;
                    $result = $db->execute("UPDATE testimonials SET is_active = ? WHERE id = ?", [$newStatus, $testimonialId]);
                    if ($result) {
                        $response = ['success' => true, 'message' => 'Testimonial status updated', 'new_status' => $newStatus];
                    } else {
                        $response = ['success' => false, 'message' => 'Failed to update testimonial status'];
                    }
                } else {
                    $response = ['success' => false, 'message' => 'Testimonial not found'];
                }
            } else {
                $response = ['success' => false, 'message' => 'Invalid testimonial ID'];
            }
            break;

        case 'delete_testimonial':
            $testimonialId = intval($_POST['testimonial_id'] ?? 0);
            if ($testimonialId > 0) {
                $result = $db->execute("DELETE FROM testimonials WHERE id = ?", [$testimonialId]);
                if ($result) {
                    $response = ['success' => true, 'message' => 'Testimonial deleted successfully'];
                } else {
                    $response = ['success' => false, 'message' => 'Failed to delete testimonial'];
                }
            } else {
                $response = ['success' => false, 'message' => 'Invalid testimonial ID'];
            }
            break;

        default:
            $response = ['success' => false, 'message' => 'Unknown action: ' . $action];
            break;
    }

} catch (Exception $e) {
    error_log("API Error in admin-crud.php: " . $e->getMessage());
    $response = [
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage(),
        'action' => $action
    ];
}

// Output response
echo json_encode($response);
?>

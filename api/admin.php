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

// Check authentication
$isLoggedIn = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

if (!$isLoggedIn) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Initialize database connection
try {
    $db = new Database();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection error']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$response = ['success' => false, 'message' => ''];

try {
    switch ($action) {
        case 'get_stats':
            $stats = [
                'contacts' => $db->fetchOne("SELECT COUNT(*) as count FROM contacts")['count'] ?? 0,
                'new_contacts' => $db->fetchOne("SELECT COUNT(*) as count FROM contacts WHERE status = 'new'")['count'] ?? 0,
                'testimonials' => $db->fetchOne("SELECT COUNT(*) as count FROM testimonials")['count'] ?? 0,
                'active_testimonials' => $db->fetchOne("SELECT COUNT(*) as count FROM testimonials WHERE is_active = 1")['count'] ?? 0
            ];
            $response = ['success' => true, 'data' => $stats];
            break;
            
        case 'get_contacts':
            $page = max(1, intval($_GET['page'] ?? 1));
            $limit = min(100, max(10, intval($_GET['limit'] ?? 20)));
            $offset = ($page - 1) * $limit;
            
            $contacts = $db->fetchAll("SELECT * FROM contacts ORDER BY created_at DESC LIMIT ? OFFSET ?", [$limit, $offset]);
            $total = $db->fetchOne("SELECT COUNT(*) as count FROM contacts")['count'] ?? 0;
            
            $response = [
                'success' => true,
                'data' => $contacts,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'pages' => ceil($total / $limit)
                ]
            ];
            break;
            
        case 'get_testimonials':
            $page = max(1, intval($_GET['page'] ?? 1));
            $limit = min(100, max(10, intval($_GET['limit'] ?? 20)));
            $offset = ($page - 1) * $limit;
            
            $testimonials = $db->fetchAll("SELECT * FROM testimonials ORDER BY created_at DESC LIMIT ? OFFSET ?", [$limit, $offset]);
            $total = $db->fetchOne("SELECT COUNT(*) as count FROM testimonials")['count'] ?? 0;
            
            $response = [
                'success' => true,
                'data' => $testimonials,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'pages' => ceil($total / $limit)
                ]
            ];
            break;
            
        case 'mark_contact_read':
            $contactId = intval($_POST['contact_id'] ?? 0);
            if ($contactId > 0) {
                $db->execute("UPDATE contacts SET status = 'read' WHERE id = ?", [$contactId]);
                $response = ['success' => true, 'message' => 'Contact marked as read'];
            } else {
                $response['message'] = 'Invalid contact ID';
            }
            break;
            
        case 'delete_contact':
            $contactId = intval($_POST['contact_id'] ?? 0);
            if ($contactId > 0) {
                $db->execute("DELETE FROM contacts WHERE id = ?", [$contactId]);
                $response = ['success' => true, 'message' => 'Contact deleted successfully'];
            } else {
                $response['message'] = 'Invalid contact ID';
            }
            break;
            
        case 'toggle_testimonial_status':
            $testimonialId = intval($_POST['testimonial_id'] ?? 0);
            if ($testimonialId > 0) {
                $current = $db->fetchOne("SELECT is_active FROM testimonials WHERE id = ?", [$testimonialId]);
                if ($current) {
                    $newStatus = $current['is_active'] ? 0 : 1;
                    $db->execute("UPDATE testimonials SET is_active = ? WHERE id = ?", [$newStatus, $testimonialId]);
                    $response = ['success' => true, 'message' => 'Testimonial status updated', 'new_status' => $newStatus];
                } else {
                    $response['message'] = 'Testimonial not found';
                }
            } else {
                $response['message'] = 'Invalid testimonial ID';
            }
            break;
            
        case 'toggle_testimonial_featured':
            $testimonialId = intval($_POST['testimonial_id'] ?? 0);
            if ($testimonialId > 0) {
                $current = $db->fetchOne("SELECT is_featured FROM testimonials WHERE id = ?", [$testimonialId]);
                if ($current) {
                    $newStatus = $current['is_featured'] ? 0 : 1;
                    $db->execute("UPDATE testimonials SET is_featured = ? WHERE id = ?", [$newStatus, $testimonialId]);
                    $response = ['success' => true, 'message' => 'Featured status updated', 'new_status' => $newStatus];
                } else {
                    $response['message'] = 'Testimonial not found';
                }
            } else {
                $response['message'] = 'Invalid testimonial ID';
            }
            break;
            
        case 'add_testimonial':
            $data = [
                'name' => trim($_POST['name'] ?? ''),
                'company' => trim($_POST['company'] ?? ''),
                'position' => trim($_POST['position'] ?? ''),
                'testimonial' => trim($_POST['testimonial'] ?? ''),
                'rating' => max(1, min(5, intval($_POST['rating'] ?? 5))),
                'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
                'is_active' => 1
            ];
            
            if (!empty($data['name']) && !empty($data['testimonial'])) {
                $db->execute(
                    "INSERT INTO testimonials (name, company, position, testimonial, rating, is_featured, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())",
                    [$data['name'], $data['company'], $data['position'], $data['testimonial'], $data['rating'], $data['is_featured'], $data['is_active']]
                );
                $response = ['success' => true, 'message' => 'Testimonial added successfully'];
            } else {
                $response['message'] = 'Name and testimonial are required';
            }
            break;
            
        case 'update_testimonial':
            $testimonialId = intval($_POST['testimonial_id'] ?? 0);
            if ($testimonialId > 0) {
                $data = [
                    'name' => trim($_POST['name'] ?? ''),
                    'company' => trim($_POST['company'] ?? ''),
                    'position' => trim($_POST['position'] ?? ''),
                    'testimonial' => trim($_POST['testimonial'] ?? ''),
                    'rating' => max(1, min(5, intval($_POST['rating'] ?? 5))),
                    'is_featured' => isset($_POST['is_featured']) ? 1 : 0
                ];
                
                if (!empty($data['name']) && !empty($data['testimonial'])) {
                    $db->execute(
                        "UPDATE testimonials SET name = ?, company = ?, position = ?, testimonial = ?, rating = ?, is_featured = ? WHERE id = ?",
                        [$data['name'], $data['company'], $data['position'], $data['testimonial'], $data['rating'], $data['is_featured'], $testimonialId]
                    );
                    $response = ['success' => true, 'message' => 'Testimonial updated successfully'];
                } else {
                    $response['message'] = 'Name and testimonial are required';
                }
            } else {
                $response['message'] = 'Invalid testimonial ID';
            }
            break;
            
        case 'delete_testimonial':
            $testimonialId = intval($_POST['testimonial_id'] ?? 0);
            if ($testimonialId > 0) {
                $db->execute("DELETE FROM testimonials WHERE id = ?", [$testimonialId]);
                $response = ['success' => true, 'message' => 'Testimonial deleted successfully'];
            } else {
                $response['message'] = 'Invalid testimonial ID';
            }
            break;
            
        case 'mark_all_contacts_read':
            $db->execute("UPDATE contacts SET status = 'read' WHERE status = 'new'");
            $response = ['success' => true, 'message' => 'All contacts marked as read'];
            break;
            
        default:
            $response['message'] = 'Unknown action';
    }
} catch (Exception $e) {
    error_log("API Error: " . $e->getMessage());
    $response['message'] = 'Server error occurred';
}

echo json_encode($response);
?>

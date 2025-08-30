<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

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
    $stats = new AdminStats($db);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection error']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$entity = $_GET['entity'] ?? $_POST['entity'] ?? '';
$response = ['success' => false, 'message' => ''];

try {
    switch ($action) {
        case 'get_stats':
            $allStats = $stats->getAllStats();
            $response = ['success' => true, 'data' => $allStats];
            break;
            
        // ==================== CONTACTS CRUD ====================
        case 'get_contacts':
            $page = max(1, intval($_GET['page'] ?? 1));
            $limit = min(100, max(10, intval($_GET['limit'] ?? 20)));
            $offset = ($page - 1) * $limit;
            $search = $_GET['search'] ?? '';
            $status = $_GET['status'] ?? '';
            
            $whereClause = '';
            $params = [];
            $conditions = [];
            
            if (!empty($search)) {
                $conditions[] = "(name ILIKE ? OR email ILIKE ? OR company ILIKE ? OR message ILIKE ?)";
                $searchParam = "%{$search}%";
                $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam]);
            }
            
            if (!empty($status)) {
                $conditions[] = "status = ?";
                $params[] = $status;
            }
            
            if (!empty($conditions)) {
                $whereClause = "WHERE " . implode(" AND ", $conditions);
            }
            
            $params = array_merge($params, [$limit, $offset]);
            
            $contacts = $db->fetchAll("SELECT * FROM contacts {$whereClause} ORDER BY created_at DESC LIMIT ? OFFSET ?", $params);
            
            $countParams = array_slice($params, 0, -2); // Remove limit and offset
            $total = $db->fetchOne("SELECT COUNT(*) as count FROM contacts {$whereClause}", $countParams)['count'] ?? 0;
            
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
            
        case 'get_contact':
            $id = intval($_GET['id'] ?? 0);
            if ($id > 0) {
                $contact = $db->fetchOne("SELECT * FROM contacts WHERE id = ?", [$id]);
                if ($contact) {
                    $response = ['success' => true, 'data' => $contact];
                } else {
                    $response['message'] = 'Contact not found';
                }
            } else {
                $response['message'] = 'Invalid contact ID';
            }
            break;
            
        case 'update_contact_status':
            $contactId = intval($_POST['contact_id'] ?? 0);
            $status = $_POST['status'] ?? '';
            $validStatuses = ['new', 'read', 'replied'];
            
            if ($contactId > 0 && in_array($status, $validStatuses)) {
                $db->execute("UPDATE contacts SET status = ? WHERE id = ?", [$status, $contactId]);
                $response = ['success' => true, 'message' => 'Contact status updated'];
            } else {
                $response['message'] = 'Invalid contact ID or status';
            }
            break;
            
        case 'delete_contact':
            $contactId = intval($_POST['contact_id'] ?? $_POST['id'] ?? 0);
            if ($contactId > 0) {
                $db->execute("DELETE FROM contacts WHERE id = ?", [$contactId]);
                $response = ['success' => true, 'message' => 'Contact deleted successfully'];
            } else {
                $response['message'] = 'Invalid contact ID';
            }
            break;
            
        case 'mark_contact_read':
            $contactId = intval($_POST['contact_id'] ?? $_POST['id'] ?? 0);
            if ($contactId > 0) {
                $db->execute("UPDATE contacts SET is_read = true WHERE id = ?", [$contactId]);
                $response = ['success' => true, 'message' => 'Contact marked as read'];
            } else {
                $response['message'] = 'Invalid contact ID';
            }
            break;
            
        case 'mark_all_contacts_read':
            $db->execute("UPDATE contacts SET is_read = true WHERE is_read = false");
            $response = ['success' => true, 'message' => 'All contacts marked as read'];
            break;
            
        // ==================== TESTIMONIALS CRUD ====================
        case 'get_testimonials':
            $page = max(1, intval($_GET['page'] ?? 1));
            $limit = min(100, max(10, intval($_GET['limit'] ?? 20)));
            $offset = ($page - 1) * $limit;
            $search = $_GET['search'] ?? '';
            $status = $_GET['status'] ?? '';
            
            $whereClause = '';
            $params = [];
            $conditions = [];
            
            if (!empty($search)) {
                $conditions[] = "(name ILIKE ? OR company ILIKE ? OR testimonial ILIKE ?)";
                $searchParam = "%{$search}%";
                $params = array_merge($params, [$searchParam, $searchParam, $searchParam]);
            }
            
            if ($status === 'active') {
                $conditions[] = "is_active = true";
            } elseif ($status === 'inactive') {
                $conditions[] = "is_active = false";
            } elseif ($status === 'featured') {
                $conditions[] = "is_featured = true";
            }
            
            if (!empty($conditions)) {
                $whereClause = "WHERE " . implode(" AND ", $conditions);
            }
            
            $params = array_merge($params, [$limit, $offset]);
            
            $testimonials = $db->fetchAll("SELECT * FROM testimonials {$whereClause} ORDER BY is_featured DESC, created_at DESC LIMIT ? OFFSET ?", $params);
            
            $countParams = array_slice($params, 0, -2);
            $total = $db->fetchOne("SELECT COUNT(*) as count FROM testimonials {$whereClause}", $countParams)['count'] ?? 0;
            
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
            
        case 'get_testimonial':
            $id = intval($_GET['id'] ?? 0);
            if ($id > 0) {
                $testimonial = $db->fetchOne("SELECT * FROM testimonials WHERE id = ?", [$id]);
                if ($testimonial) {
                    $response = ['success' => true, 'data' => $testimonial];
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
                'image_url' => trim($_POST['image_url'] ?? ''),
                'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
                'is_active' => 1
            ];
            
            if (!empty($data['name']) && !empty($data['testimonial'])) {
                $db->execute(
                    "INSERT INTO testimonials (name, company, position, testimonial, rating, image_url, is_featured, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())",
                    [$data['name'], $data['company'], $data['position'], $data['testimonial'], $data['rating'], $data['image_url'], $data['is_featured'], $data['is_active']]
                );
                $response = ['success' => true, 'message' => 'Testimonial added successfully'];
            } else {
                $response['message'] = 'Name and testimonial are required';
            }
            break;
            
        case 'create_testimonial':
            // Alias for add_testimonial for backward compatibility
            $data = [
                'name' => trim($_POST['name'] ?? ''),
                'company' => trim($_POST['company'] ?? ''),
                'position' => trim($_POST['position'] ?? ''),
                'testimonial' => trim($_POST['testimonial'] ?? ''),
                'rating' => max(1, min(5, intval($_POST['rating'] ?? 5))),
                'image_url' => trim($_POST['image_url'] ?? ''),
                'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
                'is_active' => 1
            ];
            
            if (!empty($data['name']) && !empty($data['testimonial'])) {
                $db->execute(
                    "INSERT INTO testimonials (name, company, position, testimonial, rating, image_url, is_featured, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())",
                    [$data['name'], $data['company'], $data['position'], $data['testimonial'], $data['rating'], $data['image_url'], $data['is_featured'], $data['is_active']]
                );
                $response = ['success' => true, 'message' => 'Testimonial added successfully'];
            } else {
                $response['message'] = 'Name and testimonial are required';
            }
            break;
            
        case 'update_testimonial':
            $testimonialId = intval($_POST['testimonial_id'] ?? $_POST['id'] ?? 0);
            if ($testimonialId > 0) {
                $data = [
                    'name' => trim($_POST['name'] ?? ''),
                    'company' => trim($_POST['company'] ?? ''),
                    'position' => trim($_POST['position'] ?? ''),
                    'testimonial' => trim($_POST['testimonial'] ?? ''),
                    'rating' => max(1, min(5, intval($_POST['rating'] ?? 5))),
                    'image_url' => trim($_POST['image_url'] ?? ''),
                    'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
                    'is_active' => isset($_POST['is_active']) ? 1 : 0
                ];
                
                if (!empty($data['name']) && !empty($data['testimonial'])) {
                    $db->execute(
                        "UPDATE testimonials SET name = ?, company = ?, position = ?, testimonial = ?, rating = ?, image_url = ?, is_featured = ?, is_active = ?, updated_at = NOW() WHERE id = ?",
                        [$data['name'], $data['company'], $data['position'], $data['testimonial'], $data['rating'], $data['image_url'], $data['is_featured'], $data['is_active'], $testimonialId]
                    );
                    $response = ['success' => true, 'message' => 'Testimonial updated successfully'];
                } else {
                    $response['message'] = 'Name and testimonial are required';
                }
            } else {
                $response['message'] = 'Invalid testimonial ID';
            }
            break;
            
        case 'toggle_testimonial_status':
            $testimonialId = intval($_POST['testimonial_id'] ?? 0);
            if ($testimonialId > 0) {
                $current = $db->fetchOne("SELECT is_active FROM testimonials WHERE id = ?", [$testimonialId]);
                if ($current) {
                    $newStatus = $current['is_active'] ? 0 : 1;
                    $db->execute("UPDATE testimonials SET is_active = ?, updated_at = NOW() WHERE id = ?", [$newStatus, $testimonialId]);
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
                    $db->execute("UPDATE testimonials SET is_featured = ?, updated_at = NOW() WHERE id = ?", [$newStatus, $testimonialId]);
                    $response = ['success' => true, 'message' => 'Featured status updated', 'new_status' => $newStatus];
                } else {
                    $response['message'] = 'Testimonial not found';
                }
            } else {
                $response['message'] = 'Invalid testimonial ID';
            }
            break;
            
        case 'delete_testimonial':
            $testimonialId = intval($_POST['testimonial_id'] ?? $_POST['id'] ?? 0);
            if ($testimonialId > 0) {
                $db->execute("DELETE FROM testimonials WHERE id = ?", [$testimonialId]);
                $response = ['success' => true, 'message' => 'Testimonial deleted successfully'];
            } else {
                $response['message'] = 'Invalid testimonial ID';
            }
            break;
            
        // ==================== SERVICES CRUD ====================
        case 'get_services':
            $page = max(1, intval($_GET['page'] ?? 1));
            $limit = min(100, max(10, intval($_GET['limit'] ?? 20)));
            $offset = ($page - 1) * $limit;
            $search = $_GET['search'] ?? '';
            
            $whereClause = '';
            $params = [$limit, $offset];
            
            if (!empty($search)) {
                $whereClause = "WHERE title ILIKE ? OR description ILIKE ?";
                $searchParam = "%{$search}%";
                $params = [$searchParam, $searchParam, $limit, $offset];
            }
            
            $services = $db->fetchAll("SELECT * FROM services {$whereClause} ORDER BY sort_order ASC, created_at DESC LIMIT ? OFFSET ?", $params);
            $total = $db->fetchOne("SELECT COUNT(*) as count FROM services {$whereClause}", 
                                   $search ? [$searchParam, $searchParam] : [])['count'] ?? 0;
            
            $response = [
                'success' => true,
                'data' => $services,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'pages' => ceil($total / $limit)
                ]
            ];
            break;
            
        case 'get_service':
            $id = intval($_GET['id'] ?? 0);
            if ($id > 0) {
                $service = $db->fetchOne("SELECT * FROM services WHERE id = ?", [$id]);
                if ($service) {
                    $response = ['success' => true, 'data' => $service];
                } else {
                    $response['message'] = 'Service not found';
                }
            } else {
                $response['message'] = 'Invalid service ID';
            }
            break;
            
        case 'add_service':
            $data = [
                'title' => trim($_POST['title'] ?? ''),
                'description' => trim($_POST['description'] ?? ''),
                'icon' => trim($_POST['icon'] ?? ''),
                'features' => $_POST['features'] ?? '[]',
                'price_range' => trim($_POST['price_range'] ?? ''),
                'sort_order' => intval($_POST['sort_order'] ?? 0),
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ];
            
            if (!empty($data['title']) && !empty($data['description'])) {
                $db->execute(
                    "INSERT INTO services (title, description, icon, features, price_range, sort_order, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())",
                    [$data['title'], $data['description'], $data['icon'], $data['features'], $data['price_range'], $data['sort_order'], $data['is_active']]
                );
                $response = ['success' => true, 'message' => 'Service added successfully'];
            } else {
                $response['message'] = 'Title and description are required';
            }
            break;
            
        case 'update_service':
            $serviceId = intval($_POST['service_id'] ?? 0);
            if ($serviceId > 0) {
                $data = [
                    'title' => trim($_POST['title'] ?? ''),
                    'description' => trim($_POST['description'] ?? ''),
                    'icon' => trim($_POST['icon'] ?? ''),
                    'features' => $_POST['features'] ?? '[]',
                    'price_range' => trim($_POST['price_range'] ?? ''),
                    'sort_order' => intval($_POST['sort_order'] ?? 0),
                    'is_active' => isset($_POST['is_active']) ? 1 : 0
                ];
                
                if (!empty($data['title']) && !empty($data['description'])) {
                    $db->execute(
                        "UPDATE services SET title = ?, description = ?, icon = ?, features = ?, price_range = ?, sort_order = ?, is_active = ?, updated_at = NOW() WHERE id = ?",
                        [$data['title'], $data['description'], $data['icon'], $data['features'], $data['price_range'], $data['sort_order'], $data['is_active'], $serviceId]
                    );
                    $response = ['success' => true, 'message' => 'Service updated successfully'];
                } else {
                    $response['message'] = 'Title and description are required';
                }
            } else {
                $response['message'] = 'Invalid service ID';
            }
            break;
            
        case 'delete_service':
            $serviceId = intval($_POST['service_id'] ?? 0);
            if ($serviceId > 0) {
                $db->execute("DELETE FROM services WHERE id = ?", [$serviceId]);
                $response = ['success' => true, 'message' => 'Service deleted successfully'];
            } else {
                $response['message'] = 'Invalid service ID';
            }
            break;
            
        case 'toggle_service_status':
            $serviceId = intval($_POST['service_id'] ?? 0);
            if ($serviceId > 0) {
                $current = $db->fetchOne("SELECT is_active FROM services WHERE id = ?", [$serviceId]);
                if ($current) {
                    $newStatus = $current['is_active'] ? 0 : 1;
                    $db->execute("UPDATE services SET is_active = ?, updated_at = NOW() WHERE id = ?", [$newStatus, $serviceId]);
                    $response = ['success' => true, 'message' => 'Service status updated', 'new_status' => $newStatus];
                } else {
                    $response['message'] = 'Service not found';
                }
            } else {
                $response['message'] = 'Invalid service ID';
            }
            break;
            
        // ==================== PROJECTS CRUD ====================
        case 'get_projects':
            $page = max(1, intval($_GET['page'] ?? 1));
            $limit = min(100, max(10, intval($_GET['limit'] ?? 20)));
            $offset = ($page - 1) * $limit;
            $search = $_GET['search'] ?? '';
            $status = $_GET['status'] ?? '';
            
            $whereClause = '';
            $params = [];
            $conditions = [];
            
            if (!empty($search)) {
                $conditions[] = "(title ILIKE ? OR description ILIKE ? OR client_name ILIKE ?)";
                $searchParam = "%{$search}%";
                $params = array_merge($params, [$searchParam, $searchParam, $searchParam]);
            }
            
            if ($status === 'active') {
                $conditions[] = "is_active = true";
            } elseif ($status === 'inactive') {
                $conditions[] = "is_active = false";
            } elseif ($status === 'featured') {
                $conditions[] = "is_featured = true";
            }
            
            if (!empty($conditions)) {
                $whereClause = "WHERE " . implode(" AND ", $conditions);
            }
            
            $params = array_merge($params, [$limit, $offset]);
            
            $projects = $db->fetchAll("SELECT * FROM projects {$whereClause} ORDER BY is_featured DESC, completion_date DESC, created_at DESC LIMIT ? OFFSET ?", $params);
            
            $countParams = array_slice($params, 0, -2);
            $total = $db->fetchOne("SELECT COUNT(*) as count FROM projects {$whereClause}", $countParams)['count'] ?? 0;
            
            $response = [
                'success' => true,
                'data' => $projects,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'pages' => ceil($total / $limit)
                ]
            ];
            break;
            
        case 'get_project':
            $id = intval($_GET['id'] ?? 0);
            if ($id > 0) {
                $project = $db->fetchOne("SELECT * FROM projects WHERE id = ?", [$id]);
                if ($project) {
                    $response = ['success' => true, 'data' => $project];
                } else {
                    $response['message'] = 'Project not found';
                }
            } else {
                $response['message'] = 'Invalid project ID';
            }
            break;
            
        case 'add_project':
            $data = [
                'title' => trim($_POST['title'] ?? ''),
                'description' => trim($_POST['description'] ?? ''),
                'technologies' => $_POST['technologies'] ?? '[]',
                'image_url' => trim($_POST['image_url'] ?? ''),
                'project_url' => trim($_POST['project_url'] ?? ''),
                'github_url' => trim($_POST['github_url'] ?? ''),
                'client_name' => trim($_POST['client_name'] ?? ''),
                'completion_date' => $_POST['completion_date'] ?? null,
                'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ];
            
            if (!empty($data['title']) && !empty($data['description'])) {
                $db->execute(
                    "INSERT INTO projects (title, description, technologies, image_url, project_url, github_url, client_name, completion_date, is_featured, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())",
                    [$data['title'], $data['description'], $data['technologies'], $data['image_url'], $data['project_url'], $data['github_url'], $data['client_name'], $data['completion_date'], $data['is_featured'], $data['is_active']]
                );
                $response = ['success' => true, 'message' => 'Project added successfully'];
            } else {
                $response['message'] = 'Title and description are required';
            }
            break;
            
        case 'update_project':
            $projectId = intval($_POST['project_id'] ?? 0);
            if ($projectId > 0) {
                $data = [
                    'title' => trim($_POST['title'] ?? ''),
                    'description' => trim($_POST['description'] ?? ''),
                    'technologies' => $_POST['technologies'] ?? '[]',
                    'image_url' => trim($_POST['image_url'] ?? ''),
                    'project_url' => trim($_POST['project_url'] ?? ''),
                    'github_url' => trim($_POST['github_url'] ?? ''),
                    'client_name' => trim($_POST['client_name'] ?? ''),
                    'completion_date' => $_POST['completion_date'] ?? null,
                    'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
                    'is_active' => isset($_POST['is_active']) ? 1 : 0
                ];
                
                if (!empty($data['title']) && !empty($data['description'])) {
                    $db->execute(
                        "UPDATE projects SET title = ?, description = ?, technologies = ?, image_url = ?, project_url = ?, github_url = ?, client_name = ?, completion_date = ?, is_featured = ?, is_active = ?, updated_at = NOW() WHERE id = ?",
                        [$data['title'], $data['description'], $data['technologies'], $data['image_url'], $data['project_url'], $data['github_url'], $data['client_name'], $data['completion_date'], $data['is_featured'], $data['is_active'], $projectId]
                    );
                    $response = ['success' => true, 'message' => 'Project updated successfully'];
                } else {
                    $response['message'] = 'Title and description are required';
                }
            } else {
                $response['message'] = 'Invalid project ID';
            }
            break;
            
        case 'delete_project':
            $projectId = intval($_POST['project_id'] ?? 0);
            if ($projectId > 0) {
                $db->execute("DELETE FROM projects WHERE id = ?", [$projectId]);
                $response = ['success' => true, 'message' => 'Project deleted successfully'];
            } else {
                $response['message'] = 'Invalid project ID';
            }
            break;
            
        case 'toggle_project_status':
            $projectId = intval($_POST['project_id'] ?? 0);
            if ($projectId > 0) {
                $current = $db->fetchOne("SELECT is_active FROM projects WHERE id = ?", [$projectId]);
                if ($current) {
                    $newStatus = $current['is_active'] ? 0 : 1;
                    $db->execute("UPDATE projects SET is_active = ?, updated_at = NOW() WHERE id = ?", [$newStatus, $projectId]);
                    $response = ['success' => true, 'message' => 'Project status updated', 'new_status' => $newStatus];
                } else {
                    $response['message'] = 'Project not found';
                }
            } else {
                $response['message'] = 'Invalid project ID';
            }
            break;
            
        case 'toggle_project_featured':
            $projectId = intval($_POST['project_id'] ?? 0);
            if ($projectId > 0) {
                $current = $db->fetchOne("SELECT is_featured FROM projects WHERE id = ?", [$projectId]);
                if ($current) {
                    $newStatus = $current['is_featured'] ? 0 : 1;
                    $db->execute("UPDATE projects SET is_featured = ?, updated_at = NOW() WHERE id = ?", [$newStatus, $projectId]);
                    $response = ['success' => true, 'message' => 'Featured status updated', 'new_status' => $newStatus];
                } else {
                    $response['message'] = 'Project not found';
                }
            } else {
                $response['message'] = 'Invalid project ID';
            }
            break;
            
        // ==================== BLOG CRUD ====================
        case 'get_blog_posts':
            $page = max(1, intval($_GET['page'] ?? 1));
            $limit = min(100, max(10, intval($_GET['limit'] ?? 20)));
            $offset = ($page - 1) * $limit;
            $search = $_GET['search'] ?? '';
            $status = $_GET['status'] ?? '';
            
            $whereClause = '';
            $params = [];
            $conditions = [];
            
            if (!empty($search)) {
                $conditions[] = "(title ILIKE ? OR excerpt ILIKE ? OR content ILIKE ? OR author ILIKE ?)";
                $searchParam = "%{$search}%";
                $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam]);
            }
            
            if ($status === 'published') {
                $conditions[] = "is_published = true";
            } elseif ($status === 'draft') {
                $conditions[] = "is_published = false";
            }
            
            if (!empty($conditions)) {
                $whereClause = "WHERE " . implode(" AND ", $conditions);
            }
            
            $params = array_merge($params, [$limit, $offset]);
            
            $posts = $db->fetchAll("SELECT * FROM blog_posts {$whereClause} ORDER BY created_at DESC LIMIT ? OFFSET ?", $params);
            
            $countParams = array_slice($params, 0, -2);
            $total = $db->fetchOne("SELECT COUNT(*) as count FROM blog_posts {$whereClause}", $countParams)['count'] ?? 0;
            
            $response = [
                'success' => true,
                'data' => $posts,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'pages' => ceil($total / $limit)
                ]
            ];
            break;
            
        // ==================== NEWSLETTER CRUD ====================
        case 'get_newsletter_subscribers':
            $page = max(1, intval($_GET['page'] ?? 1));
            $limit = min(100, max(10, intval($_GET['limit'] ?? 20)));
            $offset = ($page - 1) * $limit;
            $search = $_GET['search'] ?? '';
            $status = $_GET['status'] ?? '';
            
            $whereClause = '';
            $params = [];
            $conditions = [];
            
            if (!empty($search)) {
                $conditions[] = "(email ILIKE ? OR name ILIKE ?)";
                $searchParam = "%{$search}%";
                $params = array_merge($params, [$searchParam, $searchParam]);
            }
            
            if ($status === 'active') {
                $conditions[] = "is_active = true";
            } elseif ($status === 'inactive') {
                $conditions[] = "is_active = false";
            }
            
            if (!empty($conditions)) {
                $whereClause = "WHERE " . implode(" AND ", $conditions);
            }
            
            $params = array_merge($params, [$limit, $offset]);
            
            $subscribers = $db->fetchAll("SELECT * FROM newsletter_subscribers {$whereClause} ORDER BY subscribed_at DESC LIMIT ? OFFSET ?", $params);
            
            $countParams = array_slice($params, 0, -2);
            $total = $db->fetchOne("SELECT COUNT(*) as count FROM newsletter_subscribers {$whereClause}", $countParams)['count'] ?? 0;
            
            $response = [
                'success' => true,
                'data' => $subscribers,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'pages' => ceil($total / $limit)
                ]
            ];
            break;
            
        // ==================== SERVICES CRUD ====================
        case 'get_services':
            $page = max(1, intval($_GET['page'] ?? 1));
            $limit = min(100, max(10, intval($_GET['limit'] ?? 20)));
            $offset = ($page - 1) * $limit;
            $search = $_GET['search'] ?? '';
            
            $whereClause = '';
            $params = [];
            
            if (!empty($search)) {
                $whereClause = "WHERE title ILIKE ? OR description ILIKE ?";
                $searchParam = "%{$search}%";
                $params = [$searchParam, $searchParam];
            }
            
            $params = array_merge($params, [$limit, $offset]);
            
            $services = $db->fetchAll("SELECT * FROM services {$whereClause} ORDER BY created_at DESC LIMIT ? OFFSET ?", $params);
            
            $countParams = array_slice($params, 0, -2);
            $total = $db->fetchOne("SELECT COUNT(*) as count FROM services {$whereClause}", $countParams)['count'] ?? 0;
            
            $response = [
                'success' => true,
                'data' => $services,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'pages' => ceil($total / $limit)
                ]
            ];
            break;
            
        case 'add_service':
            $data = [
                'title' => trim($_POST['title'] ?? ''),
                'description' => trim($_POST['description'] ?? ''),
                'icon' => trim($_POST['icon'] ?? ''),
                'price_range' => trim($_POST['price_range'] ?? ''),
                'features' => $_POST['features'] ?? [],
                'is_active' => 1,
                'sort_order' => intval($_POST['sort_order'] ?? 0)
            ];
            
            if (!empty($data['title']) && !empty($data['description'])) {
                $featuresJson = is_array($data['features']) ? json_encode($data['features']) : $data['features'];
                $db->execute(
                    "INSERT INTO services (title, description, icon, price_range, features, is_active, sort_order, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())",
                    [$data['title'], $data['description'], $data['icon'], $data['price_range'], $featuresJson, $data['is_active'], $data['sort_order']]
                );
                $response = ['success' => true, 'message' => 'Service added successfully'];
            } else {
                $response['message'] = 'Title and description are required';
            }
            break;
            
        case 'update_service':
            $serviceId = intval($_POST['service_id'] ?? 0);
            if ($serviceId > 0) {
                $data = [
                    'title' => trim($_POST['title'] ?? ''),
                    'description' => trim($_POST['description'] ?? ''),
                    'icon' => trim($_POST['icon'] ?? ''),
                    'price_range' => trim($_POST['price_range'] ?? ''),
                    'features' => $_POST['features'] ?? [],
                    'sort_order' => intval($_POST['sort_order'] ?? 0)
                ];
                
                if (!empty($data['title']) && !empty($data['description'])) {
                    $featuresJson = is_array($data['features']) ? json_encode($data['features']) : $data['features'];
                    $db->execute(
                        "UPDATE services SET title = ?, description = ?, icon = ?, price_range = ?, features = ?, sort_order = ?, updated_at = NOW() WHERE id = ?",
                        [$data['title'], $data['description'], $data['icon'], $data['price_range'], $featuresJson, $data['sort_order'], $serviceId]
                    );
                    $response = ['success' => true, 'message' => 'Service updated successfully'];
                } else {
                    $response['message'] = 'Title and description are required';
                }
            } else {
                $response['message'] = 'Invalid service ID';
            }
            break;
            
        case 'delete_service':
            $serviceId = intval($_POST['service_id'] ?? 0);
            if ($serviceId > 0) {
                $db->execute("DELETE FROM services WHERE id = ?", [$serviceId]);
                $response = ['success' => true, 'message' => 'Service deleted successfully'];
            } else {
                $response['message'] = 'Invalid service ID';
            }
            break;
            
        case 'toggle_service_status':
            $serviceId = intval($_POST['service_id'] ?? 0);
            if ($serviceId > 0) {
                $current = $db->fetchOne("SELECT is_active FROM services WHERE id = ?", [$serviceId]);
                if ($current) {
                    $newStatus = $current['is_active'] ? 0 : 1;
                    $db->execute("UPDATE services SET is_active = ?, updated_at = NOW() WHERE id = ?", [$newStatus, $serviceId]);
                    $response = ['success' => true, 'message' => 'Service status updated successfully'];
                } else {
                    $response['message'] = 'Service not found';
                }
            } else {
                $response['message'] = 'Invalid service ID';
            }
            break;
            
        // ==================== PROJECTS CRUD ====================
        case 'get_projects':
            $page = max(1, intval($_GET['page'] ?? 1));
            $limit = min(100, max(10, intval($_GET['limit'] ?? 20)));
            $offset = ($page - 1) * $limit;
            $search = $_GET['search'] ?? '';
            
            $whereClause = '';
            $params = [];
            
            if (!empty($search)) {
                $whereClause = "WHERE title ILIKE ? OR description ILIKE ?";
                $searchParam = "%{$search}%";
                $params = [$searchParam, $searchParam];
            }
            
            $params = array_merge($params, [$limit, $offset]);
            
            $projects = $db->fetchAll("SELECT * FROM projects {$whereClause} ORDER BY created_at DESC LIMIT ? OFFSET ?", $params);
            
            $countParams = array_slice($params, 0, -2);
            $total = $db->fetchOne("SELECT COUNT(*) as count FROM projects {$whereClause}", $countParams)['count'] ?? 0;
            
            $response = [
                'success' => true,
                'data' => $projects,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'pages' => ceil($total / $limit)
                ]
            ];
            break;
            
        case 'add_project':
            $data = [
                'title' => trim($_POST['title'] ?? ''),
                'description' => trim($_POST['description'] ?? ''),
                'technologies' => $_POST['technologies'] ?? [],
                'image_url' => trim($_POST['image_url'] ?? ''),
                'project_url' => trim($_POST['project_url'] ?? ''),
                'github_url' => trim($_POST['github_url'] ?? ''),
                'client_name' => trim($_POST['client_name'] ?? ''),
                'completion_date' => $_POST['completion_date'] ?? null,
                'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
                'is_active' => 1
            ];
            
            if (!empty($data['title']) && !empty($data['description'])) {
                $technologiesJson = is_array($data['technologies']) ? json_encode($data['technologies']) : $data['technologies'];
                $db->execute(
                    "INSERT INTO projects (title, description, technologies, image_url, project_url, github_url, client_name, completion_date, is_featured, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())",
                    [$data['title'], $data['description'], $technologiesJson, $data['image_url'], $data['project_url'], $data['github_url'], $data['client_name'], $data['completion_date'], $data['is_featured'], $data['is_active']]
                );
                $response = ['success' => true, 'message' => 'Project added successfully'];
            } else {
                $response['message'] = 'Title and description are required';
            }
            break;
            
        case 'update_project':
            $projectId = intval($_POST['project_id'] ?? 0);
            if ($projectId > 0) {
                $data = [
                    'title' => trim($_POST['title'] ?? ''),
                    'description' => trim($_POST['description'] ?? ''),
                    'technologies' => $_POST['technologies'] ?? [],
                    'image_url' => trim($_POST['image_url'] ?? ''),
                    'project_url' => trim($_POST['project_url'] ?? ''),
                    'github_url' => trim($_POST['github_url'] ?? ''),
                    'client_name' => trim($_POST['client_name'] ?? ''),
                    'completion_date' => $_POST['completion_date'] ?? null,
                    'is_featured' => isset($_POST['is_featured']) ? 1 : 0
                ];
                
                if (!empty($data['title']) && !empty($data['description'])) {
                    $technologiesJson = is_array($data['technologies']) ? json_encode($data['technologies']) : $data['technologies'];
                    $db->execute(
                        "UPDATE projects SET title = ?, description = ?, technologies = ?, image_url = ?, project_url = ?, github_url = ?, client_name = ?, completion_date = ?, is_featured = ?, updated_at = NOW() WHERE id = ?",
                        [$data['title'], $data['description'], $technologiesJson, $data['image_url'], $data['project_url'], $data['github_url'], $data['client_name'], $data['completion_date'], $data['is_featured'], $projectId]
                    );
                    $response = ['success' => true, 'message' => 'Project updated successfully'];
                } else {
                    $response['message'] = 'Title and description are required';
                }
            } else {
                $response['message'] = 'Invalid project ID';
            }
            break;
            
        case 'delete_project':
            $projectId = intval($_POST['project_id'] ?? 0);
            if ($projectId > 0) {
                $db->execute("DELETE FROM projects WHERE id = ?", [$projectId]);
                $response = ['success' => true, 'message' => 'Project deleted successfully'];
            } else {
                $response['message'] = 'Invalid project ID';
            }
            break;
            
        case 'toggle_project_status':
            $projectId = intval($_POST['project_id'] ?? 0);
            if ($projectId > 0) {
                $current = $db->fetchOne("SELECT is_active FROM projects WHERE id = ?", [$projectId]);
                if ($current) {
                    $newStatus = $current['is_active'] ? 0 : 1;
                    $db->execute("UPDATE projects SET is_active = ?, updated_at = NOW() WHERE id = ?", [$newStatus, $projectId]);
                    $response = ['success' => true, 'message' => 'Project status updated successfully'];
                } else {
                    $response['message'] = 'Project not found';
                }
            } else {
                $response['message'] = 'Invalid project ID';
            }
            break;
            
        // ==================== ADDITIONAL OPERATIONS ====================
        case 'toggle_newsletter_status':
            $subscriberId = intval($_POST['subscriber_id'] ?? 0);
            if ($subscriberId > 0) {
                $current = $db->fetchOne("SELECT is_active FROM newsletter_subscribers WHERE id = ?", [$subscriberId]);
                if ($current) {
                    $newStatus = $current['is_active'] ? 0 : 1;
                    $db->execute("UPDATE newsletter_subscribers SET is_active = ? WHERE id = ?", [$newStatus, $subscriberId]);
                    $response = ['success' => true, 'message' => 'Subscriber status updated successfully'];
                } else {
                    $response['message'] = 'Subscriber not found';
                }
            } else {
                $response['message'] = 'Invalid subscriber ID';
            }
            break;
            
        case 'delete_newsletter_subscriber':
            $subscriberId = intval($_POST['subscriber_id'] ?? 0);
            if ($subscriberId > 0) {
                $db->execute("DELETE FROM newsletter_subscribers WHERE id = ?", [$subscriberId]);
                $response = ['success' => true, 'message' => 'Subscriber deleted successfully'];
            } else {
                $response['message'] = 'Invalid subscriber ID';
            }
            break;
            
        case 'get_database_stats':
            $stats = [
                'services' => $db->fetchOne("SELECT COUNT(*) as count FROM services")['count'] ?? 0,
                'projects' => $db->fetchOne("SELECT COUNT(*) as count FROM projects")['count'] ?? 0,
                'testimonials' => $db->fetchOne("SELECT COUNT(*) as count FROM testimonials")['count'] ?? 0,
                'blog_posts' => $db->fetchOne("SELECT COUNT(*) as count FROM blog_posts")['count'] ?? 0,
                'newsletter_subscribers' => $db->fetchOne("SELECT COUNT(*) as count FROM newsletter_subscribers")['count'] ?? 0,
                'active_services' => $db->fetchOne("SELECT COUNT(*) as count FROM services WHERE is_active = true")['count'] ?? 0,
                'active_projects' => $db->fetchOne("SELECT COUNT(*) as count FROM projects WHERE is_active = true")['count'] ?? 0,
                'active_testimonials' => $db->fetchOne("SELECT COUNT(*) as count FROM testimonials WHERE is_active = true")['count'] ?? 0,
                'published_posts' => $db->fetchOne("SELECT COUNT(*) as count FROM blog_posts WHERE status = 'published'")['count'] ?? 0,
                'active_subscribers' => $db->fetchOne("SELECT COUNT(*) as count FROM newsletter_subscribers WHERE is_active = true")['count'] ?? 0
            ];
            
            $response = [
                'success' => true,
                'data' => $stats
            ];
            break;
            
        default:
            $response['message'] = 'Unknown action: ' . $action;
    }
} catch (Exception $e) {
    error_log("API Error: " . $e->getMessage());
    $response['message'] = 'Server error occurred: ' . $e->getMessage();
}

echo json_encode($response);
?>

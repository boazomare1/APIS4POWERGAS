<?php
/**
 * Dormancy Analysis API Endpoints
 * For identifying and managing dormant customers
 */

// Required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include dependencies
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../objects/dormancy.php';

// Initialize database connection
$database = new Database();
global $conn;
$conn = $database->getConnection();

// Get action parameter
$action = isset($_GET['action']) ? $_GET['action'] : null;

if (!$action) {
    http_response_code(400);
    echo json_encode([
        'success' => '0',
        'message' => 'Action parameter is required',
        'available_actions' => [
            'dormancy_summary',
            'dormancy_by_salesman',
            'dormant_customers',
            'customer_dormancy_detail',
            'export_dormancy'
        ]
    ]);
    exit;
}

// Common parameters
$period = isset($_GET['period']) ? $_GET['period'] : 'month';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : null;
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : null;

// Route to appropriate handler
switch ($action) {
    
    /**
     * GET ?action=dormancy_summary
     * Returns overall dormancy statistics
     * 
     * Parameters:
     * - period: week|month|3months|year|custom (default: month)
     * - start_date: YYYY-MM-DD (required if period=custom)
     * - end_date: YYYY-MM-DD (required if period=custom)
     */
    case 'dormancy_summary':
        $response = getDormancySummary($period, $start_date, $end_date);
        echo json_encode($response);
        break;
    
    /**
     * GET ?action=dormancy_by_salesman
     * Returns dormancy breakdown per salesman
     * 
     * Parameters:
     * - period: week|month|3months|year|custom
     * - salesman_id: (optional) filter by specific salesman
     * - page: pagination page (default: 1)
     * - limit: results per page (default: 20)
     * - start_date, end_date: for custom period
     */
    case 'dormancy_by_salesman':
        $salesman_id = isset($_GET['salesman_id']) ? intval($_GET['salesman_id']) : null;
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 20;
        $response = getDormancyBySalesman($period, $salesman_id, $start_date, $end_date, $page, $limit);
        echo json_encode($response);
        break;
    
    /**
     * GET ?action=dormant_customers
     * Returns list of dormant customers with filtering
     * 
     * Parameters:
     * - period: week|month|3months|year|custom
     * - tier: critical|warning|at_risk|lost (optional)
     * - fault_type: customer|salesman|external|unknown (optional)
     * - salesman_id: filter by assigned salesman (optional)
     * - page: pagination page (default: 1)
     * - limit: results per page (default: 50)
     * - start_date, end_date: for custom period
     */
    case 'dormant_customers':
        $tier = isset($_GET['tier']) ? $_GET['tier'] : null;
        $fault_type = isset($_GET['fault_type']) ? $_GET['fault_type'] : null;
        $salesman_id = isset($_GET['salesman_id']) ? intval($_GET['salesman_id']) : null;
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;
        
        $response = getDormantCustomers($period, $tier, $fault_type, $salesman_id, $page, $limit, $start_date, $end_date);
        echo json_encode($response);
        break;
    
    /**
     * GET ?action=customer_dormancy_detail
     * Returns detailed dormancy analysis for a single customer
     * 
     * Parameters:
     * - customer_id: (required) customer ID to analyze
     */
    case 'customer_dormancy_detail':
        $customer_id = isset($_GET['customer_id']) ? intval($_GET['customer_id']) : null;
        
        if (!$customer_id) {
            http_response_code(400);
            echo json_encode(['success' => '0', 'message' => 'customer_id is required']);
            exit;
        }
        
        $response = getCustomerDormancyDetail($customer_id);
        echo json_encode($response);
        break;
    
    /**
     * GET ?action=export_dormancy
     * Exports dormancy report as CSV or JSON
     * 
     * Parameters:
     * - period: week|month|3months|year|custom
     * - format: csv|json (default: json)
     * - start_date, end_date: for custom period
     */
    case 'export_dormancy':
        $format = isset($_GET['format']) ? strtolower($_GET['format']) : 'json';
        
        if ($format === 'csv') {
            $response = exportDormancyCSV($period, $start_date, $end_date);
            
            if ($response['success'] === '1') {
                // Set headers for CSV download
                header('Content-Type: text/csv');
                header('Content-Disposition: attachment; filename="' . $response['filename'] . '"');
                header('Pragma: no-cache');
                header('Expires: 0');
                
                echo $response['content'];
            } else {
                header('Content-Type: application/json');
                echo json_encode($response);
            }
        } else {
            // JSON export
            $response = getDormantCustomers($period, null, null, null, 1, 10000, $start_date, $end_date);
            
            if ($response['success'] === '1') {
                header('Content-Disposition: attachment; filename="dormancy_report_' . $period . '_' . date('Y-m-d') . '.json"');
            }
            
            echo json_encode($response, JSON_PRETTY_PRINT);
        }
        break;
    
    /**
     * GET ?action=dormancy_tiers
     * Returns the tier definitions (for reference)
     */
    case 'dormancy_tiers':
        echo json_encode([
            'success' => '1',
            'tiers' => [
                'critical' => [
                    'code' => 'critical',
                    'label' => 'Critical',
                    'min_days' => TIER_CRITICAL,
                    'max_days' => TIER_WARNING - 1,
                    'description' => 'Not served for 7-29 days',
                    'color' => '#dc3545'
                ],
                'warning' => [
                    'code' => 'warning',
                    'label' => 'Warning',
                    'min_days' => TIER_WARNING,
                    'max_days' => TIER_AT_RISK - 1,
                    'description' => 'Not served for 30-89 days',
                    'color' => '#fd7e14'
                ],
                'at_risk' => [
                    'code' => 'at_risk',
                    'label' => 'At Risk',
                    'min_days' => TIER_AT_RISK,
                    'max_days' => TIER_LOST - 1,
                    'description' => 'Not served for 90-364 days',
                    'color' => '#ffc107'
                ],
                'lost' => [
                    'code' => 'lost',
                    'label' => 'Lost',
                    'min_days' => TIER_LOST,
                    'max_days' => null,
                    'description' => 'Not served for 365+ days',
                    'color' => '#343a40'
                ]
            ],
            'fault_types' => [
                'customer' => 'Customer-related issues (closed, unavailable, refused)',
                'salesman' => 'Salesman skipped without explanation',
                'external' => 'External factors (stock, weather, vehicle)',
                'unknown' => 'Cannot determine cause'
            ]
        ]);
        break;
    
    default:
        http_response_code(400);
        echo json_encode([
            'success' => '0',
            'message' => 'Invalid action: ' . $action,
            'available_actions' => [
                'dormancy_summary',
                'dormancy_by_salesman', 
                'dormant_customers',
                'customer_dormancy_detail',
                'export_dormancy',
                'dormancy_tiers'
            ]
        ]);
        break;
}

?>



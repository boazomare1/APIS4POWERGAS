<?php
/**
 * Dormancy Analysis Module
 * Identifies and classifies dormant customers
 * Compatible with PHP 5.6+
 */

include_once(__DIR__ . "/../common/common.php");
date_default_timezone_set('Africa/Nairobi');

// Dormancy tier thresholds (in days)
define('TIER_CRITICAL', 7);
define('TIER_WARNING', 30);
define('TIER_AT_RISK', 90);
define('TIER_LOST', 365);

/**
 * Get date range based on period
 */
function getDateRange($period, $start_date = null, $end_date = null) {
    $end = date('Y-m-d');
    
    switch ($period) {
        case 'week':
            $start = date('Y-m-d', strtotime('-7 days'));
            break;
        case 'month':
            $start = date('Y-m-d', strtotime('-30 days'));
            break;
        case '3months':
            $start = date('Y-m-d', strtotime('-90 days'));
            break;
        case 'year':
            $start = date('Y-m-d', strtotime('-365 days'));
            break;
        case 'custom':
            $start = isset($start_date) ? $start_date : date('Y-m-d', strtotime('-30 days'));
            $end = isset($end_date) ? $end_date : date('Y-m-d');
            break;
        default:
            $start = date('Y-m-d', strtotime('-30 days'));
    }
    
    return array('start' => $start, 'end' => $end);
}

/**
 * Classify days inactive into tier
 */
function getDormancyTier($days_inactive) {
    if ($days_inactive >= TIER_LOST) return 'lost';
    if ($days_inactive >= TIER_AT_RISK) return 'at_risk';
    if ($days_inactive >= TIER_WARNING) return 'warning';
    if ($days_inactive >= TIER_CRITICAL) return 'critical';
    return 'active';
}

/**
 * Classify ticket reason into fault type
 */
function classifyFault($reason) {
    $reason_text = isset($reason) ? $reason : '';
    $reason_lower = strtolower($reason_text);
    
    // Customer fault keywords
    $customer_keywords = array('closed', 'not available', 'unavailable', 'refused', 'reject', 'no money', 'out of business', 'relocated', 'not interested');
    
    // External fault keywords
    $external_keywords = array('no stock', 'out of stock', 'vehicle', 'breakdown', 'weather', 'rain', 'flood', 'route blocked', 'traffic', 'fuel');
    
    foreach ($customer_keywords as $keyword) {
        if (strpos($reason_lower, $keyword) !== false) {
            return 'customer';
        }
    }
    
    foreach ($external_keywords as $keyword) {
        if (strpos($reason_lower, $keyword) !== false) {
            return 'external';
        }
    }
    
    return 'unknown';
}

/**
 * Get dormancy summary statistics (optimized for speed)
 */
function getDormancySummary($period = 'month', $start_date = null, $end_date = null) {
    global $conn;
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    try {
        $date_range = getDateRange($period, $start_date, $end_date);
        
        // Get total active customers
        $totalQuery = "SELECT COUNT(*) as total FROM sma_customers WHERE active = 1";
        $stmt = $conn->query($totalQuery);
        $total_result = $stmt->fetch(PDO::FETCH_ASSOC);
        $total_customers = isset($total_result['total']) ? (int)$total_result['total'] : 0;
        
        // Get customers served in period
        $servedQuery = "
            SELECT COUNT(DISTINCT customer_id) as served 
            FROM sma_sales 
            WHERE date BETWEEN :start_date AND :end_date
        ";
        $stmt = $conn->prepare($servedQuery);
        $stmt->execute(array('start_date' => $date_range['start'], 'end_date' => $date_range['end']));
        $served_result = $stmt->fetch(PDO::FETCH_ASSOC);
        $served = isset($served_result['served']) ? (int)$served_result['served'] : 0;
        
        $dormant = $total_customers - $served;
        $served_percentage = $total_customers > 0 ? round(($served / $total_customers) * 100, 1) : 0;
        
        // Get dormant customers by tier - OPTIMIZED: Do counting in SQL
        $tierQuery = "
            SELECT 
                CASE 
                    WHEN days_inactive >= " . TIER_LOST . " THEN 'lost'
                    WHEN days_inactive >= " . TIER_AT_RISK . " THEN 'at_risk'
                    WHEN days_inactive >= " . TIER_WARNING . " THEN 'warning'
                    WHEN days_inactive >= " . TIER_CRITICAL . " THEN 'critical'
                    ELSE 'active'
                END as tier,
                COUNT(*) as count
            FROM (
                SELECT 
                    c.id,
                    DATEDIFF(CURDATE(), COALESCE(MAX(s.date), '1970-01-01')) as days_inactive
                FROM sma_customers c
                LEFT JOIN sma_sales s ON c.id = s.customer_id
                WHERE c.active = 1
                GROUP BY c.id
                HAVING days_inactive >= " . TIER_CRITICAL . "
            ) as dormant_data
            GROUP BY tier
        ";
        $stmt = $conn->query($tierQuery);
        $tier_results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $by_tier = array('critical' => 0, 'warning' => 0, 'at_risk' => 0, 'lost' => 0);
        foreach ($tier_results as $row) {
            if (isset($by_tier[$row['tier']])) {
                $by_tier[$row['tier']] = (int)$row['count'];
            }
        }
        
        // Get fault breakdown from tickets (fast query)
        $faultQuery = "
            SELECT reason, COUNT(*) as count 
            FROM sma_tickets 
            WHERE date BETWEEN :start_date AND :end_date
            GROUP BY reason
        ";
        $stmt = $conn->prepare($faultQuery);
        $stmt->execute(array('start_date' => $date_range['start'], 'end_date' => $date_range['end']));
        $ticket_reasons = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $by_fault = array('customer' => 0, 'salesman' => 0, 'external' => 0, 'unknown' => 0);
        foreach ($ticket_reasons as $row) {
            $fault_type = classifyFault($row['reason']);
            $by_fault[$fault_type] += (int)$row['count'];
        }
        
        // Skip the slow salesman fault calculation for summary - just show ticket-based faults
        // The detailed breakdown is available in dormancy_by_salesman endpoint
        
        // Get top 5 dormant salesmen only (reduced from 10 for speed)
        $salesmenQuery = "
            SELECT 
                u.id,
                CONCAT(u.first_name, ' ', u.last_name) as name,
                COUNT(DISTINCT c.id) as dormant_count
            FROM sma_users u
            INNER JOIN sma_companies co ON u.company_id = co.id
            INNER JOIN sma_vehicles v ON co.vehicle_id = v.id
            INNER JOIN sma_vehicle_route vr ON v.id = vr.vehicle_id
            INNER JOIN sma_shop_allocations sa ON vr.route_id = sa.route_id
            INNER JOIN sma_shops sh ON sa.shop_id = sh.id
            INNER JOIN sma_customers c ON sh.customer_id = c.id
            WHERE c.active = 1
            AND NOT EXISTS (
                SELECT 1 FROM sma_sales s 
                WHERE s.customer_id = c.id 
                AND s.date BETWEEN :start_date AND :end_date
            )
            GROUP BY u.id, u.first_name, u.last_name
            ORDER BY dormant_count DESC
            LIMIT 5
        ";
        $stmt = $conn->prepare($salesmenQuery);
        $stmt->execute(array('start_date' => $date_range['start'], 'end_date' => $date_range['end']));
        $top_salesmen = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return array(
            'success' => '1',
            'period' => $period,
            'date_range' => $date_range,
            'summary' => array(
                'total_customers' => $total_customers,
                'served' => $served,
                'dormant' => $dormant,
                'served_percentage' => $served_percentage
            ),
            'by_tier' => $by_tier,
            'by_fault' => $by_fault,
            'top_dormant_salesmen' => $top_salesmen
        );
        
    } catch (Exception $e) {
        error_log("getDormancySummary error: " . $e->getMessage());
        return array('success' => '0', 'message' => 'Error fetching dormancy summary: ' . $e->getMessage());
    }
}

/**
 * Get dormancy breakdown by salesman (SUPER FAST version)
 */
function getDormancyBySalesman($period = 'month', $salesman_id = null, $start_date = null, $end_date = null, $page = 1, $limit = 20) {
    global $conn;
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    try {
        $date_range = getDateRange($period, $start_date, $end_date);
        $offset = ($page - 1) * $limit;
        $cutoff_date = $date_range['start'];
        
        // Get salesmen with their company_id (which is used in sma_sales.salesman_id)
        $query = "
            SELECT 
                u.id as user_id,
                co.id as salesman_id,
                CONCAT(u.first_name, ' ', u.last_name) as salesman_name,
                u.phone as salesman_phone,
                u.email as salesman_email,
                co.name as company_name,
                v.plate_no
            FROM sma_users u
            INNER JOIN sma_companies co ON u.company_id = co.id
            LEFT JOIN sma_vehicles v ON co.vehicle_id = v.id
            WHERE u.active = 1
        ";
        
        $params = array();
        if ($salesman_id) {
            $query .= " AND co.id = :salesman_id";
            $params['salesman_id'] = $salesman_id;
        }
        
        $query .= " ORDER BY u.first_name ASC LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        
        $stmt = $conn->prepare($query);
        $stmt->execute($params);
        $salesmen = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // For each salesman, get counts using their company_id (salesman_id in sales/tickets)
        foreach ($salesmen as &$salesman) {
            $company_id = $salesman['salesman_id']; // This is sma_companies.id
            
            // Count sales in period (salesman_id in sma_sales = sma_companies.id)
            $salesCountQuery = "SELECT COUNT(*) as cnt FROM sma_sales WHERE salesman_id = :sid AND date >= :cutoff";
            $stmt = $conn->prepare($salesCountQuery);
            $stmt->execute(array('sid' => $company_id, 'cutoff' => $cutoff_date));
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $salesman['sales_count'] = isset($result['cnt']) ? (int)$result['cnt'] : 0;
            
            // Count tickets in period
            $ticketCountQuery = "SELECT COUNT(*) as cnt FROM sma_tickets WHERE salesman_id = :sid AND date >= :cutoff";
            $stmt = $conn->prepare($ticketCountQuery);
            $stmt->execute(array('sid' => $company_id, 'cutoff' => $cutoff_date));
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $salesman['tickets_count'] = isset($result['cnt']) ? (int)$result['cnt'] : 0;
            
            // Count unique customers served
            $customersServedQuery = "SELECT COUNT(DISTINCT customer_id) as cnt FROM sma_sales WHERE salesman_id = :sid AND date >= :cutoff";
            $stmt = $conn->prepare($customersServedQuery);
            $stmt->execute(array('sid' => $company_id, 'cutoff' => $cutoff_date));
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $salesman['customers_served'] = isset($result['cnt']) ? (int)$result['cnt'] : 0;
            
            // Performance indicator
            $salesman['needs_attention'] = $salesman['sales_count'] < 10;
        }
        
        // Total count
        $countQuery = "
            SELECT COUNT(*) as total 
            FROM sma_users u
            INNER JOIN sma_companies co ON u.company_id = co.id
            WHERE u.active = 1
        ";
        if ($salesman_id) {
            $countQuery .= " AND co.id = " . (int)$salesman_id;
        }
        $stmt = $conn->query($countQuery);
        $count_result = $stmt->fetch(PDO::FETCH_ASSOC);
        $total = isset($count_result['total']) ? (int)$count_result['total'] : 0;
        
        return array(
            'success' => '1',
            'period' => $period,
            'date_range' => $date_range,
            'pagination' => array(
                'page' => (int)$page,
                'limit' => (int)$limit,
                'total' => $total,
                'pages' => $limit > 0 ? ceil($total / $limit) : 0
            ),
            'data' => $salesmen
        );
        
    } catch (Exception $e) {
        error_log("getDormancyBySalesman error: " . $e->getMessage());
        return array('success' => '0', 'message' => 'Error fetching salesman dormancy: ' . $e->getMessage());
    }
}

/**
 * Get list of dormant customers (SUPER FAST - NOT EXISTS approach)
 */
function getDormantCustomers($period = 'month', $tier = null, $fault_type = null, $salesman_id = null, $page = 1, $limit = 50, $start_date = null, $end_date = null) {
    global $conn;
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    try {
        $date_range = getDateRange($period, $start_date, $end_date);
        $offset = ($page - 1) * $limit;
        
        // Calculate date threshold based on tier
        $days_threshold = TIER_CRITICAL; // default 7 days
        if ($tier) {
            switch ($tier) {
                case 'critical': $days_threshold = TIER_CRITICAL; break;
                case 'warning': $days_threshold = TIER_WARNING; break;
                case 'at_risk': $days_threshold = TIER_AT_RISK; break;
                case 'lost': $days_threshold = TIER_LOST; break;
            }
        }
        
        $cutoff_date = date('Y-m-d', strtotime("-{$days_threshold} days"));
        
        // SUPER FAST: Get customers with NO sales after cutoff date
        $query = "
            SELECT 
                c.id as customer_id,
                c.name as customer_name,
                c.phone,
                c.email,
                c.created_at as customer_created_at
            FROM sma_customers c
            WHERE c.active = 1
            AND NOT EXISTS (
                SELECT 1 FROM sma_sales s 
                WHERE s.customer_id = c.id 
                AND s.date > :cutoff_date
            )
            ORDER BY c.id DESC
            LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        
        $stmt = $conn->prepare($query);
        $stmt->execute(array('cutoff_date' => $cutoff_date));
        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Add tier info
        foreach ($customers as &$customer) {
            $customer['tier'] = $tier ? $tier : 'dormant';
            $customer['days_threshold'] = $days_threshold;
        }
        
        // Fast count using same logic
        $countQuery = "
            SELECT COUNT(*) as total
            FROM sma_customers c
            WHERE c.active = 1
            AND NOT EXISTS (
                SELECT 1 FROM sma_sales s 
                WHERE s.customer_id = c.id 
                AND s.date > :cutoff_date
            )
        ";
        
        $stmt = $conn->prepare($countQuery);
        $stmt->execute(array('cutoff_date' => $cutoff_date));
        $count_result = $stmt->fetch(PDO::FETCH_ASSOC);
        $total = isset($count_result['total']) ? (int)$count_result['total'] : 0;
        
        return array(
            'success' => '1',
            'period' => $period,
            'date_range' => $date_range,
            'cutoff_date' => $cutoff_date,
            'days_threshold' => $days_threshold,
            'pagination' => array(
                'page' => (int)$page,
                'limit' => (int)$limit,
                'total' => $total,
                'pages' => $limit > 0 ? ceil($total / $limit) : 0
            ),
            'data' => $customers
        );
        
    } catch (Exception $e) {
        error_log("getDormantCustomers error: " . $e->getMessage());
        return array('success' => '0', 'message' => 'Error fetching dormant customers: ' . $e->getMessage());
    }
}

/**
 * Get detailed dormancy info for a single customer
 */
function getCustomerDormancyDetail($customer_id) {
    global $conn;
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    try {
        // Get customer basic info including creation date
        $customerQuery = "
            SELECT 
                c.id as customer_id,
                c.name as customer_name,
                c.phone,
                c.email,
                c.customer_group_name,
                c.active,
                c.created_at as customer_created_at,
                sh.id as shop_id,
                sh.shop_name,
                sh.lat,
                sh.lng,
                sh.image as shop_image
            FROM sma_customers c
            LEFT JOIN sma_shops sh ON c.id = sh.customer_id
            WHERE c.id = :customer_id
        ";
        $stmt = $conn->prepare($customerQuery);
        $stmt->execute(array('customer_id' => $customer_id));
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$customer) {
            return array('success' => '0', 'message' => 'Customer not found');
        }
        
        // Get allocation info (assigned salesman, route, days)
        $allocationQuery = "
            SELECT 
                sa.route_id,
                r.name as route_name,
                v.id as vehicle_id,
                v.plate_no,
                ad.day as allocated_day,
                u.id as salesman_id,
                CONCAT(u.first_name, ' ', u.last_name) as salesman_name,
                u.phone as salesman_phone
            FROM sma_shops sh
            INNER JOIN sma_shop_allocations sa ON sh.id = sa.shop_id
            INNER JOIN sma_allocation_days ad ON sa.id = ad.allocation_id
            INNER JOIN sma_vehicle_route vr ON sa.route_id = vr.route_id AND ad.day = vr.day
            INNER JOIN sma_vehicles v ON vr.vehicle_id = v.id
            INNER JOIN sma_routes r ON sa.route_id = r.id
            LEFT JOIN sma_companies co ON v.id = co.vehicle_id
            LEFT JOIN sma_users u ON co.id = u.company_id
            WHERE sh.customer_id = :customer_id
            AND ad.active = 1
        ";
        $stmt = $conn->prepare($allocationQuery);
        $stmt->execute(array('customer_id' => $customer_id));
        $allocations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get sales history (last 90 days)
        $salesQuery = "
            SELECT 
                id, date, grand_total, payment_status,
                salesman_id
            FROM sma_sales
            WHERE customer_id = :customer_id
            AND date >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)
            ORDER BY date DESC
            LIMIT 20
        ";
        $stmt = $conn->prepare($salesQuery);
        $stmt->execute(array('customer_id' => $customer_id));
        $sales_history = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get last sale date
        $last_sale_date = !empty($sales_history) ? $sales_history[0]['date'] : null;
        $days_inactive = $last_sale_date 
            ? (int)((strtotime('now') - strtotime($last_sale_date)) / 86400)
            : 9999;
        
        // Get ticket history
        $ticketQuery = "
            SELECT 
                t.id,
                t.date,
                t.reason,
                t.status,
                t.salesman_id,
                CONCAT(u.first_name, ' ', u.last_name) as raised_by
            FROM sma_tickets t
            LEFT JOIN sma_users u ON t.salesman_id = u.id
            WHERE t.customer_id = :customer_id
            AND t.date >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)
            ORDER BY t.date DESC
        ";
        $stmt = $conn->prepare($ticketQuery);
        $stmt->execute(array('customer_id' => $customer_id));
        $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Classify tickets by fault
        $fault_summary = array('customer' => 0, 'salesman' => 0, 'external' => 0, 'unknown' => 0);
        foreach ($tickets as &$ticket) {
            $ticket['fault_type'] = classifyFault($ticket['reason']);
            $fault_summary[$ticket['fault_type']]++;
        }
        
        // Calculate expected visits in last 30 days based on allocation
        $expected_visits = count($allocations) * 4; // Assuming 4 weeks
        $actual_visits = count($sales_history);
        $skip_count = max(0, $expected_visits - $actual_visits - count($tickets));
        
        // Add unexplained skips to salesman fault
        $fault_summary['salesman'] += $skip_count;
        
        // Determine primary fault
        $primary_fault = array_search(max($fault_summary), $fault_summary);
        
        // Generate recommendation
        $recommendation = generateRecommendation($days_inactive, $primary_fault, $fault_summary);
        
        // Safe access to allocations array
        $route_name = isset($allocations[0]['route_name']) ? $allocations[0]['route_name'] : null;
        $plate_no = isset($allocations[0]['plate_no']) ? $allocations[0]['plate_no'] : null;
        $sm_id = isset($allocations[0]['salesman_id']) ? $allocations[0]['salesman_id'] : null;
        $sm_name = isset($allocations[0]['salesman_name']) ? $allocations[0]['salesman_name'] : null;
        $sm_phone = isset($allocations[0]['salesman_phone']) ? $allocations[0]['salesman_phone'] : null;
        
        return array(
            'success' => '1',
            'customer' => $customer,
            'allocation' => array(
                'scheduled_days' => array_column($allocations, 'allocated_day'),
                'route' => $route_name,
                'vehicle' => $plate_no,
                'salesman' => array(
                    'id' => $sm_id,
                    'name' => $sm_name,
                    'phone' => $sm_phone
                )
            ),
            'service_history' => array(
                'last_sale_date' => $last_sale_date,
                'days_inactive' => $days_inactive,
                'tier' => getDormancyTier($days_inactive),
                'expected_visits_30d' => $expected_visits,
                'actual_visits_30d' => $actual_visits,
                'tickets_30d' => count($tickets),
                'unexplained_skips' => $skip_count
            ),
            'sales_history' => $sales_history,
            'ticket_history' => $tickets,
            'fault_analysis' => array(
                'breakdown' => $fault_summary,
                'primary_fault' => $primary_fault,
                'recommendation' => $recommendation
            )
        );
        
    } catch (Exception $e) {
        error_log("getCustomerDormancyDetail error: " . $e->getMessage());
        return array('success' => '0', 'message' => 'Error fetching customer detail: ' . $e->getMessage());
    }
}

/**
 * Generate recommendation based on dormancy analysis
 */
function generateRecommendation($days_inactive, $primary_fault, $fault_summary) {
    $recommendations = array();
    
    // Based on tier
    if ($days_inactive >= TIER_LOST) {
        $recommendations[] = "Customer inactive for over a year - consider removing from active routes";
    } elseif ($days_inactive >= TIER_AT_RISK) {
        $recommendations[] = "Customer at high risk of churning - urgent manager intervention needed";
    } elseif ($days_inactive >= TIER_WARNING) {
        $recommendations[] = "Schedule immediate follow-up visit";
    }
    
    // Based on primary fault
    switch ($primary_fault) {
        case 'customer':
            $recommendations[] = "Contact customer directly to understand their situation";
            if ($fault_summary['customer'] > 5) {
                $recommendations[] = "Consider adjusting visit schedule to match customer availability";
            }
            break;
        case 'salesman':
            $recommendations[] = "Review salesman performance - multiple unexplained skips detected";
            $recommendations[] = "Consider reassigning to different salesman";
            break;
        case 'external':
            $recommendations[] = "Review stock levels and route logistics";
            break;
        case 'unknown':
            $recommendations[] = "Investigate - no clear pattern detected";
            break;
    }
    
    return $recommendations;
}

/**
 * Export dormancy report to CSV
 */
function exportDormancyCSV($period = 'month', $start_date = null, $end_date = null) {
    $data = getDormantCustomers($period, null, null, null, 1, 10000, $start_date, $end_date);
    
    if ($data['success'] !== '1') {
        return $data;
    }
    
    $csv_lines = array();
    
    // Header
    $csv_lines[] = implode(',', array(
        'Customer ID',
        'Customer Name', 
        'Phone',
        'Email',
        'Shop Name',
        'Last Sale Date',
        'Days Inactive',
        'Tier',
        'Sales Count',
        'Ticket Count',
        'Salesman ID',
        'Salesman Name',
        'Vehicle',
        'Route'
    ));
    
    // Data rows
    foreach ($data['data'] as $customer) {
        $customer_name = isset($customer['customer_name']) ? $customer['customer_name'] : '';
        $phone = isset($customer['phone']) ? $customer['phone'] : '';
        $email = isset($customer['email']) ? $customer['email'] : '';
        $shop_name = isset($customer['shop_name']) ? $customer['shop_name'] : '';
        $last_sale = isset($customer['last_sale_date']) ? $customer['last_sale_date'] : 'Never';
        $salesman_id = isset($customer['salesman_id']) ? $customer['salesman_id'] : '';
        $salesman_name = isset($customer['salesman_name']) ? $customer['salesman_name'] : '';
        $plate_no = isset($customer['plate_no']) ? $customer['plate_no'] : '';
        $route_name = isset($customer['route_name']) ? $customer['route_name'] : '';
        
        $csv_lines[] = implode(',', array(
            $customer['customer_id'],
            '"' . str_replace('"', '""', $customer_name) . '"',
            $phone,
            $email,
            '"' . str_replace('"', '""', $shop_name) . '"',
            $last_sale,
            $customer['days_inactive'],
            $customer['tier'],
            $customer['sales_count'],
            $customer['ticket_count'],
            $salesman_id,
            '"' . str_replace('"', '""', $salesman_name) . '"',
            $plate_no,
            '"' . str_replace('"', '""', $route_name) . '"'
        ));
    }
    
    return array(
        'success' => '1',
        'filename' => 'dormancy_report_' . $period . '_' . date('Y-m-d') . '.csv',
        'content' => implode("\n", $csv_lines),
        'row_count' => count($data['data'])
    );
}

?>

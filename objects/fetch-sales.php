<?php 
include ("../common/common.php");
date_default_timezone_set('Africa/Nairobi');
function fetchSalesReport($date, $day, $salesman_id) {
    global $conn;

    try {
        // Set PDO attributes
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

        // Fetch status of the salesman
        $statusQuery = "SELECT status FROM sma_companies WHERE id = :salesman_id";
        $stmtStatus = $conn->prepare($statusQuery);
        $stmtStatus->bindParam(':salesman_id', $salesman_id, PDO::PARAM_INT);
        $stmtStatus->execute();

        $status = null; // Initialize status variable
        if ($stmtStatus->rowCount() > 0) {
            $rowStatus = $stmtStatus->fetch(PDO::FETCH_ASSOC);
            $status = $rowStatus['status'];
        }

        // Check if status is fetched and continue only if status is found
        if ($status === null) {
            return array("success" => "0", "message" => "Failed to fetch status");
        }

        // Fetch total customers
        $customerCountQuery = "SELECT COUNT(*) AS total_customers FROM sma_customers WHERE active = 1";
        $stmtCustomerCount = $conn->prepare($customerCountQuery);
        $stmtCustomerCount->execute();
        $totalCustomers = $stmtCustomerCount->fetchColumn();

        // Fetch actual sales
        $actualSalesQuery = "SELECT SUM(amount) AS total_sales FROM sma_sales WHERE DATE(date) = :date AND salesman_id = :salesman_id";
        $stmtActualSales = $conn->prepare($actualSalesQuery);
        $stmtActualSales->bindParam(':date', $date);
        $stmtActualSales->bindParam(':salesman_id', $salesman_id, PDO::PARAM_INT);
        $stmtActualSales->execute();
        $actualSales = $stmtActualSales->fetchColumn() ?: 0;

        // Fetch ticket sales
        $ticketSalesQuery = "SELECT SUM(amount) AS total_ticket_sales FROM sma_ticket_sales WHERE DATE(date) = :date AND salesman_id = :salesman_id";
        $stmtTicketSales = $conn->prepare($ticketSalesQuery);
        $stmtTicketSales->bindParam(':date', $date);
        $stmtTicketSales->bindParam(':salesman_id', $salesman_id, PDO::PARAM_INT);
        $stmtTicketSales->execute();
        $ticketSales = $stmtTicketSales->fetchColumn() ?: 0;

        // Fetch customers not served
        $notServedQuery = "SELECT COUNT(*) AS not_served FROM sma_customers c
                           WHERE c.active = 1 AND NOT EXISTS (
                               SELECT 1 FROM sma_sales s WHERE s.customer_id = c.id AND DATE(s.date) = :date
                           )";
        $stmtNotServed = $conn->prepare($notServedQuery);
        $stmtNotServed->bindParam(':date', $date);
        $stmtNotServed->execute();
        $notServedCount = $stmtNotServed->fetchColumn();

        // Prepare response
        return array(
            "success" => "1",
            "message" => "Sales report fetched successfully",
            "data" => array(
                "total_customers" => $totalCustomers,
                "actual_sales" => $actualSales,
                "ticket_sales" => $ticketSales,
                "customers_not_served" => $notServedCount
            )
        );
        
    } catch (PDOException $e) {
        return array("success" => "0", "message" => "Failed: " . $e->getMessage());
    }
}
?>

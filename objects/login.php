<?php
class Login
{

    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function loginUser($email, $password)
    {

        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $response = "";

        try {
            $query = "SELECT active, password FROM sma_users WHERE email = ?";

            // prepare query statement
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $email);

            // execute query
            $stmt->execute();
            if ($stmt->rowCount() > 0) {

                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if (password_verify($password, $row['password'])) {
                    if ($row['active'] == "1") {

                        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                        $query2 = "SELECT s.id, s.username, s.email, s.first_name, s.last_name, s.phone, s.avatar, 
                                          IFNULL(c.id, '') AS salesman_id, 
                                          IFNULL(s.stock, 0) AS stock, 
                                          IFNULL(c.vehicle_id, '') AS vehicle_id, 
                                          IFNULL(v.plate_no, '') AS plate_no, 
                                          IFNULL(v.discount_enabled, '') AS discount_enabled, 
                                          IFNULL(r.id, '') AS route_id, 
                                          IFNULL(c.group_id, '') AS group_id, 
                                          IFNULL(c.distributor_id, '') AS distributor_id, 
                                          IFNULL(c.group_name, '') AS name
                                   FROM sma_users s 
                                   LEFT JOIN sma_companies c ON s.company_id = c.id 
                                   LEFT JOIN sma_vehicles v ON c.vehicle_id = v.id 
                                   LEFT JOIN sma_routes r ON v.plate_no = r.name 
                                   WHERE s.email = ?";

                        $stmt2 = $this->conn->prepare($query2);
                        $stmt2->bindParam(1, $email);
                        $stmt2->execute();

                        $row2 = $stmt2->fetch(PDO::FETCH_ASSOC);

                        if ($row2) {
                            $response = array("success" => "1", "message" => "login successful", "user" => $row2);
                        } else {
                            $response = array("success" => "1", "message" => "login successful", "user" => array(
                                "id" => $row['id'],
                                "username" => $row['username'],
                                "email" => $email,
                                "first_name" => $row['first_name'],
                                "last_name" => $row['last_name'],
                                "phone" => $row['phone'],
                                "avatar" => $row['avatar'],
                                "salesman_id" => '',
                                "stock" => 0,
                                "vehicle_id" => '',
                                "plate_no" => '',
                                "discount_enabled" => '',
                                "route_id" => '',
                                "group_id" => '',
                                "distributor_id" => '',
                                "name" => ''
                            ));
                        }

                    } else {
                        $response = array("success" => "4", "message" => "The user is not yet activated, Please contact system admin");
                    }

                } else {
                    $response = array("success" => "3", "message" => "The password you entered does not match");
                }

            } else {
                $response = array("success" => "0", "message" => "The username you entered does not exist");
            }
        } catch (Exception $e) {
            error_log("Exception: " . $e->getMessage()); 
            http_response_code(500);
            $response = array("success" => "2", "message" => "error occurred");
        }
        return $response;
    }

}

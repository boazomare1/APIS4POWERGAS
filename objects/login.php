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

                        $query2 = "SELECT s.id, s.username, s.email, s.first_name, s.last_name, s.phone, s.avatar, c.id as salesman_id, s.stock, c.vehicle_id, v.plate_no, v.discount_enabled, r.id as route_id, c.group_id ,c.distributor_id, c.group_name as name
                        FROM sma_users s INNER JOIN sma_companies c ON s.company_id = c.id INNER JOIN sma_vehicles v ON c.vehicle_id = v.id INNER JOIN sma_routes r ON v.plate_no = r.name WHERE s.email = ?";

                        $stmt2 = $this->conn->prepare($query2);
                        $stmt2->bindParam(1, $email);
                        $stmt2->execute();

                        $row2 = $stmt2->fetch(PDO::FETCH_ASSOC);

                        $response = array("success" => "1", "message" => "login successful", "user" => $row2);

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
            print_r($e);
            http_response_code(404);
            $response = array("success" => "2", "message" => "error occured");
        }
        return $response;
    }

}
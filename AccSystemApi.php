<?php
class AccSystemApi
{
    private $apiUrl;

    public function __construct($apiUrl)
    {
        $this->apiUrl = rtrim($apiUrl, "/");
    }

    private function sendRequest($method, $endpoint, $data = null)
    {
        $url = $this->apiUrl . $endpoint;

        $ch = curl_init($url);

        $headers = ["Content-Type: application/json"];

        if ($method == "GET" && !empty($data)) {
            $url .= "?" . http_build_query($data);
            curl_setopt($ch, CURLOPT_URL, $url);
        } elseif (!empty($data)) {
            $jsonData = json_encode($data);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        }

        // Set HTTP method
        switch ($method) {
            case "POST":
                curl_setopt($ch, CURLOPT_POST, true);
                break;
            case "GET":
                break;
            case "PUT":
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
                break;
            case "DELETE":
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
                break;
            default:
                throw new Exception("Invalid HTTP method: " . $method);
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new Exception("Request Error: " . curl_error($ch));
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $result = json_decode($response, true);

        if ($httpCode >= 200 && $httpCode < 300) {
            return $result;
        } else {
            $message = isset($result["message"])
                ? $result["message"]
                : "An error occurred";
            throw new Exception("HTTP Error " . $httpCode . ": " . $message);
        }
    }

    public function createAccount($name, $email, $password)
    {
        $data = [
            "name" => $name,
            "email" => $email,
            "password" => $password,
        ];

        return $this->sendRequest("POST", "/create_account", $data);
    }

    public function authenticate($email, $password)
    {
        $data = [
            "email" => $email,
            "password" => $password,
        ];

        return $this->sendRequest("POST", "/authenticate", $data);
    }

    public function createSession($email)
    {
        $data = [
            "email" => $email,
        ];

        return $this->sendRequest("POST", "/create_session", $data);
    }

    public function validateSession($email, $sessionToken)
    {
        $data = [
            "email" => $email,
            "session_token" => $sessionToken,
        ];

        return $this->sendRequest("POST", "/validate_session", $data);
    }

    public function createTransactionalList($email, $name, $description)
    {
        $data = [
            "email" => $email,
            "name" => $name,
            "description" => $description,
        ];

        return $this->sendRequest("POST", "/create_transactional_list", $data);
    }

    public function addUserToTransactionalList($email, $tlUUID, $memberEmail)
    {
        $data = [
            "email" => $email,
            "tl_uuid" => $tlUUID,
            "member_email" => $memberEmail,
        ];

        return $this->sendRequest(
            "POST",
            "/add_user_to_transactional_list",
            $data
        );
    }

    public function acceptInvitation($memberEmail, $tlUUID)
    {
        $data = [
            "member_email" => $memberEmail,
            "tl_uuid" => $tlUUID,
        ];

        return $this->sendRequest("POST", "/accept_invitation", $data);
    }

    public function addEvent(
        $email,
        $tlUUID,
        $name,
        $description,
        $time,
        $scheduled,
        $amount,
        $byUserEmail,
        $forUserEmail
    ) {
        $data = [
            "email" => $email,
            "tl_uuid" => $tlUUID,
            "name" => $name,
            "description" => $description,
            "time" => $time,
            "scheduled" => $scheduled,
            "amount" => $amount,
            "by_user_email" => $byUserEmail,
            "for_user_email" => $forUserEmail,
        ];

        return $this->sendRequest("POST", "/add_event", $data);
    }

    public function markEventAsPaid($email, $tlUUID, $eventUUID)
    {
        $data = [
            "email" => $email,
            "tl_uuid" => $tlUUID,
            "event_uuid" => $eventUUID,
        ];

        return $this->sendRequest("POST", "/mark_event_as_paid", $data);
    }

    public function getNotifications($email)
    {
        $params = [
            "email" => $email,
        ];

        return $this->sendRequest("GET", "/get_notifications", $params);
    }
}
?>

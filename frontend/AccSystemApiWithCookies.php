<?php
class AccSystemApiWithCookies
{
    private $apiUrl;
    private $cookieName = "acc_system_session";

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

        return $this->sendRequest("POST", "/createAccount", $data);
    }

    public function signIn($email, $password)
    {
        $data = [
            "email" => $email,
            "password" => $password,
        ];

        $authResponse = $this->sendRequest("POST", "/authenticate", $data);

        if ($authResponse["success"]) {
            $sessionResponse = $this->createSession($email);
            $this->setSessionCookie($email, $sessionResponse["sessionToken"]);
            return $authResponse;
        } else {
            throw new Exception("Authentication failed.");
        }
    }

    private function createSession($email)
    {
        $data = [
            "email" => $email,
        ];

        return $this->sendRequest("POST", "/createSession", $data);
    }

    public function validateSession()
    {
        if (!isset($_COOKIE[$this->cookieName])) {
            return false;
        }

        $sessionData = json_decode($_COOKIE[$this->cookieName], true);
        //        print_r($sessionData);

        $email = $sessionData["email"];
        $sessionToken = $sessionData["sessionToken"];

        $data = [
            "email" => $email,
            "sessionToken" => $sessionToken,
        ];

        $validateResponse = $this->sendRequest(
            "POST",
            "/validateSession",
            $data
        );
        return $validateResponse["isValid"];
    }

    public function signOut()
    {
        if (isset($_COOKIE[$this->cookieName])) {
            unset($_COOKIE[$this->cookieName]);
            setcookie($this->cookieName, "", time() - 3600, "/"); // Expire the cookie
        }
    }

    private function setSessionCookie($email, $sessionToken)
    {
        // email should be replaced bt uuid
        $sessionData = json_encode([
            "email" => $email,
            "sessionToken" => $sessionToken,
        ]);
        setcookie($this->cookieName, $sessionData, time() + 2 * 3600, "/"); // 2 hours expiry
    }

    public function isSignedIn()
    {
        return $this->validateSession();
    }

    public function createTransactionalList($email, $name, $description)
    {
        $data = [
            "email" => $email,
            "name" => $name,
            "description" => $description,
        ];

        return $this->sendRequest("POST", "/createTransactionalList", $data);
    }

    public function addUserToTransactionalList($email, $tlUUID, $memberEmail)
    {
        $data = [
            "email" => $email,
            "tl_uuid" => $tlUUID,
            "member_email" => $memberEmail,
        ];

        return $this->sendRequest("POST", "/addUserToTransactionalList", $data);
    }

    public function acceptInvitation($memberEmail, $tlUUID)
    {
        $data = [
            "member_email" => $memberEmail,
            "tl_uuid" => $tlUUID,
        ];

        return $this->sendRequest("POST", "/acceptInvitation", $data);
    }

    public function addEvent(
        $email,
        $tlUUID,
        $name,
        $description,
        $scheduled,
        $amount,
        $byUserEmail,
        $forUserEmail
    ) {
        $data = [
            "email" => $email,
            "tlUUID" => $tlUUID,
            "name" => $name,
            "description" => $description,
            "scheduled" => $scheduled,
            "amount" => $amount,
            "byUserEmail" => $byUserEmail,
            "forUserEmail" => $forUserEmail,
        ];

        return $this->sendRequest("POST", "/addEvent", $data);
    }

    public function markEventAsPaid($email, $tlUUID, $eventUUID)
    {
        $data = [
            "email" => $email,
            "tl_uuid" => $tlUUID,
            "event_uuid" => $eventUUID,
        ];

        return $this->sendRequest("POST", "/markEventAsPaid", $data);
    }

    public function getNotifications($email)
    {
        $params = [
            "email" => $email,
        ];

        return $this->sendRequest("GET", "/getNotifications", $params);
    }

    public function getUserData()
    {
        if (!isset($_COOKIE[$this->cookieName])) {
            throw new Exception("User is not signed in.");
        }

        $sessionData = json_decode($_COOKIE[$this->cookieName], true);
        if (
            !isset($sessionData["email"]) ||
            !isset($sessionData["sessionToken"])
        ) {
            throw new Exception("Email and sessionToken are required.");
        }

        $email = $sessionData["email"];
        $sessionToken = $sessionData["sessionToken"];

        $data = [
            "email" => $email,
            "sessionToken" => $sessionToken,
        ];

        return $this->sendRequest("POST", "/getUserData", $data);
    }

    public function getTransactionalList($email, $tlUUID, $sessionToken)
    {
        $data = [
            "email" => $email,
            "tlUUID" => $tlUUID,
            "sessionToken" => $sessionToken,
        ];

        return $this->sendRequest("POST", "/getTransactionalListData", $data);
    }

    public function getEvent($email, $tlUUID, $eventUUID, $sessionToken)
    {
        $data = [
            "email" => $email,
            "tlUUID" => $tlUUID,
            "eventUUID" => $eventUUID,
            "sessionToken" => $sessionToken,
        ];

        return $this->sendRequest("POST", "/getEventData", $data);
    }

    public function getUserNameByUUID($uuid)
    {
        $data = [
            "uuid" => $uuid,
        ];
        return $this->sendRequest("POST", "/getUserNameByUUID", $data);
    }
}
?>

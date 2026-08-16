<?php

require_once "../app/Core/Database.php";

class UserRepository
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function login($email, $password)
    {
        $emailEsc = $this->db->real_escape_string($email);
        $sql = "SELECT * FROM users WHERE email='$emailEsc' LIMIT 1";
        $result = $this->db->query($sql);

        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }

        return false;
    }

    public function getAllUsers()
    {
        $sql = "SELECT * FROM users ORDER BY id DESC";
        return $this->db->query($sql);
    }

    public function addUser($name, $email, $password, $role)
    {
        $localRole = null;
        $globalRole = null;

        if ($role == "admin") {
            $localRole = "local_admin";
        } else {
            $globalRole = "global_user";
        }

        $nameEsc = $this->db->real_escape_string($name);
        $emailEsc = $this->db->real_escape_string($email);
        $passwordEsc = $this->db->real_escape_string($password);
        $roleEsc = $this->db->real_escape_string($role);

        $sql = "INSERT INTO users (name,email,password,role,local_role,global_role) VALUES ('{$nameEsc}','{$emailEsc}','{$passwordEsc}','{$roleEsc}'," .
            ($localRole === null ? "NULL" : "'" . $this->db->real_escape_string($localRole) . "'") . "," .
            ($globalRole === null ? "NULL" : "'" . $this->db->real_escape_string($globalRole) . "'") . ")";

        return $this->db->query($sql);
    }

    public function deleteUser($id)
{
    $idEsc = $this->db->real_escape_string($id);

    /*
    ============================================================
    CHECK BORROW HISTORY
    ============================================================
    */

    $borrowSql = "
        SELECT COUNT(*) AS total
        FROM borrow_history
        WHERE user_id = '$idEsc'
    ";

    $borrowResult = $this->db->query($borrowSql);

    $borrowCount = 0;

    if ($borrowResult) {
        $borrowRow = $borrowResult->fetch_assoc();
        $borrowCount = (int) ($borrowRow['total'] ?? 0);
    }


    /*
    ============================================================
    CHECK HOLDS
    ============================================================
    */

    $holdSql = "
        SELECT COUNT(*) AS total
        FROM holds
        WHERE user_id = '$idEsc'
    ";

    $holdResult = $this->db->query($holdSql);

    $holdCount = 0;

    if ($holdResult) {
        $holdRow = $holdResult->fetch_assoc();
        $holdCount = (int) ($holdRow['total'] ?? 0);
    }


    /*
    ============================================================
    DO NOT DELETE IF BORROW / HOLD EXISTS
    ============================================================
    */

    if ($borrowCount > 0 && $holdCount > 0) {

        return [
            'success' => false,
            'reason'  => 'borrow_and_hold'
        ];
    }


    if ($borrowCount > 0) {

        return [
            'success' => false,
            'reason'  => 'borrow'
        ];
    }


    if ($holdCount > 0) {

        return [
            'success' => false,
            'reason'  => 'hold'
        ];
    }


    /*
    ============================================================
    SAFE DELETE
    ============================================================
    */

    try {

        $sql = "
            DELETE FROM users
            WHERE id='$idEsc'
        ";

        $success = $this->db->query($sql);

        if ($success) {

            return [
                'success' => true,
                'reason'  => 'deleted'
            ];
        }

        return [
            'success' => false,
            'reason'  => 'database_error'
        ];

    } catch (mysqli_sql_exception $e) {

        /*
        Prevent fatal error from reaching the browser.
        */

        return [
            'success' => false,
            'reason'  => 'database_error'
        ];
    }
}

    public function updateUser($id, $username, $name, $email, $role, $profileImage = null)
    {
        $idEsc = $this->db->real_escape_string($id);
        $usernameEsc = $this->db->real_escape_string($username);
        $nameEsc = $this->db->real_escape_string($name);
        $emailEsc = $this->db->real_escape_string($email);
        $roleEsc = $this->db->real_escape_string($role);

        // Role mapping
        $localRole = null;
        $globalRole = null;
        if ($role == "admin") {
            $localRole = "local_admin";
        } else {
            $globalRole = "global_user";
        }

        // Prevent duplicate username/email for other users
        if (!empty($username) && $this->existsUsername($username, $id)) {
            return false;
        }
        if (!empty($email) && $this->existsEmail($email, $id)) {
            return false;
        }

        $localRoleEsc = $localRole === null ? "NULL" : "'" . $this->db->real_escape_string($localRole) . "'";
        $globalRoleEsc = $globalRole === null ? "NULL" : "'" . $this->db->real_escape_string($globalRole) . "'";
        $profileImageClause = '';

        if ($profileImage !== null) {
            $profileImageEsc = $this->db->real_escape_string($profileImage);
            $profileImageClause = ", profile_image='{$profileImageEsc}'";
        }

        $sql = "UPDATE users SET username='{$usernameEsc}', name='{$nameEsc}', email='{$emailEsc}', role='{$roleEsc}', local_role={$localRoleEsc}, global_role={$globalRoleEsc}{$profileImageClause} WHERE id='{$idEsc}'";

        return $this->db->query($sql);
    }

    public function updatePassword($id, $hashedPassword)
    {
        $idEsc = $this->db->real_escape_string($id);
        $hashEsc = $this->db->real_escape_string($hashedPassword);
        $sql = "UPDATE users SET password='{$hashEsc}' WHERE id='{$idEsc}'";
        return $this->db->query($sql);
    }

    public function getUserById($id)
    {
        $idEsc = $this->db->real_escape_string($id);
        $sql = "SELECT * FROM users WHERE id='{$idEsc}' LIMIT 1";
        $result = $this->db->query($sql);
        return $result ? $result->fetch_assoc() : null;
    }

    public function createUser(
    $username,
    $password,
    $name,
    $email,
    $role
) {

    $dbRole = "user";
    $localRole = null;
    $globalRole = null;


    /*
        Local Admin
    */
    if ($role === "local_admin") {

        $dbRole =
            "admin";

        $localRole =
            "local_admin";
    }


    /*
        Normal Admin
        ----------------
        If AdminController sends
        "admin", it is also
        a local admin.
    */
    elseif ($role === "admin") {

        $dbRole =
            "admin";

        $localRole =
            "local_admin";
    }


    /*
        Global User
    */
    elseif ($role === "global_user") {

        $dbRole =
            "user";

        $globalRole =
            "global_user";
    }


    /*
        Normal User
    */
    elseif ($role === "user") {

        $dbRole =
            "user";
    }


    $usernameEsc =
        $this->db->real_escape_string(
            $username
        );

    $passEsc =
        $this->db->real_escape_string(
            $password
        );

    $nameEsc =
        $this->db->real_escape_string(
            $name
        );

    $emailEsc =
        $this->db->real_escape_string(
            $email
        );

    $dbRoleEsc =
        $this->db->real_escape_string(
            $dbRole
        );


    $sql =
        "INSERT INTO users
        (
            username,
            password,
            name,
            email,
            role,
            local_role,
            global_role
        )
        VALUES
        (
            '{$usernameEsc}',
            '{$passEsc}',
            '{$nameEsc}',
            '{$emailEsc}',
            '{$dbRoleEsc}',"
            .
            (
                $localRole === null
                ? "NULL"
                : "'" .
                  $this->db->real_escape_string(
                      $localRole
                  )
                  . "'"
            )
            .
            ","
            .
            (
                $globalRole === null
                ? "NULL"
                : "'" .
                  $this->db->real_escape_string(
                      $globalRole
                  )
                  . "'"
            )
            .
            ")";


    return $this->db->query(
        $sql
    );
}

    public function existsUsername($username, $excludeId = null)
    {
        $usernameEsc = $this->db->real_escape_string($username);
        $sql = "SELECT id FROM users WHERE username='{$usernameEsc}'";
        if ($excludeId) {
            $sql .= " AND id!='" . $this->db->real_escape_string($excludeId) . "'";
        }
        $res = $this->db->query($sql);
        return ($res && $res->num_rows > 0);
    }

    public function existsEmail($email, $excludeId = null)
    {
        $emailEsc = $this->db->real_escape_string($email);
        $sql = "SELECT id FROM users WHERE email='{$emailEsc}'";
        if ($excludeId) {
            $sql .= " AND id!='" . $this->db->real_escape_string($excludeId) . "'";
        }
        $res = $this->db->query($sql);
        return ($res && $res->num_rows > 0);
    }
}

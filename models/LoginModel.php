<?php
class LoginModel
{
    private $conn;
    private $email;
    private $password;

    public function __construct($db, $email, $password)
    {
        $this->conn = $db;
        $this->email = $email;
        $this->password = $password;
    }

    // Query ครอบจักรวาล: JOIN ตาม role
    private function getUserWithProfile()
    {
        $sql = "
            SELECT 
                u.user_id,
                u.email,
                u.password,
                u.role,
                u.active,
                u.created_at,

                s.first_name AS s_first_name,
                s.last_name AS s_last_name,

                t.first_name AS t_first_name,
                t.last_name AS t_last_name,

                a.first_name AS a_first_name,
                a.last_name  AS a_last_name

            FROM user u
            LEFT JOIN student s ON u.user_id = s.user_id
            LEFT JOIN teacher t ON u.user_id = t.user_id
            LEFT JOIN admin   a ON u.user_id = a.user_id
            WHERE u.email = :email
            LIMIT 1
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":email", $this->email);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function verifyPassword()
    {

        $user = $this->getUserWithProfile();

        if (!$user) return false;

        if (!password_verify($this->password, $user['password'])) return false;

        switch ($user['role']) {
            case 'student':
                $user['first_name'] = $user['s_first_name'];
                $user['last_name']  = $user['s_last_name'];
                break;

            case 'teacher':
                $user['first_name'] = $user['t_first_name'];
                $user['last_name']  = $user['t_last_name'];
                break;

            case 'admin':
                $user['first_name'] = $user['a_first_name'];
                $user['last_name']  = $user['a_last_name'];
                break;
        }

        return $user;
    }
}

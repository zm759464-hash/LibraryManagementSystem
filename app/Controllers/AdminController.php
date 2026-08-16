<?php

require_once "../app/Middleware/AuthMiddleware.php";
require_once "../app/Middleware/AdminMiddleware.php";

require_once "../app/Core/Database.php";

require_once "../app/Repositories/BorrowRepository.php";

require_once "../app/Services/BorrowService.php";
require_once "../app/Services/AdminProfileService.php";


class AdminController
{

    /*
    ========================================================
    ADMIN DASHBOARD
    ========================================================
    */

   public function dashboard()
{
    AuthMiddleware::check();

    AdminMiddleware::check();

    if (!isset($_SESSION["user"])) {

        header(
            "Location:?url=AuthController/login"
        );

        exit;
    }


    try {

        $borrowService =
            new BorrowService();

        $borrowService
            ->checkAndSendReminders();

    } catch (Throwable $e) {

        error_log(
            "Borrow Reminder Error: "
            . $e->getMessage()
        );
    }


    $user =
        $_SESSION["user"];


    $adminId =
        htmlspecialchars(
            $user["id"] ?? "-",
            ENT_QUOTES,
            "UTF-8"
        );


    $username =
        htmlspecialchars(
            $user["username"] ?? "-",
            ENT_QUOTES,
            "UTF-8"
        );


    $name =
        htmlspecialchars(
            $user["name"] ?? "Administrator",
            ENT_QUOTES,
            "UTF-8"
        );


    $email =
        htmlspecialchars(
            $user["email"] ?? "-",
            ENT_QUOTES,
            "UTF-8"
        );


    $localRole =
        htmlspecialchars(
            $user["local_role"] ?? "local_admin",
            ENT_QUOTES,
            "UTF-8"
        );


    $profilePhotoHtml = "

        <span class='admin-default-avatar'>
            👤
        </span>

    ";


    if (
        !empty(
            $user["profile_photo"]
        )
    ) {

        $profilePhoto =
            htmlspecialchars(
                $user["profile_photo"],
                ENT_QUOTES,
                "UTF-8"
            );


        $profilePhotoHtml = "

            <img
                src='{$profilePhoto}'
                alt='Profile'
                class='admin-profile-image'
            >

        ";
    }


    $borrowRepository =
        new BorrowRepository();


    $borrowDays =
        $borrowRepository
            ->getBorrowDays();


    $notifications =
        $this->getNotifications();


    $notificationCount =
        count($notifications);


    $notificationBadge =
        htmlspecialchars(
            (string)$notificationCount,
            ENT_QUOTES,
            "UTF-8"
        );


    $notificationItems = "";


    if (
        $notificationCount > 0
    ) {

        foreach (
            $notifications
            as $notification
        ) {

            $type =
                htmlspecialchars(
                    $notification["type"]
                        ?? "Notification",
                    ENT_QUOTES,
                    "UTF-8"
                );


            $title =
                htmlspecialchars(
                    $notification["title"]
                        ?? "New activity",
                    ENT_QUOTES,
                    "UTF-8"
                );


            $message =
                htmlspecialchars(
                    $notification["message"]
                        ?? "",
                    ENT_QUOTES,
                    "UTF-8"
                );


            $time =
                htmlspecialchars(
                    $notification["time"]
                        ?? "",
                    ENT_QUOTES,
                    "UTF-8"
                );


            $icon =
                $notification["icon"]
                    ?? "🔔";


            $notificationId =
                (int)(
                    $notification["id"]
                        ?? 0
                );


            $notificationItems .= "

                <div
                    class='notification-item'
                    id='notification-item-{$notificationId}'
                >

                    <div
                        class='notification-icon notification-recent'
                    >
                        {$icon}
                    </div>

                    <div class='notification-content'>

                        <strong>
                            {$title}
                        </strong>

                        <p>
                            {$message}
                        </p>

                        <small>
                            {$type}
                        </small>

                        <time>
                            {$time}
                        </time>

                    </div>

                    <button
                        type='button'
                        class='notification-delete-btn'
                        onclick='deleteNotification({$notificationId})'
                    >
                        🗑
                    </button>

                </div>

            ";

        }

    } else {

        $notificationItems = "

            <div class='notification-empty'>

                <div class='notification-empty-icon'>
                    🔕
                </div>

                <strong>
                    You're all caught up
                </strong>

                <span>
                    No new notifications
                </span>

            </div>

        ";
    }


    $badgeHtml = "";


    if (
        $notificationCount > 0
    ) {

        $badgeHtml = "

            <span
                class='notification-count'
                id='notificationBadge'
            >
                {$notificationBadge}
            </span>

        ";
    }


    /*
    ========================================================
    LOAD VIEW
    ========================================================
    */

    require
        __DIR__
        . "/../Views/admin/dashboard.php";
}


    /*
    ========================================================
    PROFILE UPDATE
    ========================================================
    */

    public function updateProfile()
{
    AuthMiddleware::check();
    AdminMiddleware::check();


    if (
        $_SERVER["REQUEST_METHOD"]
        !== "POST"
    ) {

        header(
            "Location:?url=AdminController/dashboard"
        );

        exit;
    }


    if (
        !isset($_SESSION["user"])
    ) {

        header(
            "Location:?url=AuthController/login"
        );

        exit;
    }


    try {

        $adminId =
            (int)(
                $_SESSION["user"]["id"]
                ?? 0
            );


        if ($adminId <= 0) {

            throw new Exception(
                "Invalid administrator account."
            );
        }


        /*
        ========================================================
        PROFILE SERVICE
        ========================================================
        */

        $service =
            new AdminProfileService();


        /*
        ========================================================
        UPDATE
        ========================================================
        */

        $result =
            $service->updateProfile(
                $adminId,
                $_POST,
                $_FILES
            );


        /*
        ========================================================
        UPDATE SESSION
        ========================================================
        */

        if (
            isset(
                $result["user"]
            )
        ) {

            $_SESSION["user"] =
                array_merge(
                    $_SESSION["user"],
                    $result["user"]
                );
        }


        /*
        ========================================================
        SUCCESS
        ========================================================
        */

        $_SESSION["admin_success"] =
            $result["message"]
            ?? "Profile updated successfully.";

    } catch (
        Throwable $e
    ) {

        error_log(
            "Admin Profile Update Error: "
            . $e->getMessage()
        );


        $_SESSION["admin_error"] =
            $e->getMessage();
    }


    /*
    ========================================================
    RETURN DASHBOARD
    ========================================================
    */

    header(
        "Location:?url=AdminController/dashboard"
    );

    exit;
}



    /*
    ========================================================
    MARK NOTIFICATIONS AS READ
    ========================================================
    */

    public function markNotificationsRead()
    {

        AuthMiddleware::check();

        AdminMiddleware::check();


        header(
            "Content-Type: application/json"
        );


        try {

            $database =
                new Database();


            $db =
                $database
                    ->getConnection();


            $sql = "

                UPDATE notifications

                SET is_read = 1

                WHERE is_read = 0

            ";


            $stmt =
                $db->prepare(
                    $sql
                );


            if (
                !$stmt
            ) {

                echo json_encode([
                    "success" =>
                        false,

                    "message" =>
                        "Unable to prepare query"
                ]);

                exit;
            }


            $stmt->execute();


            $stmt->close();


            echo json_encode([
                "success" =>
                    true
            ]);

        } catch (
            Throwable $e
        ) {

            error_log(
                "Mark Notifications Read Error: "
                . $e->getMessage()
            );


            echo json_encode([
                "success" =>
                    false,

                "message" =>
                    "Database error"
            ]);
        }


        exit;
    }

     


   /*
========================================================
GET CURRENT ADMIN PROFILE (AJAX)
========================================================
*/

public function getProfile()
{
    AuthMiddleware::check();
    AdminMiddleware::check();

    header("Content-Type: application/json");

    if (!isset($_SESSION["user"])) {

        echo json_encode([
            "success" => false
        ]);

        exit;
    }

    echo json_encode([
        "success" => true,
        "user" => [

            "id" =>
                $_SESSION["user"]["id"]
                ?? "",

            "username" =>
                $_SESSION["user"]["username"]
                ?? "",

            "name" =>
                $_SESSION["user"]["name"]
                ?? "",

            "email" =>
                $_SESSION["user"]["email"]
                ?? "",

            "profile_photo" =>
                $_SESSION["user"]["profile_photo"]
                ?? ""
        ]
    ]);

    exit;
}


/*
========================================================
GET NOTIFICATIONS
========================================================
*/

private function getNotifications()
{
    $notifications = [];


    try {

        $database =
            new Database();


        $db =
            $database->getConnection();


        /*
        ----------------------------------------------------
        Only unread notifications
        ----------------------------------------------------
        */

        $sql = "

            SELECT
                id,
                type,
                title,
                message,
                link,
                is_read,
                created_at

            FROM notifications

            WHERE is_read = 0

            ORDER BY
                created_at DESC,
                id DESC

            LIMIT 50

        ";


        $stmt =
            $db->prepare($sql);


        if (!$stmt) {

            return [];
        }

        

        $stmt->execute();


        $result =
            $stmt->get_result();


        while (
            $row =
            $result->fetch_assoc()
        ) {

            $type =
                $row["type"]
                ?? "Notification";


            /*
            ------------------------------------------------
            Notification icon
            ------------------------------------------------
            */

            $icon = "🔔";


            if (
                $type === "Borrow"
            ) {

                $icon = "🔄";

            } elseif (
                $type === "Return"
            ) {

                $icon = "↩️";

            } elseif (
                $type === "Hold"
            ) {

                $icon = "🔖";

            } elseif (
                $type === "User"
            ) {

                $icon = "👤";
            }


            $notifications[] = [

                "id" =>
                    (int)$row["id"],

                "type" =>
                    $type,

                "title" =>
                    $row["title"]
                    ?? "Notification",

                "message" =>
                    $row["message"]
                    ?? "",

                "time" =>
                    $row["created_at"]
                    ?? "",

                "icon" =>
                    $icon

            ];
        }


        $stmt->close();


    } catch (
        Throwable $e
    ) {

        error_log(
            "Admin Notification Error: "
            . $e->getMessage()
        );


        return [];
    }


    return $notifications;
}


    /*
    ========================================================
    DELETE SINGLE NOTIFICATION
    ========================================================
    */
     
    public function deleteNotification(
        $id
    ) {

        AuthMiddleware::check();

        AdminMiddleware::check();


        header(
            "Content-Type: application/json"
        );


        $notificationId =
            (int)$id;


        if (
            $notificationId <= 0
        ) {

            echo json_encode([
                "success" =>
                    false,

                "message" =>
                    "Invalid notification ID"
            ]);

            exit;
        }


        try {

            $database =
                new Database();


            $db =
                $database
                    ->getConnection();


            $sql = "

                DELETE FROM notifications

                WHERE id = ?

            ";


            $stmt =
                $db->prepare(
                    $sql
                );


            if (
                !$stmt
            ) {

                echo json_encode([
                    "success" =>
                        false,

                    "message" =>
                        "Unable to prepare query"
                ]);

                exit;
            }


            $stmt->bind_param(
                "i",
                $notificationId
            );


            $stmt->execute();


            $deleted =
                $stmt->affected_rows > 0;


            $stmt->close();


            echo json_encode([
                "success" =>
                    $deleted
            ]);

        } catch (
            Throwable $e
        ) {

            error_log(
                "Delete Notification Error: "
                . $e->getMessage()
            );


            echo json_encode([
                "success" =>
                    false,

                "message" =>
                    "Database error"
            ]);
        }


        exit;
    }

}
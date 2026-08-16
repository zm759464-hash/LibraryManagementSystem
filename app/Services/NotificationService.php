<?php

require_once
    __DIR__ . "/../Core/Database.php";


class NotificationService
{
    /*
        ========================================================
        CREATE NOTIFICATION
        ========================================================

        Global admin notification

        notifications table
            type
            title
            message
            link
            is_read
            created_at
    */

    public function create(
        string $type,
        string $title,
        string $message,
        string $link = ""
    ): bool {

        try {

            /*
                ------------------------------------------------
                Database Connection
                ------------------------------------------------
            */

            $database =
                new Database();

            $db =
                $database->getConnection();


            /*
                ------------------------------------------------
                Insert Notification
                ------------------------------------------------
            */

            $sql = "

                INSERT INTO notifications
                (
                    type,
                    title,
                    message,
                    link,
                    is_read,
                    created_at
                )

                VALUES
                (
                    ?,
                    ?,
                    ?,
                    ?,
                    0,
                    NOW()
                )

            ";


            $stmt =
                $db->prepare($sql);


            /*
                ------------------------------------------------
                Prepare Failed
                ------------------------------------------------
            */

            if (!$stmt) {

                error_log(
                    "Notification prepare error: "
                    . $db->error
                );

                return false;
            }


            /*
                ------------------------------------------------
                Bind Parameters
                ------------------------------------------------
            */

            $stmt->bind_param(
                "ssss",
                $type,
                $title,
                $message,
                $link
            );


            /*
                ------------------------------------------------
                Execute
                ------------------------------------------------
            */

            $success =
                $stmt->execute();


            /*
                ------------------------------------------------
                Log Error
                ------------------------------------------------
            */

            if (!$success) {

                error_log(
                    "Notification insert error: "
                    . $stmt->error
                );
            }


            /*
                ------------------------------------------------
                Close Statement
                ------------------------------------------------
            */

            $stmt->close();


            return $success;


        } catch (Throwable $e) {

            error_log(
                "NotificationService Error: "
                . $e->getMessage()
            );

            return false;
        }
    }
}
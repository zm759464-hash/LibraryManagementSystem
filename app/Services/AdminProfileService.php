<?php

require_once __DIR__ . "/../Core/Database.php";


class AdminProfileService
{

    /*
    ============================================================
    UPDATE ADMIN PROFILE
    ============================================================
    */

    public function updateProfile(
        int $adminId,
        array $data,
        array $files
    ): array {

        if ($adminId <= 0) {

            throw new Exception(
                "Invalid administrator account."
            );
        }


        /*
        ========================================================
        INPUT
        ========================================================
        */

        $name =
            trim(
                $data["name"]
                ?? ""
            );


        $username =
            trim(
                $data["username"]
                ?? ""
            );


        $email =
            trim(
                $data["email"]
                ?? ""
            );


        $password =
            $data["password"]
            ?? "";


        $borrowDays =
            isset($data["borrow_days"])
                ? (int)$data["borrow_days"]
                : 0;


        /*
        ========================================================
        VALIDATION
        ========================================================
        */

        if ($name === "") {

            throw new Exception(
                "Full name is required."
            );
        }


        if (strlen($name) < 2) {

            throw new Exception(
                "Full name must contain at least 2 characters."
            );
        }


        if ($username === "") {

            throw new Exception(
                "Username is required."
            );
        }


        if (strlen($username) < 3) {

            throw new Exception(
                "Username must contain at least 3 characters."
            );
        }


        if (
            !filter_var(
                $email,
                FILTER_VALIDATE_EMAIL
            )
        ) {

            throw new Exception(
                "Please enter a valid email address."
            );
        }


        if (
            $password !== ""
            &&
            strlen($password) < 6
        ) {

            throw new Exception(
                "Password must contain at least 6 characters."
            );
        }


        if (
            $borrowDays < 1
            ||
            $borrowDays > 365
        ) {

            throw new Exception(
                "Borrow duration must be between 1 and 365 days."
            );
        }


        /*
        ========================================================
        DATABASE
        ========================================================
        */

        $database =
            new Database();


        $db =
            $database->getConnection();


        /*
        ========================================================
        GET CURRENT PROFILE PHOTO
        ========================================================
        */

        $currentPhoto = "";


        $getUserSql = "
            SELECT
                profile_photo
            FROM users
            WHERE id = ?
            LIMIT 1
        ";


        $stmt =
            $db->prepare(
                $getUserSql
            );


        if (!$stmt) {

            throw new Exception(
                "Unable to load administrator profile."
            );
        }


        $stmt->bind_param(
            "i",
            $adminId
        );


        $stmt->execute();


        $result =
            $stmt->get_result();


        if (
            $result
            &&
            $row = $result->fetch_assoc()
        ) {

            $currentPhoto =
                $row["profile_photo"]
                ?? "";
        }


        $stmt->close();


        /*
        ========================================================
        CHECK USERNAME
        ========================================================
        */

        $checkUsernameSql = "
            SELECT id
            FROM users
            WHERE username = ?
            AND id <> ?
            LIMIT 1
        ";


        $stmt =
            $db->prepare(
                $checkUsernameSql
            );


        if (!$stmt) {

            throw new Exception(
                "Unable to check username."
            );
        }


        $stmt->bind_param(
            "si",
            $username,
            $adminId
        );


        $stmt->execute();


        $result =
            $stmt->get_result();


        if (
            $result
            &&
            $result->num_rows > 0
        ) {

            $stmt->close();


            throw new Exception(
                "Username is already being used."
            );
        }


        $stmt->close();


        /*
        ========================================================
        CHECK EMAIL
        ========================================================
        */

        $checkEmailSql = "
            SELECT id
            FROM users
            WHERE email = ?
            AND id <> ?
            LIMIT 1
        ";


        $stmt =
            $db->prepare(
                $checkEmailSql
            );


        if (!$stmt) {

            throw new Exception(
                "Unable to check email."
            );
        }


        $stmt->bind_param(
            "si",
            $email,
            $adminId
        );


        $stmt->execute();


        $result =
            $stmt->get_result();


        if (
            $result
            &&
            $result->num_rows > 0
        ) {

            $stmt->close();


            throw new Exception(
                "Email is already being used."
            );
        }


        $stmt->close();


        /*
        ========================================================
        PROFILE PHOTO UPLOAD
        ========================================================
        */

        $profilePhoto =
            $currentPhoto;


        if (
            isset(
                $files["profile_photo"]
            )
            &&
            $files["profile_photo"]["error"]
                !== UPLOAD_ERR_NO_FILE
        ) {

            if (
                $files["profile_photo"]["error"]
                !== UPLOAD_ERR_OK
            ) {

                throw new Exception(
                    "Profile photo upload failed."
                );
            }


            /*
            ----------------------------------------------------
            Maximum 2MB
            ----------------------------------------------------
            */

            if (
                $files["profile_photo"]["size"]
                > 2 * 1024 * 1024
            ) {

                throw new Exception(
                    "Profile photo must be smaller than 2MB."
                );
            }


            /*
            ----------------------------------------------------
            MIME TYPE
            ----------------------------------------------------
            */

            $finfo =
                new finfo(
                    FILEINFO_MIME_TYPE
                );


            $mime =
                $finfo->file(
                    $files["profile_photo"]["tmp_name"]
                );


            $allowedTypes = [

                "image/jpeg" =>
                    "jpg",

                "image/png" =>
                    "png",

                "image/webp" =>
                    "webp"

            ];


            if (
                !isset(
                    $allowedTypes[$mime]
                )
            ) {

                throw new Exception(
                    "Only JPG, PNG and WEBP images are allowed."
                );
            }


            /*
            ----------------------------------------------------
            Upload directory
            ----------------------------------------------------
            */

            $uploadDir =
                dirname(
                    __DIR__,
                    2
                )
                . DIRECTORY_SEPARATOR
                . "public"
                . DIRECTORY_SEPARATOR
                . "uploads"
                . DIRECTORY_SEPARATOR
                . "profiles";


            if (
                !is_dir($uploadDir)
            ) {

                if (
                    !mkdir(
                        $uploadDir,
                        0777,
                        true
                    )
                ) {

                    throw new Exception(
                        "Unable to create profile upload directory."
                    );
                }
            }


            /*
            ----------------------------------------------------
            Filename
            ----------------------------------------------------
            */

            $fileName =
                "admin_"
                . $adminId
                . "_"
                . time()
                . "_"
                . bin2hex(
                    random_bytes(4)
                )
                . "."
                . $allowedTypes[$mime];


            $destination =
                $uploadDir
                . DIRECTORY_SEPARATOR
                . $fileName;


            if (
                !move_uploaded_file(
                    $files["profile_photo"]["tmp_name"],
                    $destination
                )
            ) {

                throw new Exception(
                    "Unable to save profile photo."
                );
            }


            /*
            ----------------------------------------------------
            Browser URL
            ----------------------------------------------------
            */

            $profilePhoto =
                "/uploads/profiles/"
                . $fileName;
        }


        /*
        ========================================================
        UPDATE USERS TABLE
        ========================================================
        */

        if ($password !== "") {

            /*
            ----------------------------------------------------
            Password changed
            ----------------------------------------------------
            */

            $passwordHash =
                password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );


            $sql = "

                UPDATE users

                SET
                    name = ?,
                    username = ?,
                    email = ?,
                    password = ?,
                    profile_photo = ?

                WHERE id = ?

            ";


            $stmt =
                $db->prepare(
                    $sql
                );


            if (!$stmt) {

                throw new Exception(
                    "Unable to prepare profile update."
                );
            }


            $stmt->bind_param(
                "sssssi",
                $name,
                $username,
                $email,
                $passwordHash,
                $profilePhoto,
                $adminId
            );

        } else {

            /*
            ----------------------------------------------------
            Password unchanged
            ----------------------------------------------------
            */

            $sql = "

                UPDATE users

                SET
                    name = ?,
                    username = ?,
                    email = ?,
                    profile_photo = ?

                WHERE id = ?

            ";


            $stmt =
                $db->prepare(
                    $sql
                );


            if (!$stmt) {

                throw new Exception(
                    "Unable to prepare profile update."
                );
            }


            $stmt->bind_param(
                "ssssi",
                $name,
                $username,
                $email,
                $profilePhoto,
                $adminId
            );
        }


        /*
        ========================================================
        EXECUTE USER UPDATE
        ========================================================
        */

        if (
            !$stmt->execute()
        ) {

            $error =
                $stmt->error;


            $stmt->close();


            throw new Exception(
                "Unable to update profile: "
                . $error
            );
        }


        $stmt->close();


        /*
        ========================================================
        UPDATE BORROW SETTINGS
        ========================================================
        */

        $borrowSql = "

            INSERT INTO borrow_settings
            (
                id,
                borrow_days
            )

            VALUES
            (
                1,
                ?
            )

            ON DUPLICATE KEY UPDATE
                borrow_days = VALUES(borrow_days)

        ";


        $stmt =
            $db->prepare(
                $borrowSql
            );


        if (!$stmt) {

            throw new Exception(
                "Unable to prepare borrow settings."
            );
        }


        $stmt->bind_param(
            "i",
            $borrowDays
        );


        if (
            !$stmt->execute()
        ) {

            $error =
                $stmt->error;


            $stmt->close();


            throw new Exception(
                "Unable to update borrow settings: "
                . $error
            );
        }


        $stmt->close();


        /*
        ========================================================
        RETURN UPDATED USER DATA
        ========================================================
        */

        return [

            "success" =>
                true,

            "message" =>
                "Profile updated successfully.",

            "user" => [

                "id" =>
                    $adminId,

                "name" =>
                    $name,

                "username" =>
                    $username,

                "email" =>
                    $email,

                "profile_photo" =>
                    $profilePhoto

            ]

        ];
    }
}
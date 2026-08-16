<?php

require_once "../app/Services/DistributedQueryService.php";
require_once "../app/Middleware/AuthMiddleware.php";
require_once "../app/Middleware/UserMiddleware.php";
require_once "../app/Services/UserService.php";
require_once "../app/Middleware/AdminMiddleware.php";
require_once "../app/Services/NotificationService.php";

class UserController
{

    /*
    ============================================================
    USER DASHBOARD
    ============================================================
    */

    public function dashboard()
    {
        AuthMiddleware::check();
        UserMiddleware::check();

        $user = $_SESSION["user"];

        $userId = htmlspecialchars(
            $user["id"] ?? $user["user_id"] ?? "-"
        );

        $username = htmlspecialchars(
            $user["username"] ?? "-"
        );

        $name = htmlspecialchars(
            $user["name"] ?? "User"
        );

        $email = htmlspecialchars(
            $user["email"] ?? "-"
        );

        $globalRole = htmlspecialchars(
            $user["global_role"] ?? "global_user"
        );

        $initial = strtoupper(
            substr(
                $user["name"] ?? "U",
                0,
                1
            )
        );

        require_once __DIR__ . "/../Views/user/dashboard.php";
    }


    /*
    ============================================================
    USER SETTINGS
    ============================================================
    */

    public function settings()
    {
        AuthMiddleware::check();
        UserMiddleware::check();

        $user = $_SESSION['user'];

        require_once __DIR__ . "/../Views/user/settings.php";
    }


    public function updateSettings()
    {
        AuthMiddleware::check();
        UserMiddleware::check();

        $user = $_SESSION['user'];
        $id = $user['id'] ?? $user['user_id'] ?? null;

        if (!$id) {
            header("Location:?url=UserController/dashboard");
            exit;
        }

        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $nameFromPost = trim($_POST['name'] ?? '');
        $mode = trim($_POST['mode'] ?? 'light');

        $userService = new UserService();

        // Update username / email / name
        $existing = $userService->find($id);
        $name = $nameFromPost !== '' ? $nameFromPost : ($existing['name'] ?? ($existing['username'] ?? ''));
        $role = $existing['role'] ?? 'user';

        if ($username !== '' || $email !== '' || $nameFromPost !== '') {
            $ok = $userService->update($id, $username ?: $existing['username'], $name, $email ?: $existing['email'], $role);
            if ($ok === false) {
                $_SESSION['flash_error'] = 'Username or email is already in use by another account.';
                header("Location:?url=UserController/settings");
                exit;
            }
        }

        // Password change (optional)
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (!empty($newPassword)) {
            if ($newPassword === $confirmPassword) {
                // verify current password if provided
                $dbUser = $userService->find($id);
                if (!empty($currentPassword) && !empty($dbUser['password']) && password_verify($currentPassword, $dbUser['password'])) {
                    $userService->changePassword($id, $newPassword);
                } elseif (empty($dbUser['password'])) {
                    // fallback: if no password stored, allow set
                    $userService->changePassword($id, $newPassword);
                }
            }
        }

        // Theme mode stored in session
        $_SESSION['theme'] = in_array($mode, ['dark', 'light']) ? $mode : 'light';

        $profileImagePath = null;

        // Profile image upload
        if (!empty($_FILES['profile_image']['name'])) {
            $upload = $_FILES['profile_image'];
            if ($upload['error'] === UPLOAD_ERR_OK) {
                $tmp = $upload['tmp_name'];
                $ext = pathinfo($upload['name'], PATHINFO_EXTENSION);
                $ext = strtolower($ext);
                $allowed = ['png', 'jpg', 'jpeg', 'gif'];
                if (in_array($ext, $allowed)) {
                    $publicRoot = realpath(__DIR__ . '/../../public');
                    $dir = $publicRoot . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'profiles';
                    if (!is_dir($dir)) {
                        mkdir($dir, 0755, true);
                    }

                    $target = $dir . DIRECTORY_SEPARATOR . $id . '.' . $ext;
                    if (move_uploaded_file($tmp, $target)) {
                        $profileImagePath = 'assets/images/profiles/' . $id . '.' . $ext;
                        $_SESSION['user']['profile_image'] = $profileImagePath;
                    }
                }
            }
        }

        $currentUsername = $username !== '' ? $username : $existing['username'];
        $currentEmail = $email !== '' ? $email : $existing['email'];
        $currentName = $nameFromPost !== '' ? $nameFromPost : ($existing['name'] ?? ($existing['username'] ?? ''));

        if ($username !== '' || $email !== '' || $nameFromPost !== '' || $profileImagePath !== null) {
            $ok = $userService->update(
                $id,
                $currentUsername,
                $currentName,
                $currentEmail,
                $role,
                $profileImagePath
            );

            if ($ok === false) {
                $_SESSION['flash_error'] = 'Username or email is already in use by another account.';
                header("Location:?url=UserController/settings");
                exit;
            }
        }

        // Refresh session with latest user data
        $updated = $userService->find($id);
        if ($updated) {
            // Ensure the in-session profile_image (uploaded) is preserved onto the refreshed user array
            if (!empty($_SESSION['user']['profile_image'])) {
                $updated['profile_image'] = $_SESSION['user']['profile_image'];
            }

            $_SESSION['user'] = $updated;
        }

        $_SESSION['flash_success'] = 'Settings updated successfully.';

        header("Location:?url=UserController/dashboard");
        exit;
    }

    /*
    ============================================================
    USER BOOK LIST
    ============================================================
    */

    public function books($category = null)
    {
        AuthMiddleware::check();
        UserMiddleware::check();

        $service = new DistributedQueryService();
        $books = (array) $service->getAllBooks();

        // If a category is provided, filter the books (case-insensitive).
        if (!empty($category) && strtolower($category) !== 'all') {
            $requested = strtolower(trim($category));
            $filtered = [];

            foreach ($books as $b) {
                $bCat = strtolower(trim($b['category'] ?? ''));
                if ($bCat === $requested) {
                    $filtered[] = $b;
                }
            }

            $books = array_values($filtered);
        }

        require_once __DIR__ . "/../Views/user/books.php";
    }

    /*
    ============================================================
    BOOK DETAIL
    ============================================================
    */

    public function read($id)
    {
        AuthMiddleware::check();
        UserMiddleware::check();

        $service = new DistributedQueryService();
        $books = (array) $service->getAllBooks();

        $foundBook = null;

        foreach ($books as $book) {
            $bookId = $book["global_id"]
                ?? $book["global_book_id"]
                ?? $book["book_id"]
                ?? $book["id"]
                ?? "";

            if ((string) $bookId === (string) $id) {
                $foundBook = $book;
                break;
            }
        }

        require_once __DIR__ . "/../Views/user/read.php";
    }

    /*
    ============================================================
    OLD USER HISTORY ROUTE
    ============================================================
    */

    public function history()
    {
        AuthMiddleware::check();
        UserMiddleware::check();

        header(
            "Location:?url=BorrowController/history"
        );

        exit;
    }


    /*
    ============================================================
    ADMIN USER MANAGEMENT
    ============================================================
    */

    public function index()
{
    AuthMiddleware::check();
    AdminMiddleware::check();

    $service = new UserService();

    $users = $service->allUsers();

    $flashError = $_SESSION['flash_error'] ?? '';
    $flashSuccess = $_SESSION['flash_success'] ?? '';

    unset($_SESSION['flash_error']);
    unset($_SESSION['flash_success']);

    /*
    ============================================================
    CONVERT USERS TO ARRAY
    ============================================================
    */

    $userRows = [];

    if ($users) {

        while ($row = $users->fetch_assoc()) {
            $userRows[] = $row;
        }
    }

    $totalUsers = count($userRows);

    $adminCount = 0;
    $globalCount = 0;
    $localCount = 0;

    foreach ($userRows as $row) {

        if (($row["role"] ?? "") === "admin") {
            $adminCount++;
        }

        if (!empty($row["global_role"])) {
            $globalCount++;
        }

        if (!empty($row["local_role"])) {
            $localCount++;
        }
    }

    /*
    ============================================================
    LOAD CSS
    ============================================================
    */

    echo "
    <link
        rel='stylesheet'
        href='assets/css/user-management.css'
    >
    ";

    /*
    ============================================================
    PAGE START
    ============================================================
    */

    echo "

    <div class='um-page-wrapper'>

        <div class='user-management-page'>
    ";

    /*
    ============================================================
    FLASH ERROR
    ============================================================
    */

    if (!empty($flashError)) {

        echo "

        <div class='um-alert um-alert-error'>

            <div class='um-alert-icon'>
                ⚠️
            </div>

            <div class='um-alert-content'>

                <strong>
                    User Cannot Be Deleted
                </strong>

                <span>
                    " . htmlspecialchars($flashError) . "
                </span>

            </div>

            <button
                type='button'
                class='um-alert-close'
                onclick='this.parentElement.remove()'
            >
                ×
            </button>

        </div>

        ";
    }

    /*
    ============================================================
    FLASH SUCCESS
    ============================================================
    */

    if (!empty($flashSuccess)) {

        echo "

        <div class='um-alert um-alert-success'>

            <div class='um-alert-icon'>
                ✅
            </div>

            <div class='um-alert-content'>

                <strong>
                    Success
                </strong>

                <span>
                    " . htmlspecialchars($flashSuccess) . "
                </span>

            </div>

            <button
                type='button'
                class='um-alert-close'
                onclick='this.parentElement.remove()'
            >
                ×
            </button>

        </div>

        ";
    }

    /*
    ============================================================
    HEADER
    ============================================================
    */

    echo "

        <div class='um-header'>

            <div class='um-header-left'>

                <div class='um-header-icon'>
                    👥
                </div>

                <div>

                    <h1 class='um-title'>
                        User Management
                    </h1>

                    <p class='um-subtitle'>
                        Manage users, local roles and global roles
                        across the library system.
                    </p>

                </div>

            </div>

            <div class='um-header-actions'>

                <a
                    href='?url=AdminController/dashboard'
                    class='um-btn um-btn-secondary'
                >
                    ← Back
                </a>

                <a
                    href='?url=UserController/create'
                    class='um-btn um-btn-primary'
                >
                    ➕ Add New User
                </a>

            </div>

        </div>

        <!-- SUMMARY -->

        <div class='um-summary'>

            <div class='um-summary-card'>

                <div class='um-summary-icon'>
                    👥
                </div>

                <div>

                    <div class='um-summary-label'>
                        Total Users
                    </div>

                    <div class='um-summary-value'>
                        {$totalUsers}
                    </div>

                </div>

            </div>

            <div class='um-summary-card'>

                <div class='um-summary-icon'>
                    🛡️
                </div>

                <div>

                    <div class='um-summary-label'>
                        Administrators
                    </div>

                    <div class='um-summary-value'>
                        {$adminCount}
                    </div>

                </div>

            </div>

            <div class='um-summary-card'>

                <div class='um-summary-icon'>
                    🌐
                </div>

                <div>

                    <div class='um-summary-label'>
                        Global Roles
                    </div>

                    <div class='um-summary-value'>
                        {$globalCount}
                    </div>

                </div>

            </div>

        </div>

        <!-- TOOLBAR -->

        <div class='um-toolbar'>

            <div class='um-search'>

                <span class='um-search-icon'>
                    🔎
                </span>

                <input
                    type='text'
                    id='userSearch'
                    placeholder='Search username, name or email...'
                    onkeyup='filterUsers()'
                >

            </div>

            <div class='um-toolbar-text'>

                Showing
                <strong>
                    {$totalUsers}
                </strong>
                registered users

            </div>

        </div>

        <!-- TABLE -->

        <div class='um-table-container'>

            <div class='um-table-wrapper'>

                <table
                    class='um-table'
                    id='usersTable'
                >

                    <thead>

                        <tr>

                            <th>ID</th>
                            <th>User</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Local Role</th>
                            <th>Global Role</th>
                            <th>Actions</th>

                        </tr>

                    </thead>

                    <tbody>

    ";

    /*
    ============================================================
    EMPTY USERS
    ============================================================
    */

    if ($totalUsers === 0) {

        echo "

            <tr>

                <td
                    colspan='7'
                    class='um-empty'
                >

                    <div class='um-empty-icon'>
                        👥
                    </div>

                    <h3>
                        No Users Found
                    </h3>

                    <p>
                        There are currently no users
                        in the system.
                    </p>

                </td>

            </tr>

        ";

    } else {

        /*
        ========================================================
        USER ROWS
        ========================================================
        */

        foreach ($userRows as $row) {

            $id = htmlspecialchars(
                $row["id"] ?? ""
            );

            $username = htmlspecialchars(
                $row["username"] ?? ""
            );

            $name = htmlspecialchars(
                $row["name"] ?? ""
            );

            $email = htmlspecialchars(
                $row["email"] ?? ""
            );

            $role = htmlspecialchars(
                $row["role"] ?? ""
            );

            $localRole = htmlspecialchars(
                $row["local_role"] ?? "-"
            );

            $globalRole = htmlspecialchars(
                $row["global_role"] ?? "-"
            );

            $initial = strtoupper(
                substr(
                    $row["name"]
                        ?? $row["username"]
                        ?? "U",
                    0,
                    1
                )
            );

            $roleClass =
                ($role === "admin")
                ? "um-badge-admin"
                : "um-badge-user";

            $localClass =
                empty($row["local_role"])
                ? "um-badge-empty"
                : "um-badge-local";

            $globalClass =
                empty($row["global_role"])
                ? "um-badge-empty"
                : "um-badge-global";

            $roleText =
                ($role === "admin")
                ? "🛡️ Admin"
                : "👤 User";

            $localText =
                empty($row["local_role"])
                ? "—"
                : "📍 " . $localRole;

            $globalText =
                empty($row["global_role"])
                ? "—"
                : "🌐 " . $globalRole;

            echo "

                <tr>

                    <td>

                        <span class='um-user-id'>
                            #{$id}
                        </span>

                    </td>

                    <td>

                        <div class='um-user-cell'>

                            <div class='um-avatar'>
                                {$initial}
                            </div>

                            <div>

                                <div class='um-user-name'>
                                    {$name}
                                </div>

                                <div class='um-username'>
                                    @{$username}
                                </div>

                            </div>

                        </div>

                    </td>

                    <td>

                        <span class='um-email'>
                            {$email}
                        </span>

                    </td>

                    <td>

                        <span class='um-badge {$roleClass}'>
                            {$roleText}
                        </span>

                    </td>

                    <td>

                        <span class='um-badge {$localClass}'>
                            {$localText}
                        </span>

                    </td>

                    <td>

                        <span class='um-badge {$globalClass}'>
                            {$globalText}
                        </span>

                    </td>

                    <td>

                        <div class='um-actions'>

                            <a
                                href='?url=UserController/edit/{$id}'
                                class='um-action um-action-edit'
                                title='Edit User'
                            >
                                ✏️
                            </a>

                            <a
                                href='?url=UserController/delete/{$id}'
                                class='um-action um-action-delete'
                                title='Delete User'
                                onclick=\"return confirm('Delete this user?')\"
                            >
                                🗑️
                            </a>

                        </div>

                    </td>

                </tr>

            ";
        }
    }

    /*
    ============================================================
    PAGE FOOTER
    ============================================================
    */

    echo "

                    </tbody>

                </table>

            </div>

        </div>

        <div class='um-footer'>

            <span>
                👥 User Administration
            </span>

            <span>
                Distributed Library Management System
            </span>

        </div>

        </div>

    </div>

    <script>

    function filterUsers()
    {
        const input =
            document.getElementById('userSearch');

        const filter =
            input.value.toLowerCase();

        const table =
            document.getElementById('usersTable');

        const tbody =
            table.querySelector('tbody');

        const rows =
            tbody.querySelectorAll('tr');

        rows.forEach(function(row)
        {
            const text =
                row.textContent.toLowerCase();

            row.style.display =
                text.includes(filter)
                ? ''
                : 'none';
        });
    }

    </script>

    ";
}


    /*
    ============================================================
    EDIT USER
    ============================================================
    */

    public function edit($id)
    {
        AuthMiddleware::check();
        AdminMiddleware::check();

        $service =
            new UserService();

        $user =
            $service->find($id);

        if (!$user) {

            echo "

            <h3>
                User Not Found
            </h3>

            <a
                href='?url=UserController/index'
            >
                Back
            </a>

            ";

            return;
        }

        if (
            $_SERVER["REQUEST_METHOD"]
            == "POST"
        ) {

            $service->update(
                $id,
                $_POST["username"],
                $_POST["name"],
                $_POST["email"],
                $_POST["role"]
            );

            header(
                "Location:?url=UserController/index"
            );

            exit;
        }

        echo "

        <h2>
            Update User
        </h2>

        <form method='POST'>

        Username:

        <input
            name='username'
            value='" .
            htmlspecialchars(
                $user["username"] ?? ""
            )
            . "'
            required
        >

        <br><br>

        Name:

        <input
            name='name'
            value='" .
            htmlspecialchars(
                $user["name"] ?? ""
            )
            . "'
            required
        >

        <br><br>

        Email:

        <input
            type='email'
            name='email'
            value='" .
            htmlspecialchars(
                $user["email"] ?? ""
            )
            . "'
            required
        >

        <br><br>

        Role:

        <select name='role'>

            <option
                value='user'
                " .
            (
                ($user["role"] ?? "")
                == "user"
                ? "selected"
                : ""
            )
            . "
            >
                User
            </option>

            <option
                value='admin'
                " .
            (
                ($user["role"] ?? "")
                == "admin"
                ? "selected"
                : ""
            )
            . "
            >
                Admin
            </option>

        </select>

        <br><br>

        <button>
            Update
        </button>

        </form>

        <br>

        <a
            href='?url=UserController/index'
        >
            Back
        </a>

        ";
    }


    /*
    ============================================================
    DELETE USER
    ============================================================
    */

    public function delete($id)
{
    AuthMiddleware::check();
    AdminMiddleware::check();

    $service = new UserService();

    $result = $service->delete($id);


    /*
    ============================================================
    DELETE SUCCESS
    ============================================================
    */

    if (!empty($result['success'])) {

        $_SESSION['flash_success'] =
            'User deleted successfully.';

    }


    /*
    ============================================================
    DELETE BLOCKED
    ============================================================
    */

    else {

        $reason = $result['reason'] ?? 'database_error';


        /*
        --------------------------------------------------------
        Borrow + Hold
        --------------------------------------------------------
        */

        if ($reason === 'borrow_and_hold') {

            $_SESSION['flash_error'] =
                'Cannot delete this user. This user has borrow history and active hold records.';

        }


        /*
        --------------------------------------------------------
        Borrow only
        --------------------------------------------------------
        */

        elseif ($reason === 'borrow') {

            $_SESSION['flash_error'] =
                'Cannot delete this user. This user has borrowed books. Please make sure all borrowed books are returned first.';

        }


        /*
        --------------------------------------------------------
        Hold only
        --------------------------------------------------------
        */

        elseif ($reason === 'hold') {

            $_SESSION['flash_error'] =
                'Cannot delete this user. This user has active hold requests. Please remove the hold requests first.';

        }


        /*
        --------------------------------------------------------
        Database error
        --------------------------------------------------------
        */

        else {

            $_SESSION['flash_error'] =
                'Unable to delete this user. Please try again.';
        }
    }


    /*
    ============================================================
    RETURN TO USER MANAGEMENT
    ============================================================
    */

    header(
        "Location:?url=UserController/index"
    );

    exit;
}


    /*
    ============================================================
    CREATE USER BY ADMIN
    ============================================================
    */


    /*
    ==============================
    CREATE USER BY ADMIN
    ==============================
*/

    public function create()
    {
        AuthMiddleware::check();
        AdminMiddleware::check();

        if (
            $_SERVER["REQUEST_METHOD"]
            == "POST"
        ) {

            $service =
                new UserService();

            $service->create(
                $_POST["username"],
                $_POST["password"],
                $_POST["name"],
                $_POST["email"],
                $_POST["role"]
            );

            header(
                "Location:?url=UserController/index"
            );

            exit;
        }

        /*
    =========================================
    LOAD EXTERNAL CSS
    =========================================
    */

        echo "
    <link
        rel='stylesheet'
        href='assets/css/user-create.css'
    >
    ";

        echo "

    <div class='user-create-page'>

        <!-- =================================
             HEADER
             ================================= -->

        <div class='user-create-header'>

            <div class='user-create-title'>

                <div class='user-create-title-icon'>
                    👥
                </div>

                <div>

                    <h1>
                        Add New User
                    </h1>

                    <p>
                        Create a new account for the library management system.
                    </p>

                </div>

            </div>


            <a
                href='?url=UserController/index'
                class='user-create-back'
            >
                ← Back to Users
            </a>

        </div>


        <!-- =================================
             MAIN CARD
             ================================= -->

        <div class='user-create-card'>


            <!-- =================================
                 LEFT INFORMATION PANEL
                 ================================= -->

            <aside class='user-create-side'>

                <div class='create-side-content'>

                    <div class='create-side-icon'>
                        ➕
                    </div>

                    <h2>
                        Create Account
                    </h2>

                    <p>
                        Add a new member or administrator
                        to your distributed library system.
                    </p>


                    <div class='create-features'>

                        <div class='create-feature'>

                            <div class='create-feature-icon'>
                                🔐
                            </div>

                            <div class='create-feature-text'>

                                <strong>
                                    Secure Account
                                </strong>

                                <span>
                                    Protected login credentials
                                </span>

                            </div>

                        </div>


                        <div class='create-feature'>

                            <div class='create-feature-icon'>
                                👤
                            </div>

                            <div class='create-feature-text'>

                                <strong>
                                    User Access
                                </strong>

                                <span>
                                    Library member permissions
                                </span>

                            </div>

                        </div>


                        <div class='create-feature'>

                            <div class='create-feature-icon'>
                                🛡️
                            </div>

                            <div class='create-feature-text'>

                                <strong>
                                    Role Control
                                </strong>

                                <span>
                                    User or administrator access
                                </span>

                            </div>

                        </div>

                    </div>

                </div>

            </aside>


            <!-- =================================
                 FORM
                 ================================= -->

            <section class='user-create-form-area'>


                <div class='form-heading'>

                    <h2>
                        Account Information
                    </h2>

                    <p>
                        Enter the user's account information
                        and select the appropriate system role.
                    </p>

                </div>


                <form
                    method='POST'
                    autocomplete='off'
                >


                    <div class='user-form-grid'>


                        <!-- USERNAME -->

                        <div class='user-form-group'>

                            <label class='user-form-label'>

                                <span class='user-form-label-icon'>
                                    👤
                                </span>

                                Username

                                <span class='required'>
                                    *
                                </span>

                            </label>


                            <div class='user-input-wrapper'>

                                <input
                                    class='user-input'
                                    type='text'
                                    name='username'
                                    placeholder='Enter username'
                                    required
                                    autocomplete='username'
                                >

                            </div>


                            <div class='user-helper'>
                                This will be used to sign in to the system.
                            </div>

                        </div>


                        <!-- PASSWORD -->

                        <div class='user-form-group'>

                            <label class='user-form-label'>

                                <span class='user-form-label-icon'>
                                    🔒
                                </span>

                                Password

                                <span class='required'>
                                    *
                                </span>

                            </label>


                            <div class='user-input-wrapper'>

                                <input
                                    class='user-input'
                                    type='password'
                                    name='password'
                                    placeholder='Enter password'
                                    required
                                    autocomplete='new-password'
                                >

                            </div>


                            <div class='user-helper'>
                                Choose a secure password for this account.
                            </div>

                        </div>


                        <!-- FULL NAME -->

                        <div class='user-form-group'>

                            <label class='user-form-label'>

                                <span class='user-form-label-icon'>
                                    🪪
                                </span>

                                Full Name

                                <span class='required'>
                                    *
                                </span>

                            </label>


                            <input
                                class='user-input'
                                type='text'
                                name='name'
                                placeholder='Enter full name'
                                required
                            >


                            <div class='user-helper'>
                                Enter the user's display name.
                            </div>

                        </div>


                        <!-- EMAIL -->

                        <div class='user-form-group'>

                            <label class='user-form-label'>

                                <span class='user-form-label-icon'>
                                    ✉️
                                </span>

                                Email Address

                                <span class='required'>
                                    *
                                </span>

                            </label>


                            <input
                                class='user-input'
                                type='email'
                                name='email'
                                placeholder='example@email.com'
                                required
                                autocomplete='email'
                            >


                            <div class='user-helper'>
                                Used for account identification and communication.
                            </div>

                        </div>


                    </div>


                    <!-- =================================
                         ROLE
                         ================================= -->

                    <div class='role-section'>

                        <div class='role-section-title'>

                            🛡️ Account Role

                        </div>


                        <div class='role-options'>


                            <!-- USER -->

                            <div class='role-option'>

                                <input
                                    type='radio'
                                    id='role-user'
                                    name='role'
                                    value='user'
                                    checked
                                >

                                <label
                                    for='role-user'
                                    class='role-card'
                                >

                                    <div class='role-icon'>
                                        👤
                                    </div>

                                    <div class='role-info'>

                                        <strong>
                                            User
                                        </strong>

                                        <span>
                                            Regular library member
                                        </span>

                                    </div>

                                </label>

                            </div>


                            <!-- ADMIN -->

                            <div class='role-option'>

                                <input
                                    type='radio'
                                    id='role-admin'
                                    name='role'
                                    value='admin'
                                >

                                <label
                                    for='role-admin'
                                    class='role-card'
                                >

                                    <div class='role-icon'>
                                        🛡️
                                    </div>

                                    <div class='role-info'>

                                        <strong>
                                            Administrator
                                        </strong>

                                        <span>
                                            Manage library system
                                        </span>

                                    </div>

                                </label>

                            </div>


                        </div>

                    </div>


                    <!-- =================================
                         SECURITY NOTE
                         ================================= -->

                    <div class='security-note'>

                        <span>
                            🔒
                        </span>

                        <span>
                            Account information will be securely
                            processed by the library system.
                        </span>

                    </div>


                    <!-- =================================
                         ACTIONS
                         ================================= -->

                    <div class='user-form-actions'>


                        <a
                            href='?url=UserController/index'
                            class='user-cancel-btn'
                        >
                            Cancel
                        </a>


                        <button
                            type='submit'
                            class='user-create-btn'
                        >

                            ➕
                            Create User

                        </button>


                    </div>


                </form>


            </section>


        </div>


    </div>

    ";
    }
}

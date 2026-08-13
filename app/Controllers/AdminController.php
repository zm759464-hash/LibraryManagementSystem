<?php

require_once "../app/Middleware/AuthMiddleware.php";
require_once "../app/Middleware/AdminMiddleware.php";
require_once "../app/Core/Database.php";


class AdminController
{
    /*
        ========================================================
        ADMIN DASHBOARD
        ========================================================
    */

    public function dashboard()
    {
        // Authentication & Authorization
        AuthMiddleware::check();
        AdminMiddleware::check();
        // စာအုပ်အပ်ရန် ရက်စေ့ခါနီးသူများကို အလိုအလျောက်စစ်ပြီး Noti ထုတ်ပေးရန်
        require_once "../app/Services/BorrowService.php";
        (new BorrowService())->checkAndSendReminders();


        if (!isset($_SESSION["user"])) {
            header("Location:?url=AuthController/login");
            exit;
        }

        $user = $_SESSION["user"];

        /*
            ----------------------------------------------------
            Safe display values
            ----------------------------------------------------
        */

        $adminId = htmlspecialchars(
            $user["id"] ?? "-",
            ENT_QUOTES,
            "UTF-8"
        );

        $username = htmlspecialchars(
            $user["username"] ?? "-",
            ENT_QUOTES,
            "UTF-8"
        );

        $name = htmlspecialchars(
            $user["name"] ?? "Administrator",
            ENT_QUOTES,
            "UTF-8"
        );

        $email = htmlspecialchars(
            $user["email"] ?? "-",
            ENT_QUOTES,
            "UTF-8"
        );

        $localRole = htmlspecialchars(
            $user["local_role"] ?? "local_admin",
            ENT_QUOTES,
            "UTF-8"
        );


        /*
            ====================================================
            NOTIFICATION SYSTEM
            ====================================================
        */

        $notifications = $this->getNotifications();

$notificationCount = count($notifications);


        /*
            Escape notification count
        */

        $notificationBadge = htmlspecialchars(
            (string)$notificationCount,
            ENT_QUOTES,
            "UTF-8"
        );


        /*
            ====================================================
            NOTIFICATION HTML
            ====================================================
        */

        $notificationItems = "";


        if ($notificationCount > 0) {

            foreach ($notifications as $notification) {

                $type = htmlspecialchars(
                    $notification["type"] ?? "Notification",
                    ENT_QUOTES,
                    "UTF-8"
                );

                $title = htmlspecialchars(
                    $notification["title"] ?? "New activity",
                    ENT_QUOTES,
                    "UTF-8"
                );

                $message = htmlspecialchars(
                    $notification["message"] ?? "",
                    ENT_QUOTES,
                    "UTF-8"
                );

                $time = htmlspecialchars(
                    $notification["time"] ?? "",
                    ENT_QUOTES,
                    "UTF-8"
                );

                $icon = $notification["icon"] ?? "🔔";

                
$notificationId = (int)($notification["id"] ?? 0);

$notificationItems .= "

    <div
        class='notification-item'
        id='notification-item-{$notificationId}'
    >

        <div class='notification-icon notification-recent'>
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
            title='Delete notification'
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


        /*
            ====================================================
            NOTIFICATION BADGE
            ====================================================
        */

        $badgeHtml = "";

if ($notificationCount > 0) {

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
            ====================================================
            DASHBOARD HTML
            ====================================================
        */

        echo "

        <link
            rel='stylesheet'
            href='/assets/css/admin.css'
        >
         

        <link
    rel='stylesheet'
    href='/assets/css/admin.css'
>

<link
    rel='stylesheet'
    href='/assets/css/notification.css'
>

        <div class='admin-layout'>


            <!-- ==========================================
                 LEFT SIDEBAR
                 ========================================== -->


            <aside class='admin-sidebar'>


                <!-- BRAND -->

                <div class='admin-brand'>

                    <div class='admin-brand-icon'>
                        📚
                    </div>

                    <div>

                        <div class='admin-brand-title'>
                            LIBRARY
                        </div>

                        <div class='admin-brand-subtitle'>
                            ADMIN PANEL
                        </div>

                    </div>

                </div>


                <!-- PROFILE -->

                <div class='admin-profile'>

                    <div class='profile-avatar'>
                        👤
                    </div>

                    <div class='profile-name'>
                        {$name}
                    </div>

                    <div class='profile-username'>
                        @{$username}
                    </div>

                    <div class='profile-status'>

                        <span class='online-dot'></span>

                        Online

                    </div>


                    <div class='profile-info'>

                        <div>

                            <span>ID</span>

                            <strong>
                                #{$adminId}
                            </strong>

                        </div>


                        <div>

                            <span>ROLE</span>

                            <strong>
                                {$localRole}
                            </strong>

                        </div>

                    </div>

                </div>


                <!-- NAVIGATION -->

                <nav class='admin-nav'>


                    <div class='nav-section-title'>
                        MAIN MENU
                    </div>


                    <a
                        href='?url=AdminController/dashboard'
                        class='admin-nav-item active'
                    >

                        <span class='nav-icon'>
                            🏠
                        </span>

                        <span>
                            Dashboard
                        </span>

                    </a>


                    <a
                        href='?url=BookController/index'
                        class='admin-nav-item'
                    >

                        <span class='nav-icon'>
                            📚
                        </span>

                        <span>
                            Books
                        </span>

                    </a>


                    <a
                        href='?url=UserController/index'
                        class='admin-nav-item'
                    >

                        <span class='nav-icon'>
                            👥
                        </span>

                        <span>
                            Users
                        </span>

                    </a>


                    <a
                        href='?url=BorrowController/all'
                        class='admin-nav-item'
                    >

                        <span class='nav-icon'>
                            🔄
                        </span>

                        <span>
                            Borrowing
                        </span>

                    </a>


                    <a
                        href='?url=HoldController/index'
                        class='admin-nav-item'
                    >

                        <span class='nav-icon'>
                            🔖
                        </span>

                        <span>
                            Holds
                        </span>

                    </a>


                    <div class='nav-section-title nav-section-space'>
                        DISTRIBUTED SYSTEM
                    </div>


                    <a
                        href='?url=SearchController/index'
                        class='admin-nav-item'
                    >

                        <span class='nav-icon'>
                            🔎
                        </span>

                        <span>
                            Parallel Search
                        </span>

                    </a>


                    <a
                        href='?url=DashboardController/index'
                        class='admin-nav-item'
                    >

                        <span class='nav-icon'>
                            📊
                        </span>

                        <span>
                            Performance
                        </span>

                    </a>


                </nav>


                <!-- SIDEBAR FOOTER -->

                <div class='admin-sidebar-footer'>

                    <a
                        href='?url=AuthController/logout'
                        class='logout-link'
                    >

                        <span>
                            🚪
                        </span>

                        <span>
                            Logout
                        </span>

                    </a>

                </div>


            </aside>


            <!-- ==========================================
                 MAIN CONTENT
                 ========================================== -->


            <main class='admin-main'>


                <!-- TOP BAR -->

                <header class='admin-topbar'>


                    <div class='topbar-search'>

                        <span>
                            🔎
                        </span>

                        <input
                            type='text'
                            placeholder='Search books, users, requests...'
                        >

                    </div>


                    <div class='topbar-actions'>


                        <!-- ==================================
                             NOTIFICATION
                             ================================== -->

                        <div
                            class='notification-wrapper'
                            id='notificationWrapper'
                        >


                            <button
                                class='notification-btn'
                                id='notificationButton'
                                type='button'
                                aria-label='Notifications'
                            >

                                <span class='notification-bell'>
                                    🔔
                                </span>

                                {$badgeHtml}

                            </button>


                            <!-- Notification Dropdown -->

                            <div
    class='notification-panel'
    id='notificationDropdown'
>


                                <div class='notification-header'>

    <div>

        <strong>
            Notifications
        </strong>

        <small id='notificationHeaderCount'>
            {$notificationCount} new
        </small>

    </div>

    <button
        type='button'
        class='notification-read-btn'
        id='markNotificationsRead'
    >
        Mark all as read
    </button>

</div>


                                <div
                                    class='notification-list'
                                    id='notificationList'
                                >

                                    {$notificationItems}

                                </div>


                            </div>

                        </div>


                        <!-- TOPBAR USER -->

                        <div class='topbar-user'>

                            <div class='topbar-avatar'>
                                👤
                            </div>

                            <div>

                                <strong>
                                    {$name}
                                </strong>

                                <small>
                                    {$localRole}
                                </small>

                            </div>

                        </div>


                    </div>


                </header>


                <!-- CONTENT -->

                <div class='admin-content'>


                    <!-- WELCOME -->

                    <section class='welcome-section'>


                        <div>

                            <div class='welcome-label'>
                                ADMINISTRATOR
                            </div>

                            <h1>
                                Welcome back, {$name} 👋
                            </h1>

                            <p>
                                Manage your distributed library
                                system from one place.
                            </p>

                        </div>


                        <div class='welcome-book'>
                            📚
                        </div>


                    </section>


                    <!-- STAT CARDS -->

                    <section class='stats-grid'>


                        <a
                            href='?url=BookController/index'
                            class='admin-stat-card'
                        >

                            <div class='stat-icon books-icon'>
                                📚
                            </div>

                            <div>

                                <div class='stat-label'>
                                    LIBRARY
                                </div>

                                <div class='stat-title'>
                                    Books
                                </div>

                                <div class='stat-action'>
                                    Manage books →
                                </div>

                            </div>

                        </a>


                        <a
                            href='?url=UserController/index'
                            class='admin-stat-card'
                        >

                            <div class='stat-icon users-icon'>
                                👥
                            </div>

                            <div>

                                <div class='stat-label'>
                                    SYSTEM
                                </div>

                                <div class='stat-title'>
                                    Users
                                </div>

                                <div class='stat-action'>
                                    Manage users →
                                </div>

                            </div>

                        </a>


                        <a
                            href='?url=BorrowController/all'
                            class='admin-stat-card'
                        >

                            <div class='stat-icon borrow-icon'>
                                🔄
                            </div>

                            <div>

                                <div class='stat-label'>
                                    LIBRARY
                                </div>

                                <div class='stat-title'>
                                    Borrowing
                                </div>

                                <div class='stat-action'>
                                    View activity →
                                </div>

                            </div>

                        </a>


                        <a
                            href='?url=HoldController/index'
                            class='admin-stat-card'
                        >

                            <div class='stat-icon hold-icon'>
                                🔖
                            </div>

                            <div>

                                <div class='stat-label'>
                                    LIBRARY
                                </div>

                                <div class='stat-title'>
                                    Holds
                                </div>

                                <div class='stat-action'>
                                    View requests →
                                </div>

                            </div>

                        </a>


                    </section>


                    <!-- QUICK MANAGEMENT -->

                    <section class='dashboard-section'>


                        <div class='section-heading'>

                            <div>

                                <h2>
                                    Quick Management
                                </h2>

                                <p>
                                    Frequently used library operations
                                </p>

                            </div>

                        </div>


                        <div class='management-grid'>


                            <!-- BOOK MANAGEMENT -->

                            <div class='management-card'>


                                <div class='management-card-header'>

                                    <div class='management-icon'>
                                        📚
                                    </div>

                                    <div>

                                        <h3>
                                            Book Management
                                        </h3>

                                        <p>
                                            Manage your library collection
                                        </p>

                                    </div>

                                </div>


                                <div class='management-actions'>

                                    <a
                                        href='?url=BookController/create'
                                        class='management-action primary'
                                    >
                                        ➕ Add New Book
                                    </a>

                                    <a
                                        href='?url=BookController/index'
                                        class='management-action'
                                    >
                                        📖 View All Books
                                    </a>

                                </div>


                            </div>


                            <!-- USER MANAGEMENT -->

                            <div class='management-card'>


                                <div class='management-card-header'>

                                    <div class='management-icon'>
                                        👥
                                    </div>

                                    <div>

                                        <h3>
                                            User Management
                                        </h3>

                                        <p>
                                            Manage users and access
                                        </p>

                                    </div>

                                </div>


                                <div class='management-actions'>

                                    <a
                                        href='?url=UserController/index'
                                        class='management-action primary'
                                    >
                                        👥 View All Users
                                    </a>

                                    <a
                                        href='?url=UserController/create'
                                        class='management-action'
                                    >
                                        ➕ Add New User
                                    </a>

                                </div>


                            </div>


                            <!-- BORROW MANAGEMENT -->

                            <div class='management-card'>


                                <div class='management-card-header'>

                                    <div class='management-icon'>
                                        🔄
                                    </div>

                                    <div>

                                        <h3>
                                            Borrow Management
                                        </h3>

                                        <p>
                                            Track borrowing activity
                                        </p>

                                    </div>

                                </div>


                                <div class='management-actions'>

                                    <a
                                        href='?url=BorrowController/all'
                                        class='management-action primary'
                                    >
                                        📋 Borrow History
                                    </a>

                                    <a
                                        href='?url=BorrowController/pending'
                                        class='management-action'
                                    >
                                        ⏳ Borrow Requests
                                    </a>

                                </div>


                            </div>


                            <!-- HOLD MANAGEMENT -->

                            <div class='management-card'>


                                <div class='management-card-header'>

                                    <div class='management-icon'>
                                        🔖
                                    </div>

                                    <div>

                                        <h3>
                                            Hold Management
                                        </h3>

                                        <p>
                                            Manage reserved books
                                        </p>

                                    </div>

                                </div>


                                <div class='management-actions'>

                                    <a
                                        href='?url=HoldController/index'
                                        class='management-action primary'
                                    >
                                        🔖 View Hold Requests
                                    </a>

                                </div>


                            </div>


                        </div>


                    </section>


                    <!-- DISTRIBUTED SYSTEM -->

                    <section class='dashboard-section'>


                        <div class='section-heading'>

                            <div>

                                <h2>
                                    🌐 Distributed System
                                </h2>

                                <p>
                                    Monitor and explore your distributed
                                    library infrastructure
                                </p>

                            </div>

                        </div>


                        <div class='distributed-grid'>


                            <a
                                href='?url=SearchController/index'
                                class='distributed-card'
                            >

                                <div class='distributed-icon'>
                                    🔎
                                </div>

                                <div>

                                    <h3>
                                        Parallel Search
                                    </h3>

                                    <p>
                                        Search books across the
                                        distributed library.
                                    </p>

                                    <span>
                                        Open Search →
                                    </span>

                                </div>

                            </a>


                            <a
                                href='?url=DashboardController/index'
                                class='distributed-card'
                            >

                                <div class='distributed-icon'>
                                    📊
                                </div>

                                <div>

                                    <h3>
                                        Performance Dashboard
                                    </h3>

                                    <p>
                                        Monitor distributed system
                                        performance.
                                    </p>

                                    <span>
                                        View Performance →
                                    </span>

                                </div>

                            </a>


                        </div>


                    </section>


                    <!-- ACCOUNT INFORMATION -->

                    <section class='account-card'>


                        <div class='account-icon'>
                            👤
                        </div>


                        <div class='account-details'>

                            <span>
                                Signed in as
                            </span>

                            <strong>
                                {$name}
                            </strong>

                            <small>
                                {$email}
                            </small>

                        </div>


                        <div class='account-role'>

                            <span>
                                Local Role
                            </span>

                            <strong>
                                {$localRole}
                            </strong>

                        </div>


                    </section>


                </div>


            </main>


        </div>


        <!-- ==============================================
             NOTIFICATION JAVASCRIPT
             ============================================== -->

        <script>

        document.addEventListener(
            'DOMContentLoaded',
            function () {

                const button =
                    document.getElementById(
                        'notificationButton'
                    );

                const dropdown =
                    document.getElementById(
                        'notificationDropdown'
                    );

                const wrapper =
                    document.getElementById(
                        'notificationWrapper'
                    );

                const markRead =
                    document.getElementById(
                        'markNotificationsRead'
                    );

                const badge =
                    document.getElementById(
                        'notificationBadge'
                    );


                /*
                    ------------------------------------------
                    Open / Close Notification
                    ------------------------------------------
                */

                if (button && dropdown) {

                    button.addEventListener(
                        'click',
                        function (event) {

                            event.stopPropagation();

                            dropdown.classList.toggle(
                                'show'
                            );

                        }
                    );

                }


                /*
                    ------------------------------------------
                    Prevent dropdown from closing
                    when clicking inside
                    ------------------------------------------
                */

                if (dropdown) {

                    dropdown.addEventListener(
                        'click',
                        function (event) {

                            event.stopPropagation();

                        }
                    );

                }


                /*
                    ------------------------------------------
                    Close when clicking outside
                    ------------------------------------------
                */

                document.addEventListener(
                    'click',
                    function () {

                        if (
                            dropdown &&
                            dropdown.classList.contains(
                                'show'
                            )
                        ) {

                            dropdown.classList.remove(
                                'show'
                            );

                        }

                    }
                );


                /*
                    ------------------------------------------
                    Mark all as read
                    ------------------------------------------
                */

                if (markRead) {

                    markRead.addEventListener(
                        'click',
                        function () {

                            fetch(
                                '?url=AdminController/markNotificationsRead',
                                {
                                    method: 'POST',
                                    headers: {
                                        'X-Requested-With':
                                            'XMLHttpRequest'
                                    }
                                }
                            )
                            .then(
                                response =>
                                    response.json()
                            )
                            .then(
                                data => {

                                    if (
                                        data.success
                                    ) {

                                        if (badge) {

                                            badge.remove();

                                        }


                                        const count =
    document.getElementById(
        'notificationHeaderCount'
    );

                                        if (count) {

                                            count.textContent =
                                                '0 new';

                                        }


                                        const list =
                                            document.getElementById(
                                                'notificationList'
                                            );

                                        if (list) {

                                            list.innerHTML = `

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

                                            `;

                                        }

                                    }

                                }
                            )
                            .catch(
                                error => {
                                    console.error(
                                        'Notification error:',
                                        error
                                    );
                                }
                            );

                        }
                    );

                }

            }
        );

        function deleteNotification(notificationId) {

    if (!notificationId) {
        return;
    }

    fetch(
        '?url=AdminController/deleteNotification/' + notificationId,
        {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        }
    )
    .then(response => response.json())
    .then(data => {

        if (!data.success) {

            console.error(
                'Delete notification failed'
            );

            return;
        }

        /*
        Remove notification from UI
        */

        const item =
            document.getElementById(
                'notification-item-' + notificationId
            );

        if (item) {
            item.remove();
        }

        /*
        Update badge
        */

        const badge =
            document.getElementById(
                'notificationBadge'
            );

        if (badge) {

            let count =
                parseInt(
                    badge.textContent
                ) || 0;

            count--;

            if (count <= 0) {

                badge.remove();

            } else {

                badge.textContent = count;
            }
        }

        /*
        Update header count
        */

        const headerCount =
            document.getElementById(
                'notificationHeaderCount'
            );

        if (headerCount) {

            let countText =
                headerCount.textContent
                    .replace(
                        /[^0-9]/g,
                        ''
                    );

            let count =
                parseInt(countText) || 0;

            count--;

            headerCount.textContent =
                count + ' new';
        }

        /*
        Check if notification list is empty
        */

        const list =
            document.getElementById(
                'notificationList'
            );

        if (
            list &&
            list.children.length === 0
        ) {

            list.innerHTML = `

    <div class='notification-empty'>

        <div class='empty-icon'>
            🔕
        </div>

        <strong>
            You are all caught up
        </strong>

        <p>
            No new notifications
        </p>

    </div>

`;
        }

    })
           .catch(error => {

        console.error(
            'Delete notification error:',
            error
        );

          });
            }

        </script>

        ";
    }


    /*
        ========================================================
        GET NOTIFICATIONS
        ========================================================

        Notification sources:

        1. New Borrow
        2. New User
        3. New Hold

        Notifications are compared with the last
        notification check time.
    */


    private function getNotifications()
{
    $notifications = [];

    try {

        $database = new Database();

        $db = $database->getConnection();

        /*
        ========================================================
        GET ONLY UNREAD NOTIFICATIONS
        ========================================================
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
            ORDER BY created_at DESC, id DESC
            LIMIT 50
        ";

        $stmt = $db->prepare($sql);

        if (!$stmt) {
            return [];
        }

        $stmt->execute();

        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {

            $type = $row["type"] ?? "Notification";

            /*
            Notification icon
            */

            $icon = "🔔";

            if ($type === "Borrow") {
                $icon = "🔄";
            } elseif ($type === "Return") {
                $icon = "↩️";
            } elseif ($type === "Hold") {
                $icon = "🔖";
            } elseif ($type === "User") {
                $icon = "👤";
            }

            $notifications[] = [

                "id" =>
                    (int)$row["id"],

                "type" =>
                    htmlspecialchars(
                        $type,
                        ENT_QUOTES,
                        "UTF-8"
                    ),

                "title" =>
                    htmlspecialchars(
                        $row["title"] ?? "Notification",
                        ENT_QUOTES,
                        "UTF-8"
                    ),

                "message" =>
                    htmlspecialchars(
                        $row["message"] ?? "",
                        ENT_QUOTES,
                        "UTF-8"
                    ),

                "time" =>
                    htmlspecialchars(
                        $row["created_at"] ?? "",
                        ENT_QUOTES,
                        "UTF-8"
                    ),

                "icon" =>
                    $icon
            ];
        }

        $stmt->close();

    } catch (Exception $e) {

        error_log(
            "Admin Notification Error: " .
            $e->getMessage()
        );

        return [];
    }

    return $notifications;
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

        $database = new Database();

        $db = $database->getConnection();

        $sql = "
            UPDATE notifications
            SET is_read = 1
            WHERE is_read = 0
        ";

        $stmt = $db->prepare($sql);

        if (!$stmt) {

            echo json_encode([
                "success" => false,
                "message" => "Unable to prepare query"
            ]);

            exit;
        }

        $stmt->execute();

        $stmt->close();

        echo json_encode([
            "success" => true
        ]);

    } catch (Exception $e) {

        error_log(
            "Mark Notifications Read Error: " .
            $e->getMessage()
        );

        echo json_encode([
            "success" => false,
            "message" => "Database error"
        ]);
    }

    exit;
}

/*
========================================================
DELETE SINGLE NOTIFICATION
========================================================
*/

public function deleteNotification($id)
{
    AuthMiddleware::check();
    AdminMiddleware::check();

    header(
        "Content-Type: application/json"
    );

    $notificationId = (int)$id;

    if ($notificationId <= 0) {

        echo json_encode([
            "success" => false,
            "message" => "Invalid notification ID"
        ]);

        exit;
    }

    try {

        $database = new Database();

        $db = $database->getConnection();

        $sql = "
            DELETE FROM notifications
            WHERE id = ?
        ";

        $stmt = $db->prepare($sql);

        if (!$stmt) {

            echo json_encode([
                "success" => false,
                "message" => "Unable to prepare query"
            ]);

            exit;
        }

        $stmt->bind_param(
            "i",
            $notificationId
        );

        $stmt->execute();

        $deleted = $stmt->affected_rows > 0;

        $stmt->close();

        echo json_encode([
            "success" => $deleted
        ]);

    } catch (Exception $e) {

        error_log(
            "Delete Notification Error: " .
            $e->getMessage()
        );

        echo json_encode([
            "success" => false,
            "message" => "Database error"
        ]);
    }

    exit;
}
}
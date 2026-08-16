<?php

/*
============================================================
ADMIN DASHBOARD VIEW
============================================================

This file contains ONLY the dashboard HTML.

Controller:
    AdminController::dashboard()

Variables supplied by Controller:
    $profilePhotoHtml
    $adminId
    $username
    $name
    $email
    $localRole
    $borrowDays
    $notificationCount
    $notificationBadge
    $notificationItems
    $badgeHtml

============================================================
*/

?>

<link
    rel="stylesheet"
    href="/assets/css/admin.css"
>

<link
    rel="stylesheet"
    href="/assets/css/notification.css"
>

<link
    rel="stylesheet"
    href="/assets/css/admin-profile.css"
>


<div class="admin-layout">


    <!-- =====================================================
         SIDEBAR
         ===================================================== -->

    <aside class="admin-sidebar">


        <!-- BRAND -->

        <div class="admin-brand">

            <div class="admin-brand-icon">
                📚
            </div>

            <div>

                <div class="admin-brand-title">
                    LIBRARY
                </div>

                <div class="admin-brand-subtitle">
                    ADMIN PANEL
                </div>

            </div>

        </div>


        <!-- =================================================
             PROFILE CARD
             ================================================= -->

        <button
            type="button"
            class="admin-profile-card"
            onclick="openAdminProfileModal()"
        >

            <div
                class="profile-avatar"
            >

                <?= $profilePhotoHtml ?>

            </div>


            <div class="profile-name">
                <?= $name ?>
            </div>


            <div class="profile-username">
                @<?= $username ?>
            </div>


            <div class="profile-status">

                <span class="online-dot"></span>

                Online

            </div>


            <div class="profile-info">


                <div>

                    <span>
                        ID
                    </span>

                    <strong>
                        #<?= $adminId ?>
                    </strong>

                </div>


                <div>

                    <span>
                        ROLE
                    </span>

                    <strong>
                        <?= $localRole ?>
                    </strong>

                </div>


            </div>


        </button>


        <!-- =================================================
             NAVIGATION
             ================================================= -->

        <nav class="admin-nav">


            <div class="nav-section-title">
                MAIN MENU
            </div>


            <a
                href="?url=AdminController/dashboard"
                class="admin-nav-item active"
            >

                <span class="nav-icon">
                    🏠
                </span>

                <span>
                    Dashboard
                </span>

            </a>


            <a
                href="?url=BookController/index"
                class="admin-nav-item"
            >

                <span class="nav-icon">
                    📚
                </span>

                <span>
                    Books
                </span>

            </a>


            <a
                href="?url=UserController/index"
                class="admin-nav-item"
            >

                <span class="nav-icon">
                    👥
                </span>

                <span>
                    Users
                </span>

            </a>


            <a
                href="?url=BorrowController/all"
                class="admin-nav-item"
            >

                <span class="nav-icon">
                    🔄
                </span>

                <span>
                    Borrowing
                </span>

            </a>


            <a
                href="?url=HoldController/index"
                class="admin-nav-item"
            >

                <span class="nav-icon">
                    🔖
                </span>

                <span>
                    Holds
                </span>

            </a>


            <div class="nav-section-title nav-section-space">
                DISTRIBUTED SYSTEM
            </div>


            <a
                href="?url=SearchController/index"
                class="admin-nav-item"
            >

                <span class="nav-icon">
                    🔎
                </span>

                <span>
                    Parallel Search
                </span>

            </a>


            <a
                href="?url=DashboardController/index"
                class="admin-nav-item"
            >

                <span class="nav-icon">
                    📊
                </span>

                <span>
                    Performance
                </span>

            </a>


        </nav>


        <!-- =================================================
             SIDEBAR FOOTER
             ================================================= -->

        <div class="admin-sidebar-footer">

            <a
                href="?url=AuthController/logout"
                class="logout-link"
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



    <!-- =====================================================
         MAIN
         ===================================================== -->

    <main class="admin-main">


        <!-- =================================================
             TOP BAR
             ================================================= -->

        <header class="admin-topbar">


            <div class="topbar-search">

                <span>
                    🔎
                </span>

                <input
                    type="text"
                    placeholder="Search books, users, requests..."
                >

            </div>


            <div class="topbar-actions">


                <!-- =================================================
                     NOTIFICATIONS
                     ================================================= -->

                <div
                    class="notification-wrapper"
                    id="notificationWrapper"
                >

                    <button
                        class="notification-btn"
                        id="notificationButton"
                        type="button"
                        aria-label="Notifications"
                    >

                        <span class="notification-bell">
                            🔔
                        </span>

                        <?= $badgeHtml ?>

                    </button>


                    <div
                        class="notification-panel"
                        id="notificationDropdown"
                    >

                        <div class="notification-header">

                            <div>

                                <strong>
                                    Notifications
                                </strong>

                                <small id="notificationHeaderCount">
                                    <?= $notificationCount ?> new
                                </small>

                            </div>


                            <button
                                type="button"
                                class="notification-read-btn"
                                id="markNotificationsRead"
                            >
                                Mark all as read
                            </button>

                        </div>


                        <div
                            class="notification-list"
                            id="notificationList"
                        >

                            <?= $notificationItems ?>

                        </div>

                    </div>

                </div>



                <!-- =================================================
                     TOP RIGHT PROFILE
                     ================================================= -->

                <button
                    type="button"
                    class="topbar-user"
                    onclick="openAdminProfileModal()"
                >

                    <div class="topbar-avatar">

                        <?= $profilePhotoHtml ?>

                    </div>


                    <div>

                        <strong>
                            <?= $name ?>
                        </strong>

                        <small>
                            <?= $localRole ?>
                        </small>

                    </div>

                </button>


            </div>


        </header>



        <!-- =====================================================
             DASHBOARD CONTENT
             ===================================================== -->

        <div class="admin-content">


            <!-- WELCOME -->

            <section class="welcome-section">

                <div>

                    <div class="welcome-label">
                        ADMINISTRATOR
                    </div>


                    <h1>
                        Welcome back, <?= $name ?> 👋
                    </h1>


                    <p>
                        Manage your distributed library
                        system from one place.
                    </p>

                </div>


                <div class="welcome-book">
                    📚
                </div>

            </section>



            <!-- =================================================
                 STAT CARDS
                 ================================================= -->

            <section class="stats-grid">


                <a
                    href="?url=BookController/index"
                    class="admin-stat-card"
                >

                    <div class="stat-icon books-icon">
                        📚
                    </div>

                    <div>

                        <div class="stat-label">
                            LIBRARY
                        </div>

                        <div class="stat-title">
                            Books
                        </div>

                        <div class="stat-action">
                            Manage books →
                        </div>

                    </div>

                </a>



                <a
                    href="?url=UserController/index"
                    class="admin-stat-card"
                >

                    <div class="stat-icon users-icon">
                        👥
                    </div>

                    <div>

                        <div class="stat-label">
                            SYSTEM
                        </div>

                        <div class="stat-title">
                            Users
                        </div>

                        <div class="stat-action">
                            Manage users →
                        </div>

                    </div>

                </a>



                <a
                    href="?url=BorrowController/all"
                    class="admin-stat-card"
                >

                    <div class="stat-icon borrow-icon">
                        🔄
                    </div>

                    <div>

                        <div class="stat-label">
                            LIBRARY
                        </div>

                        <div class="stat-title">
                            Borrowing
                        </div>

                        <div class="stat-action">
                            View activity →
                        </div>

                    </div>

                </a>



                <a
                    href="?url=HoldController/index"
                    class="admin-stat-card"
                >

                    <div class="stat-icon hold-icon">
                        🔖
                    </div>

                    <div>

                        <div class="stat-label">
                            LIBRARY
                        </div>

                        <div class="stat-title">
                            Holds
                        </div>

                        <div class="stat-action">
                            View requests →
                        </div>

                    </div>

                </a>


            </section>



            <!-- =================================================
                 QUICK MANAGEMENT
                 ================================================= -->

            <section class="dashboard-section">


                <div class="section-heading">

                    <div>

                        <h2>
                            Quick Management
                        </h2>

                        <p>
                            Frequently used library operations
                        </p>

                    </div>

                </div>


                <div class="management-grid">


                    <!-- BOOK -->

                    <div class="management-card">

                        <div class="management-card-header">

                            <div class="management-icon">
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


                        <div class="management-actions">

                            <a
                                href="?url=BookController/create"
                                class="management-action primary"
                            >
                                ➕ Add New Book
                            </a>


                            <a
                                href="?url=BookController/index"
                                class="management-action"
                            >
                                📖 View All Books
                            </a>

                        </div>

                    </div>



                    <!-- USER -->

                    <div class="management-card">

                        <div class="management-card-header">

                            <div class="management-icon">
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


                        <div class="management-actions">

                            <a
                                href="?url=UserController/index"
                                class="management-action primary"
                            >
                                👥 View All Users
                            </a>


                            <a
                                href="?url=UserController/create"
                                class="management-action"
                            >
                                ➕ Add New User
                            </a>

                        </div>

                    </div>



                    <!-- BORROW -->

                    <div class="management-card">

                        <div class="management-card-header">

                            <div class="management-icon">
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


                        <div class="management-actions">

                            <a
                                href="?url=BorrowController/all"
                                class="management-action primary"
                            >
                                📋 Borrow History
                            </a>


                            <a
                                href="?url=BorrowController/pending"
                                class="management-action"
                            >
                                ⏳ Borrow Requests
                            </a>

                        </div>

                    </div>



                    <!-- HOLD -->

                    <div class="management-card">

                        <div class="management-card-header">

                            <div class="management-icon">
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


                        <div class="management-actions">

                            <a
                                href="?url=HoldController/index"
                                class="management-action primary"
                            >
                                🔖 View Hold Requests
                            </a>

                        </div>

                    </div>


                </div>

            </section>



            <!-- =================================================
                 DISTRIBUTED SYSTEM
                 ================================================= -->

            <section class="dashboard-section">


                <div class="section-heading">

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


                <div class="distributed-grid">


                    <a
                        href="?url=SearchController/index"
                        class="distributed-card"
                    >

                        <div class="distributed-icon">
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
                        href="?url=DashboardController/index"
                        class="distributed-card"
                    >

                        <div class="distributed-icon">
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



            <!-- =================================================
                 ACCOUNT INFORMATION
                 ================================================= -->

            <section class="account-card">


                <div class="account-icon">
                    👤
                </div>


                <div class="account-details">

                    <span>
                        Signed in as
                    </span>

                    <strong>
                        <?= $name ?>
                    </strong>

                    <small>
                        <?= $email ?>
                    </small>

                </div>


                <div class="account-role">

                    <span>
                        Local Role
                    </span>

                    <strong>
                        <?= $localRole ?>
                    </strong>

                </div>


            </section>


        </div>

    </main>

</div>



<!-- ==========================================================
     ADMIN PROFILE MODAL
     
     IMPORTANT:
     "admin-profile-modal" is HIDDEN by CSS.
     It will ONLY appear when openAdminProfileModal()
     is called.
     ========================================================== -->

<div
    id="adminProfileModal"
    class="admin-profile-modal"
    aria-hidden="true"
>


    <!-- Overlay -->

    <div
        class="admin-profile-overlay"
        onclick="closeAdminProfileModal()"
    ></div>



    <!-- Modal -->

    <div
        class="admin-profile-dialog"
        role="dialog"
        aria-modal="true"
        aria-labelledby="adminProfileTitle"
    >


        <!-- HEADER -->

        <div class="admin-profile-header">


            <div>

                <div
                    class="admin-profile-title"
                    id="adminProfileTitle"
                >
                    👤 Profile
                </div>


                <div class="admin-profile-subtitle">
                    Update your administrator account
                </div>

            </div>


            <button
                type="button"
                class="admin-profile-close"
                onclick="closeAdminProfileModal()"
                aria-label="Close"
            >
                ×
            </button>


        </div>



        <!-- FORM -->

        <form
    method="POST"
    action="?url=AdminController/updateProfile"
    enctype="multipart/form-data"
>


            <!-- =================================================
                 PROFILE PHOTO
                 ================================================= -->

            <div class="admin-profile-photo-section">


                <div class="admin-profile-preview">

                    <?= $profilePhotoHtml ?>

                </div>


                <div class="admin-profile-photo-info">

                    <strong>
                        Profile Photo
                    </strong>


                    <span>
                        JPG, PNG or WEBP
                    </span>


                    <span>
                        Maximum 2MB
                    </span>


                    <label
                        for="adminProfilePhoto"
                        class="admin-photo-upload-button"
                    >
                        📷 Upload Photo
                    </label>


                   <input
    type="file"
    name="profile_photo"
    accept=".jpg,.jpeg,.png,.webp"
>


                    <span
                        id="adminProfileFileName"
                        class="admin-profile-file-name"
                    >
                        No file chosen
                    </span>

                </div>


            </div>



            <!-- =================================================
                 USERNAME
                 ================================================= -->

            <div class="admin-profile-field">

                <label for="adminUsername">
                    Username
                </label>


                <input
    type="text"
    name="username"
    value="<?= htmlspecialchars($user["username"] ?? "") ?>"
    required
>

            </div>

            <div class="profile-form-group">

    <label for="profileName">
        Full Name
    </label>

    <input
        type="text"
        id="profileName"
        name="name"
        value="<?= htmlspecialchars(
            $user["name"] ?? "",
            ENT_QUOTES,
            "UTF-8"
        ) ?>"
        required
    >

</div>



            <!-- =================================================
                 EMAIL
                 ================================================= -->

            <div class="admin-profile-field">

                <label for="adminEmail">
                    Email
                </label>


                <input
    type="email"
    name="email"
    value="<?= htmlspecialchars($user["email"] ?? "") ?>"
    required
>

            </div>



            <!-- =================================================
                 PASSWORD
                 ================================================= -->

            <div class="admin-profile-field">

                <label for="adminPassword">
                    New Password
                </label>


                <div class="admin-password-wrapper">


                    <input
    type="password"
    name="password"
    placeholder="Leave empty to keep current password"
>


                    <button
                        type="button"
                        class="admin-password-toggle"
                        onclick="toggleAdminPassword()"
                        aria-label="Show password"
                    >
                        👁
                    </button>


                </div>


                <small>
                    Leave this empty if you do not want
                    to change your password.
                </small>

            </div>



            <!-- =================================================
                 BORROW DAYS
                 ================================================= -->

            <div class="admin-profile-field">

                <label for="adminBorrowDays">
                    Borrow Duration
                </label>


                <input
    type="number"
    name="borrow_days"
    value="<?= (int)($borrowDays ?? 4) ?>"
    min="1"
    max="365"
    required
>


                <small>
                    Number of days a user can keep a borrowed book.
                </small>

            </div>



            <!-- =================================================
                 ACTIONS
                 ================================================= -->

            <div class="admin-profile-actions">


                <button
                    type="button"
                    class="admin-profile-cancel"
                    onclick="closeAdminProfileModal()"
                >
                    Cancel
                </button>


                <button
    type="submit"
    class="profile-save-btn"
>
    💾 Save Changes
</button>


            </div>


        </form>


    </div>

</div>



<!-- ==========================================================
     JAVASCRIPT
     ========================================================== -->

<script>

/*
==============================================================
OPEN PROFILE MODAL
==============================================================
*/

function openAdminProfileModal()
{

    const modal =
        document.getElementById(
            "adminProfileModal"
        );


    if (!modal) {
        return;
    }


    modal.classList.add(
        "show"
    );


    modal.setAttribute(
        "aria-hidden",
        "false"
    );


    document.body.classList.add(
        "admin-profile-modal-open"
    );

}



/*
==============================================================
CLOSE PROFILE MODAL
==============================================================
*/

function closeAdminProfileModal()
{

    const modal =
        document.getElementById(
            "adminProfileModal"
        );


    if (!modal) {
        return;
    }


    modal.classList.remove(
        "show"
    );


    modal.setAttribute(
        "aria-hidden",
        "true"
    );


    document.body.classList.remove(
        "admin-profile-modal-open"
    );

}



/*
==============================================================
ESC KEY
==============================================================
*/

document.addEventListener(
    "keydown",
    function(event)
    {

        if (
            event.key === "Escape"
        ) {

            closeAdminProfileModal();

        }

    }
);



/*
==============================================================
PASSWORD SHOW / HIDE
==============================================================
*/

function toggleAdminPassword()
{

    const password =
        document.getElementById(
            "adminPassword"
        );


    if (!password) {
        return;
    }


    if (
        password.type === "password"
    ) {

        password.type =
            "text";

    } else {

        password.type =
            "password";

    }

}



/*
==============================================================
PROFILE PHOTO FILE NAME
==============================================================
*/

document.addEventListener(
    "DOMContentLoaded",
    function()
    {

        const input =
            document.getElementById(
                "adminProfilePhoto"
            );


        const fileName =
            document.getElementById(
                "adminProfileFileName"
            );


        if (
            input &&
            fileName
        ) {

            input.addEventListener(
                "change",
                function()
                {

                    if (
                        input.files &&
                        input.files.length > 0
                    ) {

                        fileName.textContent =
                            input.files[0].name;

                    } else {

                        fileName.textContent =
                            "No file chosen";

                    }

                }
            );

        }

    }
);



/*
==============================================================
NOTIFICATIONS
==============================================================
*/

document.addEventListener(
    "DOMContentLoaded",
    function()
    {

        const button =
            document.getElementById(
                "notificationButton"
            );


        const dropdown =
            document.getElementById(
                "notificationDropdown"
            );


        const markRead =
            document.getElementById(
                "markNotificationsRead"
            );


        const badge =
            document.getElementById(
                "notificationBadge"
            );


        /*
        ------------------------------------------------------
        Open notification
        ------------------------------------------------------
        */

        if (
            button &&
            dropdown
        ) {

            button.addEventListener(
                "click",
                function(event)
                {

                    event.stopPropagation();


                    dropdown.classList.toggle(
                        "show"
                    );

                }
            );

        }


        /*
        ------------------------------------------------------
        Prevent close inside dropdown
        ------------------------------------------------------
        */

        if (dropdown) {

            dropdown.addEventListener(
                "click",
                function(event)
                {

                    event.stopPropagation();

                }
            );

        }


        /*
        ------------------------------------------------------
        Close notification outside
        ------------------------------------------------------
        */

        document.addEventListener(
            "click",
            function()
            {

                if (
                    dropdown &&
                    dropdown.classList.contains(
                        "show"
                    )
                ) {

                    dropdown.classList.remove(
                        "show"
                    );

                }

            }
        );


        /*
        ------------------------------------------------------
        Mark all read
        ------------------------------------------------------
        */

        if (markRead) {

            markRead.addEventListener(
                "click",
                function()
                {

                    fetch(
                        "?url=AdminController/markNotificationsRead",
                        {
                            method: "POST",

                            headers: {
                                "X-Requested-With":
                                    "XMLHttpRequest"
                            }
                        }
                    )
                    .then(
                        response =>
                            response.json()
                    )
                    .then(
                        data =>
                        {

                            if (
                                data.success
                            ) {

                                if (badge) {
                                    badge.remove();
                                }


                                const count =
                                    document.getElementById(
                                        "notificationHeaderCount"
                                    );


                                if (count) {

                                    count.textContent =
                                        "0 new";

                                }


                                const list =
                                    document.getElementById(
                                        "notificationList"
                                    );


                                if (list) {

                                    list.innerHTML = `

                                        <div class="notification-empty">

                                            <div class="notification-empty-icon">
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
                        error =>
                        {

                            console.error(
                                "Notification error:",
                                error
                            );

                        }
                    );

                }
            );

        }

    }
);



/*
==============================================================
DELETE NOTIFICATION
==============================================================
*/

function deleteNotification(
    notificationId
)
{

    if (!notificationId) {
        return;
    }


    fetch(
        "?url=AdminController/deleteNotification/"
            + notificationId,
        {
            method: "POST",

            headers: {
                "X-Requested-With":
                    "XMLHttpRequest"
            }
        }
    )
    .then(
        response =>
            response.json()
    )
    .then(
        data =>
        {

            if (!data.success) {

                return;

            }


            const item =
                document.getElementById(
                    "notification-item-"
                    + notificationId
                );


            if (item) {
                item.remove();
            }


            const badge =
                document.getElementById(
                    "notificationBadge"
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

                    badge.textContent =
                        count;

                }

            }


            const headerCount =
                document.getElementById(
                    "notificationHeaderCount"
                );


            if (headerCount) {

                let count =
                    parseInt(
                        headerCount.textContent
                    ) || 0;


                count--;


                if (count < 0) {
                    count = 0;
                }


                headerCount.textContent =
                    count + " new";

            }


            const list =
                document.getElementById(
                    "notificationList"
                );


            if (
                list &&
                list.children.length === 0
            ) {

                list.innerHTML = `

                    <div class="notification-empty">

                        <div class="notification-empty-icon">
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
    )
    .catch(
        error =>
        {

            console.error(
                "Delete notification error:",
                error
            );

        }
    );

}

</script>
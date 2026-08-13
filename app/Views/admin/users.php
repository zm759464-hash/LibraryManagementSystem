<?php

/*
|--------------------------------------------------------------------------
| Admin User Management
|--------------------------------------------------------------------------
| Expected:
| $users = mysqli_result
|--------------------------------------------------------------------------
*/

$usersList = [];

if (isset($users) && $users instanceof mysqli_result) {
    while ($user = $users->fetch_assoc()) {
        $usersList[] = $user;
    }
} elseif (isset($users) && is_array($users)) {
    $usersList = $users;
}


/*
|--------------------------------------------------------------------------
| Statistics
|--------------------------------------------------------------------------
*/

$totalUsers = count($usersList);
$totalAdmins = 0;
$totalGlobalUsers = 0;
$totalLocalAdmins = 0;

foreach ($usersList as $user) {

    if (($user['role'] ?? '') === 'admin') {
        $totalAdmins++;
    }

    if (($user['global_role'] ?? '') === 'global_user') {
        $totalGlobalUsers++;
    }

    if (($user['local_role'] ?? '') === 'local_admin') {
        $totalLocalAdmins++;
    }
}


/*
|--------------------------------------------------------------------------
| Helper
|--------------------------------------------------------------------------
*/

function getInitial($name)
{
    $name = trim($name ?? '');

    if ($name === '') {
        return '?';
    }

    return strtoupper(substr($name, 0, 1));
}

?>

<link rel="stylesheet" href="/assets/css/user-management.css">


<div class="user-management-page">

    <!-- =========================================================
         HEADER
    ========================================================== -->

    <div class="um-header">

        <div class="um-header-left">

            <div class="um-header-icon">
                👥
            </div>

            <div>
                <h1 class="um-title">
                    User Management
                </h1>

                <p class="um-subtitle">
                    Manage system users, local administrators and global users.
                </p>
            </div>

        </div>


        <div class="um-header-actions">

            <a
                href="/UserController/create"
                class="um-btn um-btn-primary">
                ➕ Add User
            </a>

        </div>

    </div>


    <!-- =========================================================
         SUMMARY CARDS
    ========================================================== -->

    <div class="um-summary">

        <div class="um-summary-card">

            <div class="um-summary-icon">
                👥
            </div>

            <div>

                <div class="um-summary-label">
                    Total Users
                </div>

                <div class="um-summary-value">
                    <?= $totalUsers ?>
                </div>

            </div>

        </div>


        <div class="um-summary-card">

            <div class="um-summary-icon">
                🛡️
            </div>

            <div>

                <div class="um-summary-label">
                    Administrators
                </div>

                <div class="um-summary-value">
                    <?= $totalAdmins ?>
                </div>

            </div>

        </div>


        <div class="um-summary-card">

            <div class="um-summary-icon">
                🌐
            </div>

            <div>

                <div class="um-summary-label">
                    Global Users
                </div>

                <div class="um-summary-value">
                    <?= $totalGlobalUsers ?>
                </div>

            </div>

        </div>

    </div>


    <!-- =========================================================
         TOOLBAR
    ========================================================== -->

    <div class="um-toolbar">

        <div class="um-search">

            <span class="um-search-icon">
                🔍
            </span>

            <input
                type="text"
                id="userSearch"
                placeholder="Search users by name, username or email..."
                autocomplete="off">

        </div>


        <div class="um-toolbar-text">

            <?= $totalUsers ?>
            user<?= $totalUsers !== 1 ? 's' : '' ?>
            found

        </div>

    </div>


    <!-- =========================================================
         USER TABLE
    ========================================================== -->

    <div class="um-table-container">

        <?php if (empty($usersList)): ?>

            <div class="um-empty">

                <div class="um-empty-icon">
                    👤
                </div>

                <h3>
                    No Users Found
                </h3>

                <p>
                    There are currently no users in the system.
                </p>

            </div>

        <?php else: ?>

            <div class="um-table-wrapper">

                <table class="um-table" id="usersTable">

                    <thead>

                        <tr>

                            <th>
                                ID
                            </th>

                            <th>
                                User
                            </th>

                            <th>
                                Email
                            </th>

                            <th>
                                Role
                            </th>

                            <th>
                                Local Role
                            </th>

                            <th>
                                Global Role
                            </th>

                            <th>
                                Actions
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        <?php foreach ($usersList as $user): ?>

                            <?php

                            $id =
                                $user['id'] ?? '';

                            $username =
                                $user['username'] ?? '';

                            $name =
                                $user['name'] ?? '';

                            $email =
                                $user['email'] ?? '';

                            $role =
                                $user['role'] ?? '';

                            $localRole =
                                $user['local_role'] ?? '';

                            $globalRole =
                                $user['global_role'] ?? '';

                            ?>

                            <tr
                                class="user-row"
                                data-search="
                                <?= htmlspecialchars(
                                    strtolower(
                                        $username . ' ' .
                                            $name . ' ' .
                                            $email . ' ' .
                                            $role . ' ' .
                                            $localRole . ' ' .
                                            $globalRole
                                    ),
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            ">

                                <!-- ID -->

                                <td>

                                    <span class="um-user-id">
                                        #<?= htmlspecialchars($id) ?>
                                    </span>

                                </td>


                                <!-- USER -->

                                <td>

                                    <div class="um-user-cell">

                                        <div class="um-avatar">

                                            <?= htmlspecialchars(
                                                getInitial($name ?: $username)
                                            ) ?>

                                        </div>


                                        <div>

                                            <div class="um-user-name">

                                                <?= htmlspecialchars(
                                                    $name ?: 'Unnamed User'
                                                ) ?>

                                            </div>


                                            <?php if ($username !== ''): ?>

                                                <div class="um-username">

                                                    @<?= htmlspecialchars(
                                                            $username
                                                        ) ?>

                                                </div>

                                            <?php endif; ?>

                                        </div>

                                    </div>

                                </td>


                                <!-- EMAIL -->

                                <td>

                                    <span class="um-email">

                                        <?= htmlspecialchars(
                                            $email ?: '-'
                                        ) ?>

                                    </span>

                                </td>


                                <!-- ROLE -->

                                <td>

                                    <?php if ($role === 'admin'): ?>

                                        <span class="um-badge um-badge-admin">
                                            🛡️ Admin
                                        </span>

                                    <?php elseif ($role === 'user'): ?>

                                        <span class="um-badge um-badge-user">
                                            👤 User
                                        </span>

                                    <?php else: ?>

                                        <span class="um-badge um-badge-empty">
                                            <?= htmlspecialchars(
                                                $role ?: 'N/A'
                                            ) ?>
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- LOCAL ROLE -->

                                <td>

                                    <?php if ($localRole === 'local_admin'): ?>

                                        <span class="um-badge um-badge-local">
                                            🏠 Local Admin
                                        </span>

                                    <?php else: ?>

                                        <span class="um-badge um-badge-empty">
                                            —
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- GLOBAL ROLE -->

                                <td>

                                    <?php if ($globalRole === 'global_user'): ?>

                                        <span class="um-badge um-badge-global">
                                            🌐 Global User
                                        </span>

                                    <?php else: ?>

                                        <span class="um-badge um-badge-empty">
                                            —
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- ACTIONS -->

                                <td>

                                    <div class="um-actions">

                                        <a
                                            href="/UserController/edit/<?= urlencode($id) ?>"
                                            class="um-action um-action-edit"
                                            title="Edit User">
                                            ✏️
                                        </a>


                                        <a
                                            href="/UserController/delete/<?= urlencode($id) ?>"
                                            class="um-action um-action-delete"
                                            title="Delete User"
                                            onclick="
                                            return confirm(
                                                'Are you sure you want to delete this user?'
                                            );
                                        ">
                                            🗑️
                                        </a>

                                    </div>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        <?php endif; ?>

    </div>


    <!-- =========================================================
         FOOTER
    ========================================================== -->

    <div class="um-footer">

        <span>
            Library Management System
        </span>

        <span>
            <?= $totalUsers ?> total users
        </span>

    </div>

</div>


<script>
    document.addEventListener(
        "DOMContentLoaded",
        function() {

            const searchInput =
                document.getElementById("userSearch");

            const rows =
                document.querySelectorAll(".user-row");


            if (!searchInput) {
                return;
            }


            searchInput.addEventListener(
                "input",
                function() {

                    const keyword =
                        this.value
                        .toLowerCase()
                        .trim();


                    rows.forEach(
                        function(row) {

                            const searchText =
                                row.dataset.search || "";


                            if (
                                searchText.includes(keyword)
                            ) {

                                row.style.display = "";

                            } else {

                                row.style.display = "none";

                            }

                        }
                    );

                }
            );

        }
    );
</script>
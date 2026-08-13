<?php
$userData = $user ?? ($_SESSION["user"] ?? []);
$dashboardUserId = htmlspecialchars($userData["id"] ?? $userData["user_id"] ?? "-");
$dashboardUsername = htmlspecialchars($userData["username"] ?? "-");
$dashboardName = htmlspecialchars($userData["name"] ?? "User");
$dashboardEmail = htmlspecialchars($userData["email"] ?? "-");
$dashboardGlobalRole = htmlspecialchars($userData["global_role"] ?? "global_user");
$dashboardInitial = strtoupper(substr($userData["name"] ?? "U", 0, 1));
?>

<link rel='stylesheet' href='assets/css/user-management.css'>
<link rel='stylesheet' href='assets/css/user-dashboard.css'>

<?php $theme = $_SESSION['theme'] ?? 'light'; ?>
<div class='library-dashboard <?php echo $theme === 'dark' ? 'theme-dark' : ''; ?>'>
    <aside class='library-sidebar'>
        <div class='library-logo'>
            <div class='library-logo-icon'>📚</div>
            <div>
                <div class='library-logo-title'>LIBRARY</div>
                <div class='library-logo-subtitle'>Management System</div>
            </div>
        </div>

        <div class='user-profile' style='padding:22px 16px 18px;background:rgba(255,255,255,0.08);border-radius:22px;text-align:center;'>
            <?php
            $profileImage = $userData['profile_image'] ?? '';
            if (empty($profileImage)) {
                $profileId = $userData['id'] ?? $userData['user_id'] ?? null;
                if (!empty($profileId)) {
                    $profileDir = __DIR__ . '/../../../public/assets/images/profiles/';
                    $matches = glob($profileDir . $profileId . '.*');
                    if (!empty($matches)) {
                        $profileImage = 'assets/images/profiles/' . basename($matches[0]);
                    }
                }
            }
            $profilePathAbs = __DIR__ . '/../../../public/' . ($profileImage ?: '');
            ?>
            <a href='?url=UserController/settings' style='text-decoration:none;color:inherit;'>
                <div style='position:relative;display:inline-block;margin:0 auto 12px;'>
                    <?php if (!empty($profileImage) && file_exists($profilePathAbs)) : ?>
                        <img src='<?php echo $profileImage; ?>' alt='Profile' style='width:78px;height:78px;border-radius:50%;object-fit:cover;border:4px solid rgba(255,255,255,0.12);display:block;'>
                    <?php else : ?>
                        <div class='user-avatar' style='width:78px;height:78px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:28px;margin:0 auto;background:rgba(255,255,255,0.12);'><?php echo $dashboardInitial; ?></div>
                    <?php endif; ?>

                    <span class='user-status' title='Online' style='position:absolute;right:-2px;bottom:-2px;width:14px;height:14px;background:#10b981;border-radius:50%;display:inline-block;box-shadow:0 0 0 3px rgba(16,185,129,0.12);border:2px solid #fff;'></span>
                </div>
            </a>
            <div class='user-name' style='font-weight:700;font-size:16px;margin-top:4px;'><?php echo $dashboardName; ?></div>
        </div>

        <div class='sidebar-menu'>
            <div class='sidebar-menu-title'>MENU</div>
            <a class='sidebar-link active' href='?url=UserController/dashboard'>
                <span class='sidebar-icon'>🏠</span> Dashboard
            </a>
            <a class='sidebar-link' href='?url=UserController/books'>
                <span class='sidebar-icon'>📚</span> Browse Books
            </a>
            <a class='sidebar-link' href='?url=SearchController/index'>
                <span class='sidebar-icon'>🔍</span> Search Books
            </a>
            <a class='sidebar-link' href='?url=BorrowController/history'>
                <span class='sidebar-icon'>📖</span> Borrow History
            </a>
            <a class='sidebar-link' href='?url=ReturnController/history'>
                <span class='sidebar-icon'>↩️</span> Return History
            </a>
            <a class='sidebar-link' href='?url=HoldController/myholds'>
                <span class='sidebar-icon'>🔖</span> My Holds
            </a>
        </div>

        <div class='sidebar-bottom'>
            <a class='logout-link' href='?url=AuthController/logout'>
                <span class='sidebar-icon'>🚪</span> Logout
            </a>
        </div>
    </aside>

    <main class='library-main'>
        <div class='library-topbar'>
            <div class='welcome-text'>
                <small>Your personal library</small>
                <h1>Welcome back, <?php echo $dashboardName; ?> 👋</h1>
            </div>

            <a href='?url=SearchController/index' class='top-search'>
                <span>🔍</span>
                <span class='top-search-text'>Find your next book...</span>
            </a>
        </div>

        <div class='stats-grid'>
            <a href='?url=UserController/books' class='stat-card'>
                <div class='stat-icon'>📚</div>
                <div>
                    <div class='stat-title'>Library</div>
                    <div class='stat-value'>Explore Books</div>
                </div>
            </a>

            <a href='?url=BorrowController/history' class='stat-card'>
                <div class='stat-icon'>📖</div>
                <div>
                    <div class='stat-title'>Borrowing</div>
                    <div class='stat-value'>My History</div>
                </div>
            </a>

            <a href='?url=HoldController/myholds' class='stat-card'>
                <div class='stat-icon'>🔖</div>
                <div>
                    <div class='stat-title'>Saved</div>
                    <div class='stat-value'>My Holds</div>
                </div>
            </a>
        </div>

        <div class='section-header'>
            <h2>Explore Categories</h2>
            <a class='view-all' href='?url=UserController/books'>View all →</a>
        </div>

        <div class='category-list'>
            <a class='category-item active' href='?url=UserController/books'>All Books</a>
            <a class='category-item' href='?url=UserController/books/Technology'>💻 Technology</a>
            <a class='category-item' href='?url=UserController/books/Science'>🔬 Science</a>
            <a class='category-item' href='?url=UserController/books/Fiction'>📖 Fiction</a>
        </div>

        <div class='section-header'>
            <h2>Featured Books</h2>
            <a class='view-all' href='?url=UserController/books'>Browse library →</a>
        </div>

        <div class='book-grid'>
            <div class='book-card'>
                <div class='book-cover'>💻</div>
                <div class='book-title'>Technology Collection</div>
                <div class='book-author'>Programming & Computer Science</div>
                <div class='book-footer'>
                    <span class='available-badge'>Available</span>
                    <a class='book-button' href='?url=UserController/books'>Browse</a>
                </div>
            </div>

            <div class='book-card'>
                <div class='book-cover science'>🔬</div>
                <div class='book-title'>Science Collection</div>
                <div class='book-author'>Science & Research</div>
                <div class='book-footer'>
                    <span class='available-badge'>Available</span>
                    <a class='book-button' href='?url=UserController/books'>Browse</a>
                </div>
            </div>

            <div class='book-card'>
                <div class='book-cover fiction'>📖</div>
                <div class='book-title'>Fiction Collection</div>
                <div class='book-author'>Novels & Stories</div>
                <div class='book-footer'>
                    <span class='available-badge'>Available</span>
                    <a class='book-button' href='?url=UserController/books'>Browse</a>
                </div>
            </div>
        </div>

        <div class='section-header quick-actions-section'>
            <h2>Quick Actions</h2>
        </div>

        <div class='quick-actions'>
            <a class='quick-action' href='?url=SearchController/index'>🔍 <span>Search for a book</span></a>
            <a class='quick-action' href='?url=BorrowController/history'>📖 <span>View borrow history</span></a>
            <a class='quick-action' href='?url=HoldController/myholds'>🔖 <span>View my holds</span></a>
        </div>
    </main>
</div>
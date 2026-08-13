<?php
/*
    ========================================================
    GLOBAL DATA INITIALIZATION & AUTH CHECK
    ========================================================
*/
$data = isset($GLOBALS['data']) ? $GLOBALS['data'] : [];

if (!isset($_SESSION["user"])) {
    header("Location:?url=AuthController/login");
    exit;
}
?>
<!-- 
    ========================================================
    MODERN & CLEAN MY BORROW HISTORY VIEW
    ========================================================
-->

<link
    rel="stylesheet"
    href="/assets/css/dashboard.css">

<link
    rel="stylesheet"
    href="/assets/css/style.css">


<div
    class="borrow-history-wrapper"
    style="padding: 40px 20px; max-width: 1400px; margin: 0 auto; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f8f9fa; min-height: 100vh;">

    <!-- HEADER SECTION -->
    <div
        style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 2px solid #eef2f5; padding-bottom: 15px;">
        <div>
            <h2 style="color: #2c3e50; font-size: 28px; font-weight: 700; margin: 0; letter-spacing: 0.5px;">
                My Borrow History
            </h2>
            <p style="margin: 5px 0 0 0; color: #7f8c8d; font-size: 14px;">
                View and manage your recently borrowed library books
            </p>
        </div>

        <a
            href="?url=UserController/dashboard"
            style="display: inline-flex; align-items: center; background-color: #fff; color: #34495e; text-decoration: none; font-size: 14px; font-weight: 600; padding: 10px 18px; border-radius: 6px; border: 1px solid #dcdde1; box-shadow: 0 2px 4px rgba(0,0,0,0.02); transition: all 0.2s ease;"
            onmouseover="this.style.backgroundColor='#f1f2f6'; this.style.borderColor='#b2bec3';"
            onmouseout="this.style.backgroundColor='#fff'; this.style.borderColor='#dcdde1';">
            <span style="margin-right: 8px;">⬅</span> Back to Dashboard
        </a>
    </div>

    <!-- TABLE CONTAINER -->
    <?php if (!empty($_SESSION['flash_success'])) : ?>
        <div style="margin-bottom: 16px; padding: 14px 18px; border-radius: 10px; background: #dcfce7; color: #14532d; border: 1px solid #86efac; max-width: 1140px;">
            <?php echo htmlspecialchars($_SESSION['flash_success'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['flash_success']); ?>
        </div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['flash_error'])) : ?>
        <div style="margin-bottom: 16px; padding: 14px 18px; border-radius: 10px; background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; max-width: 1140px;">
            <?php echo htmlspecialchars($_SESSION['flash_error'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['flash_error']); ?>
        </div>
    <?php endif; ?>
    <div
        style="background: #ffffff; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); overflow: hidden; border: 1px solid #e1e8ed;">
        <table
            style="width: 100%; border-collapse: collapse; text-align: left; margin: 0;">
            <thead>
                <tr style="background: linear-gradient(135deg, #3f51b5, #5c6bc0); color: #ffffff;">
                    <th style="padding: 18px 20px; font-size: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; width: 16%;">Book ID</th>
                    <th style="padding: 18px 20px; font-size: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; width: 24%;">Title</th>
                    <th style="padding: 18px 20px; font-size: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; width: 16%;">Category</th>
                    <th style="padding: 18px 20px; font-size: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; width: 14%;">Borrow Date</th>
                    <th style="padding: 18px 20px; font-size: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; width: 14%;">Return Date</th>
                    <th style="padding: 18px 20px; font-size: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; width: 10%; text-align: center;">Status</th>
                    <th style="padding: 18px 20px; font-size: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; width: 12%; text-align: center;">Action</th>
                </tr>
            </thead>

            <tbody>
                <?php
                $found = false;
                $counter = 0;
                ?>

                <?php foreach ($data as $row): ?>
                    <?php
                    $found = true;
                    $counter++;
                    // 行ごとに背景色ကို ဖြည့်ပေးခြင်း (Zebra Striping)
                    $rowBg = ($counter % 2 === 0) ? '#f8f9fa' : '#ffffff';
                    ?>

                    <tr
                        style="border-bottom: 1px solid #eceff1; background-color: <?php echo $rowBg; ?>; transition: background-color 0.2s;"
                        onmouseover="this.style.backgroundColor='#f1f3f9';"
                        onmouseout="this.style.backgroundColor='<?php echo $rowBg; ?>';">
                        <!-- BOOK ID -->
                        <td style="padding: 16px 20px; color: #475569; font-family: 'Courier New', Courier, monospace; font-weight: 600; font-size: 14px;">
                            <?php echo !empty($row["global_book_id"]) ? htmlspecialchars($row["global_book_id"], ENT_QUOTES, "UTF-8") : "-"; ?>
                        </td>

                        <!-- TITLE -->
                        <td style="padding: 16px 20px; font-weight: 600; color: #1e293b; font-size: 15px;">
                            <span style="display: inline-flex; align-items: center;">
                                <span style="margin-right: 10px; font-size: 16px;">📖</span>
                                <?php echo !empty($row["title"]) ? htmlspecialchars($row["title"], ENT_QUOTES, "UTF-8") : "-"; ?>
                            </span>
                        </td>

                        <!-- CATEGORY -->
                        <td style="padding: 16px 20px; color: #64748b; font-size: 14px;">
                            <span style="background-color: #f1f5f9; padding: 4px 10px; border-radius: 20px; border: 1px solid #e2e8f0; font-size: 13px;">
                                <?php echo !empty($row["category"]) ? htmlspecialchars($row["category"], ENT_QUOTES, "UTF-8") : "-"; ?>
                            </span>
                        </td>

                        <!-- BORROW DATE -->
                        <td style="padding: 16px 20px; color: #334155; font-size: 14px;">
                            <?php echo !empty($row["borrow_date"]) ? htmlspecialchars($row["borrow_date"], ENT_QUOTES, "UTF-8") : "-"; ?>
                        </td>

                        <!-- RETURN DATE -->
                        <td style="padding: 16px 20px; color: #334155; font-size: 14px;">
                            <?php
                            if (!empty($row["return_date"]) && $row["return_date"] !== "-") {
                                echo htmlspecialchars($row["return_date"], ENT_QUOTES, "UTF-8");
                            } else {
                                echo '<span style="color: #94a3b8; font-style: italic;">Not returned yet</span>';
                            }
                            ?>
                        </td>

                        <!-- STATUS BADGE -->
                        <td style="padding: 16px 20px; text-align: center;">
                            <?php
                            $status = !empty($row["status"]) ? strtolower($row["status"]) : "borrowed";
                            // Normalized display status: show 'BORROW' for non-returned states
                            $displayStatus = ($status === 'returned') ? 'RETURNED' : 'BORROW';
                            $badgeBg = '#e0e7ff';
                            $badgeColor = '#4f46e5';

                            if ($status === 'returned') {
                                $badgeBg = '#dcfce7';
                                $badgeColor = '#15803d';
                            } elseif ($status === 'overdue') {
                                $badgeBg = '#fee2e2';
                                $badgeColor = '#b91c1c';
                            }
                            ?>
                            <span
                                style="display: inline-block; padding: 5px 12px; border-radius: 6px; font-size: 12px; font-weight: 700; text-transform: uppercase; background-color: <?php echo $badgeBg; ?>; color: <?php echo $badgeColor; ?>; letter-spacing: 0.5px; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                                <?php echo htmlspecialchars($displayStatus, ENT_QUOTES, "UTF-8"); ?>
                            </span>
                        </td>

                        <!-- ACTION -->
                        <td style="padding: 16px 20px; text-align: center;">
                            <?php if ($status !== 'returned'): ?>
                                <a href="?url=ReturnController/returnBook/<?php echo urlencode($row['global_book_id']); ?>" onclick="if(!confirm('Are you sure you want to return this book?')){return false;} this.style.pointerEvents='none'; this.innerText='Processing...';" style="display: inline-flex; align-items: center; justify-content: center; padding: 8px 12px; border-radius: 8px; background-color: #0ea5e9; color: #ffffff; text-decoration: none; font-size: 13px; font-weight: 600; border: 1px solid rgba(14,165,233,0.2); transition: background-color 0.2s;">Return</a>
                            <?php else: ?>
                                <!-- Make 'Done' actionable: clicking it will confirm and trigger return if user wants to re-run return -->
                                <a href="?url=ReturnController/returnBook/<?php echo urlencode($row['global_book_id']); ?>" onclick="if(!confirm('This book is already marked returned. Do you want to re-run the return action?')){return false;} this.style.pointerEvents='none'; this.innerText='Processing...';" style="display: inline-flex; align-items: center; justify-content: center; padding: 8px 12px; border-radius: 8px; background-color: #e2e8f0; color: #64748b; font-size: 13px; font-weight: 600; text-decoration: none; border: 1px solid #e6edf3;">Done</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (!$found): ?>
                    <tr>
                        <td
                            colspan="7"
                            style="padding: 40px; text-align: center; color: #94a3b8; font-size: 16px; background-color: #ffffff;">
                            <div style="font-size: 40px; margin-bottom: 10px;">📂</div>
                            <strong>No Borrow History Found</strong>
                            <p style="margin: 5px 0 0 0; font-size: 14px; color: #cbd5e1;">You haven't borrowed any books from the library yet.</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
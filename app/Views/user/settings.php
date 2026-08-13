<?php
$userData = $user ?? ($_SESSION['user'] ?? []);
$userId = htmlspecialchars($userData['id'] ?? $userData['user_id'] ?? '-');
$username = htmlspecialchars($userData['username'] ?? '');
$email = htmlspecialchars($userData['email'] ?? '');
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
$theme = $_SESSION['theme'] ?? 'light';
?>

<link rel='stylesheet' href='assets/css/user-management.css'>

<div class='settings-page' style='max-width:900px;margin:28px auto;padding:20px;background:#fff;border-radius:12px;'>
    <h1>Profile Settings</h1>

    <?php if (!empty($_SESSION['flash_error'])) : ?>
        <div style='background:#ffe4e6;color:#991b1b;padding:10px;border-radius:6px;margin-bottom:12px;'><?php echo htmlspecialchars($_SESSION['flash_error']);
                                                                                                            unset($_SESSION['flash_error']); ?></div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['flash_success'])) : ?>
        <div style='background:#ecfdf5;color:#065f46;padding:10px;border-radius:6px;margin-bottom:12px;'><?php echo htmlspecialchars($_SESSION['flash_success']);
                                                                                                            unset($_SESSION['flash_success']); ?></div>
    <?php endif; ?>

    <div style='display:flex;gap:20px;align-items:flex-start;'>
        <div style='width:180px;'>
            <?php if (!empty($profileImage) && file_exists(__DIR__ . '/../../../public/' . $profileImage)) : ?>
                <img src='<?php echo $profileImage; ?>' style='width:160px;height:200px;object-fit:cover;border-radius:8px;border:1px solid #e6e8ef;'>
            <?php else : ?>
                <div style='width:160px;height:200px;display:flex;align-items:center;justify-content:center;background:#f3f4f6;border-radius:8px;font-size:48px;color:#334155;'>
                    <?php echo strtoupper(substr($username ?: ($userData['name'] ?? 'U'), 0, 1)); ?>
                </div>
            <?php endif; ?>

            <form method='POST' enctype='multipart/form-data' action='?url=UserController/updateSettings' style='margin-top:12px;'>
                <label style='display:block;margin-bottom:6px;font-weight:600;'>Profile Image</label>
                <input type='file' name='profile_image' accept='image/*'>
                <div style='margin-top:10px;'><button class='um-btn um-btn-primary' type='submit'>Upload / Save</button></div>
            </form>
        </div>

        <div style='flex:1;'>
            <form method='POST' action='?url=UserController/updateSettings'>
                <div style='margin-bottom:12px;'>
                    <label style='display:block;font-weight:600;'>Full Name</label>
                    <input type='text' name='name' value='<?php echo htmlspecialchars($userData['name'] ?? ''); ?>' style='width:100%;padding:8px;border:1px solid #e6e8ef;border-radius:6px;'>
                </div>

                <div style='margin-bottom:12px;'>
                    <label style='display:block;font-weight:600;'>Username</label>
                    <input type='text' name='username' value='<?php echo $username; ?>' style='width:100%;padding:8px;border:1px solid #e6e8ef;border-radius:6px;'>
                </div>

                <div style='margin-bottom:12px;'>
                    <label style='display:block;font-weight:600;'>Email</label>
                    <input type='email' name='email' value='<?php echo $email; ?>' style='width:100%;padding:8px;border:1px solid #e6e8ef;border-radius:6px;'>
                </div>

                <div style='margin-bottom:12px;'>
                    <label style='display:block;font-weight:600;'>Theme</label>
                    <select name='mode' style='padding:8px;border:1px solid #e6e8ef;border-radius:6px;'>
                        <option value='light' <?php echo $theme === 'light' ? 'selected' : ''; ?>>Light</option>
                        <option value='dark' <?php echo $theme === 'dark' ? 'selected' : ''; ?>>Dark</option>
                    </select>
                </div>

                <hr>

                <h3>Change Password</h3>

                <div style='margin-bottom:8px;'>
                    <label style='display:block;'>Current Password (required)</label>
                    <div style="position:relative;">
                        <input type='password' name='current_password' id='current_password' style='width:100%;padding:8px;border:1px solid #e6e8ef;border-radius:6px;padding-right:36px;'>
                        <button type='button' tabindex='-1' class='pw-toggle' data-target='current_password' style='position:absolute;right:6px;top:6px;border:0;background:transparent;cursor:pointer;font-size:14px;'>👁️</button>
                    </div>
                </div>

                <div style='margin-bottom:8px;'>
                    <label style='display:block;'>New Password</label>
                    <div style="position:relative;">
                        <input type='password' name='new_password' id='new_password' style='width:100%;padding:8px;border:1px solid #e6e8ef;border-radius:6px;padding-right:36px;'>
                        <button type='button' tabindex='-1' class='pw-toggle' data-target='new_password' style='position:absolute;right:6px;top:6px;border:0;background:transparent;cursor:pointer;font-size:14px;'>👁️</button>
                    </div>
                </div>

                <div style='margin-bottom:12px;'>
                    <label style='display:block;'>Confirm New Password</label>
                    <div style="position:relative;">
                        <input type='password' name='confirm_password' id='confirm_password' style='width:100%;padding:8px;border:1px solid #e6e8ef;border-radius:6px;padding-right:36px;'>
                        <button type='button' tabindex='-1' class='pw-toggle' data-target='confirm_password' style='position:absolute;right:6px;top:6px;border:0;background:transparent;cursor:pointer;font-size:14px;'>👁️</button>
                    </div>
                </div>

                <div style='display:flex;gap:10px;'>
                    <button class='um-btn um-btn-primary' type='submit'>Save Changes</button>
                    <a href='?url=UserController/dashboard' class='um-btn um-btn-secondary'>Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
// end file
?>

<script>
    document.addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('pw-toggle')) {
            var targetId = e.target.getAttribute('data-target');
            var inp = document.getElementById(targetId);
            if (!inp) return;
            if (inp.type === 'password') {
                inp.type = 'text';
                e.target.textContent = '🙈';
            } else {
                inp.type = 'password';
                e.target.textContent = '👁️';
            }
        }
    });
</script>
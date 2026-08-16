<?php

$user =
    $_SESSION["user"]
    ?? [];


$profileUsername =
    htmlspecialchars(
        $user["username"] ?? "",
        ENT_QUOTES,
        "UTF-8"
    );


$profileEmail =
    htmlspecialchars(
        $user["email"] ?? "",
        ENT_QUOTES,
        "UTF-8"
    );


$profileName =
    htmlspecialchars(
        $user["name"] ?? "Administrator",
        ENT_QUOTES,
        "UTF-8"
    );


$profilePhoto =
    $user["profile_photo"]
    ?? "";


$profilePhotoSafe =
    htmlspecialchars(
        $profilePhoto,
        ENT_QUOTES,
        "UTF-8"
    );

?>

<div
    id="adminProfileModal"
    class="admin-profile-modal"
>


    <div
        class="admin-profile-overlay"
        onclick="closeAdminProfileModal()"
    ></div>


    <div
        class="admin-profile-box"
        role="dialog"
        aria-modal="true"
        aria-labelledby="adminProfileTitle"
    >


        <!-- HEADER -->

        <div class="admin-profile-header">

            <div>

                <div class="admin-profile-title">
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
            id="adminProfileForm"
            method="POST"
            action="?url=AdminController/updateProfile"
            enctype="multipart/form-data"
        >


            <!-- PROFILE PHOTO -->

            <div class="admin-profile-photo-section">


                <div
                    class="admin-profile-photo-preview"
                    id="adminProfilePreview"
                >

                    <?php if (
                        !empty($profilePhotoSafe)
                    ): ?>

                        <img
                            src="<?php echo $profilePhotoSafe; ?>"
                            alt="Profile Photo"
                        >

                    <?php else: ?>

                        <span>
                            👤
                        </span>

                    <?php endif; ?>

                </div>


                <div class="admin-profile-photo-info">

                    <strong>
                        Profile Photo
                    </strong>


                    <p>
                        JPG, PNG or WEBP
                    </p>


                    <p>
                        Maximum 2MB
                    </p>


                    <label
                        for="admin_profile_photo"
                        class="admin-photo-upload-btn"
                    >
                        📷 Upload Photo
                    </label>


                    <input
                        type="file"
                        id="admin_profile_photo"
                        name="profile_photo"
                        accept="image/jpeg,image/png,image/webp"
                    >

                </div>

            </div>


            <!-- USERNAME -->

            <div class="admin-profile-field">

                <label for="admin_username">
                    Username
                </label>


                <input
                    type="text"
                    id="admin_username"
                    name="username"
                    value="<?php echo $profileUsername; ?>"
                    minlength="3"
                    maxlength="100"
                    autocomplete="username"
                    required
                >

            </div>


            <!-- EMAIL -->

            <div class="admin-profile-field">

                <label for="admin_email">
                    Email
                </label>


                <input
                    type="email"
                    id="admin_email"
                    name="email"
                    value="<?php echo $profileEmail; ?>"
                    maxlength="255"
                    autocomplete="email"
                    required
                >

            </div>


            <!-- PASSWORD -->

            <div class="admin-profile-field">

                <label for="admin_password">
                    New Password
                </label>


                <div class="admin-password-wrapper">

                    <input
                        type="password"
                        id="admin_password"
                        name="password"
                        minlength="6"
                        maxlength="255"
                        autocomplete="new-password"
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
                    Leave this empty if you do not want to change
                    your password.
                </small>

            </div>


            <!-- BUTTONS -->

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
                    class="admin-profile-save"
                >
                    💾 Save Changes
                </button>

            </div>


        </form>

    </div>

</div>
/* ============================================================
   ADMIN PROFILE MODAL
============================================================ */


/*
|--------------------------------------------------------------------------
| OPEN MODAL
|--------------------------------------------------------------------------
*/

function openAdminProfileModal() {

    const modal =
        document.getElementById(
            "adminProfileModal"
        );


    if (!modal) {

        console.error(
            "Admin profile modal not found."
        );

        return;
    }


    modal.classList.add("show");

    modal.setAttribute(
        "aria-hidden",
        "false"
    );


    document.body.classList.add(
        "admin-profile-open"
    );

}


/*
|--------------------------------------------------------------------------
| CLOSE MODAL
|--------------------------------------------------------------------------
*/

function closeAdminProfileModal() {

    const modal =
        document.getElementById(
            "adminProfileModal"
        );


    if (!modal) {

        return;

    }


    modal.classList.remove("show");

    modal.setAttribute(
        "aria-hidden",
        "true"
    );


    document.body.classList.remove(
        "admin-profile-open"
    );

}


/*
|--------------------------------------------------------------------------
| ESC KEY
|--------------------------------------------------------------------------
*/

document.addEventListener(
    "keydown",
    function (event) {

        if (
            event.key === "Escape"
        ) {

            closeAdminProfileModal();

        }

    }
);


/*
|--------------------------------------------------------------------------
| PASSWORD SHOW / HIDE
|--------------------------------------------------------------------------
*/

function toggleAdminPassword() {

    const input =
        document.getElementById(
            "adminPassword"
        );


    if (!input) {

        return;

    }


    if (
        input.type === "password"
    ) {

        input.type = "text";

    } else {

        input.type = "password";

    }

}


/*
|--------------------------------------------------------------------------
| PROFILE PHOTO FILE NAME
|--------------------------------------------------------------------------
*/

document.addEventListener(
    "DOMContentLoaded",
    function () {

        const input =
            document.getElementById(
                "adminProfilePhoto"
            );


        const fileName =
            document.getElementById(
                "profileFileName"
            );


        if (
            input &&
            fileName
        ) {

            input.addEventListener(
                "change",
                function () {

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
<?php


require_once "../app/Services/AuthService.php";


class AuthController
{
    public function login()
    {
        /*
        ========================================================
        START SESSION
        ========================================================
        */

        if (
            session_status() === PHP_SESSION_NONE
        ) {
            session_start();
        }


        /*
        ========================================================
        LOGIN REQUEST
        ========================================================
        */

        if ($_SERVER["REQUEST_METHOD"] == "POST") {


            $service =
                new AuthService();


            $user = $service->login(
    trim($_POST["email"] ?? ""),
    $_POST["password"] ?? ""
);


            if ($user) {


                /*
                ====================================================
                SAVE LOGGED-IN USER
                ====================================================
                */

                $_SESSION["user"] = $user;


                /*
                ====================================================
                KEEP ORIGINAL ROLE LOGIC
                ====================================================
                */

                if (
                    $user["role"] == "admin"
                ) {


                    header(
                        "Location:?url=AdminController/dashboard"
                    );

                } else {


                    header(
                        "Location:?url=UserController/dashboard"
                    );
                }


                exit;

            } else {


                echo "


                <div class='auth-page'>


                    <div class='library-bg'>
                        <div class='floating-book book-one'>📘</div>
                        <div class='floating-book book-two'>📕</div>
                        <div class='floating-book book-three'>📗</div>
                        <div class='floating-book book-four'>📙</div>


                        <div class='light-orb orb-one'></div>
                        <div class='light-orb orb-two'></div>
                        <div class='light-orb orb-three'></div>
                    </div>


                    <div class='login-card'>


                        <div class='brand-section'>


                            <div class='brand-icon'>
                                📚
                            </div>


                            <div class='brand-title'>
                                LIBRARY
                            </div>


                            <div class='brand-subtitle'>
                                Management System
                            </div>


                        </div>


                        <div class='login-heading'>
                            Welcome Back
                        </div>


                        <div class='login-description'>
                            Sign in to continue to your library
                        </div>


                        <div class='error-message'>
                            <span>⚠</span>
                            Invalid Email/Username or Password
                        </div>


                        <a
                            class='login-retry'
                            href='?url=AuthController/login'>
                            Try Again
                        </a>


                    </div>


                </div>


                ";
            }


        } else {


            echo "


            <div class='auth-page'>


                <div class='library-bg'>


                    <div class='library-glow glow-one'></div>
                    <div class='library-glow glow-two'></div>
                    <div class='library-glow glow-three'></div>


                    <div class='floating-book book-one'>📘</div>
                    <div class='floating-book book-two'>📕</div>
                    <div class='floating-book book-three'>📗</div>
                    <div class='floating-book book-four'>📙</div>
                    <div class='floating-book book-five'>📓</div>


                    <div class='floating-particle particle-one'></div>
                    <div class='floating-particle particle-two'></div>
                    <div class='floating-particle particle-three'></div>
                    <div class='floating-particle particle-four'></div>
                    <div class='floating-particle particle-five'></div>


                </div>



                <div class='login-card'>


                    <div class='brand-section'>


                        <div class='brand-icon'>
                            📚
                        </div>


                        <div class='brand-title'>
                            LIBRARY
                        </div>


                        <div class='brand-subtitle'>
                            Management System
                        </div>


                    </div>



                    <div class='login-heading'>
                        Welcome Back
                    </div>


                    <div class='login-description'>
                        Sign in to access your personal library
                    </div>



                    <form
                        method='POST'
                        class='login-form'>


                        <div class='form-group'>


                            <label>
                                Username or Email
                            </label>


                            <div class='input-wrapper'>


                                <span class='input-icon'>
                                    👤
                                </span>


                                <input
                                    name='email'
                                    type='text'
                                    placeholder='Enter your username or email'
                                    autocomplete='username'
                                    required>


                            </div>


                        </div>



                        <div class='form-group'>


                            <label>
                                Password
                            </label>


                            <div class='input-wrapper'>


                                <span class='input-icon'>
                                    🔒
                                </span>


                                <input
                                    type='password'
                                    name='password'
                                    placeholder='Enter your password'
                                    autocomplete='current-password'
                                    required>


                            </div>


                        </div>



                        <button
                            type='submit'
                            class='login-button'>


                            <span>
                                Sign In
                            </span>


                            <span class='button-arrow'>
                                →
                            </span>


                        </button>



                    </form>



                    <div class='register-divider'>


                        <span>
                            New to the library?
                        </span>


                    </div>



                    <a
                        class='register-button'
                        href='?url=AuthController/register'>


                        <span>
                            Create Account
                        </span>


                        <span>
                            →
                        </span>


                    </a>



                    <div class='login-footer'>


                        <span>
                            🔐 Secure Library Access
                        </span>


                        <span class='footer-dot'>
                            •
                        </span>


                        <span>
                            Distributed System
                        </span>


                    </div>


                </div>


            </div>


            ";
        }
    }



    public function register()
    {
        /*
        ========================================================
        START SESSION
        ========================================================
        */

        if (
            session_status() === PHP_SESSION_NONE
        ) {
            session_start();
        }


        /*
        ========================================================
        REGISTER REQUEST
        ========================================================
        */

        if ($_SERVER["REQUEST_METHOD"] == "POST") {


            $service =
                new AuthService();


            $result =
                $service->register(
                    $_POST["username"],
                    $_POST["name"],
                    $_POST["email"],
                    $_POST["password"],
                    $_POST["role"]
                );


            if ($result === true) {


                header(
                    "Location:?url=AuthController/login"
                );


                exit;

            } else {


                echo "


                <div class='auth-page'>


                    <div class='library-bg'></div>


                    <div class='login-card'>


                        <div class='brand-section'>


                            <div class='brand-icon'>
                                📚
                            </div>


                            <div class='brand-title'>
                                LIBRARY
                            </div>


                            <div class='brand-subtitle'>
                                Management System
                            </div>


                        </div>


                        <div class='error-message'>


                            <span>
                                ⚠
                            </span>


                            " . htmlspecialchars(
                                $result,
                                ENT_QUOTES,
                                "UTF-8"
                            ) . "


                        </div>


                        <a
                            class='register-button'
                            href='?url=AuthController/register'>


                            Back to Registration


                        </a>


                    </div>


                </div>


                ";
            }


        } else {


            echo "


            <div class='auth-page'>


                <div class='library-bg'>


                    <div class='library-glow glow-one'></div>
                    <div class='library-glow glow-two'></div>
                    <div class='library-glow glow-three'></div>


                    <div class='floating-book book-one'>📘</div>
                    <div class='floating-book book-two'>📕</div>
                    <div class='floating-book book-three'>📗</div>


                </div>



                <div class='login-card register-card'>


                    <div class='brand-section'>


                        <div class='brand-icon'>
                            📚
                        </div>


                        <div class='brand-title'>
                            LIBRARY
                        </div>


                        <div class='brand-subtitle'>
                            Management System
                        </div>


                    </div>



                    <div class='login-heading'>
                        Create Account
                    </div>


                    <div class='login-description'>
                        Join our distributed library system
                    </div>



                    <form
                        method='POST'
                        class='login-form'>


                        <div class='form-group'>


                            <label>
                                Username
                            </label>


                            <div class='input-wrapper'>


                                <span class='input-icon'>
                                    👤
                                </span>


                                <input
                                    name='username'
                                    type='text'
                                    placeholder='Choose a username'
                                    required>


                            </div>


                        </div>



                        <div class='form-group'>


                            <label>
                                Full Name
                            </label>


                            <div class='input-wrapper'>


                                <span class='input-icon'>
                                    ✨
                                </span>


                                <input
                                    name='name'
                                    type='text'
                                    placeholder='Enter your full name'
                                    required>


                            </div>


                        </div>



                        <div class='form-group'>


                            <label>
                                Email
                            </label>


                            <div class='input-wrapper'>


                                <span class='input-icon'>
                                    ✉️
                                </span>


                                <input
                                    type='email'
                                    name='email'
                                    placeholder='Enter your email'
                                    required>


                            </div>


                        </div>



                        <div class='form-group'>


                            <label>
                                Password
                            </label>


                            <div class='input-wrapper'>


                                <span class='input-icon'>
                                    🔒
                                </span>


                                <input
                                    type='password'
                                    name='password'
                                    placeholder='Create a secure password'
                                    required>


                            </div>


                        </div>



                        <div class='form-group'>


                            <label>
                                Account Type
                            </label>


                            <div class='input-wrapper'>


                                <span class='input-icon'>
                                    🪪
                                </span>


                                <select name='role'>


                                    <option value='user'>
                                        User
                                    </option>


                                    <option value='admin'>
                                        Admin
                                    </option>


                                </select>


                            </div>


                        </div>



                        <button
                            type='submit'
                            class='login-button'>


                            <span>
                                Create Account
                            </span>


                            <span class='button-arrow'>
                                →
                            </span>


                        </button>



                    </form>



                    <div class='password-rule'>


                        <div class='rule-title'>
                            🔐 Password Requirements
                        </div>


                        <div class='rule-item'>
                            • 8–16 characters
                        </div>


                        <div class='rule-item'>
                            • Capital letter
                        </div>


                        <div class='rule-item'>
                            • Small letter
                        </div>


                        <div class='rule-item'>
                            • Digit
                        </div>


                        <div class='rule-item'>
                            • Special character
                        </div>


                    </div>



                    <a
                        class='register-button'
                        href='?url=AuthController/login'>


                        ← Back to Login


                    </a>



                </div>


            </div>


            ";
        }
    }



    public function logout()
    {
        /*
        ========================================================
        START SESSION
        ========================================================
        */

        if (
            session_status() === PHP_SESSION_NONE
        ) {
            session_start();
        }


        /*
        ========================================================
        DESTROY CURRENT SESSION
        ========================================================
        */

        $_SESSION = [];


        session_destroy();


        /*
        ========================================================
        BACK TO LOGIN
        ========================================================
        */

        header(
            "Location:?url=AuthController/login"
        );


        exit;
    }
}
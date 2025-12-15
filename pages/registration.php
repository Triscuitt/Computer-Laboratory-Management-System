<?php

require "../dbconnection.php";
require "../utilities/utils.php";
require "../utilities/validation.php";
require "../utilities/queries.php";
require_once "../model/user.php";

session_start();
checkPost();
clearPost();


function checkPost()
{
    if (isset($_POST['registerBtn'])) {
        $_SESSION['formData'] = $_POST;
        register(isset($_POST['stud_no']) ? $_POST['stud_no']: 'N/A', $_POST['fname'], $_POST['midname'], $_POST['lname'], $_POST['suffix'], $_POST['userRole'], $_POST['username'], $_POST['email'], $_POST['password'], $_POST['confirmPassword']);
        echo 'Num: '.$_POST['stud_no'];
    }
}


function register($student_number, $first_name, $middle_name, $last_name, $suffix, $userRole, $username, $email, $password, $confirmPassword)
{
    
    $fname = trim($first_name);
    $midname = trim($middle_name);
    $lname = trim($last_name);
    $suffix = trim($suffix);
    $role = $userRole;
    $email = trim($email);
    $username = trim($username);
    $password = trim($password);
    $confirmPassword =  trim($confirmPassword);

    echo 'Num: '.$student_number;

    // Validation array
    $validation = [validateEmptyField($student_number, $fname, $lname, $email, $username, $password, $confirmPassword), validateLength($fname, $lname),  validateStudentNumber($student_number, $role), validateEmail($email), validatePassword($password, $confirmPassword)];

    // iterates the validation array and validate one by one.
    foreach ($validation as $validate) {
        if (!$validate['status']) {
            $_SESSION['alertMessage'] = $validate['message'];
            echo $validate['message'];
            headto('../pages/registration.php');
        }
    }

    sendOtp($email, $username);
    $registeredUser = new User($fname, $lname, $role, $username, $student_number, $email, $password);
    $_SESSION['registeredUserEmail'] = $registeredUser->getEmail();
    $_SESSION['registeredUser'] = $registeredUser;
    headto('../pages/verification.php');

}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Computer Laboratory Management System</title>
    <link rel="stylesheet" href="../asset/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <main>
        <div class="container">
            <!-- Left Side Panel -->
            <div class="side-panel">
                <h1>Computer Laboratory Management System</h1>
                <p>Join us and start managing your laboratory services easily and securely.</p>
            </div>

            <!-- Right Form Panel -->
            <div class="form-panel">
                <div class="form-header">
                    <h2 class="subtitle">Create Account</h2>
                    <p class="form-description">Fill in the details below to get started</p>
                </div>

                <?php if (isset($_SESSION['alertMessage'])): ?>
                    <div class="alert alert-error">
                        <?php echo $_SESSION['alertMessage'];
                        unset($_SESSION['alertMessage']); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="form">
                    <div class="form-row">
                        <div class="form-group">
                            <label>First Name <span class="required">*</span></label>
                            <div class="input-wrapper">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <input type="text" name="fname" placeholder="Enter first name" class="input" value="<?php echo isset($_SESSION['formData']['fname']) ? htmlspecialchars($_SESSION['formData']['fname']) : ''; ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Middle Name</label>
                            <div class="input-wrapper">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <input type="text" name="midname" placeholder="Enter middle name" class="input" value="<?php echo isset($_SESSION['formData']['midname']) ? htmlspecialchars($_SESSION['formData']['midname']) : ''; ?>">
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Last Name <span class="required">*</span></label>
                            <div class="input-wrapper">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <input type="text" name="lname" placeholder="Enter last name" class="input" value="<?php echo isset($_SESSION['formData']['lname']) ? htmlspecialchars($_SESSION['formData']['lname']) : ''; ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Student Number <span class="required">*</span></label>
                            <div class="input-wrapper">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                                </svg>
                                <input id="studnoInput" type="text" name="stud_no" placeholder="Enter student number" class="input" value="<?php echo isset($_SESSION['formData']['stud_no']) ? htmlspecialchars($_SESSION['formData']['stud_no']) : ''; ?>">
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Username <span class="required">*</span></label>
                            <div class="input-wrapper">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                                </svg>
                                <input type="text" name="username" placeholder="Choose a username" class="input" value="<?php echo isset($_SESSION['formData']['username']) ? htmlspecialchars($_SESSION['formData']['username']) : ''; ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Suffix (Optional)</label>
                            <div class="input-wrapper">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                                </svg>
                                <input type="text" name="suffix" placeholder="Jr., Sr., III, etc." class="input" value="<?php echo isset($_SESSION['formData']['suffix']) ? htmlspecialchars($_SESSION['formData']['suffix']) : ''; ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Role</label>
                            <div class="input-wrapper">
                                <select id="userRoleInput" name="userRole" class="form-select form-select-sm" style="max-width:220px">
                                    <option disabled selected value> -- select an option -- </option>
                                    <option value="student">Student</option>
                                    <option value="faculty">Faculty</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Email <span class="required">*</span></label>
                        <div class="input-wrapper">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <input type="email" name="email" placeholder="Enter your email address" class="input" value="<?php echo isset($_SESSION['formData']['email']) ? htmlspecialchars($_SESSION['formData']['email']) : ''; ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Password <span class="required">*</span></label>
                            <div class="input-wrapper">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                                <input type="password" name="password" placeholder="Create a password" class="input">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Confirm Password <span class="required">*</span></label>
                            <div class="input-wrapper">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                                <input type="password" name="confirmPassword" placeholder="Confirm your password" class="input">
                            </div>
                        </div>
                    </div>

                    <button type="submit" name="registerBtn" class="btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                        Continue
                    </button>

                    <p class="login-text">Already have an account? <a href="login.php" class="link">Login here</a></p>
                </form>
            </div>
        </div>
    </main>
    <script>
        const selectElement = document.getElementById('userRoleInput');
        const studentNumInput = document.getElementById('studnoInput');

        selectElement.addEventListener('change', (event) =>{

            const role = event.target.value;
            if(role == 'faculty'){
                studentNumInput.value = 'N/A';
                studentNumInput.disabled = true;
                console.log(studentNumInput.textContent);
            }else{
                studentNumInput.disabled = false;
                studentNumInput.value = '';
            }
            console.log('Role',role);
        })
    </script>
</body>

</html>
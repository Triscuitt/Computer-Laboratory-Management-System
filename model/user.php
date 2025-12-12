<?php
class User
{
    # user infox    
    private $first_name;
    private $middle_name;
    private $last_name;
    private $suffix;

    # account info
    private $email;
    private $role;
    private $student_no;
    private $department;
    private $profile_image;
    private $username;
    private $password;

    function __construct($firstName, $lastName, $userRole, $userNickname, $studentNumber, $userEmail, $userPassword)
    {
        $this->first_name = $firstName;
        $this->last_name = $lastName;
        $this->role = $userRole;
        $this->username = $userNickname;
        $this->student_no =  $studentNumber;
        $this->email = $userEmail;
        $this->password = $userPassword;
    }

    public function getFirstName()
    {
        return $this->first_name;
    }

    public function getMiddleName()
    {
        if (empty($this->middle_name)) {
            return '';
        }
        return $this->middle_name;
    }

    public function getLastName()
    {
        return $this->last_name;
    }

    public function getSuffix(){
        if (empty($this->suffix)) {
            return '';
        }
        return $this->suffix;
    }

    public function getEmail()
    {
        return $this->email;
    }


    public function getRole()
    {
        return $this->role;
    }

    public function getStudentNo(){
        return $this->student_no;
    }

    public function getDepartment()
    {
        return $this->department;
    }

    public function getProfileImage()
    {
        return $this->profile_image;
    }
    public function setProfileImage($imagePath)
    {
        $this->profile_image = $imagePath;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setUsername($newUsername)
    {
        $this->username = $newUsername;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($newPassword)
    {
        $this->password = $newPassword;
    }
}

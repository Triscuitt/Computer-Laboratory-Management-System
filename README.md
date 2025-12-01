# 🗃️ Database setup
```sql
CREATE TABLE users(
    id INT NOT NULL AUTO_INCREMENT  PRIMARY KEY,
    student_number VARCHAR(11) UNIQUE NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100),
    last_name VARCHAR(100) NOT NULL,
    suffix VARCHAR(10),
    username VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    account_status int(11) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE verification_codes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    code VARCHAR(255) NOT NULL,
    expires_at TIMESTAMP NOT NULL DEFAULT (NOW() + INTERVAL 5 MINUTE),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE equipment (
    equipment_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    category VARCHAR(100),
    status ENUM('Available', 'Borrowed', 'With Error', 'Pulled out') DEFAULT 'Available'
);

CREATE TABLE borrow (
    borrow_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    equipment_id INT NOT NULL,
    borrow_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    return_date TIMESTAMP NULL,
    status ENUM('Borrowed', 'Returned', 'Overdue') DEFAULT 'Borrowed',

    FOREIGN KEY (user_id) REFERENCES users(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    FOREIGN KEY (equipment_id) REFERENCES equipment(equipment_id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
);

CREATE TABLE request (
	request_id INT AUTO_INCREMENT PRIMARY KEY,
    submitter_id INT NOT NULL,
    request_title VARCHAR(50) NOT NULL,
    request_type ENUM('Software installation', 'Purchase', 'Peripheral', 'Hardware') DEFAULT 'Hardware',
    request_priority ENUM('Low', 'Medium', 'High'),
    request_description VARCHAR(250),
    
    FOREIGN KEY (submitter_id) REFERENCES users(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
);
```

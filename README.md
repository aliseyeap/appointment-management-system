# 💅 Appointment Management System for Elvira True Beauty Salon

This is a secure web-based **Appointment Management System** developed for **Elvira True Beauty Salon** to streamline appointment scheduling, enhance customer interaction, and improve salon operations. It supports multiple user roles (Administrator, Beautician, Customer) with role-specific modules, Two-Factor Authentication (2FA), and a Zero-Trust security model.

This system was built using **PHP (backend)**, **HTML/CSS/JavaScript (frontend)**, and **MySQL** for the database. 

---

## 🚀 Features

### 🔐 Security Features
- Two-Factor Authentication (2FA) via WhatsApp
- Strong password policy enforcement
- CAPTCHA protection for login
- Zero-Trust Principle (security questions on sensitive modules)

### 👥 General Features (All Users)
- Login / Logout with role-based access
- Account creation and management
- Password reset and change
- Dashboard with calendar view
- Secure user session and inactivity timeout

---

## 🎯 Role-Based Modules

### 👤 Customer
- Book online appointments
- View, reschedule, and cancel appointments
- Receive automated reminders
- Send messages to salon

### 💇‍♀️ Beautician (Staff)
- View and manage appointments
- Manage customer details
- Set availability and service offerings
- View personalized dashboard and calendar

### 🛠️ Administrator
- Manage appointments 
- View and respond to customer messages
- Monitor login activities
- Generate operational reports
- Manage beauticians, customers, and services
- Assign user roles and permissions

---

## 🧰 Tech Stack

- **Frontend:** HTML, CSS, JavaScript
- **Backend:** PHP
- **Database:** MySQL
- **Web Server:** Apache via XAMPP
- **Security:** WhatsApp OTP, CAPTCHA, SSL, Zero Trust

---

## 💻 Getting Started

### 1. Clone or Download the Repository
- Place it in your XAMPP `htdocs/` folder.
```bash
git clone https://github.com/aliseyeap/appointment-management-system.git
```

### 2. Set Up the Database
- Open phpMyAdmin
- Create a database named:
```bash
appointment_management_system
```
- Import the provided SQL

### 3. Configure config.php
- Update your database credentials inside config.php
```bash
$host = 'localhost'; // database host
$db_username = 'root'; // database username
$password = ''; // database password
$database = 'appointment_management_system'; // database name
```
### 4. Configure WhatsApp API Integration
- Fontee integration is implemented using direct API calls in various PHP files.

#### ⚠️ Replace the Default API Token
A placeholder API token is currently hardcoded into the following files. You **must replace** it with your **own Fontee API token** to enable messaging functionality:

| File                               | 
|------------------------------------|
| `admin-add-appointment.php`        | 
| `admin-delete-appointment.php`     | 
| `admin-update-appointment.php`     | 
| `customer-add-appointment.php`     |
| `customer-cancel-appointment.php`  | 
| `customer-update-reschedule.php`   | 
| `staff-add-appointment.php`        | 
| `staff-delete-appointment.php`     | 
| `staff-update-appointment.php`     | 
| `phone_number.php` (OTP sender)    | 

In all these files, you’ll find a line like:
```bash
$apiToken = 'your_fontee_api_token_here';
```

### 5. Configure SMTP credentials
- In the **register.php** file, update the following lines with your own Gmail or SMTP credentials:
```bash
$mail->Username = 'your_email@example.com'; // Replace with your email
$mail->Password = 'your_email_password';    // Replace with your App Password or SMTP password
```
### 6. Run the Application
- Start Apache and MySQL from XAMPP Control Panel
- Open your browser and navigate to:
```bash
http://localhost/appointment-management-salon/
```
---

## 📷 Screenshots
### 👤 Customer Interface
#### 📅 Appointment Booking
<img src="https://github.com/user-attachments/assets/1edb7ef3-17e5-49ae-8d5f-1e295f3678be" width="400"/>

#### 📋 Appointment Management
<img src="https://github.com/user-attachments/assets/8c715c09-a3a2-4b4a-a9c2-1a8a78a5224c" width="400"/>

### 💇‍♀️ Beautician Interface
#### 🗓️ Dashboard
<img src="https://github.com/user-attachments/assets/0fce30a0-fd08-4bcf-a888-7f91ee8cc637" width="400"/>

#### ✅ Set Availability
<img src="https://github.com/user-attachments/assets/5d9671d5-1510-4fe0-a296-4ef42e998645" width="400"/>

### 🛠️ Admin Interface
#### 📊 Full Dashboard View
<img src="https://github.com/user-attachments/assets/dc14a9a2-2ea8-4fdc-90a0-cff136d392c7" width="400"/>

#### 💬 User Management
<img src="https://github.com/user-attachments/assets/90e7180d-251c-486c-88d0-382f007fda86" width="400"/>

---
## 👨‍💻 Author
Developed by @aliseyeap.

---
## 📰 Publication
This system is academically recognized and published in the UTHM Publisher Platform – AITCS Journal @ https://publisher.uthm.edu.my/periodicals/index.php/aitcs/article/view/16459

---
## 📄 License
This project is developed for academic purposes and is not intended for commercial distribution. Please consult the authors for reuse or enhancement.

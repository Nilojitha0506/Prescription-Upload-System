# Prescription Upload System by Nilojitha Mariyathas

## Overview

This project is a **Prescription Upload System** where users can send prescriptions digitally to registered pharmacies. Pharmacies can respond with quotations, and users can accept or reject them. Users and pharmacies receive email notifications about quotation updates. Admins manage the entire system.

---

## Features

- User registration and login with secure authentication  
- Prescription upload with notes  
- View, accept, or reject quotations  
- Email notifications for quotations  
- Role-based access control for User, Pharmacy, and Admin  
- Admin can manage users, pharmacies, prescriptions, and quotations  

---

## Roles and Functionalities

### User

- **Send Prescription:** Users can upload prescriptions to a pharmacy with an optional note.  
- **View Quotations:** Users can view quotations sent by the pharmacy for their prescriptions.  
- **Accept/Reject Quotation:** Users can accept or reject the quotation.  
- **Email Notification:** Users receive an email when a pharmacy sends a quotation.  

### Pharmacy

- **Register/Login:** Pharmacies can register and log in to the system.  
- **View Prescriptions:** Pharmacies can view prescriptions uploaded by users.  
- **Create Quotation:** Based on the prescription, pharmacies can add drugs, set prices and quantities, and send quotations to the user.  
- **Edit Profile:** Pharmacies can update their account details.  
- **User Notification:** The system notifies pharmacies when a user accepts or rejects a quotation.  
- **Email Notifications:** Pharmacies need a Gmail account and Google App Password to send emails to users.  

### Admin

- **Full System Management:** Admins can manage the entire system including Users, Pharmacies, Prescriptions, and Quotations.  
- **CRUD Operations:** Create, read, update, and delete records for users, pharmacies, prescriptions, and quotations.  
- **Monitor Activities:** Track system usage, quotation status, and user interactions.  

---

## Technology Stack

- **Frontend:** HTML, CSS, JavaScript (Bootstrap/Tailwind optional)  
- **Backend:** PHP  
- **Database:** MySQL  
- **Email Notifications:** PHP mailer (Gmail with App Password)  

## Estimated Completion Time

This small-scale project was completed in **approximately 5–6 hours**. It includes:  

- User registration and login  
- Prescription upload and quotation management  
- Pharmacy dashboard to send quotations  
- Admin panel for basic system management  
- Email notifications for quotations  

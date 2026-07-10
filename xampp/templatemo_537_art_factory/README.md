# ACTIVAURA - Health & Fitness Hub

A comprehensive health and fitness platform with user authentication, nutrition planning, workout recommendations, and task tracking.

## 🚀 Complete Authentication Pipeline

### 1. **User Registration (Signup)**
- Users create accounts with name, email, and password
- Email validation and password security (bcrypt hashing)
- Duplicate email prevention
- Automatic login after successful registration

### 2. **User Login**
- Secure email/password authentication
- Session-based authentication
- Automatic redirect to dashboard after login

### 3. **Protected Features**
- **Nutrition Planner**: Create personalized nutrition plans
- **Workout Recommender**: AI-powered workout suggestions
- **Task Tracker**: Daily wellness habit tracking
- All features require authentication and save data with user ID

### 4. **Data Persistence**
- All user data is saved with unique user ID
- Nutrition profiles, workout plans, and task logs are user-specific
- Secure database storage with proper relationships

## 📁 Project Structure

```
templatemo_537_art_factory/
├── index.html              # Main landing page with login/signup
├── dashboard.html          # User dashboard (protected)
├── nutri.html             # Nutrition planner (protected)
├── workout.html           # Workout recommender (protected)
├── task.html              # Task tracker (protected)
├── setup_database.php     # Database setup script
├── test_connection.php    # Connection test script
├── backend/
│   ├── config.php         # Database configuration
│   ├── db.php            # Database connection & helpers
│   ├── schema.sql        # Database schema
│   ├── auth_login.php    # Login endpoint
│   ├── auth_signup.php   # Signup endpoint
│   ├── auth_logout.php   # Logout endpoint
│   ├── auth_check.php    # Authentication check
│   ├── nutrition_save.php # Save nutrition data
│   ├── workout_save.php  # Save workout data
│   ├── tasks_save.php    # Save task data
│   └── me.php           # Get user info
└── assets/
    ├── js/
    │   ├── auth.js       # Authentication manager
    │   ├── backend.js    # API wrapper
    │   └── ...
    └── css/
        └── ...
```

## 🛠️ Setup Instructions

### 1. **Prerequisites**
- XAMPP (Apache + MySQL)
- PHP 7.4+
- Modern web browser

### 2. **Database Setup**
1. Start XAMPP Apache and MySQL services
2. Visit: `http://localhost/templatemo_537_art_factory/setup_database.php`
3. This will create the database and all required tables

### 3. **Test Connection**
Visit: `http://localhost/templatemo_537_art_factory/test_connection.php`

### 4. **Access the Application**
Visit: `http://localhost/templatemo_537_art_factory/index.html`

## 🔐 Authentication Flow

### **Step 1: User Registration**
1. Click "Join us" on the homepage
2. Click "Create account" in the login modal
3. Fill in name, email, and password
4. Submit to create account
5. User is automatically logged in

### **Step 2: User Login**
1. Click "Join us" on the homepage
2. Enter email and password
3. Submit to login
4. User is redirected to dashboard

### **Step 3: Access Protected Features**
After login, users can access:
- **Dashboard**: Overview of all features and stats
- **Nutrition**: Create personalized nutrition plans
- **Workout**: Get AI workout recommendations
- **Tasks**: Track daily wellness habits

### **Step 4: Data Saving**
- All feature data is automatically saved with user ID
- Users can see their saved data in the dashboard
- Data persists across sessions

## 🗄️ Database Schema

### **Users Table**
```sql
CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### **Nutrition Profiles**
```sql
CREATE TABLE nutrition_profiles (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  age INT, gender VARCHAR(20), region VARCHAR(30),
  goal VARCHAR(30), diet VARCHAR(20), concerns VARCHAR(255),
  height_cm DECIMAL(6,2), weight_kg DECIMAL(6,2), bmi DECIMAL(5,2),
  calories INT, carbs_g INT, protein_g INT, fat_g INT,
  plan_json JSON, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### **Workout Plans**
```sql
CREATE TABLE workout_plans (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  goal VARCHAR(40), level VARCHAR(20), minutes INT,
  equipment VARCHAR(40), bodypart VARCHAR(40),
  plan_json JSON, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### **Task Logs**
```sql
CREATE TABLE task_logs (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  tasks_json JSON NOT NULL,
  progress_percent INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

## 🔧 Features

### **Authentication System**
- ✅ Secure user registration and login
- ✅ Session-based authentication
- ✅ Password hashing with bcrypt
- ✅ Email validation
- ✅ Protected routes
- ✅ Automatic logout functionality

### **Nutrition Planner**
- ✅ BMI calculation
- ✅ Calorie and macro recommendations
- ✅ Personalized meal plans
- ✅ Diet preference support (veg/non-veg)
- ✅ Age and goal-based customization
- ✅ Data saved with user ID

### **Workout Recommender**
- ✅ AI-powered exercise recommendations
- ✅ Fitness level customization
- ✅ Equipment-based filtering
- ✅ Body part targeting
- ✅ Time-based workout plans
- ✅ Data saved with user ID

### **Task Tracker**
- ✅ Daily wellness habit tracking
- ✅ Progress percentage calculation
- ✅ Custom habit addition
- ✅ Step counter integration
- ✅ Motivation quotes
- ✅ Data saved with user ID

### **Dashboard**
- ✅ User overview and statistics
- ✅ Quick access to all features
- ✅ Recent activity tracking
- ✅ User profile management

## 🚀 Usage Examples

### **Creating a New Account**
1. Visit the homepage
2. Click "Join us"
3. Click "Create account"
4. Enter your details
5. Start using the features!

### **Using Nutrition Planner**
1. Login to your account
2. Go to Nutrition feature
3. Enter your details (age, height, weight, goals)
4. Get personalized nutrition plan
5. Click "Save Nutrition Plan" to store it

### **Using Workout Recommender**
1. Login to your account
2. Go to Workout feature
3. Select your fitness goals and preferences
4. Get AI recommendations
5. Click "Save Workout Plan" to store it

### **Using Task Tracker**
1. Login to your account
2. Go to Task feature
3. Check off your daily wellness habits
4. Add custom habits if needed
5. Click "Save Tasks" to store your progress

## 🔒 Security Features

- **Password Security**: Bcrypt hashing with salt
- **Session Security**: HTTP-only cookies, strict mode
- **SQL Injection Protection**: Prepared statements
- **XSS Protection**: Input validation and sanitization
- **CSRF Protection**: Session-based tokens
- **Authentication Required**: All features protected

## 🐛 Troubleshooting

### **Database Connection Issues**
1. Ensure XAMPP MySQL is running
2. Check database credentials in `backend/config.php`
3. Run `setup_database.php` to create database

### **Authentication Issues**
1. Clear browser cookies
2. Check browser console for errors
3. Verify PHP sessions are working

### **Feature Access Issues**
1. Ensure you're logged in
2. Check if session is active
3. Try logging out and back in

## 📞 Support

For issues or questions:
1. Check the browser console for errors
2. Verify all files are in the correct locations
3. Ensure XAMPP services are running
4. Test database connection with `test_connection.php`

---

**ACTIVAURA** - Empowering your health and fitness journey with personalized plans and secure data management. 
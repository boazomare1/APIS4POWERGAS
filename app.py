from flask import Flask, request, jsonify
from flask_bcrypt import Bcrypt
import MySQLdb
import MySQLdb.cursors
from datetime import datetime
import os
from dotenv import load_dotenv

# Load environment variables from .env file
load_dotenv()

app = Flask(__name__)
bcrypt = Bcrypt(app)

# MySQL configuration
app.config['MYSQL_HOST'] = os.getenv('MYSQL_HOST', 'localhost')
app.config['MYSQL_USER'] = os.getenv('MYSQL_USER', 'root')
app.config['MYSQL_PASSWORD'] = os.getenv('MYSQL_PASSWORD', '')
app.config['MYSQL_DB'] = os.getenv('MYSQL_DB', 'powergas')

def get_db_connection():
    return MySQLdb.connect(
        host=app.config['MYSQL_HOST'],
        user=app.config['MYSQL_USER'],
        password=app.config['MYSQL_PASSWORD'],
        db=app.config['MYSQL_DB'],
        cursorclass=MySQLdb.cursors.DictCursor
    )

@app.route('/check_connection', methods=['GET'])
def check_connection():
    try:
        connection = get_db_connection()
        cursor = connection.cursor()
        cursor.execute("SELECT 1")
        connection.close()
        return jsonify({"message": "Database connection successful"})
    except Exception as e:
        print(f"An error occurred: {e}")
        return jsonify({"error": "Database connection failed"}), 500

@app.route('/register_user', methods=['POST'])
def register_user():
    data = request.json
    username = data.get('username')
    email = data.get('email')
    password = data.get('password')
    phone = data.get('phone')
    first_name = data.get('first_name', '')
    last_name = data.get('last_name', '')

    # Validate required fields
    if not username or not email or not password or not phone:
        return jsonify({"error": "Missing required fields"}), 400

    # Hash the password
    hashed_password = bcrypt.generate_password_hash(password).decode('utf-8')
    created_on = int(datetime.utcnow().timestamp())
    ip_address = request.remote_addr  # Store IP as a string
    group_id = 1  # Default group ID

    try:
        connection = get_db_connection()
        cursor = connection.cursor()

        # Check if email already exists
        cursor.execute("SELECT email FROM sma_users WHERE email = %s", (email,))
        if cursor.fetchone():
            connection.close()
            return jsonify({"error": "The email address is already registered"}), 400
        # check if username already exists
        cursor.execute("SELECT username FROM sma_users WHERE username =%s", (username,))
        if cursor.fetchone():
            connection.close()
            return jsonify({"error": "The username is already taken "}), 400
        # check if phone number already exists
        cursor.execute("SELECT phone FROM sma_users where phone =%s",(phone,))
        if cursor.fetchone():
            connection.close()
            return jsonify({"error": "The phone number is already taken "}), 400 
        # Insert into sma_users
        query = """
            INSERT INTO sma_users (
                username, email, password, phone, first_name, last_name, created_on, group_id, ip_address
            ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)
        """
        cursor.execute(query, (username, email, hashed_password, phone, first_name, last_name, created_on, group_id, ip_address))
        connection.commit()
        connection.close()

        return jsonify({"message": "User registered successfully"})

    except Exception as e:
        print(f"An error occurred: {e}")
        return jsonify({"error": "An error occurred while registering the user"}), 500

@app.route('/login_user', methods=['POST'])
def login_user():
    data = request.json
    email = data.get('email')
    password = data.get('password')

    # Validate required fields
    if not email or not password:
        return jsonify({"error": "Missing required fields"}), 400

    try:
        connection = get_db_connection()
        cursor = connection.cursor()

        # Get user data
        query = "SELECT id, username, password, active, first_name, last_name, phone, avatar, stock, group_id FROM sma_users WHERE email = %s"
        cursor.execute(query, (email,))
        user = cursor.fetchone()

        connection.close()

        if user and bcrypt.check_password_hash(user['password'], password):
            if user['active'] == 1:
                user_data = {
                    "id": user['id'],
                    "username": user['username'],
                    "email": email,
                    "first_name": user['first_name'],
                    "last_name": user['last_name'],
                    "phone": user['phone'],
                    "avatar": user['avatar'],
                    "stock": user['stock'],
                    "group_id": user['group_id']
                }
                return jsonify({"success": "1", "message": "Login successful", "user": user_data})
            else:
                return jsonify({"success": "4", "message": "The user is not yet activated, please contact system admin"})
        else:
            return jsonify({"success": "0", "message": "The email or password is incorrect"})
    except Exception as e:
        print(f"An error occurred: {e}")
        return jsonify({"error": "An error occurred while logging in"}), 500

@app.route('/edit_user', methods=['PUT'])
def edit_user():
    data = request.json
    user_id = data.get('id')
    username = data.get('username')
    email = data.get('email')
    phone = data.get('phone')
    first_name = data.get('first_name', '')
    last_name = data.get('last_name', '')

    # Validate required fields
    if not user_id:
        return jsonify({"error": "Missing user ID"}), 400

    try:
        connection = get_db_connection()
        cursor = connection.cursor()

        # Update user data
        query = """
            UPDATE sma_users
            SET username = %s, email = %s, phone = %s, first_name = %s, last_name = %s
            WHERE id = %s
        """
        cursor.execute(query, (username, email, phone, first_name, last_name, user_id))
        connection.commit()
        connection.close()

        if cursor.rowcount > 0:
            return jsonify({"message": "User updated successfully"})
        else:
            return jsonify({"error": "User not found"}), 404

    except Exception as e:
        print(f"An error occurred: {e}")
        return jsonify({"error": "An error occurred while updating the user"}), 500

@app.route('/error_check', methods=['GET'])
def error_check():
    return jsonify({"message": "This is an error checking endpoint"})

if __name__ == '__main__':
    app.run(debug=True)
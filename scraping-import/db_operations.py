import mysql.connector
import random
from scraper import CURRENT_TIMESTAMP, CURRENT_USER, determine_category

# Database configuration
DB_CONFIG = {
    "host": "localhost",
    "database": "freelancima",
    "user": "root",
    "password": ""
}

def create_db_connection():
    """Create and return a database connection"""
    try:
        connection = mysql.connector.connect(**DB_CONFIG, autocommit=False)
        print("✅ Database connection established")
        return connection
    except mysql.connector.Error as err:
        print(f"❌ Database connection failed: {err}")
        return None

def import_to_database(scraped_data, stats):
    """Import scraped data to database"""
    connection = None
    cursor = None
    
    try:
        # Initialize database connection
        connection = create_db_connection()
        if not connection:
            return False
        
        # Initialize cursor
        cursor = connection.cursor()
        
        # Get existing profile links
        cursor.execute("SELECT profile_link FROM freelancers")
        existing_links = {row[0] for row in cursor.fetchall()}
        
        added_count = 0
        
        # Process each scraped freelancer
        for freelancer in scraped_data:
            try:
                username = freelancer["username"]
                profile_link = freelancer["profile_link"]
                profile_image = freelancer["profile_image"]
                rating = freelancer["rating"]
                reviews = freelancer["reviews"]
                description = freelancer["description"]
                
                if profile_link in existing_links:
                    print(f"⏩ Skipping existing freelancer: {username}")
                    continue
                
                # Calculate price and category
                price = str(max(15, min(50, int(25 * (1 + float(rating) / 10 + 
                             (int(reviews) if reviews.isdigit() else 0) / 200)))))
                category = determine_category(description)
                
                # Insert into database
                cursor.execute("""
                    INSERT INTO freelancers 
                    (username, profile_link, profile_image, rating, reviews, 
                     short_description, price, category, created_at)
                    VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)
                """, (
                    username, profile_link, profile_image, float(rating),
                    int(reviews) if reviews.isdigit() else 0,
                    description, float(price), category, CURRENT_TIMESTAMP
                ))
                
                existing_links.add(profile_link)
                added_count += 1
                print(f"✅ Added to database: {username} ({category})")
                
                # Commit every 5 freelancers
                if added_count % 5 == 0:
                    connection.commit()
                
            except Exception as e:
                print(f"⚠️ Error importing freelancer: {str(e)}")
                continue
        
        # Insert final metadata
        cursor.execute("""
            INSERT INTO metadata (last_updated, updated_by, record_count)
            VALUES (%s, %s, %s)
        """, (CURRENT_TIMESTAMP, CURRENT_USER, added_count))
        
        connection.commit()
        print(f"\n✅ Successfully imported {added_count} freelancers to the database")
        
        return True
        
    except Exception as e:
        if connection:
            connection.rollback()
        print(f"\n❌ Error during database import: {str(e)}")
        return False
        
    finally:
        if cursor:
            cursor.close()
        if connection:
            connection.close()

if __name__ == "__main__":
    print("=== FreeLanci.ma Database Importer ===")
    print(f"Current user: {CURRENT_USER}")
    print(f"Timestamp: {CURRENT_TIMESTAMP}")
    print("=" * 50)
    
    # This code will only run if you execute this file directly
    print("To import data, first run the scraper.py file.")
    print("This module is designed to be imported by scraper.py")
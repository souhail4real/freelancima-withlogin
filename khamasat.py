import requests
from bs4 import BeautifulSoup
import re
import time
import random
import urllib.parse
import mysql.connector
from datetime import datetime

# Constants - using exact values provided
CURRENT_USER = "souhail4real"
CURRENT_DATETIME = "2025-06-05 14:25:05"  # Format UTC YYYY-MM-DD HH:MM:SS

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

def extract_service_data(service_div, category="Desktop Applications"):
    """Extract data from a service div using corrected selectors"""
    try:
        data = {}
        
        # Get service ID (only to avoid duplicates, not exported)
        service_id = ""
        div_id = service_div.get('id', '')
        if div_id:
            id_match = re.search(r'service-(\d+)', div_id)
            if id_match:
                service_id = id_match.group(1)
        
        # Service title (short_description)
        title_el = service_div.select_one("div.product-body > h4 > a")
        if not title_el:
            title_el = service_div.select_one("a.card-service-link, a.url-product")
        
        if title_el:
            data['short_description'] = title_el.get('title', '') or title_el.text.strip()
            service_href = title_el.get('href', '')
            
            # Extract category if present
            cat_match = re.search(r'/programming/([^/]+)/', service_href)
            if cat_match:
                category = cat_match.group(1).replace('-', ' ').title()
        else:
            data['short_description'] = ""
        
        # Profile link and username
        profile_link = service_div.select_one("a.seller-card__avatar-link, a[href^='/user/']")
        if profile_link:
            profile_href = profile_link.get('href', '')
            data['profile_link'] = "https://khamsat.com" + profile_href if profile_href.startswith('/') else profile_href
            username_match = re.search(r'/user/([^/]+)', profile_href)
            if username_match:
                # Decode Arabic usernames
                encoded_username = username_match.group(1)
                data['username'] = urllib.parse.unquote(encoded_username)
            else:
                data['username'] = "unknown"
        else:
            data['profile_link'] = ""
            data['username'] = "unknown"
        
        # Profile image
        profile_img = service_div.select_one("img.profile-img, img.avatar-img")
        if not profile_img:
            # Fallback to service image
            profile_img = service_div.select_one("img.product-img")
        data['profile_image'] = profile_img.get('src', '') if profile_img else ""
        
        # Rating
        rating_el = service_div.select_one("div.service-rating span, .rating span")
        if rating_el and rating_el.text.strip():
            try:
                rating_text = rating_el.text.strip()
                rating_match = re.search(r'(\d+(?:\.\d+)?)', rating_text)
                data['rating'] = float(rating_match.group(1)) if rating_match else 4.0
            except:
                data['rating'] = 4.0
        else:
            data['rating'] = 4.0
        
        # Reviews
        reviews_el = service_div.select_one("div.product-body-rate.line-clamp-1 > a > ul > li.c-list__item.info")
        if not reviews_el:
            reviews_el = service_div.select_one(".service-stats .reviews, .evaluation-count")
        
        if reviews_el:
            reviews_text = reviews_el.text.strip()
            # First look for format (49)
            reviews_match = re.search(r'\((\d+)\)', reviews_text)
            if reviews_match:
                data['reviews'] = int(reviews_match.group(1))
            else:
                # Standard format
                reviews_match = re.search(r'(\d+)', reviews_text)
                data['reviews'] = int(reviews_match.group(1)) if reviews_match else 0
        else:
            data['reviews'] = 0
        
        # Price - without currency field
        price_el = service_div.select_one("div.product-body > div.product-price > div > span")
        if not price_el:
            price_el = service_div.select_one("span.service-price, .price")
        
        if price_el:
            price_text = price_el.text.strip()
            # First check dollar format
            dollar_match = re.search(r'(\d+(?:\.\d+)?)\$', price_text)
            if dollar_match:
                data['price'] = float(dollar_match.group(1))
            else:
                # Standard format
                price_match = re.search(r'(\d+)', price_text)
                data['price'] = int(price_match.group(1)) if price_match else 0
        else:
            data['price'] = 0
        
        # Category and timestamp
        data['category'] = category
        data['created_at'] = CURRENT_DATETIME
        
        return data, service_id  # Return ID separately to avoid duplicates
    except Exception as e:
        print(f"Error extracting data: {e}")
        return None, None

def scrape_and_import_khamsat():
    url = "https://khamsat.com/programming/desktop-app"  # Default URL
    headers = {
        "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36",
        "Accept-Language": "en-US,en;q=0.9,ar;q=0.8"
    }
    
    connection = None
    cursor = None
    
    try:
        # Initialize database connection
        connection = create_db_connection()
        if not connection:
            return False
        
        # Initialize cursor
        cursor = connection.cursor()
        
        print(f"Scraping started by: {CURRENT_USER} at {CURRENT_DATETIME}")
        r = requests.get(url, headers=headers, timeout=15)
        r.raise_for_status()
        
        soup = BeautifulSoup(r.text, "html.parser")
        
        # Try multiple selectors to find services
        selectors = [
            "div[id^='service-']",  # This selector is now priority
            "div.service-card", 
            "div.product-card"
        ]
        
        service_divs = []
        for selector in selectors:
            service_divs = soup.select(selector)
            if len(service_divs) > 0:
                print(f"Found {len(service_divs)} services with selector: {selector}")
                break
        
        if not service_divs:
            print("No services found with known selectors!")
            return False
        
        seen_services = set()  # To avoid duplicates
        added_count = 0
        
        for i, service_div in enumerate(service_divs):
            print(f"Processing service {i+1}/{len(service_divs)}")
            data, service_id = extract_service_data(service_div)
            
            # Avoid duplicates by service ID or profile link
            if data and data.get('username') != "unknown":
                service_key = service_id or data.get('profile_link', '')
                if service_key and service_key not in seen_services:
                    # Insert into database
                    cursor.execute("""
                        INSERT INTO freelancers 
                        (username, profile_link, profile_image, rating, reviews, 
                         short_description, price, category, created_at)
                        VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)
                    """, (
                        data['username'], 
                        data['profile_link'], 
                        data['profile_image'], 
                        float(data['rating']),
                        int(data['reviews']),
                        data['short_description'], 
                        float(data['price']), 
                        data['category'], 
                        data['created_at']
                    ))
                    
                    added_count += 1
                    seen_services.add(service_key)
                    
                    # Commit every 5 freelancers
                    if added_count % 5 == 0:
                        connection.commit()
                        print(f"✅ Committed batch of 5 freelancers (total: {added_count})")
            
            time.sleep(random.uniform(0.1, 0.3))  # Be nice to the server
        
        # Insert metadata
        cursor.execute("""
            INSERT INTO metadata (last_updated, updated_by, record_count)
            VALUES (%s, %s, %s)
        """, (CURRENT_DATETIME, CURRENT_USER, added_count))
        
        # Final commit
        connection.commit()
        
        print(f"✅ Successfully extracted and imported {added_count} freelancers to database")
        
        # Display a sample for validation
        if added_count > 0:
            cursor.execute("SELECT * FROM freelancers ORDER BY id DESC LIMIT 1")
            sample = cursor.fetchone()
            
            if sample:
                print("\nSample data imported:")
                print(f"ID: {sample[0]}")
                print(f"Username: {sample[1]}")
                print(f"Description: {sample[6][:50]}...")
                print(f"Price: {sample[7]}")
                print(f"Reviews: {sample[5]}")
        
        return True
    
    except requests.exceptions.RequestException as e:
        if connection:
            connection.rollback()
        print(f"Request error: {e}")
        return False
    except mysql.connector.Error as e:
        if connection:
            connection.rollback()
        print(f"Database error: {e}")
        return False
    except Exception as e:
        if connection:
            connection.rollback()
        print(f"Unexpected error: {e}")
        return False
    finally:
        if cursor:
            cursor.close()
        if connection:
            connection.close()

if __name__ == "__main__":
    print("=== Khamsat Scraper and Database Importer ===")
    print(f"Current user: {CURRENT_USER}")
    print(f"Timestamp: {CURRENT_DATETIME}")
    print("=" * 50)
    
    success = scrape_and_import_khamsat()
    
    if success:
        print("\n✅ Scraping and database import completed successfully!")
    else:
        print("\n❌ Process failed. Please check the logs for details.")
    
    print("\nScript execution completed.")
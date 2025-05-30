from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.edge.service import Service
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
import mysql.connector
import time
import random
from datetime import datetime

# Update with your current timestamp and username
CURRENT_TIMESTAMP = "2025-05-28 10:13:59"
CURRENT_USER = "souhail4real"

# Database configuration
DB_CONFIG = {
    "host": "localhost",
    "database": "freelancima",
    "user": "root",
    "password": ""
}

# Your existing categories dictionary remains the same
categories = {
    'web-development': ['web', 'developer', 'development', 'javascript', 'react', 'vue', 'angular', 'node', 'php', 'laravel', 'html', 'css', 'bootstrap', 'tailwind', 'wordpress', 'shopify', 'frontend', 'backend', 'full stack'],
    'mobile-development': ['mobile', 'android', 'ios', 'flutter', 'react native', 'kotlin', 'swift', 'dart', 'xamarin', 'ionic', 'app development', 'pwa', 'mobile app'],
    'data-science-ml': ['data', 'machine learning', 'artificial intelligence', 'ai', 'ml', 'python', 'pandas', 'tensorflow', 'pytorch', 'scikit', 'data analysis', 'data scientist', 'big data', 'nlp', 'deep learning', 'neural network'],
    'cybersecurity': ['security', 'cyber', 'ethical hacking', 'penetration testing', 'pen test', 'infosec', 'firewall', 'cryptography', 'encryption', 'vulnerability', 'security audit', 'siem', 'compliance', 'gdpr'],
    'cloud-devops': ['cloud', 'aws', 'azure', 'gcp', 'google cloud', 'devops', 'docker', 'kubernetes', 'jenkins', 'ci/cd', 'terraform', 'ansible', 'infrastructure', 'iaas', 'paas', 'saas', 'microservices', 'serverless']
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

def setup_edge_driver():
    """Setup and return Edge WebDriver with proper options"""
    options = webdriver.EdgeOptions()
    options.add_argument('--disable-blink-features=AutomationControlled')
    options.add_argument('--disable-notifications')
    options.add_experimental_option('excludeSwitches', ['enable-automation'])
    options.add_experimental_option('useAutomationExtension', False)
    
    service = Service(r"C:\Users\SOUHAIL\Downloads\edgedriver_win64\msedgedriver.exe")
    driver = webdriver.Edge(service=service, options=options)
    
    # Add undetectable properties
    driver.execute_script("Object.defineProperty(navigator, 'webdriver', {get: () => undefined})")
    
    return driver

def scrape_and_import():
    """Main function to scrape data and import directly to database without verification"""
    connection = None
    driver = None
    cursor = None
    
    try:
        # Initialize database connection
        connection = create_db_connection()
        if not connection:
            return False
        
        # Initialize cursor
        cursor = connection.cursor()
        
        # Initialize Selenium
        driver = setup_edge_driver()
        added_count = 0
        
        # Loop through pages
        for page in range(1,5):
            try:
                print(f"\n📄 Processing page {page} of 39...")
                url = f"https://www.peopleperhour.com/services/technology-programming/mobile-app-development?page={page}"
                
                driver.get(url)
                time.sleep(5)  # Add initial wait
                
                # Wait for freelancer elements
                freelancer_elements = WebDriverWait(driver, 15).until(
                    EC.presence_of_all_elements_located(
                        (By.CSS_SELECTOR, "a.card__user-link⤍HourlieTileMeta⤚F1h11")
                    )
                )
                
                # Process each freelancer
                for freelancer in freelancer_elements[:15]:
                    try:
                        username = freelancer.find_element(
                            By.CSS_SELECTOR, "span.card__username⤍HourlieTileMeta⤚1hJNR"
                        ).text.strip()
                        
                        profile_link = freelancer.get_attribute("href")
                        
                        # Extract other data
                        profile_image = freelancer.find_element(By.CSS_SELECTOR, "img").get_attribute("src")
                        rating = freelancer.find_element(
                            By.CSS_SELECTOR, "span.card__freelancer-ratings⤍HourlieTileMeta⤚1zn5P"
                        ).text.split()[0]
                        reviews = freelancer.find_element(
                            By.CSS_SELECTOR, "span.card__freelancer-reviews⤍HourlieTileMeta⤚HCTu6"
                        ).text.strip().replace("(", "").replace(")", "")
                        
                        # Visit profile page
                        driver.execute_script("window.open(arguments[0]);", profile_link)
                        driver.switch_to.window(driver.window_handles[-1])
                        time.sleep(3)
                        
                        try:
                            description = WebDriverWait(driver, 10).until(
                                EC.presence_of_element_located((By.CSS_SELECTOR, "p.member-job"))
                            ).text.strip()
                        except:
                            description = "Professional Freelancer"
                        
                        # Close profile tab and switch back
                        driver.close()
                        driver.switch_to.window(driver.window_handles[0])
                        
                        # Calculate price and category
                        price = str(max(15, min(50, int(25 * (1 + float(rating) / 10 + 
                                 (int(reviews) if reviews.isdigit() else 0) / 200)))))
                        category = determine_category(description)
                        
                        # Use current timestamp instead of random date
                        created_at = CURRENT_TIMESTAMP
                        
                        # Insert into database without checking if it already exists
                        cursor.execute("""
                            INSERT INTO freelancers 
                            (username, profile_link, profile_image, rating, reviews, 
                             short_description, price, category, created_at)
                            VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)
                        """, (
                            username, profile_link, profile_image, float(rating),
                            int(reviews) if reviews.isdigit() else 0,
                            description, float(price), category, created_at
                        ))
                        
                        added_count += 1
                        print(f"✅ Added: {username} ({category}) - Created at: {created_at}")
                        
                        # Commit every 5 freelancers
                        if added_count % 5 == 0:
                            connection.commit()
                        
                    except Exception as e:
                        print(f"⚠️ Error processing freelancer: {str(e)}")
                        continue
                
                # Add delay between pages
                time.sleep(random.uniform(2, 4))
                
            except Exception as e:
                print(f"⚠️ Error processing page {page}: {str(e)}")
                continue
            
            # Commit at the end of each page
            connection.commit()
        
        # Insert final metadata
        cursor.execute("""
            INSERT INTO metadata (last_updated, updated_by, record_count)
            VALUES (%s, %s, %s)
        """, (CURRENT_TIMESTAMP, CURRENT_USER, added_count))
        
        connection.commit()
        print(f"\n✅ Successfully processed and added {added_count} freelancers with current timestamp")
        
        return True
        
    except Exception as e:
        if connection:
            connection.rollback()
        print(f"\n❌ Error during scraping and import: {str(e)}")
        return False
        
    finally:
        if cursor:
            cursor.close()
        if connection:
            connection.close()
        if driver:
            driver.quit()

def determine_category(description):
    """Your existing determine_category function"""
    description = description.lower()
    category_matches = {category: sum(1 for keyword in keywords if keyword in description) 
                       for category, keywords in categories.items()}
    max_matches = max(category_matches.values(), default=0)
    if max_matches > 0:
        best_categories = [cat for cat, matches in category_matches.items() 
                         if matches == max_matches]
        return random.choice(best_categories)
    return 'web-development'

if __name__ == "__main__":
    print("=== FreeLanci.ma Scraper and Importer ===")
    print(f"Current user: {CURRENT_USER}")
    print(f"Timestamp: {CURRENT_TIMESTAMP}")
    print("=" * 50)
    
    success = scrape_and_import()
    
    if success:
        print("\n✅ Scraping and import completed successfully!")
    else:
        print("\n❌ Process failed. Please check the logs for details.")
    
    print("\nScript execution completed.")
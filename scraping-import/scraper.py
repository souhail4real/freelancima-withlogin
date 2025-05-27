from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.edge.service import Service
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
import time
import random

# Update with your current timestamp and username
CURRENT_TIMESTAMP = "2025-05-26 23:39:12"
CURRENT_USER = "souhail4real"

# Your existing categories dictionary
categories = {
    'web-development': ['web', 'developer', 'development', 'javascript', 'react', 'vue', 'angular', 'node', 'php', 'laravel', 'html', 'css', 'bootstrap', 'tailwind', 'wordpress', 'shopify', 'frontend', 'backend', 'full stack'],
    'mobile-development': ['mobile', 'android', 'ios', 'flutter', 'react native', 'kotlin', 'swift', 'dart', 'xamarin', 'ionic', 'app development', 'pwa', 'mobile app'],
    'data-science-ml': ['data', 'machine learning', 'artificial intelligence', 'ai', 'ml', 'python', 'pandas', 'tensorflow', 'pytorch', 'scikit', 'data analysis', 'data scientist', 'big data', 'nlp', 'deep learning', 'neural network'],
    'cybersecurity': ['security', 'cyber', 'ethical hacking', 'penetration testing', 'pen test', 'infosec', 'firewall', 'cryptography', 'encryption', 'vulnerability', 'security audit', 'siem', 'compliance', 'gdpr'],
    'cloud-devops': ['cloud', 'aws', 'azure', 'gcp', 'google cloud', 'devops', 'docker', 'kubernetes', 'jenkins', 'ci/cd', 'terraform', 'ansible', 'infrastructure', 'iaas', 'paas', 'saas', 'microservices', 'serverless']
}

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

def scrape_freelancers(existing_links=None):
    """Function to scrape freelancers data from PeoplePerHour"""
    if existing_links is None:
        existing_links = set()
        
    driver = None
    scraped_data = []
    stats = {"added": 0, "skipped": 0}
    
    try:
        # Initialize Selenium
        driver = setup_edge_driver()
        
        # Loop through pages
        for page in range(1, 3):
            try:
                print(f"\n📄 Processing page {page} of 30...")
                url = f"https://www.peopleperhour.com/services/technology-programming/website-development?page={page}"
                
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
                        
                        if profile_link in existing_links:
                            print(f"⏩ Skipping existing freelancer: {username}")
                            stats["skipped"] += 1
                            continue
                        
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
                        
                        # Append data to scraped_data list
                        scraped_data.append({
                            "username": username,
                            "profile_link": profile_link,
                            "profile_image": profile_image,
                            "rating": rating,
                            "reviews": reviews,
                            "description": description
                        })
                        
                        stats["added"] += 1
                        print(f"✅ Scraped: {username}")
                        
                    except Exception as e:
                        print(f"⚠️ Error processing freelancer: {str(e)}")
                        continue
                
                # Add delay between pages
                time.sleep(random.uniform(2, 4))
                
            except Exception as e:
                print(f"⚠️ Error processing page {page}: {str(e)}")
                continue
        
        return scraped_data, stats
        
    except Exception as e:
        print(f"\n❌ Error during scraping: {str(e)}")
        return scraped_data, stats
        
    finally:
        if driver:
            driver.quit()

def determine_category(description):
    """Determine category based on description"""
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
    print("=== FreeLanci.ma Scraper ===")
    print(f"Current user: {CURRENT_USER}")
    print(f"Timestamp: {CURRENT_TIMESTAMP}")
    print("=" * 50)
    
    # This code will only run if you execute this file directly
    import db_operations
    
    # Get existing links from database
    connection = db_operations.create_db_connection()
    cursor = connection.cursor()
    cursor.execute("SELECT profile_link FROM freelancers")
    existing_links = {row[0] for row in cursor.fetchall()}
    cursor.close()
    connection.close()
    
    # Scrape freelancers
    scraped_data, stats = scrape_freelancers(existing_links)
    
    # Import to database
    success = db_operations.import_to_database(scraped_data, stats)
    
    if success:
        print("\n✅ Scraping and import completed successfully!")
    else:
        print("\n❌ Process failed. Please check the logs for details.")
    
    print("\nScript execution completed.")
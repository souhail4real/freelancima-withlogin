import requests
from bs4 import BeautifulSoup
import json
import re
import time
import random
import logging
import datetime

# Set up logging
logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(levelname)s - %(message)s')

# Define constants
CURRENT_USER = "souhail4real"
CURRENT_DATETIME = datetime.datetime.now().strftime('%Y-%m-%d %H:%M:%S')  # Current time

def extract_service_data(service_div, category="Desktop Applications"):
    """Extract all relevant data from a service div for JSON storage"""
    try:
        data = {}
        
        # Current timestamp for created_at
        data['created_at'] = CURRENT_DATETIME
        
        # Category (from function parameter)
        data['category'] = category
        
        # Service URL and profile link extraction
        service_link = service_div.select_one("a.card-service-link, a.url-product, a[href*='programming/desktop-app']")
        if service_link:
            service_href = service_link.get('href', '')
            # Store the service URL
            service_url = "https://khamsat.com" + service_href if service_href.startswith('/') else service_href
            
            # Try to get the service ID from the URL for reference
            service_id_match = re.search(r'/(\d+)-', service_href)
            service_id = service_id_match.group(1) if service_id_match else "unknown"
            
            logging.debug(f"Found service URL: {service_url}, ID: {service_id}")
        else:
            service_url = ""
            service_id = "unknown"
        
        # Profile info - try multiple selectors
        profile_link = service_div.select_one("a.seller-card__avatar-link, a.profile-card-link, a.profile-link, a[href^='/user/'], .seller-info a, .trusted-seller a")
        if profile_link:
            profile_href = profile_link.get('href', '')
            data['profile_link'] = "https://khamsat.com" + profile_href if profile_href.startswith('/') else profile_href
            
            # Extract username from profile link
            username_match = re.search(r'/user/([^/]+)', profile_href)
            if username_match:
                data['username'] = username_match.group(1)
            else:
                data['username'] = profile_link.text.strip()
            
            logging.debug(f"Found profile: {data.get('username', '')}, URL: {data.get('profile_link', '')}")
        else:
            # Fallback: try to find any link that contains '/user/'
            any_user_link = service_div.select_one("a[href*='/user/']")
            if any_user_link:
                profile_href = any_user_link.get('href', '')
                data['profile_link'] = "https://khamsat.com" + profile_href if profile_href.startswith('/') else profile_href
                
                # Extract username from profile link
                username_match = re.search(r'/user/([^/]+)', profile_href)
                data['username'] = username_match.group(1) if username_match else "unknown_user"
            else:
                data['profile_link'] = ""
                data['username'] = f"unknown_user_{service_id}"
                logging.warning(f"No profile link found for service {service_id}")
        
        # Profile image - try more selectors with fallback to service image
        profile_img = service_div.select_one("img.profile-img, img.avatar-img, div.avatar img, div.user-img img, .seller-card__avatar img, .seller-info img")
        if profile_img:
            data['profile_image'] = profile_img.get('src', '')
            logging.debug(f"Found profile image: {data['profile_image']}")
        else:
            # Fallback to service image if no profile image
            service_img = service_div.select_one("img.product-img, img.service-img, .card-img-top")
            if service_img:
                data['profile_image'] = service_img.get('src', '')
                logging.debug(f"Using service image as profile image: {data['profile_image']}")
            else:
                data['profile_image'] = ""
                logging.warning(f"No profile image found for {data.get('username', 'unknown')}")
        
        # Rating - try more comprehensive selectors
        rating_div = service_div.select_one("div.rating, span.stars, div.rate, .rating-stars, .service-rating, .seller-rating")
        if rating_div:
            # Try to extract numeric rating (usually between 0-5)
            rating_text = rating_div.text.strip()
            rating_match = re.search(r'(\d+(\.\d+)?)', rating_text)
            if rating_match:
                data['rating'] = float(rating_match.group(1))
            else:
                # Count star icons as fallback
                filled_stars = len(rating_div.select("i.fas.fa-star, i.fa.fa-star.active, .star-filled, .star.active"))
                half_stars = len(rating_div.select("i.fas.fa-star-half-alt, i.fa.fa-star-half, .star-half"))
                empty_stars = len(rating_div.select("i.far.fa-star, i.fa.fa-star:not(.active), .star:not(.active)"))
                
                if filled_stars > 0 or half_stars > 0 or empty_stars > 0:
                    total_stars = filled_stars + half_stars + empty_stars
                    data['rating'] = (filled_stars + (half_stars * 0.5)) / (total_stars if total_stars > 0 else 1) * 5
                else:
                    # If we can't determine stars, default to average rating
                    data['rating'] = 4.0  # Default reasonable rating
            logging.debug(f"Found rating: {data.get('rating', 0)}")
        else:
            # Default to average rating if not found
            data['rating'] = 4.0
            logging.warning(f"No rating found for {data.get('username', 'unknown')}, using default")
        
        # Reviews count
        reviews_el = service_div.select_one("li.c-list__item.info, .rating-count, span.reviews, .service-stats, .evaluation-count")
        if reviews_el:
            reviews_text = reviews_el.text.strip()
            reviews_match = re.search(r'(\d+)', reviews_text)
            data['reviews'] = int(reviews_match.group(1)) if reviews_match else 0
            logging.debug(f"Found reviews: {data['reviews']}")
        else:
            data['reviews'] = 0
            logging.warning(f"No reviews found for {data.get('username', 'unknown')}")
        
        # Service title (used as short_description)
        title_el = service_div.select_one("a[title], h3.product-title, .service-title, .card-title, .product-name")
        if title_el:
            if title_el.has_attr('title'):
                data['short_description'] = title_el['title']
            else:
                data['short_description'] = title_el.text.strip()
            logging.debug(f"Found description: {data['short_description']}")
        else:
            # Try to find any heading element
            heading = service_div.select_one("h1, h2, h3, h4, h5")
            if heading:
                data['short_description'] = heading.text.strip()
            else:
                data['short_description'] = f"Service by {data.get('username', 'unknown')}"
                logging.warning(f"No service title/description found for {data.get('username', 'unknown')}")
        
        # Price
        price_el = service_div.select_one(".product-price span, .service-price, .price, .card-price, .amount")
        if price_el:
            price_text = price_el.text.strip()
            price_match = re.search(r'(\d+)', price_text)
            data['price'] = int(price_match.group(1)) if price_match else 0
            logging.debug(f"Found price: {data['price']}")
        else:
            data['price'] = 0
            logging.warning(f"No price found for {data.get('username', 'unknown')}")
            
        return data
    except Exception as e:
        logging.error(f"Error extracting service data: {e}", exc_info=True)
        return None

def scrape_khamsat_desktop_app():
    url = "https://khamsat.com/programming/desktop-app"
    headers = {
        "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36",
        "Accept-Language": "en-US,en;q=0.9,ar;q=0.8",
        "Accept": "text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8",
        "Connection": "keep-alive",
        "Cache-Control": "max-age=0",
        "Referer": "https://khamsat.com/"
    }
    
    try:
        logging.info(f"Script started by user: {CURRENT_USER} at {CURRENT_DATETIME}")
        logging.info(f"Requesting {url}")
        r = requests.get(url, headers=headers, timeout=15)
        r.raise_for_status()
        
        logging.info(f"Request successful. Status code: {r.status_code}")
        
        # Save the raw HTML for debugging purposes
        with open("khamsat_raw.html", "w", encoding="utf-8") as f:
            f.write(r.text)
        logging.info("Saved raw HTML to khamsat_raw.html for debugging")
        
        soup = BeautifulSoup(r.text, "html.parser")
        
        # Print the title as a quick check
        page_title = soup.title.text if soup.title else "No title found"
        logging.info(f"Page title: {page_title}")
        
        # Try multiple selectors to find service cards
        selectors = [
            "section.services-list > div[id^='service-'] > div",
            "div.service-card",
            "div.product-card",
            "div.service-wrap",
            "div.service-entry",
            "div.card-service",
            "div[id^='service-']"
        ]
        
        service_divs = []
        for selector in selectors:
            service_divs = soup.select(selector)
            logging.info(f"Selector '{selector}' found {len(service_divs)} service divs")
            if len(service_divs) > 0:
                break
        
        if len(service_divs) == 0:
            # If all selectors failed, look for elements containing relevant classes
            logging.warning("No services found with specific selectors, trying broader approach...")
            possible_services = []
            
            # Look for elements with URL-product links
            url_products = soup.select("a.url-product")
            for link in url_products:
                parent = link.parent
                if parent and parent not in possible_services:
                    possible_services.append(parent)
            
            logging.info(f"Found {len(possible_services)} potential service divs via url-product links")
            service_divs = possible_services
        
        freelancers = []
        
        for i, service_div in enumerate(service_divs):
            logging.info(f"Processing service {i+1}/{len(service_divs)}")
            data = extract_service_data(service_div, category="Desktop Applications")
            
            if data and (data.get('username') or data.get('short_description')):
                # Format the data to match your database schema
                freelancer = {
                    "username": data.get('username', ''),
                    "profile_link": data.get('profile_link', ''),
                    "profile_image": data.get('profile_image', ''),
                    "rating": data.get('rating', 0),
                    "reviews": data.get('reviews', 0),
                    "short_description": data.get('short_description', ''),
                    "price": data.get('price', 0),
                    "category": data.get('category', 'Desktop Applications'),
                    "created_at": data.get('created_at', CURRENT_DATETIME)
                }
                
                freelancers.append(freelancer)
                logging.info(f"Added freelancer: {freelancer['username']}")
            else:
                logging.warning(f"Skipped service {i+1} - insufficient data extracted")
            
            time.sleep(random.uniform(0.2, 0.6))  # Be polite
        
        # Save to JSON file
        if freelancers:
            # Create JSON output with metadata
            output = {
                "metadata": {
                    "user": CURRENT_USER,
                    "timestamp": CURRENT_DATETIME,
                    "source": url,
                    "count": len(freelancers)
                },
                "freelancers": freelancers
            }
            
            with open("khamsat_freelancers.json", "w", encoding="utf-8") as f:
                json.dump(output, f, ensure_ascii=False, indent=2)
            
            logging.info(f"Saved {len(freelancers)} freelancers to khamsat_freelancers.json")
            print(f"Successfully extracted data from {len(freelancers)} services")
            print(f"Data saved to khamsat_freelancers.json")
        else:
            logging.error("No freelancers were extracted successfully")
            print("Error: No services were found or extracted successfully")
    
    except requests.exceptions.RequestException as e:
        logging.error(f"Request error: {e}")
        print(f"Error connecting to website: {e}")
    except Exception as e:
        logging.error(f"Unexpected error: {e}", exc_info=True)
        print(f"An unexpected error occurred: {e}")

if __name__ == "__main__":
    scrape_khamsat_desktop_app()
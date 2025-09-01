from datetime import datetime
from bs4 import BeautifulSoup
from urllib.parse import urljoin
import requests
import random
import markdown
from PIL import Image
import re
from io import BytesIO
import pytz
import mysql.connector
import random
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.support.ui import WebDriverWait
from urllib.parse import urljoin
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.chrome.options import Options
from webdriver_manager.chrome import ChromeDriverManager
import openai


def search_google_news(keyword, country):
    url = "https://serpapi.com/search"
    api_key = "465aef34475a0092c9f4e4974e5075a0152db7994e26bb453b23be77e44da6e0"

    
    params = {
        "engine": "google_news",
        "q": keyword,
        "api_key": api_key,
        "gl": country,  # Country code (e.g., 'us' for United States, 'es' for Spain)
    }
    
    response = requests.get(url, params=params)
    
    if response.status_code == 200:
        news_results = response.json().get("news_results", [])
        
        if news_results:
            news = news_results[0]
            title = news.get("title", "No title")
            link = news.get("link", "No link")
            image_url = news.get("image", {}).get("src", "No image")
            
            # Assigning the link (URL) to a variable
            news_url = link
            
            # You can now use the `news_url` variable for further processing
            return title, news_url, image_url
        else:
            print("No news results found.")
            return None, None, None
    else:
        print(f"Error: Unable to fetch results (Status Code: {response.status_code})")
        return None, None, None

def scrape_google_news_article_new(country, keyword):
    # Define the categories
  
    # Set up Chrome options for headless browsing
    chrome_options = Options()
    chrome_options.add_argument("--headless")
    chrome_options.add_argument("--no-sandbox")
    chrome_options.add_argument("--disable-dev-shm-usage")

    # Initialize WebDriver
    driver = webdriver.Chrome(service=Service(ChromeDriverManager().install()), options=chrome_options)
    
    # Access the initial news page
    driver.get(f"https://news.google.com/search?q={keyword}&hl={'es-419' if country == 'MX' else 'es'}&gl={country}&ceid={country}%3A{'es-419' if country == 'MX' else 'es'}")

    wait = WebDriverWait(driver, 20)

    # Accept the cookie prompt if it appears
    try:
        consent_button = wait.until(EC.element_to_be_clickable((By.XPATH, "//span[text()='Aceptar todo']")))
        consent_button.click()
        print("Cookie consent accepted.")
    except:
        print("No consent prompt appeared.")

    # Try to retrieve article information
    try:
        first_article = wait.until(EC.presence_of_element_located((By.CSS_SELECTOR, "article")))

        # Extract the article link
        link_element = first_article.find_element(By.CSS_SELECTOR, "a")
        article_link = urljoin(driver.current_url, link_element.get_attribute("href"))

        # Extract the image URL
        try:
            img_element = first_article.find_element(By.CSS_SELECTOR, "img")
            image_url = urljoin(driver.current_url, img_element.get_attribute("src"))
        except:
            image_url = "No image found"

        try:
            title_element = first_article.find_element(By.CSS_SELECTOR, "a.JtKRv")
            article_title = title_element.text
        except:
            article_title = "No title found"

        # Store article info
        article_info = {
            'title': article_title,
            'url': article_link
        }

        # Print article details
        print("Article Info:")
        print(f"Title: {article_title}")
        print(f"Link: {article_link}")
        print(f"Image URL: {image_url}")

        # Handle opening article link in a new window and obtaining the real URL
        original_window = driver.current_window_handle
        link_element.click()

        wait.until(EC.number_of_windows_to_be(2))
        for window_handle in driver.window_handles:
            if window_handle != original_window:
                driver.switch_to.window(window_handle)
                break

        # Update URL with the current URL in the new window
        wait.until(EC.none_of(EC.url_contains("news.google.com")))

        article_info['url'] = driver.current_url

        return article_info  # Return article information after all operations
    except Exception as e:
        print("Failed to retrieve article information:", e)
        return {}  # Return an empty dictionary on failure

    finally:
        driver.quit()
        

def get_db_connection():
    return mysql.connector.connect(
        host="localhost",
        user="root",
        password="0955321170",
        database="automation"
    )
        
        
def get_prompt(phase_name):
    connection = get_db_connection()
    cursor = connection.cursor(dictionary=True)
    
    query = "SELECT first_phase, second_phase, title_phase FROM promts LIMIT 1"
    cursor.execute(query)
    result = cursor.fetchone()
    
    cursor.close()
    connection.close()
    
    return result[phase_name]



def get_random_personality(company_id):
    """
    Fetch a random personality for a given company_id using the get_db_connection function.

    Args:
        company_id (int): The ID of the company whose personality we want to fetch.

    Returns:
        dict: A dictionary with the selected personality details or None if no results are found.
    """
    try:
        # Use the get_db_connection function to establish the connection
        connection = get_db_connection()
        cursor = connection.cursor(dictionary=True)

        # Query to fetch all personalities for the given company_id
        query = """
        SELECT `id`, `company_id`, `name`, `description`, `tone`, `example`, `created_at`, `updated_at` 
        FROM `companies_personalities` 
        WHERE `company_id` = %s
        """
        cursor.execute(query, (company_id,))
        personalities = cursor.fetchall()

        # If no personalities are found, return None
        if not personalities:
            return None

        # Select a random personality from the fetched records
        selected_personality = random.choice(personalities)
        return selected_personality

    except mysql.connector.Error as err:
        print(f"Database error: {err}")
        return None

    finally:
        # Ensure the database connection is properly closed
        if 'connection' in locals() and connection.is_connected():
            cursor.close()
            connection.close()


def get_personality(text_to_replace_with, company_id):

    # Get database connection
    connection = get_db_connection()
    cursor = connection.cursor()

    try:
        # Select rows based on company ID
        query = """
            SELECT first, second, third, fourth, fifth
            FROM personalities
            WHERE company = %s
        """
        cursor.execute(query, (company_id,))
        rows = cursor.fetchall()

        if not rows:
            return None  # Return None if no rows are found

        # Choose a random column from a random row
        random_row = random.choice(rows)
        random_column_value = random.choice(random_row)

        # Perform the replacement
        random_column_value = random_column_value.replace('1st step', text_to_replace_with)

        return random_column_value

    finally:
        cursor.close()
        connection.close()


def make_it_good_article_with_per(first_phase, company):

    endpoint = 'https://api.openai.com/v1/chat/completions'
    api_key = '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'
    headers = {
        'Authorization': f'Bearer {api_key}',
        'Content-Type': 'application/json'
    }
    
    prompt = get_personality(first_phase, company_id=company)
    print ('here is the pers' + prompt)
    if not prompt:
        raise Exception("Failed to generate valid prompt for company: " + str(company))

    data = {
        "model": "gpt-4",
        "messages": [{"role": "user", "content": prompt}],
        'max_tokens': 3000
    }
    
    response = requests.post(endpoint, headers=headers, json=data)
    if response.status_code == 200:
        return response.json()['choices'][0]['message']['content']
    else:
        raise Exception("Failed to expand article: " + response.text)


def make_it_good_article(title, link):
    endpoint = 'https://api.openai.com/v1/chat/completions'
    api_key = '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'
    headers = {
        'Authorization': f'Bearer {api_key}',
        'Content-Type': 'application/json'
    }
    
    prompt = get_prompt("first_phase").format(title=title, url=link)
    data = {
        "model": "gpt-4-turbo",
        "messages": [{"role": "user", "content": prompt}],
        'max_tokens': 3000
    }
    
    response = requests.post(endpoint, headers=headers, json=data)
    if response.status_code == 200:
        return response.json()['choices'][0]['message']['content']
    else:
        raise Exception("Failed to expand article: " + response.text)

    
def make_article_formatted_seo(article_p_1):
    import requests
    
    endpoint = 'https://api.openai.com/v1/chat/completions'
    api_key = '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'
    headers = {
        'Authorization': f'Bearer {api_key}',
        'Content-Type': 'application/json'
    }
    
    prompt = get_prompt("second_phase").format(article_p_1=article_p_1)
    data = {
        "model": "gpt-4-turbo",
        "messages": [{"role": "user", "content": prompt}],
        'max_tokens': 3000
    }
    
    response = requests.post(endpoint, headers=headers, json=data)
    if response.status_code == 200:
        content = response.json()['choices'][0]['message']['content']
        # Remove triple backticks and optional "html" language specifier
        content_cleaned = content.replace('```html', '').replace('```', '').strip()
        return content_cleaned
    else:
        raise Exception("Failed to expand article: " + response.text)

def find_good_title(article):
    endpoint = 'https://api.openai.com/v1/chat/completions'
    api_key = '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'
    headers = {
        'Authorization': f'Bearer {api_key}',
        'Content-Type': 'application/json'
    }
    
    prompt = get_prompt("title_phase").format(article=article)
    data = {
        "model": "gpt-4-turbo",
        "messages": [{"role": "user", "content": prompt}],
        'max_tokens': 3000
    }
    
    response = requests.post(endpoint, headers=headers, json=data)
    if response.status_code == 200:
        first = response.json()['choices'][0]['message']['content']
        
        return first.replace('"', '')

    else:
        raise Exception("Failed to expand article: " + response.text)

def resize_and_compress_image(image_data_io, max_width=800, max_height=800, quality=85):
    with Image.open(image_data_io) as img:
        original_width, original_height = img.size
        ratio = min(max_width / original_width, max_height / original_height)
        new_width = int(original_width * ratio)
        new_height = int(original_height * ratio)
        img_resized = img.resize((new_width, new_height), Image.LANCZOS)
        img_output = BytesIO()
        img_resized.save(img_output, format=img.format)
        img_output.seek(0)
        return img_output

def find_image_with_title(title):
    access_key = "djSHEdiDRMwf3QRFNRchcPRnBf85fAvc3imYKNYAKQc"
    search_url = "https://api.unsplash.com/search/photos"

    def search_image(search_title):
        params = {
            "query": search_title,
            "client_id": access_key,
            "per_page": 1,  # Fetch only 1 image
        }
        try:
            response = requests.get(search_url, params=params)
            response.raise_for_status()  # Raise exception for HTTP errors
            return response.json()
        except requests.exceptions.HTTPError as e:
            print(f"HTTP Error while searching for images: {e}")
            return None
        except requests.exceptions.RequestException as e:
            print(f"Request exception: {e}")
            return None

    try:
        search_title = title  # Use the full title
        search_results = search_image(search_title)
        if not search_results or not search_results.get("results"):
            return None  # No results found

        image_info = search_results["results"][0]  # Get the first (and only) image
        image_url = image_info["urls"]["raw"]
        try:
            with requests.get(image_url, stream=True) as img_response:
                img_response.raise_for_status()  # Check if image retrieval was successful
                image_data = BytesIO(img_response.content)
                with Image.open(image_data) as img:
                    print(f"Image format for {image_url}: {img.format}")  # Print image format
                return image_data
        except requests.exceptions.HTTPError as e:
            print(f"Error retrieving image: {image_url} (HTTP Error: {e})")
        except Exception as e:
            print(f"General exception for image URL {image_url}: {e}")

    except requests.exceptions.RequestException as e:
        print(f"Overall Request Exception: {e}")

    return None

def upload_image_to_wordpress(image_data_io, auth_header,url):
    try:
        # Resize the image to prevent large uploads
        resized_image_data_io = resize_and_compress_image(image_data_io)
        
        if resized_image_data_io is None or not isinstance(resized_image_data_io, BytesIO):
            print("Invalid image data")
            return None

        # Prepare the file data for upload
        files = {
            'file': ('featured_image.jpg', resized_image_data_io.getvalue(), 'image/jpeg'),
        }

        # Make the POST request to upload the image
        response = requests.post(
        f'{url}wp-json/wp/v2/media',
        headers=auth_header, 
        files=files
        )


        if response.status_code == 201:
            media_data = response.json()
            media_id = media_data['id']
            media_url = media_data['source_url']
            return media_id, media_url

        else:
            print(f"Error uploading image to WordPress: {response.status_code} - {response.text}")
            return None
    except Exception as e:
        print(f"Error uploading image to WordPress: {str(e)}")
        return None



def post_to_wordpress(title, content, featured_media_ids, auth_header, category_id,url):
    # Convert Markdown bold syntax to HTML bold tags
    content = re.sub(r'\*\*(.*?)\*\*', r'<strong>\1</strong>', content)
    full_content = f"{content}"
    spain_tz = pytz.timezone('Europe/Madrid')
    mexico_tz = pytz.timezone('America/Mexico_City')

# Get current time in Spain and convert it to Mexico time

    spain_time = datetime.now(spain_tz)
    mexico_time = spain_time.astimezone(mexico_tz)

# Adjust the hour to a range between 9 AM and 3 PM in Mexico time
    if mexico_time.hour < 9:
        mexico_time = mexico_time.replace(hour=9, minute=0, second=0)
    elif mexico_time.hour > 15:
        mexico_time = mexico_time.replace(hour=15, minute=0, second=0)

# Convert to the format required by WordPress
    today_str = mexico_time.strftime('%Y-%m-%dT%H:%M:%S')

    url = f'{url}wp-json/wp/v2/posts'
    data = {
        'title': title,
        'content': full_content,
        
        'status': 'draft',  ### draft
        'featured_media': featured_media_ids,
        'date': today_str,
    }

    response = requests.post(url, headers=auth_header, json=data)

    if response.status_code == 201:
        print("Post created successfully")
        response_data = response.json()
        public_post_url = response_data.get('link')
        post_status = response_data.get('status')

        # Return a dictionary containing both 'link' and 'status'
        return {'link': public_post_url, 'status': post_status}

    else:
        print("Error creating post:", response.status_code)
        print("Error details:", response.text)
        # Return None or an empty dictionary to indicate failure
        return None
    



# Paso 3: Procesar el artículo
def get_random_personality(company_id):
    """
    Fetch a random personality for a given company_id from the database.

    Args:
        company_id (int): The ID of the company whose personality we want to fetch.

    Returns:
        dict: A dictionary with the selected personality details or None if no results are found.
    """
    try:
        # Establish the database connection
        connection = get_db_connection()
        cursor = connection.cursor(dictionary=True)

        # Query to fetch all personalities for the given company_id
        query = """
        SELECT `name`, `description`, `tone`, `example`
        FROM `companies_personalities`
        WHERE `company_id` = %s
        """
        cursor.execute(query, (company_id,))
        personalities = cursor.fetchall()

        # If no personalities are found, return None
        if not personalities:
            return None

        # Select a random personality from the fetched records
        selected_personality = random.choice(personalities)
        return selected_personality

    except mysql.connector.Error as err:
        print(f"Database error: {err}")
        return None

    finally:
        # Ensure the database connection is properly closed
        if 'connection' in locals() and connection.is_connected():
            cursor.close()
            connection.close()

# Step 3: Process the article

def process_article(article, company_id):
    openai.api_key = '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'

    # Fetch a random personality
    # Fetch a random personality
    personality_details = get_random_personality(company_id)
    if not personality_details:
        raise ValueError(f"No personalities found for Company ID {company_id}")

    # Neutral version with detailed prompts
    neutral_prompt = f"""
    Rewrite the following article in a neutral and objective tone, expanding each section with examples, case studies, and actionable insights. Ensure the content is detailed, engaging, and valuable for readers.

    Article:
    {article}
    """
    try:
        neutral_response = openai.ChatCompletion.create(
            model="gpt-4",
            messages=[{"role": "user", "content": neutral_prompt}],
            temperature=0.5,
            max_tokens=2500,
        )
        neutral_version = neutral_response['choices'][0]['message']['content']
    except openai.error.OpenAIError as e:
        print(f"Error generating neutral version: {e}")
        return None

    # Personality version with enriched tone
    personality_prompt = [
        {"role": "system", "content": f"You are a writer who {personality_details['description']}."},
        {"role": "user", "content": f"""
        Rewrite the following article using a {personality_details['tone']} tone. Provide detailed examples, use storytelling techniques, and incorporate actionable steps. Use {personality_details['example']} as inspiration.

        Article:
        {neutral_version}
        """}
    ]
    try:
        personality_response = openai.ChatCompletion.create(
            model="gpt-4",
            messages=personality_prompt,
            temperature=0.7,
            max_tokens=3000,
        )
        personality_version = personality_response['choices'][0]['message']['content']
    except openai.error.OpenAIError as e:
        print(f"Error generating personality version: {e}")
        return None

    # SEO and HTML conversion
    seo_prompt = f"""
    Convert the following article into SEO-optimized HTML with rich content:

    - Use <h1> for the main title, <h2> for main sections, and <h3> for subsections.
    - Expand each section with detailed explanations, examples, and action steps.
    - Add meta tags: a meta title (60 characters) and a meta description (150 characters).
    - Include 5-10 keywords naturally within the text.

    Article:
    {personality_version}
    """
    try:
        seo_response = openai.ChatCompletion.create(
            model="gpt-4",
            messages=[{"role": "user", "content": seo_prompt}],
            temperature=0.5,
            max_tokens=3000,
        )
        seo_html = seo_response['choices'][0]['message']['content']
    except openai.error.OpenAIError as e:
        print(f"Error generating SEO HTML: {e}")
        return None

    return seo_html

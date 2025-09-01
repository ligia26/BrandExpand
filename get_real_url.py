import logging
import urllib.parse
import time
from flask import Flask, request, jsonify
from selenium import webdriver
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from webdriver_manager.chrome import ChromeDriverManager

# Configure logging
logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(levelname)s - %(message)s')

# Initialize Flask app
app = Flask(__name__)

def get_final_url(initial_url):
    # Set up Chrome options
    chrome_options = Options()
    chrome_options.add_argument("--headless")
    chrome_options.add_argument("--no-sandbox")
    chrome_options.add_argument("--disable-dev-shm-usage")
    chrome_options.add_argument("--disable-gpu")

    driver = webdriver.Chrome(service=Service(ChromeDriverManager().install()), options=chrome_options)

    try:
        driver.get(initial_url)
        initial_page_url = driver.current_url

        logging.info(f"Initial URL: {initial_page_url}")

        try:
            WebDriverWait(driver, 60).until(
                lambda d: d.current_url != initial_page_url or
                          len(d.find_elements(By.XPATH, '//span[@jsname="V67aGc" and contains(text(), "Accept all")]')) > 0
            )
        except Exception as e:
            logging.error(f"Error while waiting for URL change or consent interaction: {e}")
            return driver.current_url

        current_url = driver.current_url
        logging.info(f"Current URL after waiting: {current_url}")

        if "consent.google.com" in current_url:
            try:
                consent_buttons = driver.find_elements(By.XPATH, '//span[@jsname="V67aGc" and contains(text(), "Accept all")]')
                if consent_buttons:
                    consent_buttons[0].click()
                    WebDriverWait(driver, 60).until(
                        lambda d: d.current_url != current_url
                    )
                    time.sleep(10)
                    WebDriverWait(driver, 30).until(
                        EC.presence_of_element_located((By.XPATH, '//body'))
                    )
                    current_url = driver.current_url

                parsed_url = urllib.parse.urlparse(current_url)
                query_params = urllib.parse.parse_qs(parsed_url.query)
                real_url = query_params.get('continue', [None])[0]
                if real_url:
                    real_url = urllib.parse.unquote(real_url)
                    logging.info(f"Real URL extracted: {real_url}")
                    return real_url
                else:
                    logging.error("Real URL not found in 'continue' parameter.")
            except Exception as e:
                logging.error(f"Error handling consent page: {e}")

        return current_url
    except Exception as e:
        logging.error(f"An error occurred: {e}")
        return None
    finally:
        driver.quit()

# Define a Flask route to expose the functionality
@app.route('/get-url', methods=['POST'])
def get_url():
    data = request.json
    initial_url = data.get('url')

    # If the URL is received as a dictionary, extract the value
    if isinstance(initial_url, dict):
        initial_url = initial_url.get('0')

    if not isinstance(initial_url, str):
        logging.error(f"Received invalid URL: {initial_url}")
        return jsonify({"error": "Invalid URL format."}), 400

    logging.info(f"Received URL: {initial_url}")

    final_url = get_final_url(initial_url)

    if final_url:
        return jsonify({"final_url": final_url})
    else:
        return jsonify({"error": "Failed to extract the final URL."}), 500

# Start the Flask app
if __name__ == "__main__":
    app.run(host='0.0.0.0', port=5001)

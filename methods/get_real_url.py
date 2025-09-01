from selenium import webdriver
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from webdriver_manager.chrome import ChromeDriverManager
import logging
import urllib.parse
import time

# Configure logging
logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(levelname)s - %(message)s')

def get_final_url(initial_url):
    # Set up Chrome options
    chrome_options = Options()
    chrome_options.add_argument("--headless")  # Run Chrome in headless mode
    chrome_options.add_argument("--no-sandbox")  # Required in some environments
    chrome_options.add_argument("--disable-dev-shm-usage")  # Overcome limited resource problems
    chrome_options.add_argument("--disable-gpu")  # Applicable to some environments

    # Set up the Selenium WebDriver with ChromeDriver
    driver = webdriver.Chrome(service=Service(ChromeDriverManager().install()), options=chrome_options)

    try:
        logging.info(f"Navigating to initial URL: {initial_url}")
        driver.get(initial_url)

        # Get the initial URL to compare with later
        initial_page_url = driver.current_url
        logging.info(f"Initial page URL: {initial_page_url}")

        # Wait for the consent page or for URL change
        try:
            WebDriverWait(driver, 60).until(
                lambda d: d.current_url != initial_page_url or
                          len(d.find_elements(By.XPATH, '//span[@jsname="V67aGc" and contains(text(), "Accept all")]')) > 0
            )
        except Exception as e:
            logging.error(f"Waiting for URL change or consent interaction failed: {e}")
            # Log the page source for debugging
            logging.debug(f"Page source when waiting failed:\n{driver.page_source}")
            return driver.current_url

        # Check the current URL after the wait
        current_url = driver.current_url
        logging.info(f"Current URL after waiting: {current_url}")

        # Handle consent pages or other redirects
        if "consent.google.com" in current_url:
            logging.info("Consent page detected, attempting to handle consent.")
            try:
                # Log the HTML of the consent page
                logging.debug(f"HTML of consent page:\n{driver.page_source}")

                # Try to find and click the consent button
                consent_buttons = driver.find_elements(By.XPATH, '//span[@jsname="V67aGc" and contains(text(), "Accept all")]')
                if consent_buttons:
                    logging.info("Clicking consent button.")
                    consent_buttons[0].click()
                    
                    # Wait for the page to redirect or load after clicking
                    WebDriverWait(driver, 60).until(
                        lambda d: d.current_url != current_url
                    )
                    
                    # Additional wait to ensure the page fully loads
                    time.sleep(10)  # Sleep for an additional 10 seconds
                    WebDriverWait(driver, 30).until(
                        EC.presence_of_element_located((By.XPATH, '//body'))
                    )
                    
                    # Update current_url after further potential redirections
                    current_url = driver.current_url
                    logging.info(f"URL after clicking consent and additional waits: {current_url}")

                    # Log the HTML after clicking the consent button
                    logging.debug(f"HTML after clicking consent button:\n{driver.page_source}")
                
                # Extract the real URL from the consent page
                parsed_url = urllib.parse.urlparse(current_url)
                logging.debug(f"Parsed URL: {parsed_url}")
                query_params = urllib.parse.parse_qs(parsed_url.query)
                real_url = query_params.get('continue', [None])[0]
                if real_url:
                    real_url = urllib.parse.unquote(real_url)
                    logging.info(f"Real URL extracted from consent page: {real_url}")
                    return real_url
                else:
                    logging.error("Real URL not found in 'continue' parameter.")
            except Exception as e:
                logging.error(f"Error handling consent page: {e}", exc_info=True)
        
        # Return the URL after potential redirection
        return current_url
    except Exception as e:
        logging.error(f"An error occurred: {e}", exc_info=True)
    finally:
        driver.quit()  # Ensure the browser is closed properly

if __name__ == "__main__":
    initial_url = 'https://news.google.com/read/CBMimAFBVV95cUxQUVJHeVdsU205cy1XblR4RFA5NHlYeGl4WFhvR2NqRWhPcjdwZDA0bkFVYjNyTTd3eG0tMC1rS010SE1sRlJsUnJ4QkE3R0R2NXRkMVJ2bDdDVi1Pejcyb2ZaT2JTblNtQ2dEVGlKWjhZektTdjYxT1ZoOWswVHJXbGJBVHdNOXpWdkpYOWo1RG9ITlNabHp1YQ?hl=en-US&gl=US&ceid=US%3Aen'
    final_url = get_final_url(initial_url)
    print('Final URL:', final_url)

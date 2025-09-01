import random
import subprocess
from pyvirtualdisplay import Display
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.support.ui import WebDriverWait
from urllib.parse import urljoin
from webdriver_manager.chrome import ChromeDriverManager

# Start virtual display
display = Display(visible=0, size=(1920, 1080), use_xauth=True)
display.start()
print(f"Virtual display started on :{display.display}")

# Start x11vnc in the background
vnc_command = [
    'x11vnc',
    '-display', f':{display.display}',
    '-nopw',
    '-forever',
    '-shared'
]
vnc_process = subprocess.Popen(vnc_command)
print("VNC server started.")

try:
    # Navigate to Google News with CRM search
    chrome_options = Options()
    # Do not add the headless argument
    # chrome_options.add_argument("--headless")
    chrome_options.add_argument("--no-sandbox")
    chrome_options.add_argument("--disable-dev-shm-usage")
    chrome_options.add_argument("--disable-gpu")
    chrome_options.add_argument(
        "user-agent=Mozilla/5.0 (Windows NT 10.0; Win64; x64)"
        " AppleWebKit/537.36 (KHTML, like Gecko)"
        " Chrome/114.0.5735.199 Safari/537.36"
    )

    driver = webdriver.Chrome(
        service=Service(ChromeDriverManager().install()), options=chrome_options
    )
    driver.get("https://news.google.com/search?q=crm&hl=es&gl=ES&ceid=ES%3Aes")

    wait = WebDriverWait(driver, 60)  # Increased timeout

    # Accept the cookie prompt if it appears
    try:
        consent_button = wait.until(
            EC.element_to_be_clickable((By.XPATH, "//span[text()='Aceptar todo']"))
        )
        consent_button.click()
        print("Cookie consent accepted.")
    except Exception as e:
        print(f"No consent prompt appeared: {e}")

    # Wait for the first article to load and collect article information
    try:
        print("Locating the first article...")
        first_article = wait.until(
            EC.presence_of_element_located((By.CSS_SELECTOR, "article"))
        )

        print("Locating article link...")
        link_element = first_article.find_element(By.CSS_SELECTOR, "a[href]")
        article_link = urljoin(driver.current_url, link_element.get_attribute("href"))

        print("Clicking on article link...")
        original_window = driver.current_window_handle
        link_element.click()

        print("Waiting for a new window to open...")
        wait.until(EC.number_of_windows_to_be(2))

        print("Switching to the new window...")
        for window_handle in driver.window_handles:
            if window_handle != original_window:
                driver.switch_to.window(window_handle)
                print(f"Switched to window: {window_handle}")
                break

        print("Waiting for the article page to load...")
        wait.until(EC.presence_of_element_located((By.TAG_NAME, "body")))
        print("Article page loaded successfully.")

        # Pause the script to allow manual CAPTCHA solving
        print("Please connect to the VNC server to solve the CAPTCHA.")
        input("After solving the CAPTCHA, press Enter here to continue...")

        # After solving the CAPTCHA, proceed to save the page source
        print("Saving page source...")
        try:
            with open(
                "/var/www/automation.datainnovation.io/html/article_page_source.html",
                "w",
                encoding="utf-8",
            ) as file:
                file.write(driver.page_source)
            print("Page source saved successfully.")
        except Exception as e:
            print(f"Failed to save page source: {e}")

    except Exception as e:
        print("Failed to retrieve article information:", str(e))

except Exception as e:
    print(f"An error occurred: {e}")

finally:
    driver.quit()
    vnc_process.terminate()
    display.stop()
    print("Virtual display and VNC server stopped.")

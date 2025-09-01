def get_real_url(short_url):
    import time
    from selenium import webdriver
    from selenium.webdriver.chrome.service import Service
    from selenium.webdriver.chrome.options import Options
    from selenium.webdriver.support.ui import WebDriverWait
    from selenium.webdriver.support import expected_conditions as EC
    from selenium.webdriver.common.by import By
    from webdriver_manager.chrome import ChromeDriverManager
    
    # Set up Chrome options for headless browsing
    chrome_options = Options()
    chrome_options.add_argument("--headless")
    chrome_options.add_argument("--no-sandbox")
    chrome_options.add_argument("--disable-dev-shm-usage")
    chrome_options.add_argument("--disable-gpu")
    
    # Initialize WebDriver
    driver = webdriver.Chrome(service=Service(ChromeDriverManager().install()), options=chrome_options)
    wait = WebDriverWait(driver, 20)
    
    try:
        # Attempt to accept cookie consent if it appears
        consent_button = wait.until(EC.element_to_be_clickable((By.XPATH, "//span[@class='VfPpkd-vQzf8d' and text()='Aceptar todo']")))
        consent_button.click()
        print("Cookie consent accepted.")
        
        # Save the page source at the consent page stage
        page_source_consent = driver.page_source
        with open("page_source_consent.html", "w", encoding="utf-8") as file:
            file.write(page_source_consent)
    except:
        print("No consent prompt appeared.")

    
    # Wait for the page to load
    time.sleep(3)

    try:
        # Open the shortened URL
        driver.get(short_url)
        
        # Save the page source after loading the main page
        page_source_main = driver.page_source
        with open("page_source_main.html", "w", encoding="utf-8") as file:
            file.write(page_source_main)
        
        # Wait for the URL to change if it redirects
        wait.until(EC.url_changes(short_url))
        
        # Get the final URL after redirection
        real_url = driver.current_url
    except Exception as e:
        print("Failed to retrieve real URL:", e)
        real_url = None
    finally:
        driver.quit()
    
    print(real_url)
    return real_url



get_real_url('https://news.google.com/read/CBMi8gFBVV95cUxNNUhHQ3EzOTFOMHc4TGVKUElBenN2QjlPZlhpNjgzc1NkSUpaMWthaDZjMWpNWVhXS3cwSlZKdWVFTldOR0xsOTRwZ3RfRnVCOF9TRmd2MHdpQWp4VHJQUHZTLXhSbjZmR2tTSDU0bERwVmZ3ZU1FSVFPT2Qyc0wwOUt0S3ZBM2dhMnhzdUJqZ1FOb2duLUQtSF9hbC02Z3FXMHNxMkxJRTdNXzlzclAyb2liN3lhcWFKUTRIcFFoOEczQ0M4ZWdCM2d0LWJkdVhQVVFLRXpGY01vdHp2TnEtWTVkRXRXNHhxTUlualZrbXBsQQ?hl=es&gl=ES&ceid=ES%3Aes')
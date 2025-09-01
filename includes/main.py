import base64
import re
import requests
from bs4 import BeautifulSoup
from datetime import datetime, timedelta   
from io import BytesIO
from datetime import datetime 
from urllib.parse import urljoin
from requests_oauthlib import OAuth2Session
from oauthlib.oauth2 import BackendApplicationClient
import random
import markdown
from PIL import Image
from jinja2 import Template
import pytz
import mysql.connector




from functions import  make_article_formatted_seo,find_good_title, make_it_good_article, make_it_good_article_with_per, process_article, scrape_google_news_article_new,find_image_with_title, search_google_news, upload_image_to_wordpress,post_to_wordpress




config = {
    'user': 'root',
    'password': '0955321170',
    'host': 'localhost',
    'database': 'automation'
}







conn = mysql.connector.connect(**config)
cursor = conn.cursor(dictionary=True)  # Using dictionary=True for better readability

# SQL query to select data
query = """
SELECT cs.id, cs.company_name, cs.profession, cs.countries_of_audience, cs.audience_language, cs.about,
       cs.logo, cs.author, cs.audience, cs.mechanics, cs.frequency, cs.channels,
       cs.objective, cs.content_keywords, cs.not_allowed_keywords, cs.content_with_images,
       cs.preferred_days, cs.preferred_time, cs.example_content, cs.created_at, cs.user_id,
       cc.id AS credential_id, cc.linkedin_token, cc.wp_url, cc.wp_user, cc.wp_pass
FROM content_setup cs
LEFT JOIN sending_log sl ON cs.id = sl.company AND sl.date = CURDATE()
LEFT JOIN company_credentials cc ON cs.id = cc.company_id
WHERE sl.id IS NULL AND cs.active = 1

"""




# Execute the query
cursor.execute(query)

# Fetch all results and assign to a variable
results = cursor.fetchall()

# Now `results` is a list of dictionaries, where each dictionary represents a row
# For example, you can access the first row like this:
# print(results[0])

# Close the connection

# Example usage:
for row in results:
    try:
        
        id =  row['id']
        company_name = row['company_name']
        about = row['about']
        
        profession = row['profession']
        countries_of_audience = row['countries_of_audience']
        audience_language = row['audience_language']
        logo = row['logo']
        author = row['author']
        audience = row['audience']
        mechanics = row['mechanics']
        frequency = row['frequency']
        channels = row['channels']
        objective = row['objective']
        content_keywords = row['content_keywords']
        not_allowed_keywords = row['not_allowed_keywords']
        content_with_images = row['content_with_images']
        preferred_days = row['preferred_days']
        preferred_time = row['preferred_time']
        example_content = row['example_content']
        created_at = row['created_at']
        user_id = row['user_id']
        wp_url = row['wp_url']
        wp_user = row['wp_user']
        wp_token = row['wp_pass']
        
        
        
        
        ## get the google url according to client
        ##get keywords 
        keywords = [keyword.strip() for keyword in content_keywords.split(',')]
        random_keyword = random.choice(keywords)
        ##get countries 
        countries_of_audience = [country.strip() for country in countries_of_audience.split(',')]
        username = wp_user
        passwrd = wp_token
        
        token = base64.b64encode(f'{username}:{passwrd}'.encode()).decode()
        auth_header = {'Authorization': f'Basic {token}'}
        today = datetime.now().date()

        
        
        # article_first_phase = generate_articles_chatgpt(article_text , title)
        # article_second_phase = expand_article(article_first_phase)
        # article_third_phase = expand_article2(article_second_phase)
        # new_title = rephrase_title_with_chatgpt(article_second_phase)
        # image = find_image_with_title(new_title)
        # image_id = upload_image_to_wordpress(image,auth_header)
        # post_to_wordpress(new_title,article_second_phase,image_id,auth_header,4)
        
        
        
        #article_phase_1_googlenews = scrape_google_news_article_new('MX',keywords)  
        
        title, news_url, image_url = search_google_news(random_keyword, countries_of_audience)
        
        print(random_keyword)
        print(countries_of_audience)

        print(title)
        print(news_url)


        #title = article_phase_1_googlenews['title']
        #url = article_phase_1_googlenews['url']
        #print(url)


        #article_phase_1 = make_it_good_article(title,news_url)    # make articale
        
        
        #article_phase_add_pers = make_it_good_article_with_per(article_phase_1,id)    # make articale


        #article_phase_2 = make_article_formatted_seo(article_phase_add_pers)
        
        #article_phase_3 = make_article_formatted_seo(article_phase_add_pers)
        
        article_phase_3 = process_article(news_url,id)

        # SEO + fromating
        find_good_title_formated  = find_good_title(title)       # SEO + fromating
        find_good_image = find_image_with_title(find_good_title_formated)
        image_id, image_url = upload_image_to_wordpress(find_good_image, auth_header, wp_url) 
        
        post_response = post_to_wordpress(find_good_title_formated, article_phase_3, image_id, auth_header, 4, wp_url)
        post_link = post_response.get('link')
        post_status = post_response.get('status')

        # Prepare data for database insertion
        insert_query = """
        INSERT INTO posts (title, body, image, link, date, company, status)
        VALUES (%s, %s, %s, %s, %s, %s, %s)
        """
        data_tuple = (find_good_title_formated, article_phase_3, image_url, post_link, today, id, post_status)

        # Execute the INSERT statement
        cursor.execute(insert_query, data_tuple)
        conn.commit()
    except Exception as e:
        # Log the error with company details
        # 
     print(f"Error processing company ID {id} ({company_name}): {e}")
        # Optionally, write the error to a log file or database
    continue  # Proceed to the next company    

# Close the cursor and connection
cursor.close()
conn.close()


        
 






    
    
    
    
    

    
    
    
    
    
    
    

    
    
    
    


































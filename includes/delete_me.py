
import mysql.connector


def get_db_connection():
    return mysql.connector.connect(
        host="localhost",
        user="root",
        password="0955321170",
        database="automation"
    )
 

def get_personality(text_to_replace_with, company_id):
    # Import necessary libraries
    import random

    # Get database connection
    connection = get_db_connection()
    cursor = connection.cursor()

    try:
        # Select a random row based on company ID
        query = f"""
            SELECT first, second, third, fourth, fifth
            FROM personalities
            WHERE company = %s
        """
        cursor.execute(query, (company_id,))
        rows = cursor.fetchall()

        if not rows:
            return None  # Return None if no rows are found

        # Choose a random column from the selected row
        random_row = random.choice(rows)
        random_column_value = random.choice(random_row)

        # Replace '1st step' if found in the selected column value
        if "'1st step'" in random_column_value:
            return random_column_value.replace("'1st step'", text_to_replace_with)

        return random_column_value  # Return the original value if no replacement is needed

    finally:
        cursor.close()
        connection.close()



result = get_personality(":(New Text)", 2)
print(result)  # This prints the returned valueprint(result)()
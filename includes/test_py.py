# script.py
import json

# Example function that performs some operation
def example_function():
    result = {"status": "success", "message": "Hello from Python!"}
    return json.dumps(result)  # Encode as JSON to easily handle in PHP

# Call the function and print the result
if __name__ == "__main__":
    print(example_function())  # Output is captured by PHP's shell_exec

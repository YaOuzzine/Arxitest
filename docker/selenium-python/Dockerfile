FROM arxitest/selenium-python

# Update ChromeDriver to match Chrome version
RUN apt-get update && apt-get install -y curl unzip \
    && rm -f /usr/local/bin/chromedriver \
    && CHROME_VERSION=$(google-chrome --version | grep -oE "[0-9]{1,3}\.[0-9]{1,3}") \
    && CHROMEDRIVER_VERSION=$(curl -sS "https://chromedriver.storage.googleapis.com/LATEST_RELEASE_$CHROME_VERSION") \
    && echo "Installing ChromeDriver version: $CHROMEDRIVER_VERSION" \
    && wget -q "https://chromedriver.storage.googleapis.com/${CHROMEDRIVER_VERSION}/chromedriver_linux64.zip" -O /tmp/chromedriver.zip \
    && unzip -o /tmp/chromedriver.zip -d /usr/local/bin/ \
    && rm /tmp/chromedriver.zip \
    && chmod +x /usr/local/bin/chromedriver

# Add additional Chrome flags to fix initialization errors
RUN echo "export CHROME_OPTS='--no-sandbox --headless --disable-gpu --disable-dev-shm-usage --disable-software-rasterizer --remote-debugging-port=9222'" >> /etc/environment

# Test the Chrome setup works
RUN python -c "from selenium import webdriver; from selenium.webdriver.chrome.options import Options; options = Options(); options.add_argument('--headless'); options.add_argument('--no-sandbox'); options.add_argument('--disable-gpu'); options.add_argument('--disable-dev-shm-usage'); driver = webdriver.Chrome(options=options); print('ChromeDriver works!'); driver.quit()"

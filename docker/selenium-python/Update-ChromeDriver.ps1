$tempDir = "$env:TEMP\chromedriver-update"
New-Item -Path $tempDir -ItemType Directory -Force

# Create the Dockerfile
@"
FROM arxitest/selenium-python

# Get Chrome version
RUN google-chrome --version | grep -oE "[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}" > /tmp/chrome_version.txt
RUN cat /tmp/chrome_version.txt | cut -d '.' -f 1 > /tmp/chrome_major.txt

# Remove old ChromeDriver
RUN rm -f /usr/local/bin/chromedriver

# Install matching ChromeDriver
RUN CHROME_MAJOR=`$(cat /tmp/chrome_major.txt) && \
    CHROMEDRIVER_VERSION=`$(curl -sS "https://chromedriver.storage.googleapis.com/LATEST_RELEASE_`$CHROME_MAJOR") && \
    echo "Installing ChromeDriver version: `$CHROMEDRIVER_VERSION" && \
    wget -q "https://chromedriver.storage.googleapis.com/`${CHROMEDRIVER_VERSION}/chromedriver_linux64.zip" -O /tmp/chromedriver.zip && \
    unzip /tmp/chromedriver.zip -d /usr/local/bin/ && \
    rm /tmp/chromedriver.zip && \
    chmod +x /usr/local/bin/chromedriver

# Test if ChromeDriver works
RUN python -c "import time; from selenium import webdriver; from selenium.webdriver.chrome.options import Options; options = Options(); options.add_argument('--headless'); options.add_argument('--no-sandbox'); options.add_argument('--disable-dev-shm-usage'); driver = webdriver.Chrome(options=options); print('ChromeDriver is working!'); driver.quit()"
"@ | Out-File -FilePath "$tempDir\Dockerfile" -Encoding ascii

# Build the updated image
Set-Location $tempDir
docker build -t arxitest/selenium-python:latest -f Dockerfile .

# Clean up
Set-Location $env:USERPROFILE
Remove-Item -Path $tempDir -Recurse -Force

Write-Host "Container updated successfully. Try running your tests now." -ForegroundColor Green
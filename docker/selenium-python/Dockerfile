# Use the official Python base image
FROM python:3.12-slim-bookworm

# Set up Chrome and system dependencies
RUN apt-get update -qqy \
    && apt-get install -qqy --no-install-recommends \
       wget \
       gnupg \
    && wget -q -O - https://dl.google.com/linux/linux_signing_key.pub | gpg --dearmor > /usr/share/keyrings/googlechrome-linux-keyring.gpg \
    && echo "deb [arch=amd64 signed-by=/usr/share/keyrings/googlechrome-linux-keyring.gpg] http://dl.google.com/linux/chrome/deb/ stable main" > /etc/apt/sources.list.d/google-chrome.list \
    && apt-get update -qqy \
    && apt-get install -qqy --no-install-recommends \
       google-chrome-stable \
       fonts-ipafont-gothic \
       fonts-freefont-ttf \
    && rm -rf /var/lib/apt/lists/* /var/cache/apt/*

# Create and update requirements.txt with pytest directly
RUN echo "selenium>=4.11\nwebdriver-manager>=4.0\npytest>=7.0\npytest-selenium>=4.0.0" > requirements.txt

# Install Python dependencies
RUN pip install --no-cache-dir -r requirements.txt

# Add pytest installation verification
RUN python -c "import pytest; import selenium; print(f'Pytest version: {pytest.__version__}'); print(f'Selenium version: {selenium.__version__}')"

# Set up work directory
WORKDIR /tests

# Default entrypoint that can be overridden
ENTRYPOINT ["python", "-m", "pytest"]

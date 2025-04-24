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

# Install Python dependencies
COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

# Copy test files and set working directory
COPY . /tests
WORKDIR /tests

# Set entrypoint with recommended Chrome flags
ENTRYPOINT ["python", "-u", "your_tests.py"]

FROM node:18-slim

# Install dependencies
RUN apt-get update && \
    apt-get install -y \
    libgtk2.0-0 \
    libgtk-3-0 \
    libgbm-dev \
    libnotify-dev \
    libgconf-2-4 \
    libnss3 \
    libxss1 \
    libasound2 \
    libxtst6 \
    xauth \
    xvfb \
    && rm -rf /var/lib/apt/lists/*

# Set working directory
WORKDIR /tests

# Install Cypress
RUN npm install cypress

# Add Cypress binary to path
ENV PATH="/node_modules/.bin:${PATH}"

# Set default command
CMD ["cypress", "run"]

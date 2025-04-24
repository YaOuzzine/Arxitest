Param(
  [string]$ImageName = 'arxitest/selenium-python',
  [string]$Tag       = 'latest'
)

# 1) get the full Chrome version from inside the container
$verOutput = docker run --rm "$ImageName:$Tag" google-chrome --version 2>$null
if ($verOutput -match '([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)') {
  $fullVersion = $Matches[1]
  Write-Host "Detected Chrome version inside container: $fullVersion"
} else {
  Write-Error "Could not detect Chrome version. Got: $verOutput"
  exit 1
}

# 2) build a Dockerfile that pulls that exact driver
$df = @"
FROM $ImageName:$Tag

RUN apt-get update \
 && apt-get install -y curl unzip \
 && rm -f /usr/local/bin/chromedriver \
 && echo 'Downloading matching ChromeDriver $fullVersion …' \
 && wget -q "https://chromedriver.storage.googleapis.com/$fullVersion/chromedriver_linux64.zip" -O /tmp/chromedriver.zip \
 && unzip -o /tmp/chromedriver.zip -d /usr/local/bin/ \
 && chmod +x /usr/local/bin/chromedriver \
 && rm /tmp/chromedriver.zip

# bake in headless flags
RUN echo "export CHROME_OPTS='--no-sandbox --headless --disable-gpu --disable-dev-shm-usage --remote-debugging-port=9222'" \
    >> /etc/environment
"@

$dfPath = Join-Path (Get-Location) 'update_chromedriver.dockerfile'
$df | Out-File -FilePath $dfPath -Encoding ascii

# 3) build & tag
Write-Host "Building $ImageName:$Tag with ChromeDriver $fullVersion" -ForegroundColor Cyan
docker build -t "$ImageName:$Tag" -f $dfPath .

# 4) cleanup
Remove-Item $dfPath
Write-Host "✅ Done! Rebuilt $ImageName:$Tag with matching driver." -ForegroundColor Green

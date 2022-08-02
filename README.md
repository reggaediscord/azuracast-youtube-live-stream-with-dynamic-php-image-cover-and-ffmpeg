# azuracast-youtube-live-stream-with-dynamic-php-image-cover-and-ffmpeg
Azuracast Youtube Live stream with dynamic PHP image cover and ffmpeg example

**Instalation**

```
sudo apt-get update
sudo apt-get install php php-gd php-curl ffmpeg

sudo chmod +x cover.sh
sudo nano /etc/systemd/system/cover.service

[Unit]
Description=cover
After=network.target
[Service]
WorkingDirectory=/home/radio
User=radio
Group=radio
Type=simple
ExecStart=/home/radio/cover.sh
RestartSec=1
Restart=always
[Install]
WantedBy=multi-user.target

sudo chmod +x youtube.sh
sudo nano /etc/systemd/system/radio.service

[Unit]
Description=radio
After=network.target
[Service]
WorkingDirectory=/home/radio
User=radio
Group=radio
Type=simple
ExecStart=/home/radio/youtube.sh
RestartSec=1
Restart=always
[Install]
WantedBy=multi-user.target
```

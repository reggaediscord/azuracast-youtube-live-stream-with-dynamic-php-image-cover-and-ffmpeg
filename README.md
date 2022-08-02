# azuracast-youtube-live-stream-with-php-cover
Azuracast Youtube Live stream with PHP cover example


sudo nano /etc/systemd/system/cover.service

[Unit]
Description=cover
After=network.target
[Service]
WorkingDirectory=/home/radio/
User=root
Group=root
Type=simple
ExecStart=/home/radio/cover.sh
RestartSec=1
Restart=always
[Install]
WantedBy=multi-user.target

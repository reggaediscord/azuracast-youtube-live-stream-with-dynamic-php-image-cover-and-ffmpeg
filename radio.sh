#!/bin/sh

YOUTUBE_URL="rtmp://a.rtmp.youtube.com/live2"
KEY="XXXX-XXXX-XXXX-XXXX-XXXX"

VIDEO_SOURCE="/home/radio/bg.gif"
AUDIO_SOURCE="http://XXX.XXX.XXX.XXX:8000/radio.mp3"

nice -n 19 ffmpeg \
    -re -stream_loop -1 -i "$VIDEO_SOURCE" -pattern_type glob -stream_loop -1 -framerate 1 -re -i '/home/radio/cove*.png' -filter_complex '[0:v][1:v] overlay' \
    -thread_queue_size 512 -i "$AUDIO_SOURCE" \
    -map 0:v:0 -map 1:a:0 \
    -map_metadata:g 1:g \
    -vcodec libx264 -pix_fmt yuv420p -preset ultrafast -r 24 -g 48 -b:v 1500k  \
    -acodec copy \
    -threads 1 \
    -f flv "$YOUTUBE_URL/$KEY" 2> /home/radio/ffmpeg.log

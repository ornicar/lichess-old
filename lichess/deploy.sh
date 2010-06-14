#!/bin/sh
# Usage: ./deploy.sh username@server
rsync --archive --force --delete --progress --compress --checksum --exclude-from=lichess/config/rsync_exclude.txt -e "ssh -i /home/thib/.ssh/lichess.pem" ./ $1

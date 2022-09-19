#!/bin/bash
SCRIPT_PATH="$(cd "$(dirname "$0")" && pwd)"
dest=/etc/apache2/sites-enabled

sudo rm "$dest"/"$1" 
sudo service apache2 reload
if [ -e "$dest" ]; then
echo "true"
else
echo "error"
exit
fi

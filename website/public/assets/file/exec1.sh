#!/bin/bash
SCRIPT_PATH="$(cd "$(dirname "$0")" && pwd)"
dest=/etc/apache2/sites-enabled

sudo cp "$SCRIPT_PATH"/"$1" $dest
chmod 777 $dest
sudo service apache2 reload
if [ -e "$dest" ]; then
echo "true"
else
echo "error"
exit
fi

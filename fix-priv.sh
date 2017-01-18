find ./ -type d -exec chmod 775 {} +
find ./ -type f -exec chmod 664 {} +
chmod -R 775 ./storage
chmod 660 .env

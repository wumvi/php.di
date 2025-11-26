```bash
docker run -ti --rm -v "$(pwd)":/data/ --workdir /data/ --network host php:8.4.15-cli-alpine3.22 sh

 curl -sS https://getcomposer.org/installer -o /tmp/composer-setup.php
 php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer
```